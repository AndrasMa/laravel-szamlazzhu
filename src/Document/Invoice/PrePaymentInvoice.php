<?php

namespace Omisai\SzamlazzhuAgent\Document\Invoice;

use Omisai\SzamlazzhuAgent\Header\PrePaymentInvoiceHeader;

/**
 * Előlegszámla kiállításához használható segédosztály
 */
class PrePaymentInvoice extends Invoice
{
    /**
     * Előlegszámla létrehozása
     *
     * @param  int  $type számla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct($type = self::INVOICE_TYPE_P_INVOICE)
    {
        parent::__construct(null);
        // Alapértelmezett fejléc adatok hozzáadása
        $this->setHeader(new PrePaymentInvoiceHeader($type));
    }
}
