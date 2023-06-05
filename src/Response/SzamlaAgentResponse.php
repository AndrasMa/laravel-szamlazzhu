<?php

namespace Omisai\SzamlazzhuAgent\Response;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Omisai\SzamlazzhuAgent\Document\Document;
use Omisai\SzamlazzhuAgent\Document\Invoice\Invoice;
use Omisai\SzamlazzhuAgent\Header\InvoiceHeader;
use Omisai\SzamlazzhuAgent\SimpleXMLExtended;
use Omisai\SzamlazzhuAgent\SzamlaAgent;
use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentRequest;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

class SzamlaAgentResponse
{
    public const RESULT_AS_TEXT = 1;

    public const RESULT_AS_XML = 2;

    /**
     * Számla Agent kérésre adott válasz a NAV Online Számla Rendszer által visszaadott XML formátumú lesz
     *
     * @see https://onlineszamla.nav.gov.hu/dokumentaciok
     */
    public const RESULT_AS_TAXPAYER_XML = 3;

    private SzamlaAgent $agent;

    private array $response;

    private int $httpCode;

    private string $errorMessage = '';

    private int $errorCode;

    private string $documentNumber;

    private \SimpleXMLElement $xmlData;

    private string $pdfFile;

    private string $content;

    private object $responseObj;

    private string $xmlSchemaType;

