<?php

namespace Omisai\SzamlazzhuAgent\CreditNote;

use Omisai\SzamlazzhuAgent\Document\Document;

/**
 * HU: Jóváírás
 */
class CreditNote
{
    protected string $paymentMode;

    protected float $amount;

    protected string $description = '';

    protected array $requiredFields = ['paymentMode', 'amount'];

    protected function __construct(string $paymentMode = Document::PAYMENT_METHOD_TRANSFER, float $amount = 0.0, string $description = '')
    {
        $this->setPaymentMode($paymentMode);
        $this->setAmount($amount);
        $this->setDescription($description);
    }

    protected function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    public function getPaymentMode(): string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(string $paymentMode):void
    {
        $this->paymentMode = $paymentMode;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = (float) $amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
