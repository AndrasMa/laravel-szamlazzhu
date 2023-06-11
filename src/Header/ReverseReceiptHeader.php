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

    /**
     * @throws SzamlaAgentException
     */
    public function checkField($field, $value): mixed
    {
        if (property_exists(get_parent_class($this), $field) || property_exists($this, $field)) {
            $required = in_array($field, $this->requiredFields);
            switch ($field) {
                case 'receiptNumber':
                case 'pdfTemplate':
                case 'callId':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }

        return $value;
    }
}
