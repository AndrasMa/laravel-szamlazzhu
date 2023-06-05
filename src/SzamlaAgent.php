<?php

namespace Omisai\Szamlazzhu;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Omisai\Szamlazzhu\Document\DeliveryNote;
use Omisai\Szamlazzhu\Document\Document;
use Omisai\Szamlazzhu\Document\Invoice\CorrectiveInvoice;
use Omisai\Szamlazzhu\Document\Invoice\FinalInvoice;
use Omisai\Szamlazzhu\Document\Invoice\Invoice;
use Omisai\Szamlazzhu\Document\Invoice\PrePaymentInvoice;
use Omisai\Szamlazzhu\Document\Invoice\ReverseInvoice;
use Omisai\Szamlazzhu\Document\Proforma;
use Omisai\Szamlazzhu\Document\Receipt\Receipt;
use Omisai\Szamlazzhu\Document\Receipt\ReverseReceipt;
use Omisai\Szamlazzhu\Header\DocumentHeader;
use Omisai\Szamlazzhu\Response\SzamlaAgentResponse;

/**
 * Initialises the "Számla Agent" and handles the sending and receiving of data
 */
class SzamlaAgent
{
    public const API_VERSION = '0.9.0';

    public const API_URL = 'https://www.szamlazz.hu/szamla/';

    public const MINIMUM_PHP_VERSION = '8.1';

    public const CHARSET = 'utf-8';

    public const CERTIFICATION_FILENAME = 'cacert.pem';

    public const PDF_FILE_SAVE_PATH = 'pdf';

    public const XML_FILE_SAVE_PATH = 'xmls';

    private SzamlaAgentSetting $setting;

    private SzamlaAgentRequest $request;

    private  int $requestTimeout = SzamlaAgentRequest::REQUEST_TIMEOUT;

    private SzamlaAgentResponse $response;

    /**
     * @var SzamlaAgent[]
     */
    protected static array $agents = [];

    protected array $customHTTPHeaders = [];

    protected string $apiUrl = self::API_URL;

    protected bool $xmlFileSave = false;

    protected bool $requestXmlFileSave = false;

    protected bool $responseXmlFileSave = false;

    protected bool $pdfFileSave = true;

    protected array $environment = [];

    private CookieHandler $cookieHandler;

    protected function __construct(?string $username, ?string $password, ?string $apiKey, bool $downloadPdf, int $responseType = SzamlaAgentResponse::RESULT_AS_TEXT, string $aggregator = '')
    {
        $this->setSetting(new SzamlaAgentSetting($username, $password, $apiKey, $downloadPdf, SzamlaAgentSetting::DOWNLOAD_COPIES_COUNT, $responseType, $aggregator));
        $this->setCookieHandler(new CookieHandler($this));
        Log::channel('szamlazzhu')->debug(sprintf('Számla Agent inicializálása kész ($username: %s, apiKey: %s)', $username, $apiKey));

        $this->setPdfFileSave($downloadPdf);
        $this->setXmlFileSave(config('szamlazzhu.xml.file_save', false));
        $this->setRequestXmlFileSave(config('szamlazzhu.xml.request_file_save', false));
        $this->setResponseXmlFileSave(config('szamlazzhu.xml.response_file_save', false));
    }

    /**
     * @deprecated Not recommended the username/password authetnication mode
     * use instead the SzamlaAgent::createWithAPIkey($apiKey)
     */
    public static function createWithUsername(string $username, string $password, bool $downloadPdf = true)
    {
        $index = self::getHash($username);

        $agent = null;
        if (isset(self::$agents[$index])) {
            $agent = self::$agents[$index];
        }

        if ($agent === null) {
            return self::$agents[$index] = new self($username, $password, null, $downloadPdf);
        } else {
            return $agent;
        }
    }