    public function __construct(SzamlaAgent $agent, array $response)
    {
        $this->setAgent($agent);
        $this->setResponse($response);
        $this->setXmlSchemaType($response['headers']['Schema-Type']);
    }

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function handleResponse(): SzamlaAgentResponse
    {
        $response = $this->getResponse();
        $agent = $this->getAgent();

        if (empty($response) || $response === null) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_IS_EMPTY);
        }

        if (isset($response['headers']) && ! empty($response['headers'])) {
            $headers = $response['headers'];

            if (isset($headers['szlahu_down']) && SzamlaAgentUtil::isNotBlank($headers['szlahu_down'])) {
                throw new SzamlaAgentException(SzamlaAgentException::SYSTEM_DOWN, 500);
            }
        } else {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_HEADER);
        }

        if (! isset($response['body']) || empty($response['body'])) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_CONTENT);
        }

        if (array_key_exists('http_code', $headers)) {
            $this->setHttpCode($headers['http_code']);
        }

        // XML adatok beállítása és a fájl létrehozása
        if ($this->isXmlResponse()) {
            $this->buildResponseXmlData();
        } else {
            $this->buildResponseTextData();
        }

        $this->buildResponseObjData();
        if ($agent->isXmlFileSave() || $agent->isResponseXmlFileSave()) {
            $this->createXmlFile($this->getXmlData());
        }
        $this->checkFields();

        if ($this->hasInvoiceNotificationSendError()) {
            Log::channel('szamlazzhu')->debug(SzamlaAgentException::INVOICE_NOTIFICATION_SEND_FAILED);
        }

        if ($this->isFailed()) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_ERROR.": [{$this->getErrorCode()}], {$this->getErrorMessage()}");
        } elseif ($this->isSuccess()) {
            Log::channel('szamlazzhu')->debug('The Agent call succesfully ended.');

            if ($this->isNotTaxPayerXmlResponse()) {
                try {
                    $responseObj = $this->getResponseObj();
                    $this->setDocumentNumber($responseObj->getDocumentNumber());
                    if ($agent->isDownloadPdf()) {
                        $pdfData = $responseObj->getPdfFile();
                        $xmlName = $agent->getRequest()->getXmlName();
                        if (empty($pdfData) && ! in_array($xmlName, [SzamlaAgentRequest::XML_SCHEMA_SEND_RECEIPT, SzamlaAgentRequest::XML_SCHEMA_PAY_INVOICE])) {
                            throw new SzamlaAgentException(SzamlaAgentException::DOCUMENT_DATA_IS_MISSING);
                        } elseif (! empty($pdfData)) {
                            $this->setPdfFile($pdfData);

                            if ($agent->isPdfFileSave()) {
                                $realPath = $this->getPdfFileName();
                                $isSaved = Storage::disk('payment')->put($realPath, $pdfData);

                                if ($isSaved) {
                                    Log::channel('szamlazzhu')->debug(SzamlaAgentException::PDF_FILE_SAVE_SUCCESS, ['path' => $realPath]);
                                } else {
                                    $errorMessage = SzamlaAgentException::PDF_FILE_SAVE_FAILED.': '.SzamlaAgentException::FILE_CREATION_FAILED;
                                    Log::channel('szamlazzhu')->debug($errorMessage);
                                    throw new SzamlaAgentException($errorMessage);
                                }
                            }
                        }
                    } else {
                        $this->setContent($response['body']);
                    }
                } catch (\Exception $e) {
                    Log::channel('szamlazzhu')->debug(SzamlaAgentException::PDF_FILE_SAVE_FAILED, ['error_message' => $e->getMessage()]);
                    throw $e;
                }
            }
        }

        return $this;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function checkFields()
    {
        $response = $this->getResponse();

        if ($this->isAgentInvoiceResponse()) {
            $keys = implode(',', array_keys($response['headers']));
            if (! preg_match('/(szlahu_)/', $keys, $matches)) {
                throw new SzamlaAgentException(SzamlaAgentException::NO_SZLAHU_KEY_IN_HEADER);
            }
        }
    }

    /**
     * @throws SzamlaAgentException
     * @throws \ReflectionException
     */
    private function createXmlFile(\SimpleXMLElement $xml): void
    {
        $agent = $this->getAgent();

        if ($this->isTaxPayerXmlResponse()) {
            $response = $this->getResponse();
            $xml = SzamlaAgentUtil::formatResponseXml($response['body']);
        } else {
            $xml = SzamlaAgentUtil::formatXml($xml);
        }

        $type = $agent->getResponseType();

        $name = '';
        if ($this->isFailed()) {
            $name = 'error-';
        }
        $name .= strtolower($agent->getRequest()->getXmlName());

        switch ($type) {
            case self::RESULT_AS_XML:
            case self::RESULT_AS_TAXPAYER_XML: $postfix = '-xml';
            break;
            case self::RESULT_AS_TEXT: $postfix = '-text';
            break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::RESPONSE_TYPE_NOT_EXISTS." ($type)");
        }

        $filename = SzamlaAgentUtil::getXmlFileName('response', $name.$postfix, $agent->getRequest()->getEntity());
        $realPath = sprintf('%s/response/%s', SzamlaAgent::XML_FILE_SAVE_PATH, $filename);
        $isXmlSaved = Storage::disk('payment')->put($realPath, $xml->saveXML());

        if (!$isXmlSaved) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_FILE_SAVE_FAILED);
        }
        Log::channel('szamlazzhu')->debug('XML fájl mentése sikeres', ['path' => $realPath]);
    }

    public function getPdfFileName(bool $withPath = true): string
    {
        $header = $this->getAgent()->getRequestEntityHeader();

        if ($header instanceof InvoiceHeader && $header->isPreviewPdf()) {
            $documentNumber = sprintf('preview-invoice-%s', SzamlaAgentUtil::getDateTimeWithMilliseconds());
        } else {
            $documentNumber = $this->getDocumentNumber();
        }

        if ($withPath) {
            return $this->getPdfFilePath($documentNumber.'.pdf');
        } else {
            return $documentNumber.'.pdf';
        }

    }

    protected function getPdfFilePath($pdfFileName): string
    {
        return sprintf('%s/%s', SzamlaAgent::PDF_FILE_SAVE_PATH, $pdfFileName);
    }

    public function isSuccess(): bool
    {
        return !$this->isFailed();
    }

    public function isFailed(): bool
    {
        $result = true;
        $obj = $this->getResponseObj();
        if ($obj != null) {
            $result = $obj->isError();
        }

        return $result;
    }

    private function getAgent(): SzamlaAgent
    {
        return $this->agent;
    }

    private function setAgent(SzamlaAgent $agent): void
    {
        $this->agent = $agent;
    }

    public function getResponse(): array
    {
        return $this->response;
    }

    private function setResponse(array $response): void
    {
        $this->response = $response;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    private function setHttpCode(int $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    private function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    private function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    public function getDocumentNumber(): string
    {
        return $this->documentNumber;
    }

    private function setDocumentNumber(string $documentNumber): void
    {
        $this->documentNumber = $documentNumber;
    }

    private function setPdfFile(string $pdfFile)
    {
        $this->pdfFile = $pdfFile;
    }

    protected function getXmlData(): \SimpleXMLElement
    {
        return $this->xmlData;
    }

    protected function setXmlData(\SimpleXMLElement $xmlData): void
    {
        $this->xmlData = $xmlData;
    }

    protected function getContent(): string
    {
        return $this->content;
    }

    protected function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getXmlSchemaType(): string
    {
        return $this->xmlSchemaType;
    }

    protected function setXmlSchemaType(string $xmlSchemaType): void
    {
        $this->xmlSchemaType = $xmlSchemaType;
    }

    public function getResponseObj(): object
    {
        return $this->responseObj;
    }

    public function setResponseObj(object $responseObj): void
    {
        $this->responseObj = $responseObj;
    }

    protected function isAgentInvoiceTextResponse(): bool
    {
        return $this->isAgentInvoiceResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_TEXT;
    }

    protected function isAgentInvoiceXmlResponse(): bool
    {
        return $this->isAgentInvoiceResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_XML;
    }

    protected function isAgentReceiptTextResponse(): bool
    {
        return $this->isAgentReceiptResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_TEXT;
    }

    protected function isAgentReceiptXmlResponse(): bool
    {
        return $this->isAgentReceiptResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_XML;
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'adózó' típusú volt-e
     *
     * @return bool
     */
    public function isTaxPayerXmlResponse(): bool
    {
        $result = true;

        if ($this->getXmlSchemaType() != 'taxpayer') {
            return false;
        }

        if ($this->getAgent()->getResponseType() != self::RESULT_AS_TAXPAYER_XML) {
            $result = false;
        }

        return $result;
    }

    public function isNotTaxPayerXmlResponse(): bool
    {
        return ! $this->isTaxPayerXmlResponse();
    }

    protected function isXmlResponse(): bool
    {
        return $this->isAgentInvoiceXmlResponse() || $this->isAgentReceiptXmlResponse() || $this->isTaxPayerXmlResponse();
    }

    public function isAgentInvoiceResponse(): bool
    {
        return $this->getXmlSchemaType() == Document::DOCUMENT_TYPE_INVOICE;
    }

    public function isAgentProformaResponse(): bool
    {
        return $this->getXmlSchemaType() == Document::DOCUMENT_TYPE_PROFORMA;
    }

    public function isAgentReceiptResponse(): bool
    {
        return $this->getXmlSchemaType() == Document::DOCUMENT_TYPE_RECEIPT;
    }

    public function isTaxPayerResponse(): bool
    {
        return $this->getXmlSchemaType() == 'taxpayer';
    }

    private function buildResponseTextData()
    {
        $response = $this->getResponse();
        $xmlData = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response></response>');
        $headers = $xmlData->addChild('headers');

        foreach ($response['headers'] as $key => $value) {
            $headers->addChild($key, $value);
        }

        if ($this->isAgentReceiptResponse()) {
            $content = base64_encode($response['body']);
        } else {
            $content = ($this->getAgent()->isDownloadPdf()) ? base64_encode($response['body']) : $response['body'];
        }

        $xmlData->addChild('body', $content);

        $this->setXmlData($xmlData);
    }

    private function buildResponseXmlData()
    {
        $response = $this->getResponse();
        if ($this->isTaxPayerXmlResponse()) {
            $xmlData = new SimpleXMLExtended($response['body']);
            $xmlData = SzamlaAgentUtil::removeNamespaces($xmlData);
        } else {
            $xmlData = new \SimpleXMLElement($response['body']);
            // Fejléc adatok hozzáadása
            $headers = $xmlData->addChild('headers');
            foreach ($response['headers'] as $key => $header) {
                $headers->addChild($key, $header);
            }
        }
        $this->setXmlData($xmlData);
    }

    public function toPdf(): string
    {
        return $this->getPdfFile();
    }

    public function getPdfFile(): string
    {
        return $this->pdfFile;
    }

    public function toXML(): string
    {
        if (! empty($this->getXmlData())) {
            $data = $this->getXmlData();

            return $data->asXML();
        }

        return null;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function toJson(): string
    {
        $result = json_encode($this->getResponseData());
        if ($result === false || is_null($result) || ! SzamlaAgentUtil::isValidJSON($result)) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_JSON);
        }

        return $result;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function toArray(): mixed
    {
        return json_decode($this->toJson(), true);
    }

    /**
     * @throws SzamlaAgentException
     */
    public function getData(): mixed
    {
        return $this->toArray();
    }

    public function getDataObj(): object
    {
        return $this->getResponseObj();
    }

    public function getResponseData(): array
    {
        if ($this->isNotTaxPayerXmlResponse()) {
            $result['documentNumber'] = $this->getDocumentNumber();
        }

        if (! empty($this->getXmlData())) {
            $result['result'] = $this->getXmlData();
        } else {
            $result['result'] = $this->getContent();
        }

        return $result;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function buildResponseObjData()
    {
        $obj = null;
        $type = $this->getAgent()->getResponseType();
        $result = $this->getData()['result'];

        if ($this->isAgentInvoiceResponse()) {
            $obj = InvoiceResponse::parseData($result, $type);
        } elseif ($this->isAgentProformaResponse()) {
            $obj = ProformaDeletionResponse::parseData($result);
        } elseif ($this->isAgentReceiptResponse()) {
            $obj = ReceiptResponse::parseData($result, $type);
        } elseif ($this->isTaxPayerXmlResponse()) {
            $obj = TaxPayerResponse::parseData($result);
        }

        $this->setResponseObj($obj);

        if ($obj->isError() || $this->hasInvoiceNotificationSendError()) {
            $this->setErrorCode($obj->getErrorCode());
            $this->setErrorMessage($obj->getErrorMessage());
        }
    }

    public function hasInvoiceNotificationSendError(): bool
    {
        if ($this->isAgentInvoiceResponse() && $this->getResponseObj()->hasInvoiceNotificationSendError()) {
            return true;
        }

        return false;
    }

    public function getTaxPayerData(): ?string
    {
        $data = null;
        if ($this->isTaxPayerResponse()) {
            $response = $this->getResponse();
            $data = $response['body'];
        }

        return $data;
    }

    public function getCookieSessionId(): string
    {
        return $this->agent->getCookieSessionId();
    }
}
