<?php

namespace Omisai\SzamlazzhuAgent\Document;

use Omisai\SzamlazzhuAgent\Document\Invoice\Invoice;
use Omisai\SzamlazzhuAgent\Header\DeliveryNoteHeader;

/**
 * Szállítólevél segédosztály
 */
class DeliveryNote extends Invoice
{
    /**
     * Szállítólevél kiállítása
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct(null);
        // Alapértelmezett fejléc adatok hozzáadása
        $this->setHeader(new DeliveryNoteHeader());
    }
}
