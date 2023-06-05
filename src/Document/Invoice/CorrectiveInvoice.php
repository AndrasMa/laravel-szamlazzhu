<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\CorrectiveInvoiceHeader;

/**
 * HU: Helyesbítő számla
 */
class CorrectiveInvoice extends Invoice
{
    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new CorrectiveInvoiceHeader($type));
    }
}
