<?php

namespace Omisai\SzamlazzhuAgent\Header;

/**
 * Szállítólevél fejléc
 */
class DeliveryNoteHeader extends InvoiceHeader
{
    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    public function __construct()
    {
        parent::__construct();
        $this->setDeliveryNote(true);
        $this->setPaid(false);
    }
}
