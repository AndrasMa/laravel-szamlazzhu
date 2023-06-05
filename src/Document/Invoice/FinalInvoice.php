<?php

namespace Omisai\SzamlazzhuAgent\Document\Invoice;

use Omisai\SzamlazzhuAgent\Header\FinalInvoiceHeader;

/**
 * HU: Végszámla
 */
class FinalInvoice extends Invoice
{
    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new FinalInvoiceHeader($type));
    }
}
