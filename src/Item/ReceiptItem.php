<?php

namespace Omisai\SzamlazzhuAgent\Item;

use Omisai\SzamlazzhuAgent\Ledger\ReceiptItemLedger;
use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

/**
 * Nyugtatétel
 */
class ReceiptItem extends Item
{
    protected $ledgerData;

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
        $data['netto'] = SzamlaAgentUtil::doubleFormat($this->getNetPrice());
        $data['afa'] = SzamlaAgentUtil::doubleFormat($this->getVatAmount());
        $data['brutto'] = SzamlaAgentUtil::doubleFormat($this->getGrossAmount());

        if (SzamlaAgentUtil::isNotNull($this->getLedgerData())) {
            $data['fokonyv'] = $this->getLedgerData()->buildXmlData();
        }

        return $data;
    }

    public function getLedgerData(): ReceiptItemLedger
    {
        return $this->ledgerData;
    }

    public function setLedgerData(ReceiptItemLedger $ledgerData): void
    {
        $this->ledgerData = $ledgerData;
    }
}
