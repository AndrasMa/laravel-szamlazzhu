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
        $this->validateFields();

        $data = [];
        $data['megnevezes'] = $this->name;
        if (!empty($this->id)) {
            $data['azonosito'] = $this->id;
        }
        $data['mennyiseg'] = number_format($this->quantity, 2);
        $data['mennyisegiEgyseg'] = $this->quantityUnit;
        $data['nettoEgysegar'] = $this->netUnitPrice;
        $data['afakulcs'] = $this->vat;
        $data['arresAfaAlap'] = number_format($this->priceGapVatBase, 2);
        $data['nettoErtek'] = number_format($this->netPrice, 2);
        $data['afaErtek'] = number_format($this->vatAmount, 2);
        $data['bruttoErtek'] = number_format($this->grossAmount, 2);

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
