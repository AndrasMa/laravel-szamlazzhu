<?php

namespace Omisai\Szamlazzhu\Header;

use Omisai\Szamlazzhu\Document\Invoice\Invoice;

/**
 * Előlegszámla fejléc
 */
class PrePaymentInvoiceHeader extends InvoiceHeader
{
    /**
     * @param  int  $type
     *
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct($type = Invoice::INVOICE_TYPE_P_INVOICE)
    {
        parent::__construct($type);
        $this->setPrePayment(true);
        $this->setPaid(false);
    }
}
