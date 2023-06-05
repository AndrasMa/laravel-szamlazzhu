<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\ReverseInvoiceHeader;

/**
 * HU: Sztornó számla
 */
class ReverseInvoice extends Invoice
{
    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new ReverseInvoiceHeader($type));
    }
}
