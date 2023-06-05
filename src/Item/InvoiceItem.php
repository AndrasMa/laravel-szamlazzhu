<?php

namespace Omisai\Szamlazzhu\Item;

use Omisai\Szamlazzhu\Ledger\InvoiceItemLedger;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

/**
 * HU: Számlatétel
 */
class InvoiceItem extends Item
{
    protected InvoiceItemLedger $ledgerData;

    public function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, string $vat = self::DEFAULT_VAT)
    {
        parent::__construct($name, $netUnitPrice, $quantity, $quantityUnit, $vat);
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {
        $data = [];
        $this->checkFields();

        $data['megnevezes'] = $this->getName();

        if (SzamlaAgentUtil::isNotBlank($this->getId())) {
            $data['azonosito'] = $this->getId();
        }

        $data['mennyiseg'] = SzamlaAgentUtil::doubleFormat($this->getQuantity());
        $data['mennyisegiEgyseg'] = $this->getQuantityUnit();
        $data['nettoEgysegar'] = SzamlaAgentUtil::doubleFormat($this->getNetUnitPrice());
        $data['afakulcs'] = $this->getVat();

        if (SzamlaAgentUtil::isNotNull($this->getPriceGapVatBase())) {
            $data['arresAfaAlap'] = SzamlaAgentUtil::doubleFormat($this->getPriceGapVatBase());
        }

        $data['nettoErtek'] = SzamlaAgentUtil::doubleFormat($this->getNetPrice());
        $data['afaErtek'] = SzamlaAgentUtil::doubleFormat($this->getVatAmount());
        $data['bruttoErtek'] = SzamlaAgentUtil::doubleFormat($this->getGrossAmount());

        if (SzamlaAgentUtil::isNotBlank($this->getComment())) {
            $data['megjegyzes'] = $this->getComment();
        }

        if (SzamlaAgentUtil::isNotNull($this->getLedgerData())) {
            $data['tetelFokonyv'] = $this->getLedgerData()->buildXmlData();
        }

        return $data;
    }

    public function getPriceGapVatBase(): float
    {
        return $this->priceGapVatBase;
    }

    public function setPriceGapVatBase(float $priceGapVatBase): void
    {
        $this->priceGapVatBase = (float) $priceGapVatBase;
    }

    public function getLedgerData(): InvoiceItemLedger
    {
        return $this->ledgerData;
    }

    public function setLedgerData(InvoiceItemLedger $ledgerData): void
    {
        $this->ledgerData = $ledgerData;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
