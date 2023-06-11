<?php

namespace Omisai\Szamlazzhu\CreditNote;

use Omisai\Szamlazzhu\PaymentMethod;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

class InvoiceCreditNote extends CreditNote
{
    protected array $requiredFields = ['date', 'paymentMethod', 'amount'];

    public function __construct(string $date, string $amount, PaymentMethod $paymentMethod = PaymentMethod::PAYMENT_METHOD_TRANSFER, string $description = '')
    {
        parent::__construct($paymentMethod, $amount, $description);
        $this->date = $date;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->requiredFields);
            switch ($field) {
                case 'date':
                    SzamlaAgentUtil::checkDateField($field, $value, $required, self::class);
                    break;
                case 'amount':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, self::class);
                    break;
                case 'paymentMethod':
                    SzamlaAgentUtil::checkStrField($field, $value->value, $required, self::class);
                    break;
                case 'description':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, self::class);
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

        if (!empty($this->date)) {
            $data['datum'] = $this->date;
        }

        $data['jogcim'] = $this->getPaymentMethod();

        if (!empty($this->amount)) {
            $data['osszeg'] = $this->amount;
        }
        if (!empty($this->description)) {
            $data['leiras'] = $this->description;
        }

        return $data;
    }
}
