<?php

namespace Omisai\Szamlazzhu\Ledger;

use Omisai\Szamlazzhu\HasXmlBuildInterface;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

class InvoiceItemLedger extends ItemLedger implements HasXmlBuildInterface
{
    protected string $economicEventType;

    protected string $vatEconomicEventType;

    protected string $settlementPeriodStart;

    protected string $settlementPeriodEnd;

    public function __construct(string $economicEventType = '', string $vatEconomicEventType = '', string $revenueLedgerNumber = '', string $vatLedgerNumber = '')
    {
        parent::__construct((string) $revenueLedgerNumber, (string) $vatLedgerNumber);
        $this->setEconomicEventType($economicEventType);
        $this->setVatEconomicEventType($vatEconomicEventType);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField(string $field, mixed $value): mixed
    {
        if (property_exists($this, $field)) {
            switch ($field) {
                case 'settlementPeriodStart':
                case 'settlementPeriodEnd':
                    SzamlaAgentUtil::checkDateField($field, $value, false, __CLASS__);
                    break;
                case 'economicEventType':
                case 'vatEconomicEventType':
                case 'revenueLedgerNumber':
                case 'vatLedgerNumber':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     *
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

        if (!empty($this->economicEventType)) {
            $data['gazdasagiEsem'] = $this->economicEventType;
        }
        if (!empty($this->vatEconomicEventType)) {
            $data['gazdasagiEsemAfa'] = $this->vatEconomicEventType;
        }
        if (!empty($this->revenueLedgerNumber)) {
            $data['arbevetelFokonyviSzam'] = $this->revenueLedgerNumber;
        }
        if (!empty($this->vatLedgerNumber)) {
            $data['afaFokonyviSzam'] = $this->vatLedgerNumber;
        }
        if (!empty($this->settlementPeriodStart)) {
            $data['elszDatumTol'] = $this->settlementPeriodStart;
        }
        if (!empty($this->settlementPeriodEnd)) {
            $data['elszDatumIg'] = $this->settlementPeriodEnd;
        }

        return $data;
    }

    public function setEconomicEventType(string $economicEventType): self
    {
        $this->economicEventType = $economicEventType;

        return $this;
    }

    public function setVatEconomicEventType(string $vatEconomicEventType): self
    {
        $this->vatEconomicEventType = $vatEconomicEventType;

        return $this;
    }

    public function setSettlementPeriodStart(string $settlementPeriodStart): self
    {
        $this->settlementPeriodStart = $settlementPeriodStart;

        return $this;
    }

    public function setSettlementPeriodEnd(string $settlementPeriodEnd): self
    {
        $this->settlementPeriodEnd = $settlementPeriodEnd;

        return $this;
    }
}
