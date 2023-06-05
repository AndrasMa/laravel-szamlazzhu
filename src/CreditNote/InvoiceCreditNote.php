<?php

namespace Omisai\SzamlazzhuAgent\CreditNote;

use Omisai\SzamlazzhuAgent\Document\Document;
use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

/**
 * HU: Számla jóváírás
 */
class InvoiceCreditNote extends CreditNote
{
    protected string $date;

    protected array $requiredFields = ['date', 'paymentMode', 'amount'];

    public function __construct(string $date, string $amount, float $paymentMode = Document::PAYMENT_METHOD_TRANSFER, string $description = '')
    {
        parent::__construct($paymentMode, $amount, $description);
        $this->setDate($date);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'date':
                    SzamlaAgentUtil::checkDateField($field, $value, $required, __CLASS__);
                    break;
                case 'amount':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'paymentMode':
                case 'description':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkFields()
    {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {
        $data = [];
        $this->checkFields();

        if (SzamlaAgentUtil::isNotBlank($this->getDate())) {
            $data['datum'] = $this->getDate();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getPaymentMode())) {
            $data['jogcim'] = $this->getPaymentMode();
        }
        if (SzamlaAgentUtil::isNotNull($this->getAmount())) {
            $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->getAmount());
        }
        if (SzamlaAgentUtil::isNotBlank($this->getDescription())) {
            $data['leiras'] = $this->getDescription();
        }

        return $data;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}
