<?php

namespace Omisai\Szamlazzhu\Item;

/**
 * HU: Díjbekérő tétel
 */
class ProformaItem extends InvoiceItem
{
    public function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, string $vat = self::DEFAULT_VAT)
    {
        parent::__construct($name, $netUnitPrice, $quantity, $quantityUnit, $vat);
    }
}
