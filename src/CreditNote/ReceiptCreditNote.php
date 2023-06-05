<?php

namespace Omisai\SzamlazzhuAgent\CreditNote;

use Omisai\SzamlazzhuAgent\Document\Document;
use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

/**
 * HU: Nyugta jóváírás
 */
class ReceiptCreditNote extends CreditNote
{
    protected string $paymentMode;

    protected float $amount;

    protected string $description = '';

    public function __construct(string $paymentMode = Document::PAYMENT_METHOD_CASH, float $amount = 0.0, string $description = '')
    {
        parent::__construct($paymentMode, $amount, $description);
    }

    protected function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
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

        if (SzamlaAgentUtil::isNotBlank($this->getPaymentMode())) {
            $data['fizetoeszkoz'] = $this->getPaymentMode();
        }
        if (SzamlaAgentUtil::isNotNull($this->getAmount())) {
            $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->getAmount());
        }
        if (SzamlaAgentUtil::isNotBlank($this->getDescription())) {
            $data['leiras'] = $this->getDescription();
        }

        return $data;
    }
}
