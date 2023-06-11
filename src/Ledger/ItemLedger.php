<?php

namespace Omisai\Szamlazzhu\Ledger;

/**
 * HU: Tétel főkönyvi adatok
 */
class ItemLedger
{
    protected string $revenueLedgerNumber;

    protected string $vatLedgerNumber;

    protected function __construct(string $revenueLedgerNumber = '', string $vatLedgerNumber = '')
    {
        $this->setRevenueLedgerNumber($revenueLedgerNumber);
        $this->setVatLedgerNumber($vatLedgerNumber);
    }

    public function setRevenueLedgerNumber(string $revenueLedgerNumber): self
    {
        $this->revenueLedgerNumber = $revenueLedgerNumber;

        return $this;
    }

    public function setVatLedgerNumber(string $vatLedgerNumber): self
    {
        $this->vatLedgerNumber = $vatLedgerNumber;

        return $this;
    }
}
