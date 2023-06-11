<?php

namespace Omisai\Szamlazzhu\Waybill;

use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentRequest;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

/**
 * HU: MPL fuvarlevél
 */
class MPLWaybill extends Waybill
{
    protected string $buyerCode;

    protected string $barcode;

    /**
     * HU: A csomag tömege, tartalmazhat tizedes pontot, ha szükséges
     */
    protected string $weight;

    /**
     * HU: A különszolgáltatásokhoz megadható ikonok konfigurációja, ha nincs megadva, akkor egy ikon sem jelenik meg
     */
    protected string $service;

    protected float $insuredValue;

    protected array $requiredFields = ['buyerCode', 'barcode', 'weight'];

    public function __construct(string $destination = '', string $barcode = '', string $comment = '')
    {
        parent::__construct($destination, self::WAYBILL_TYPE_MPL, $barcode, $comment);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField(string $field, mixed $value): mixed
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->requiredFields);
            switch ($field) {
                case 'insuredValue':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'buyerCode':
                case 'weight':
                case 'service':
                case 'shippingTime':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array
    {
        $this->checkFields(get_class());
        $data = parent::buildXmlData($request);

        $data['mpl'] = [];
        $data['mpl']['vevokod'] = $this->buyerCode;
        $data['mpl']['vonalkod'] = $this->buyerCode;
        $data['mpl']['tomeg'] = $this->weight;

        if (!empty($this->service)) {
            $data['mpl']['kulonszolgaltatasok'] = $this->service;
        }

        if (!empty($this->insuredValue)) {
            $data['mpl']['erteknyilvanitas'] = $this->insuredValue;
        }

        return $data;
    }

    public function setBuyerCode(string $buyerCode): self
    {
        $this->buyerCode = $buyerCode;

        return $this;
    }

    public function setWeight(string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function setService(string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function setInsuredValue(float $insuredValue): self
    {
        $this->insuredValue = (float) $insuredValue;

        return $this;
    }
}
