<?php

namespace Omisai\Szamlazzhu\Document\Invoice;

use Omisai\Szamlazzhu\Header\FinalInvoiceHeader;
use Omisai\Szamlazzhu\SzamlaAgentException;

/**
 * HU: Végszámla
 */
class FinalInvoice extends Invoice
{
    /**
     * @throws SzamlaAgentException
     */
    public function __construct(int $type = self::INVOICE_TYPE_E_INVOICE)
    {
        $this->setHeader(new FinalInvoiceHeader($type));
    }
}
