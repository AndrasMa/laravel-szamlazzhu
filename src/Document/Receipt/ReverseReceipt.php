<?php

namespace Omisai\SzamlazzhuAgent\Document\Receipt;

use Omisai\SzamlazzhuAgent\Header\ReverseReceiptHeader;

/**
 * HU: Sztornó nyugta
 */
class ReverseReceipt extends Receipt
{
    public function __construct(string $receiptNumber = '')
    {
        $this->setHeader(new ReverseReceiptHeader($receiptNumber));
    }
}
