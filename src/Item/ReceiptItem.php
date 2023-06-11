<?php

namespace Omisai\Szamlazzhu\Item;

use Omisai\Szamlazzhu\HasXmlBuildInterface;
use Omisai\Szamlazzhu\Ledger\ReceiptItemLedger;
use Omisai\Szamlazzhu\SzamlaAgentException;

class ReceiptItem extends Item implements HasXmlBuildInterface
{
    protected ReceiptItemLedger $ledgerData;

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
        $data['netto'] = $this->netPrice;
        $data['afa'] = $this->vatAmount;
        $data['brutto'] = $this->grossAmount;

        if (!empty($this->ledgerData)) {
            $data['fokonyv'] = $this->ledgerData->buildXmlData();
        }

        return $data;
    }

    public function setLedgerData(ReceiptItemLedger $ledgerData): self
    {
        $this->ledgerData = $ledgerData;

        return $this;
    }
}
