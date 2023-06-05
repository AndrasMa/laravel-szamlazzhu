<?php

namespace Omisai\Szamlazzhu\Document\Receipt;

use Omisai\Szamlazzhu\Header\ReverseReceiptHeader;

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
