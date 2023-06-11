<?php

namespace Omisai\Szamlazzhu\Item;

use Omisai\Szamlazzhu\HasXmlBuildInterface;
use Omisai\Szamlazzhu\Ledger\InvoiceItemLedger;
use Omisai\Szamlazzhu\SzamlaAgentException;

class InvoiceItem extends Item implements HasXmlBuildInterface
{
    protected InvoiceItemLedger $ledgerData;

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {
        $data = [];
        $this->checkFields();

        $data['megnevezes'] = $this->name;

        if (!empty($this->id)) {
            $data['azonosito'] = $this->id;
        }

        $data['mennyiseg'] = $this->quantity;
        $data['mennyisegiEgyseg'] = $this->quantityUnit;
        $data['nettoEgysegar'] = $this->netUnitPrice;
        $data['afakulcs'] = $this->vat;

        if (!empty($this->priceGapVatBase)) {
            $data['arresAfaAlap'] = $this->priceGapVatBase;
        }

        if (!empty($this->netPrice)) {
            $data['nettoErtek'] = $this->netPrice;
        }

        if (!empty($this->vatAmount)) {
            $data['afaErtek'] = $this->vatAmount;
        }

        if (!empty($this->grossAmount)) {
            $data['bruttoErtek'] = $this->grossAmount;
        }

        if (!empty($this->comment)) {
            $data['megjegyzes'] = $this->comment;
        }

        if (!empty($this->ledgerData)) {
            $data['tetelFokonyv'] = $this->ledgerData->buildXmlData();
        }

        return $data;
    }

    public function setLedgerData(InvoiceItemLedger $ledgerData): self
    {
        $this->ledgerData = $ledgerData;

        return $this;
    }
}
