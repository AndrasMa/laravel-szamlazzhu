<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\ReverseInvoiceHeader;
use Omisai\Szamlazzhu\SzamlaAgentException;

/**
 * HU: Sztornó számla
 */
class ReverseInvoice extends Invoice
{
    /**
     * @throws SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new ReverseInvoiceHeader($type));
    }
}
