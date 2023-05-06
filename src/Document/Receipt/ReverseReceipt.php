<?php

namespace Omisai\SzamlazzhuAgent\Document\Receipt;

use Omisai\SzamlazzhuAgent\Header\ReverseReceiptHeader;

/**
 * Sztornó nyugta
 */
class ReverseReceipt extends Receipt
{
    /**
     * Sztornó nyugta létrehozása nyugtaszám alapján
     *
     * @param  string  $receiptNumber
     */
    public function __construct($receiptNumber = '')
    {
        parent::__construct(null);
        $this->setHeader(new ReverseReceiptHeader($receiptNumber));
    }
}
