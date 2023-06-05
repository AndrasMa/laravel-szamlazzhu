<?php

namespace Omisai\SzamlazzhuAgent;

/**
 * HU: A vevő főkönyvi adatai
 */
class BuyerLedger
{
    protected string $buyerId;

    protected string $bookingDate;

    protected string $buyerLedgerNumber;

    protected bool $continuedFulfillment;

    protected string $settlementPeriodStart;

    protected string $settlementPeriodEnd;

    public function __construct(string $buyerId = '', string $bookingDate = '', string $buyerLedgerNumber = '', bool $continuedFulfillment = false)
    {
        $this->setBuyerId($buyerId);
        $this->setBookingDate($bookingDate);
        $this->setBuyerLedgerNumber($buyerLedgerNumber);
        $this->setContinuedFulfillment($continuedFulfillment);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            switch ($field) {
                case 'bookingDate':
                case 'settlementPeriodStart':
                case 'settlementPeriodEnd':
                    SzamlaAgentUtil::checkDateField($field, $value, false, __CLASS__);
                    break;
                case 'continuedFulfillment':
                    SzamlaAgentUtil::checkBoolField($field, $value, false, __CLASS__);
                    break;
                case 'buyerId':
                case 'buyerLedgerNumber':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
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
    public function getXmlData(): array
    {
        $data = [];
        $this->checkFields();

        if (SzamlaAgentUtil::isNotBlank($this->getBookingDate())) {
            $data['konyvelesDatum'] = $this->getBookingDate();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getBuyerId())) {
            $data['vevoAzonosito'] = $this->getBuyerId();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getBuyerLedgerNumber())) {
            $data['vevoFokonyviSzam'] = $this->getBuyerLedgerNumber();
        }
        if ($this->isContinuedFulfillment()) {
            $data['folyamatosTelj'] = $this->isContinuedFulfillment();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodStart())) {
            $data['elszDatumTol'] = $this->getSettlementPeriodStart();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodEnd())) {
            $data['elszDatumIg'] = $this->getSettlementPeriodEnd();
        }

        return $data;
    }

    public function getBuyerId(): string
    {
        return $this->buyerId;
    }

    public function setBuyerId(string $buyerId): void
    {
        $this->buyerId = $buyerId;
    }

    public function getBookingDate(): string
    {
        return $this->bookingDate;
    }

    public function setBookingDate(string $bookingDate): void
    {
        $this->bookingDate = $bookingDate;
    }

    public function getBuyerLedgerNumber(): string
    {
        return $this->buyerLedgerNumber;
    }

    public function setBuyerLedgerNumber(string $buyerLedgerNumber): void
    {
        $this->buyerLedgerNumber = $buyerLedgerNumber;
    }

    public function isContinuedFulfillment(): bool
    {
        return $this->continuedFulfillment;
    }

    public function setContinuedFulfillment(bool $continuedFulfillment): void
    {
        $this->continuedFulfillment = $continuedFulfillment;
    }

    public function getSettlementPeriodStart(): string
    {
        return $this->settlementPeriodStart;
    }

    public function setSettlementPeriodStart(string $settlementPeriodStart): void
    {
        $this->settlementPeriodStart = $settlementPeriodStart;
    }

    public function getSettlementPeriodEnd(): string
    {
        return $this->settlementPeriodEnd;
    }

    public function setSettlementPeriodEnd(string $settlementPeriodEnd): void
    {
        $this->settlementPeriodEnd = $settlementPeriodEnd;
    }
}