    /**
     * API key is the recommended authentication mode
     */
    public static function createWithAPIkey(string $apiKey, bool $downloadPdf = true, int $responseType = SzamlaAgentResponse::RESULT_AS_TEXT, string $aggregator = '')
    {
        $index = self::getHash($apiKey);

        $agent = null;
        if (isset(self::$agents[$index])) {
            $agent = self::$agents[$index];
        }

        if ($agent === null) {
            return self::$agents[$index] = new self(null, null, $apiKey, $downloadPdf, $responseType, $aggregator);
        } else {
            return $agent;
        }
    }

    /**
     * @param  string  $instanceId : email, username or api key
     * @throws SzamlaAgentException
     */
    public static function get($instanceId): SzamlaAgent
    {
        $index = self::getHash($instanceId);
        $agent = self::$agents[$index];

        if ($agent === null) {
            if (strpos($instanceId, '@') === false && strlen($instanceId) == SzamlaAgentSetting::API_KEY_LENGTH) {
                throw new SzamlaAgentException(SzamlaAgentException::NO_AGENT_INSTANCE_WITH_APIKEY);
            } else {
                throw new SzamlaAgentException(SzamlaAgentException::NO_AGENT_INSTANCE_WITH_USERNAME);
            }
        }

        return $agent;
    }

