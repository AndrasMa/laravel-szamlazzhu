<?php

namespace Omisai\Szamlazzhu\CreditNote;

use Omisai\Szamlazzhu\Document\Document;
use Omisai\Szamlazzhu\PaymentMethod;

/**
 * HU: Jóváírás
 */
class CreditNote
{
    protected float $amount;

    protected string $date;

    protected PaymentMethod $paymentMethod;

    protected string $description = '';

    protected array $requiredFields = ['paymentMethod', 'amount'];

    protected function __construct(PaymentMethod $paymentMethod = PaymentMethod::PAYMENT_METHOD_TRANSFER, float $amount = 0.0, string $description = '')
    {
        $this->setPaymentMethod($paymentMethod);
        $this->setAmount($amount);
        $this->setDescription($description);
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod->value;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function setAmount(float $amount):  self
    {
        $this->amount = (float) $amount;

        return $this;
    }

    public function setDescription(string $description):  self
    {
        $this->description = $description;

        return $this;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}
