<?php

namespace Omisai\SzamlazzhuAgent\Document;

use Omisai\SzamlazzhuAgent\Document\Invoice\Invoice;
use Omisai\SzamlazzhuAgent\Header\DeliveryNoteHeader;

/**
 * HU: Szállítólevél
 */
class DeliveryNote extends Invoice
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setHeader(new DeliveryNoteHeader());
    }
}
