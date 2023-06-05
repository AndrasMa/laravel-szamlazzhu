<?php

namespace Omisai\SzamlazzhuAgent\Document\Invoice;

use Omisai\SzamlazzhuAgent\Header\PrePaymentInvoiceHeader;

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
