<?php

namespace Omisai\Szamlazzhu\Ledger;

use Omisai\Szamlazzhu\HasXmlBuildInterface;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

class ReceiptItemLedger extends ItemLedger implements HasXmlBuildInterface
{
    /**
     * @throws SzamlaAgentException
     */
    protected function checkField(string $field, mixed $value): mixed
    {
        if (property_exists($this, $field)) {
            switch ($field) {
                case 'revenueLedgerNumber':
                case 'vatLedgerNumber':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkFields(): void
    {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {
        $data = [];
        $this->checkFields();

        if (!empty($this->revenueLedgerNumber)) {
            $data['arbevetel'] = $this->revenueLedgerNumber;
        }
        if (!empty($this->vatLedgerNumber)) {
            $data['afa'] = $this->vatLedgerNumber;
        }

        return $data;
    }
}
