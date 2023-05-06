<?php

namespace Omisai\SzamlazzhuAgent\Header;

use Omisai\SzamlazzhuAgent\Document\Invoice\Invoice;

/**
 * Helyesbítő számla fejléc
 */
class CorrectiveInvoiceHeader extends InvoiceHeader
{
    /**
     * @param  int  $type
     *
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct($type = Invoice::INVOICE_TYPE_P_INVOICE)
    {
        parent::__construct($type);
        $this->setCorrective(true);
    }
}
