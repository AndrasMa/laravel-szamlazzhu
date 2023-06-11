<?php

namespace Omisai\Szamlazzhu\Header;

use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentUtil;
use Omisai\Szamlazzhu\Header\Type;

class ReverseReceiptHeader extends ReceiptHeader
{
    protected array $requiredFields = ['receiptNumber'];

    public function __construct(string $receiptNumber = '')
    {
        parent::__construct($receiptNumber);
        $this->setType(Type::REVERSE_RECEIPT);
    }
}
