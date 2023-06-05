<?php

namespace Omisai\SzamlazzhuAgent\Document\Invoice;

use Omisai\SzamlazzhuAgent\Header\CorrectiveInvoiceHeader;

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
