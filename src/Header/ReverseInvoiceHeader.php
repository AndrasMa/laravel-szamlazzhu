<?php

namespace Omisai\Szamlazzhu\Header;

use Omisai\Szamlazzhu\Document\Document;
use Omisai\Szamlazzhu\Document\Invoice\Invoice;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentRequest;
use Omisai\Szamlazzhu\SzamlaAgentUtil;
use Omisai\Szamlazzhu\Header\Type;

/**
 * Sztornó számla fejléc
 */
class ReverseInvoiceHeader extends InvoiceHeader
{
    protected array $requiredFields = ['invoiceNumber'];

    /**
     * @throws SzamlaAgentException
     */
    public function __construct(int $type = Invoice::INVOICE_TYPE_P_INVOICE)
    {
        parent::__construct($type);
        $this->setType(Type::REVERSE_INVOICE);
    }

    /**
     * @throws SzamlaAgentException
     */
    public function checkField(string $field, mixed $value): mixed
    {
        if (property_exists(get_parent_class($this), $field) || property_exists($this, $field)) {
            $required = in_array($field, $this->requiredFields);
            switch ($field) {
                case 'issueDate':
                case 'fulfillment':
                    SzamlaAgentUtil::checkDateField($field, $value, $required, __CLASS__);
                    break;
                case 'invoiceNumber':
                case 'comment':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array
    {

        try {
            if (empty($request)) {
                throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_NOT_AVAILABLE);
            }

            $data['szamlaszam'] = $this->getInvoiceNumber();

            if (!empty($this->issueDate)) {
                $data['keltDatum'] = $this->issueDate;
            }
            if (!empty($this->fulfillment)) {
                $data['teljesitesDatum'] = $this->fulfillment;
            }
            if (!empty($this->comment)) {
                $data['megjegyzes'] = $this->comment;
            }

            $data['tipus'] = Document::DOCUMENT_TYPE_REVERSE_INVOICE_CODE;

            if (!empty($this->invoiceTemplate)) {
                $data['szamlaSablon'] = $this->invoiceTemplate;
            }

            $this->checkFields();

            return $data;
        } catch (SzamlaAgentException $e) {
            throw $e;
        }
    }
}
