<?php

namespace Omisai\SzamlazzhuAgent;

use Omisai\SzamlazzhuAgent\Document\Document;
use Omisai\SzamlazzhuAgent\Document\Invoice\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SzamlaAgentRequest
{
    public const HTTP_OK = 200;

    public const CRLF = "\r\n";

    public const XML_BASE_URL = 'http://www.szamlazz.hu/';

    public const REQUEST_TIMEOUT = 30;

    /**
     * HU: Számlakészítéshez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agent/xmlszamla.xsd
     */
    public const XML_SCHEMA_CREATE_INVOICE = 'xmlszamla';

    /**
     * HU: Számla sztornózásához használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentst/xmlszamlast.xsd
     */
    public const XML_SCHEMA_CREATE_REVERSE_INVOICE = 'xmlszamlast';

    /**
     * HU: Jóváírás rögzítéséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentkifiz/xmlszamlakifiz.xsd
     */
    public const XML_SCHEMA_PAY_INVOICE = 'xmlszamlakifiz';

    /**
     * HU: Számla adatok lekéréséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentxml/xmlszamlaxml.xsd
     */
    public const XML_SCHEMA_REQUEST_INVOICE_XML = 'xmlszamlaxml';

    /**
     * HU: Számla PDF lekéréséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentpdf/xmlszamlapdf.xsd
     */
    public const XML_SCHEMA_REQUEST_INVOICE_PDF = 'xmlszamlapdf';

    /**
     * HU: Nyugta készítéséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtacreate/xmlnyugtacreate.xsd
     */
    public const XML_SCHEMA_CREATE_RECEIPT = 'xmlnyugtacreate';

    /**
     * HU: Nyugta sztornóhoz használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtast/xmlnyugtast.xsd
     */
    public const XML_SCHEMA_CREATE_REVERSE_RECEIPT = 'xmlnyugtast';

    /**
     * HU: Nyugta kiküldéséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtasend/xmlnyugtasend.xsd
     */
    public const XML_SCHEMA_SEND_RECEIPT = 'xmlnyugtasend';

    /**
     * HU: Nyugta megjelenítéséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtaget/xmlnyugtaget.xsd
     */
    public const XML_SCHEMA_GET_RECEIPT = 'xmlnyugtaget';

    /**
     * HU: Adózó adatainak lekérdezéséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/taxpayer/xmltaxpayer.xsd
     */
    public const XML_SCHEMA_TAXPAYER = 'xmltaxpayer';

    /**
     * HU: Díjbekérő törléséhez használt XML séma
     *
     * @see https://www.szamlazz.hu/szamla/docs/xsds/dijbekerodel/xmlszamladbkdel.xsd
     */
    public const XML_SCHEMA_DELETE_PROFORMA = 'xmlszamladbkdel';

    public const REQUEST_AUTHORIZATION_BASIC_AUTH = 1;

    private SzamlaAgent $agent;

    private string $type;

    private object $entity;

    private string $xmlData;

    private string $xmlName;

    private string $xmlFilePath;

    private string $xmlDirectory;

    private string $fileName;

    private string $delim;

    private string $postFields;

    private bool $cData = true;

    private int $requestTimeout;

    public function __construct(SzamlaAgent $agent, string $type, object $entity)
    {
        $this->setAgent($agent);
        $this->setType($type);
        $this->setEntity($entity);
        $this->setCData(true);
        $this->setRequestTimeout($agent->getRequestTimeout());
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    private function buildXmlData(): void
    {
        $this->setXmlFileData($this->getType());
        $agent = $this->getAgent();
        Log::channel('szamlazzhu')->debug('Started to build the XML data.');
        $xmlData = $this->getEntity()->buildXmlData($this);

        $xml = new SimpleXMLExtended($this->getXmlBase());
        $this->arrayToXML($xmlData, $xml);
        try {
            $result = SzamlaAgentUtil::checkValidXml($xml->saveXML());
            if (! empty($result)) {
                throw new SzamlaAgentException(SzamlaAgentException::XML_NOT_VALID." a {$result[0]->line}. sorban: {$result[0]->message}. ");
            }
            $formatXml = SzamlaAgentUtil::formatXml($xml);
            $this->setXmlData($formatXml->saveXML());
            // Ha nincs hiba az XML-ben, elmentjük
            Log::channel('szamlazzhu')->debug('XML adatok létrehozása kész.');
            if (($agent->isXmlFileSave() || $agent->isRequestXmlFileSave())) {
                $this->createXmlFile($formatXml);
            }
        } catch (\Exception $e) {
            try {
                $formatXml = SzamlaAgentUtil::formatXml($xml);
                $this->setXmlData($formatXml->saveXML());
                if (! empty($this->getXmlData())) {
                    $xmlData = $this->getXmlData();
                }
            } catch (\Exception $ex) {
                // ha az adatok alapján nem állítható össze az XML, továbblépünk és naplózzuk az eredetileg beállított XML adatokat
            }
            Log::channel('szamlazzhu')->debug('XML', ['data' => print_r($xmlData, true)]);
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_BUILD_FAILED.":  {$e->getMessage()} ");
        }
    }

    private function arrayToXML(array $xmlData, SimpleXMLExtended &$xmlFields): void
    {
        foreach ($xmlData as $key => $value) {
            if (is_array($value)) {
                $fieldKey = $key;
                if (strpos($key, 'item') !== false) {
                    $fieldKey = 'tetel';
                }
                if (strpos($key, 'note') !== false) {
                    $fieldKey = 'kifizetes';
                }
                $subNode = $xmlFields->addChild("$fieldKey");
                $this->arrayToXML($value, $subNode);
            } else {
                if (is_bool($value)) {
                    $value = ($value) ? 'true' : 'false';
                } elseif (! $this->isCData()) {
                    $value = htmlspecialchars("$value");
                }

                if ($this->isCData()) {
                    $xmlFields->addChildWithCData("$key", $value);
                } else {
                    $xmlFields->addChild("$key", $value);
                }
            }
        }
    }

    /**
     * @throws SzamlaAgentException
     * @throws \ReflectionException
     */
    private function createXmlFile(\DOMDocument $xml): void
    {
        $filename = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName(), $this->getEntity());
        $realPath = sprintf('%s/%s/%s', SzamlaAgent::XML_FILE_SAVE_PATH, $this->getXmlDirectory(), $filename);
        Storage::disk('payment')->put($realPath, $xml->saveXML());
        Log::channel('szamlazzhu')->debug('XML file saved', ['path' => $realPath]);
        $this->setXmlFilePath($realPath);
    }

    private function getXmlBase(): string
    {
        $xmlName = $this->getXmlName();

        $queryData = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $queryData .= '<'.$xmlName.' xmlns="'.$this->getXmlNamespace($xmlName).'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="'.$this->getSchemaLocation($xmlName).'">'.PHP_EOL;
        $queryData .= '</'.$xmlName.'>'.self::CRLF;

        return $queryData;
    }

    private function getSchemaLocation($xmlName): string
    {
        return self::XML_BASE_URL."szamla/{$xmlName} http://www.szamlazz.hu/szamla/docs/xsds/{$this->getXmlDirectory()}/{$xmlName}.xsd";
    }

    private function getXmlNamespace($xmlName): string
    {
        return self::XML_BASE_URL."{$xmlName}";
    }

    /**
     * Összeállítja az elküldendő POST adatokat
     */
    private function buildQuery()
    {
        $this->setDelim(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16));

        $queryData = '--'.$this->getDelim().self::CRLF;
        $queryData .= 'Content-Disposition: form-data; name="'.$this->getFileName().'"; filename="'.$this->getFileName().'"'.self::CRLF;
        $queryData .= 'Content-Type: text/xml'.self::CRLF.self::CRLF;
        $queryData .= $this->getXmlData().self::CRLF;
        $queryData .= '--'.$this->getDelim().'--'.self::CRLF;

        $this->setPostFields($queryData);
    }

    /**
     * @throws SzamlaAgentException
     */
    private function setXmlFileData(string $type)
    {
        switch ($type) {
            // HU: Számlakészítés (normál, előleg, végszámla)
            case 'generateProforma':
            case 'generateInvoice':
            case 'generatePrePaymentInvoice':
            case 'generateFinalInvoice':
            case 'generateCorrectiveInvoice':
            case 'generateDeliveryNote':
                $fileName = 'action-xmlagentxmlfile';
                $xmlName = self::XML_SCHEMA_CREATE_INVOICE;
                $xmlDirectory = 'agent';
                break;
                // HU: Számla sztornó
            case 'generateReverseInvoice':
                $fileName = 'action-szamla_agent_st';
                $xmlName = self::XML_SCHEMA_CREATE_REVERSE_INVOICE;
                $xmlDirectory = 'agentst';
                break;
                // HU: Jóváírás rögzítése
            case 'payInvoice':
                $fileName = 'action-szamla_agent_kifiz';
                $xmlName = self::XML_SCHEMA_PAY_INVOICE;
                $xmlDirectory = 'agentkifiz';
                break;
                // HU: Számla adatok lekérése
            case 'requestInvoiceData':
                $fileName = 'action-szamla_agent_xml';
                $xmlName = self::XML_SCHEMA_REQUEST_INVOICE_XML;
                $xmlDirectory = 'agentxml';
                break;
                // HU: Számla PDF lekérése
            case 'requestInvoicePDF':
                $fileName = 'action-szamla_agent_pdf';
                $xmlName = self::XML_SCHEMA_REQUEST_INVOICE_PDF;
                $xmlDirectory = 'agentpdf';
                break;
                // HU: Nyugta készítés
            case 'generateReceipt':
                $fileName = 'action-szamla_agent_nyugta_create';
                $xmlName = self::XML_SCHEMA_CREATE_RECEIPT;
                $xmlDirectory = 'nyugtacreate';
                break;
                // HU: Nyugta sztornó
            case 'generateReverseReceipt':
                $fileName = 'action-szamla_agent_nyugta_storno';
                $xmlName = self::XML_SCHEMA_CREATE_REVERSE_RECEIPT;
                $xmlDirectory = 'nyugtast';
                break;
                // HU: Nyugta kiküldés
            case 'sendReceipt':
                $fileName = 'action-szamla_agent_nyugta_send';
                $xmlName = self::XML_SCHEMA_SEND_RECEIPT;
                $xmlDirectory = 'nyugtasend';
                break;
                // HU: Nyugta adatok lekérése
            case 'requestReceiptData':
            case 'requestReceiptPDF':
                $fileName = 'action-szamla_agent_nyugta_get';
                $xmlName = self::XML_SCHEMA_GET_RECEIPT;
                $xmlDirectory = 'nyugtaget';
                break;
                // HU: Adózó adatainak lekérdezése
            case 'getTaxPayer':
                $fileName = 'action-szamla_agent_taxpayer';
                $xmlName = self::XML_SCHEMA_TAXPAYER;
                $xmlDirectory = 'taxpayer';
                break;
                // HU: Díjbekérő törlése
            case 'deleteProforma':
                $fileName = 'action-szamla_agent_dijbekero_torlese';
                $xmlName = self::XML_SCHEMA_DELETE_PROFORMA;
                $xmlDirectory = 'dijbekerodel';
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::REQUEST_TYPE_NOT_EXISTS.": {$type}");
        }

        $this->setFileName($fileName);
        $this->setXmlName($xmlName);
        $this->setXmlDirectory($xmlDirectory);
    }

    /**
     * Számla Agent kérés küldése a szamlazz.hu felé
     *
     * @return array
     *
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function send()
    {
        $this->buildXmlData();
        $this->buildQuery();
        $response = $this->makeHttpRequest();

        $this->checkXmlFileSave();

        return $response;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function makeHttpRequest(): array
    {
        try {
            $agent = $this->getAgent();
            $cookieHandler = $agent->getCookieHandler();

            $client = Http::withOptions([
                'verify' => true,
                'ssl_key' => $agent->getCertificationFile(),
                'debug' => true,
            ]);

            if ($this->isBasicAuthRequest()) {
                $client = $client->withBasicAuth(
                    $this->getBasicAuthUserPwd()['username'],
                    $this->getBasicAuthUserPwd()['password']
                );
            }

            $xmlContent = 'data://application/octet-stream;base64,' . base64_encode($this->getXmlData());
            $fileName = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName(), $this->getEntity());
            $client = $client->attach($this->getFileName(), $xmlContent, $fileName);

            $httpHeaders = [
                'charset' => SzamlaAgent::CHARSET,
            ];

            if (!$cookieHandler->isHandleModeText()) {
                $cookieHandler->addCookieToHeader();
            } else {
                $cookieHandler->checkCookieFile();
                $cookieFile = $cookieHandler->getCookieFile();
                if ($cookieHandler->isUsableCookieFile()) {
                    $https['Cookie'] = $cookieFile;
                }
            }

            $customHttpHeaders = $agent->getCustomHTTPHeaders();
            if (!empty($customHttpHeaders)) {
                foreach ($customHttpHeaders as $key => $value) {
                    $httpHeaders[$key] = $value;
                }
            }

            if ($this->hasAttachments()) {
                $attachments = $this->getEntity()->getAttachments();
                foreach ($attachments as $key => $attachment) {
                    $client = $client->attach('attachfile'. $key, Storage::disk('payment')->get($attachment));
                }
            }

            $response = $client->withHeaders($httpHeaders)
                ->withBody($this->getPostFields(), 'multipart/form-data')
                ->timeout($this->getRequestTimeout())
                ->post($agent->getApiUrl());

            $headers = $response->headers();
            $body = $response->body();

            // Beállítjuk a session id-t ha kapunk újat
            $cookieHandler->handleSessionId($headers);

            $responseData = [
                'headers' => $this->getHeadersFromResponse($headers),
                'body' => $body,
            ];

            if ($response->failed()) {
                $error = $response->clientError() ? 'Client Error' : 'Server Error';
                Log::channel('szamlazzhu')->error(SzamlaAgentException::CONNECTION_ERROR, ['error_message' => $error]);
                throw new SzamlaAgentException($error);
            } else {
                $keys = implode(',', array_keys($headers));
                if ($responseData['headers']['Content-Type'] == 'application/pdf' || (!preg_match('/(szlahu_)/', $keys, $matches))) {
                    $message = $responseData['headers'];
                } else {
                    $message = $responseData;
                }

                $responseData['headers']['Schema-Type'] = $this->getXmlSchemaType();
                Log::channel('szamlazzhu')->debug('Sending of HTTP data is succesfully ended', ['message' => print_r($message, true)]);
            }

            if ($cookieHandler->isHandleModeJson()) {
                $cookieHandler->saveSessions();
            }

            return $responseData;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getHeadersFromResponse($headerContent): array
    {
        $headers = [];
        foreach ($headerContent as $index => $content) {
            if (SzamlaAgentUtil::isNotBlank($content)) {
                if ($index === 0) {
                    $headers['http_code'] = $content;
                } else {
                    $pos = strpos($content, ':');
                    if ($pos !== false) {
                        [$key, $value] = explode(': ', $content);
                        $headers[$key] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    public function getAgent(): SzamlaAgent
    {
        return $this->agent;
    }

    private function setAgent(SzamlaAgent $agent): void
    {
        $this->agent = $agent;
    }

    private function getType(): string
    {
        return $this->type;
    }

    private function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    private function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }

    private function getXmlData(): string
    {
        return $this->xmlData;
    }

    private function setXmlData(string $xmlData): void
    {
        $this->xmlData = $xmlData;
    }

    private function getDelim(): string
    {
        return $this->delim;
    }

    private function setDelim(string $delim): void
    {
        $this->delim = $delim;
    }

    private function getPostFields(): string
    {
        return $this->postFields;
    }

    private function setPostFields(string $postFields): void
    {
        $this->postFields = $postFields;
    }

    private function isCData(): bool
    {
        return $this->cData;
    }

    private function setCData(bool $cData): void
    {
        $this->cData = $cData;
    }

    public function getXmlName(): string
    {
        return $this->xmlName;
    }

    private function setXmlName(string $xmlName): void
    {
        $this->xmlName = $xmlName;
    }

    private function getFileName(): string
    {
        return $this->fileName;
    }

    private function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getXmlFilePath()
    {
        return $this->xmlFilePath;
    }

    private function setXmlFilePath(string $xmlFilePath): void
    {
        $this->xmlFilePath = $xmlFilePath;
    }

    private function getXmlDirectory(): string
    {
        return $this->xmlDirectory;
    }

    private function setXmlDirectory(string $xmlDirectory): void
    {
        $this->xmlDirectory = $xmlDirectory;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function getXmlSchemaType(): string
    {
        switch ($this->getXmlName()) {
            case self::XML_SCHEMA_CREATE_INVOICE:
            case self::XML_SCHEMA_CREATE_REVERSE_INVOICE:
            case self::XML_SCHEMA_PAY_INVOICE:
            case self::XML_SCHEMA_REQUEST_INVOICE_XML:
            case self::XML_SCHEMA_REQUEST_INVOICE_PDF:
                $type = Document::DOCUMENT_TYPE_INVOICE;
                break;
            case self::XML_SCHEMA_DELETE_PROFORMA:
                $type = Document::DOCUMENT_TYPE_PROFORMA;
                break;
            case self::XML_SCHEMA_CREATE_RECEIPT:
            case self::XML_SCHEMA_CREATE_REVERSE_RECEIPT:
            case self::XML_SCHEMA_SEND_RECEIPT:
            case self::XML_SCHEMA_GET_RECEIPT:
                $type = Document::DOCUMENT_TYPE_RECEIPT;
                break;
            case self::XML_SCHEMA_TAXPAYER:
                $type = 'taxpayer';
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS.": {$this->getXmlName()}");
        }

        return $type;
    }

    private function hasAttachments()
    {
        $entity = $this->getEntity();
        if (is_a($entity, Invoice::class)) {
            return count($entity->getAttachments()) > 0;
        }

        return false;
    }

    private function isBasicAuthRequest(): bool
    {
        $agent = $this->getAgent();

        return $agent->hasEnvironment() && $agent->getEnvironmentAuthType() == self::REQUEST_AUTHORIZATION_BASIC_AUTH;
    }

    private function getBasicAuthUserPwd(): string
    {
        return $this->getAgent()->getEnvironmentAuthUser().':'.$this->getAgent()->getEnvironmentAuthPassword();
    }

    private function getRequestTimeout(): int
    {
        if ($this->requestTimeout == 0) {
            return self::REQUEST_TIMEOUT;
        } else {
            return $this->requestTimeout;
        }
    }

    private function setRequestTimeout(int $timeout): void
    {
        $this->requestTimeout = $timeout;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function checkXmlFileSave()
    {
        if ($this->agent != null && ($this->agent->isNotXmlFileSave() || $this->agent->isNotRequestXmlFileSave())) {
            try {
                $xmlFilePath = $this->getXmlFilePath();
                if (SzamlaAgentUtil::isNotNull($xmlFilePath) && Storage::disk('payment')->exists($xmlFilePath)) {
                    Storage::disk('payment')->delete($xmlFilePath);
                }
            } catch (\Exception $e) {
                Log::channel('szamlazzhu')->warning('XML file deletion failed', ['error_message' => $e->getMessage()]);
            }
        }
    }
}
