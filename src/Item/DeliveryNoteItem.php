<?php

namespace Omisai\SzamlazzhuAgent\Item;

/**
 * HU: Szállítólevél tétel
 */
class DeliveryNoteItem extends InvoiceItem
{
    public function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, string $vat = self::DEFAULT_VAT)
    {
        parent::__construct($name, $netUnitPrice, $quantity, $quantityUnit, $vat);
    }
}
