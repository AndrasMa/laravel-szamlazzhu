<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\FinalInvoiceHeader;

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
