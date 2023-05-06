<?php

namespace Omisai\SzamlazzhuAgent\Document\Invoice;

use Omisai\SzamlazzhuAgent\Header\FinalInvoiceHeader;

/**
 * Végszámla kiállításához használható segédosztály
 */
class FinalInvoice extends Invoice
{
    /**
     * Végszámla létrehozása
     *
     * @param  int  $type végszámla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct($type = self::INVOICE_TYPE_P_INVOICE)
    {
        parent::__construct(null);
        // Alapértelmezett fejléc adatok hozzáadása
        $this->setHeader(new FinalInvoiceHeader($type));
    }
}