    protected static function getHash($username): string
    {
        return hash('sha1', $username);
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    private function sendRequest(SzamlaAgentRequest $request): SzamlaAgentResponse
    {
        try {
            $this->setRequest($request);
            $response = new SzamlaAgentResponse($this, $request->send());

            return $response->handleResponse();
        } catch (SzamlaAgentException $sze) {
            throw $sze;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * HU: Bizonylat elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateDocument(string $type, Document $document): SzamlaAgentResponse
    {
        $request = new SzamlaAgentRequest($this, $type, $document);

        return $this->sendRequest($request);
    }

    /**
     * HU: Számla elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateInvoice(Invoice $invoice): SzamlaAgentResponse
    {
        return $this->generateDocument('generateInvoice', $invoice);
    }

    /**
     * HU: Előlegszámla elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generatePrePaymentInvoice(PrePaymentInvoice $invoice): SzamlaAgentResponse
    {
        return $this->generateInvoice($invoice);
    }

    /**
     * HU: Végszámla elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateFinalInvoice(FinalInvoice $invoice): SzamlaAgentResponse
    {
        return $this->generateInvoice($invoice);
    }

    /**
     * HU: Helyesbítő számla elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateCorrectiveInvoice(CorrectiveInvoice $invoice): SzamlaAgentResponse
    {
        return $this->generateInvoice($invoice);
    }

    /**
     * HU: Nyugta elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateReceipt(Receipt $receipt): SzamlaAgentResponse
    {
        return $this->generateDocument('generateReceipt', $receipt);
    }

    /**
     * HU: Számla jóváírás rögzítése
     *
     * @throws SzamlaAgentException
     */
    public function payInvoice(Invoice $invoice): SzamlaAgentResponse
    {
        if ($this->getResponseType() != SzamlaAgentResponse::RESULT_AS_TEXT) {
            $message = 'Helytelen beállítási kísérlet a számla kifizetettségi adatok elküldésénél: a kérésre adott válaszverziónak TEXT formátumúnak kell lennie!';
            Log::channel('szamlazzhu')->warning($message);
        }
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_TEXT);

        return $this->generateDocument('payInvoice', $invoice);
    }

    /**
     * HU: Nyugta elküldése
     *
     * @throws SzamlaAgentException
     */
    public function sendReceipt(Receipt $receipt): SzamlaAgentResponse
    {
        return $this->generateDocument('sendReceipt', $receipt);
    }

    /**
     * @throws SzamlaAgentException
     */
    public function getInvoiceData(string $data, int $type = Invoice::FROM_INVOICE_NUMBER, $downloadPdf = false): SzamlaAgentResponse
    {
        $invoice = new Invoice();

        if ($type == Invoice::FROM_INVOICE_NUMBER) {
            $invoice->getHeader()->setInvoiceNumber($data);
        } else {
            $invoice->getHeader()->setOrderNumber($data);
        }

        if ($this->getResponseType() !== SzamlaAgentResponse::RESULT_AS_XML) {
            $message = 'Helytelen beállítási kísérlet a számla adatok lekérdezésénél: Számla adatok letöltéséhez a kérésre adott válasznak xml formátumúnak kell lennie!';
            Log::channel('szamlazzhu')->warning($message);
        }

        $this->setDownloadPdf($downloadPdf);
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);

        return $this->generateDocument('requestInvoiceData', $invoice);
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getInvoicePdf(string $data, int $type = Invoice::FROM_INVOICE_NUMBER): SzamlaAgentResponse
    {
        $invoice = new Invoice();

        if ($type == Invoice::FROM_INVOICE_NUMBER) {
            $invoice->getHeader()->setInvoiceNumber($data);
        } elseif ($type == Invoice::FROM_INVOICE_EXTERNAL_ID) {
            if (SzamlaAgentUtil::isBlank($data)) {
                throw new SzamlaAgentException(SzamlaAgentException::INVOICE_EXTERNAL_ID_IS_EMPTY);
            }
            $this->getSetting()->setInvoiceExternalId($data);
        } else {
            $invoice->getHeader()->setOrderNumber($data);
        }

        if (! $this->isDownloadPdf()) {
            $message = 'Helytelen beállítási kísérlet a számla PDF lekérdezésénél: Számla letöltéshez a "downloadPdf" paraméternek "true"-nak kell lennie!';
            Log::channel('szamlazzhu')->warning($message);
        }
        $this->setDownloadPdf(true);

        return $this->generateDocument('requestInvoicePDF', $invoice);
    }

    /**
     * @return bool
     */
    public function isExistsInvoiceByExternalId(string|int $invoiceExternalId): bool
    {
        try {
            $result = $this->getInvoicePdf($invoiceExternalId, Invoice::FROM_INVOICE_EXTERNAL_ID);
            if ($result->isSuccess() && SzamlaAgentUtil::isNotBlank($result->getDocumentNumber())) {
                return true;
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getReceiptData(string $receiptNumber): SzamlaAgentResponse
    {
        return $this->generateDocument('requestReceiptData', new Receipt($receiptNumber));
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getReceiptPdf(string $receiptNumber): SzamlaAgentResponse
    {
        return $this->generateDocument('requestReceiptPDF', new Receipt($receiptNumber));
    }

    /**
     * HU: A választ a NAV Online Számla XML formátumában kapjuk vissza
     * EN: The response will be returned in the XML format of NAV Online Számla
     *
     * @throws SzamlaAgentException
     */
    public function getTaxPayer(string $taxPayerId): SzamlaAgentResponse
    {
        $request = new SzamlaAgentRequest($this, 'getTaxPayer', new TaxPayer($taxPayerId));
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_TAXPAYER_XML);

        return $this->sendRequest($request);
    }

    /**
     * HU: Sztornó számla elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateReverseInvoice(ReverseInvoice $invoice): SzamlaAgentResponse
    {
        return $this->generateDocument('generateReverseInvoice', $invoice);
    }

    /**
     * HU: Sztornó nyugta elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateReverseReceipt(ReverseReceipt $receipt): SzamlaAgentResponse
    {
        return $this->generateDocument('generateReverseReceipt', $receipt);
    }

    /**
     * HU: Díjbekérő elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateProforma(Proforma $proforma): SzamlaAgentResponse
    {
        return $this->generateDocument('generateProforma', $proforma);
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getDeleteProforma(string $data, int $type = Proforma::FROM_INVOICE_NUMBER): SzamlaAgentResponse
    {
        $proforma = new Proforma();

        if ($type == Proforma::FROM_INVOICE_NUMBER) {
            $proforma->getHeader()->setInvoiceNumber($data);
        } else {
            $proforma->getHeader()->setOrderNumber($data);
        }

        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);
        $this->setDownloadPdf(false);

        return $this->generateDocument('deleteProforma', $proforma);
    }

    /**
     * HU: Szállítólevél elkészítése
     *
     * @throws SzamlaAgentException
     */
    public function generateDeliveryNote(DeliveryNote $deliveryNote): SzamlaAgentResponse
    {
        return $this->generateDocument('generateDeliveryNote', $deliveryNote);
    }

    public function getApiVersion(): string
    {
        return self::API_VERSION;
    }

    public function getCertificationFile(): ?string
    {
        return Storage::disk('payment')->get(self::CERTIFICATION_FILENAME);
    }

    public function getCookieFilePath(): string
    {
        return $this->cookieHandler->getCookieFilePath();
    }

    public function getSetting(): SzamlaAgentSetting
    {
        return $this->setting;
    }

    public function setSetting(SzamlaAgentSetting $setting): void
    {
        $this->setting = $setting;
    }

    /**
     * @return SzamlaAgent[]
     */
    public static function getAgents(): array
    {
        return self::$agents;
    }

    public function getUsername(): ?string
    {
        return $this->getSetting()->getUsername();
    }

    /**
     * The username is the email address or a specificied username
     * used on the https://www.szamlazz.hu/szamla/login website.
     */
    public function setUsername(?string $username): void
    {
        $this->getSetting()->setUsername($username);
    }

    public function getPassword(): ?string
    {
        return $this->getSetting()->getPassword();
    }

    /**
     * The password is used on the https://www.szamlazz.hu/szamla/login website.
     */
    public function setPassword(?string $password): void
    {
        $this->getSetting()->setPassword($password);
    }

    public function getApiKey(): ?string
    {
        return $this->getSetting()->getApiKey();
    }

    /**
     * @link Docs: https://www.szamlazz.hu/blog/2019/07/szamla_agent_kulcsok/
     */
    public function setApiKey(?string $apiKey): void
    {
        $this->getSetting()->setApiKey($apiKey);
    }

    public function getApiUrl(): string
    {
        if (SzamlaAgentUtil::isNotBlank($this->getEnvironmentUrl())) {
            $this->setApiUrl($this->getEnvironmentUrl());
        } elseif (SzamlaAgentUtil::isBlank($this->apiUrl)) {
            $this->setApiUrl(self::API_URL);
        }

        return $this->apiUrl;
    }

    public function setApiUrl(string $apiUrl): void
    {
        $this->apiUrl = $apiUrl;
    }

    public function isDownloadPdf(): bool
    {
        return $this->getSetting()->isDownloadPdf();
    }

    public function setDownloadPdf(bool $downloadPdf): void
    {
        $this->getSetting()->setDownloadPdf($downloadPdf);
    }

    public function getDownloadCopiesCount(): int
    {
        return $this->getSetting()->getDownloadCopiesCount();
    }

    /**
     * HU: Amennyiben az Agenttel papír alapú számlát készítesz és kéred a számlaletöltést ($downloadPdf = true),
     * akkor opcionálisan megadható, hogy nem csak a számla eredeti példányát kéred, hanem a másolatot is egyetlen pdf-ben.
     *
     * EN: If you use Agent to create a paper invoice and request an invoice download ($downloadPdf = true),
     * you can optionally specify that you request not only the original invoice, but also a copy in a single pdf file.
     */
    public function setDownloadCopiesCount(int $downloadCopiesCount): void
    {
        $this->getSetting()->setDownloadCopiesCount($downloadCopiesCount);
    }

    public function getResponseType(): int
    {
        return $this->getSetting()->getResponseType();
    }

    /**
     * HU:
     * 1: RESULT_AS_TEXT - egyszerű szöveges válaszüzenetet vagy pdf-et ad vissza.
     * 2: RESULT_AS_XML  - xml válasz, ha kérted a pdf-et az base64 kódolással benne van az xml-ben.
     * EN:
     * 1: RESULT_AS_TEXT - return a plain text response message or pdf.
     * 2: RESULT_AS_XML  - xml response, if you requested the pdf, then it is included in the xml with base64 encoding.
     */
    public function setResponseType(int $responseType): void
    {
        $this->getSetting()->setResponseType($responseType);
    }

    public function getAggregator(): string
    {
        return $this->getSetting()->getAggregator();
    }

    /**
     * @example WooCommerce, OpenCart, PrestaShop, Shoprenter, Superwebáruház, Drupal invoice Agent, etc.
     */
    public function setAggregator(string $aggregator): void
    {
        $this->getSetting()->setAggregator($aggregator);
    }

    public function getGuardian(): bool
    {
        return $this->getSetting()->getGuardian();
    }

    public function setGuardian(bool $guardian): void
    {
        $this->getSetting()->setGuardian($guardian);
    }

    public function getInvoiceExternalId(): string
    {
        return $this->getSetting()->getInvoiceExternalId();
    }

    /**
     * HU: A számlát a külső rendszer (Számla Agentet használó rendszer) ezzel az adattal azonosítja.
     * (a számla adatai később ezzel az adattal is lekérdezhetők lesznek)
     *
     * EN: The external system (the system using the Számla Agent)
     * identifies the invoice with this data.
     * (the invoice data will also be retrieved later with this data)
     */
    public function setInvoiceExternalId(string $invoiceExternalId): void
    {
        $this->getSetting()->setInvoiceExternalId($invoiceExternalId);
    }

    public function getRequest(): SzamlaAgentRequest
    {
        return $this->request;
    }

    public function setRequest(SzamlaAgentRequest $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): SzamlaAgentResponse
    {
        return $this->response;
    }

    public function setResponse(SzamlaAgentResponse $response): void
    {
        $this->response = $response;
    }


    public function getCustomHTTPHeaders(): array
    {
        return $this->customHTTPHeaders;
    }

    public function addCustomHTTPHeader(string $key, string $value): void
    {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            $this->customHTTPHeaders[$key] = $value;
        } else {
            Log::channel('szamlazzhu')->warning('Egyedi HTTP fejléchez megadott kulcs nem lehet üres');
        }
    }

    public function removeCustomHTTPHeader(string $key)
    {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            unset($this->customHTTPHeaders[$key]);
        }
    }

    public function isPdfFileSave(): bool
    {
        return $this->pdfFileSave;
    }

    public function setPdfFileSave(bool $pdfFileSave): void
    {
        $this->pdfFileSave = $pdfFileSave;
    }

    public function isXmlFileSave(): bool
    {
        return $this->xmlFileSave;
    }

    public function isNotXmlFileSave(): bool
    {
        return ! $this->isXmlFileSave();
    }

    public function setXmlFileSave(bool $xmlFileSave): void
    {
        $this->xmlFileSave = $xmlFileSave;
    }

    public function isRequestXmlFileSave(): bool
    {
        return $this->requestXmlFileSave;
    }

    public function isNotRequestXmlFileSave(): bool
    {
        return ! $this->isRequestXmlFileSave();
    }

    public function setRequestXmlFileSave(bool $requestXmlFileSave): void
    {
        $this->requestXmlFileSave = $requestXmlFileSave;
    }

    public function isResponseXmlFileSave(): bool
    {
        return $this->responseXmlFileSave;
    }

    public function setResponseXmlFileSave(bool $responseXmlFileSave): void
    {
        $this->responseXmlFileSave = $responseXmlFileSave;
    }

    /**
     * @return Document|object
     */
    public function getRequestEntity()
    {
        return $this->getRequest()->getEntity();
    }

    /**
     * @return DocumentHeader|null
     */
    public function getRequestEntityHeader()
    {
        $header = null;

        $request = $this->getRequest();
        $entity = $request->getEntity();

        if ($entity != null && $entity instanceof Invoice) {
            $header = $entity->getHeader();
        }

        return $header;
    }

    public function getRequestTimeout(): int
    {
        return $this->requestTimeout;
    }

    public function setRequestTimeout(int $timeout): void
    {
        $this->requestTimeout = $timeout;
    }

    public function isInvoiceItemIdentifier(): bool
    {
        return $this->getSetting()->isInvoiceItemIdentifier();
    }

    public function setInvoiceItemIdentifier(bool $invoiceItemIdentifier): void
    {
        $this->getSetting()->setInvoiceItemIdentifier($invoiceItemIdentifier);
    }

    public function getEnvironment(): array
    {
        return $this->environment;
    }

    public function hasEnvironment(): bool
    {
        return $this->environment != null && is_array($this->environment) && ! empty($this->environment);
    }

    public function getEnvironmentName(): ?string
    {
        return $this->hasEnvironment() && array_key_exists('name', $this->environment) ? $this->environment['name'] : null;
    }

    public function getEnvironmentUrl(): ?string
    {
        return $this->hasEnvironment() && array_key_exists('url', $this->environment) ? $this->environment['url'] : null;
    }

    public function setEnvironment(string $name, string $url, array $authorization = []): void
    {
        $this->environment = [
            'name' => $name,
            'url' => $url,
            'auth' => $authorization,
        ];
    }

    public function hasEnvironmentAuth(): bool
    {
        return $this->hasEnvironment() && array_key_exists('auth', $this->environment) && is_array($this->environment['auth']);
    }

    public function getEnvironmentAuthType(): int
    {
        return $this->hasEnvironmentAuth() && array_key_exists('type', $this->environment['auth']) ? $this->environment['auth']['type'] : 0;
    }

    public function getEnvironmentAuthUser(): string
    {
        return $this->hasEnvironmentAuth() && array_key_exists('user', $this->environment['auth']) ? $this->environment['auth']['user'] : null;
    }

    public function getEnvironmentAuthPassword(): string
    {
        return $this->hasEnvironmentAuth() && array_key_exists('password', $this->environment['auth']) ? $this->environment['auth']['password'] : null;
    }

    public function getCookieHandleMode(): int
    {
        return $this->cookieHandler->getCookieHandleMode();
    }

    /**
     * HU:
     * Sütikezelési mód beállítása
     *
     * 1. Alapértelmezett mód esetén a főkönyvtárban lesznek tárolva a sütik (CookieHandler::COOKIE_HANDLE_MODE_DEFAULT)
     * 2. JSON mód használata esetén a cookie mappában lesznek tárolva a sütik (CookieHandler::COOKIE_HANDLE_MODE_JSON)
     * 3. Adatbázis mód használata esetén a tárolást magadnak kell megvalósítanod (CookieHandler::COOKIE_HANDLE_MODE_DATABASE)
     *
     * Fontos! Több számlázási fiókba való számlázás esetén erősen ajánlott az adatbázis mód használata!
     * Párhuzamos futtatás esetén (pl. cronjob) a JSON módot ne használd - használd helyette az adatbázis módot!
     *
     * EN:
     * TODO: making translation
     */
    public function setCookieHandleMode(int $cookieHandleMode): void
    {
        $this->cookieHandler->setCookieHandleMode($cookieHandleMode);
    }

    public function getCookieSessionId(): string
    {
        return $this->cookieHandler->getCookieSessionId();
    }

    public function setCookieSessionId(string $cookieSessionId): void
    {
        $this->cookieHandler->setCookieSessionId($cookieSessionId);
    }

    public function getCookieHandler(): CookieHandler
    {
        return $this->cookieHandler;
    }

    protected function setCookieHandler(CookieHandler $cookieHandler): void
    {
        $this->cookieHandler = $cookieHandler;
    }
}
