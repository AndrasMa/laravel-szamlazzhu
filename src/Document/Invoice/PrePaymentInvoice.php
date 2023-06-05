<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\PrePaymentInvoiceHeader;

/**
 * HU: Előlegszámla
 */
class PrePaymentInvoice extends Invoice
{
    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new PrePaymentInvoiceHeader($type));
    }
}
