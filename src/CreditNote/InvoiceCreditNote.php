<?php

namespace Omisai\Szamlazzhu\CreditNote;

use Omisai\Szamlazzhu\FieldsValidationTrait;
use Omisai\Szamlazzhu\PaymentMethod;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Carbon\Carbon;

class InvoiceCreditNote extends CreditNote
{
    use FieldsValidationTrait;

    protected array $requiredFields = ['date', 'paymentMethod', 'amount'];

    public function __construct(Carbon $date, string $amount, PaymentMethod $paymentMethod = PaymentMethod::PAYMENT_METHOD_TRANSFER, string $description = '')
    {
        parent::__construct($paymentMethod, $amount, $description);
        $this->date = $date;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {
        $this->validateFields();

        $data = [];
        if (!empty($this->date)) {
            $data['datum'] = $this->date->format('Y-m-d');
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
