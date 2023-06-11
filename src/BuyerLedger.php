<?php

namespace Omisai\Szamlazzhu;

use Carbon\Carbon;

/**
 * HU: A vevő főkönyvi adatai
 */
class BuyerLedger
{
    protected string $buyerId;

    protected Carbon $bookingDate;

    protected string $buyerLedgerNumber;

    protected bool $continuedFulfillment;

    protected Carbon $settlementPeriodStart;

    protected Carbon $settlementPeriodEnd;

    /**
     * @throws SzamlaAgentException
     */
    public function getXmlData(): array
    {
        $data = [];
        if (!empty($this->bookingDate)) {
            $data['konyvelesDatum'] = $this->bookingDate->format('Y-m-d');
        }
        if (!empty($this->buyerId)) {
            $data['vevoAzonosito'] = $this->buyerId;
        }
        if (!empty($this->buyerLedgerNumber)) {
            $data['vevoFokonyviSzam'] = $this->buyerLedgerNumber;
        }
        if ($this->continuedFulfillment) {
            $data['folyamatosTelj'] = $this->continuedFulfillment;
        }
        if (!empty($this->settlementPeriodStart)) {
            $data['elszDatumTol'] = $this->settlementPeriodStart->format('Y-m-d');
        }
        if (!empty($this->settlementPeriodEnd)) {
            $data['elszDatumIg'] = $this->settlementPeriodEnd->format('Y-m-d');
        }

        return $data;
    }

    public function setBuyerId(string $buyerId): self
    {
        $this->buyerId = $buyerId;

        return $this;
    }

    public function setBookingDate(Carbon $bookingDate): self
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function setBuyerLedgerNumber(string $buyerLedgerNumber): self
    {
        $this->buyerLedgerNumber = $buyerLedgerNumber;

        return $this;
    }

    public function setContinuedFulfillment(bool $continuedFulfillment): self
    {
        $this->continuedFulfillment = $continuedFulfillment;

        return $this;
    }

    public function setSettlementPeriodStart(Carbon $settlementPeriodStart): self
    {
        $this->settlementPeriodStart = $settlementPeriodStart;

        return $this;
    }

    public function setSettlementPeriodEnd(Carbon $settlementPeriodEnd): self
    {
        $this->settlementPeriodEnd = $settlementPeriodEnd;

        return $this;
    }
}
