<?php

namespace Omisai\Szamlazzhu;

/**
 * HU: Adózó (adóalany)
 */
class TaxPayer
{
    /**
     * Non-EU enterprise
     */
    public const TAXPAYER_NON_EU_ENTERPRISE = 7;

    /**
     * EU enterprise
     */
    public const TAXPAYER_EU_ENTERPRISE = 6;

    /**
     * has a Hungarian tax number
     */
    public const TAXPAYER_HAS_TAXNUMBER = 1;

    /**
     * we don't know
     */
    public const TAXPAYER_WE_DONT_KNOW = 0;

    /**
     * no tax number
     */
    public const TAXPAYER_NO_TAXNUMBER = -1;

    /**
     * @var string
     */
    protected string $taxPayerId;

    /**
     * @var int
     */
    protected int $taxPayerType;

    /**
     * @var array
     */
    protected array $requiredFields = ['taxPayerId'];

    /**
     * @param  string  $taxpayerId
     * @param  int  $taxPayerType
     */
    public function __construct($taxpayerId = '', $taxPayerType = self::TAXPAYER_WE_DONT_KNOW)
    {
        $this->setTaxPayerId($taxpayerId);
        $this->setTaxPayerType($taxPayerType);
    }

    /**
     * @return array
     */
    protected function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    protected function setRequiredFields(array $requiredFields)
    {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @return int
     */
    public function getDefault(): int
    {
        return self::TAXPAYER_WE_DONT_KNOW;
    }

    /**
     * Validates the field type
     *
     * @param  string  $field
     * @param  mixed  $value
     * @return string
     *
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value)
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'taxPayerType':
                    SzamlaAgentUtil::checkIntField($field, $value, $required, __CLASS__);
                    break;
                case 'taxPayerId':
                    SzamlaAgentUtil::checkStrFieldWithRegExp($field, $value, false, __CLASS__, '/[0-9]{8}/');
                    break;
            }
        }

        return $value;
    }

    /**
     * Validates the attributes
     *
     * @throws SzamlaAgentException
     */
    protected function checkFields()
    {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * Creates the Taxpayer's XML data
     *
     * @return array
     *
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request)
    {
        $this->checkFields();

        $data = [];
        $data['beallitasok'] = $request->getAgent()->getSetting()->buildXmlData($request);
        $data['torzsszam'] = $this->getTaxPayerId();

        return $data;
    }

    /**
     * @return string
     */
    public function getTaxPayerId()
    {
        return $this->taxPayerId;
    }

    /**
     * @param  string  $taxPayerId
     */
    public function setTaxPayerId(string $taxPayerId)
    {
        $this->taxPayerId = substr($taxPayerId, 0, 8);
    }

    /**
     * @return int
     */
    public function getTaxPayerType(): int
    {
        return $this->taxPayerType;
    }

    /**
     * Taxpayer type
     *
     * This information is stored as data by the partner in the system and can be modified there.
     *
     * This field can take the following values:
     *  7: TaxPayer::TAXPAYER_NON_EU_ENTERPRISE - Non-EU enterprise
     *  6: TaxPayer::TAXPAYER_EU_ENTERPRISE     - EU enterprise
     *  1: TaxPayer::TAXPAYER_HAS_TAXNUMBER     - has a Hungarian tax number
     *  0: TaxPayer::TAXPAYER_WE_DONT_KNOW      - we don't know
     * -1: TaxPayer::TAXPAYER_NO_TAXNUMBER      - no tax number
     *
     * @see https://tudastar.szamlazz.hu/gyik/vevo-adoszama-szamlan
     *
     * @param  int  $taxPayerType
     */
    public function setTaxPayerType($taxPayerType)
    {
        $this->taxPayerType = $taxPayerType;
    }
}
