<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\PrePaymentInvoiceHeader;
use Omisai\Szamlazzhu\SzamlaAgentException;

/**
 * HU: Előlegszámla
 */
class PrePaymentInvoice extends Invoice
{
    /**
     * @throws SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new PrePaymentInvoiceHeader($type));
    }
}
