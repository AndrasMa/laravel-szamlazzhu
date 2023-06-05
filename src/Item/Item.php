<?php

namespace Omisai\SzamlazzhuAgent\Item;

use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

/**
 * HU: Tétel
 */
class Item
{
    /**
     * HU: Áfakulcs: tárgyi adómentes
     */
    const VAT_TAM = 'TAM';

    /**
     * HU: Áfakulcs: alanyi adómentes
     */
    const VAT_AAM = 'AAM';

    /**
     * HU: Áfakulcs: EU-n belül
     */
    const VAT_EU = 'EU';

    /**
     * HU: Áfakulcs: EU-n kívül
     */
    const VAT_EUK = 'EUK';

    /**
     * HU: Áfakulcs: mentes az adó alól
     */
    const VAT_MAA = 'MAA';

    /**
     * HU: Áfakulcs: fordított áfa
     */
    const VAT_F_AFA = 'F.AFA';

    /**
     * HU: Áfakulcs: különbözeti áfa
     */
    const VAT_K_AFA = 'K.AFA';

    /**
     * HU: Áfakulcs: áfakörön kívüli
     */
    const VAT_AKK = 'ÁKK';

    /**
     * HU: Áfakulcs: áfakörön kívüli
     */
    const VAT_TAHK = 'TAHK';

    /**
     * HU: Áfakulcs: áfakörön kívüli
     */
    const VAT_TEHK = 'TEHK';

    /**
     * HU: Áfakulcs: EU-n belüli termék értékesítés
     */
    const VAT_EUT = 'EUT';

    /**
     * HU: Áfakulcs: EU-n kívüli termék értékesítés
     */
    const VAT_EUKT = 'EUKT';

    /**
     * HU: Áfakulcs: EU-n belüli
     */
    const VAT_KBAET = 'KBAET';

    /**
     * HU: Áfakulcs: EU-n belüli
     */
    const VAT_KBAUK = 'KBAUK';

    /**
     * HU: Áfakulcs: EU-n kívüli
     */
    const VAT_EAM = 'EAM';

    /**
     * HU: Áfakulcs: Mentes az adó alól
     */
    const VAT_NAM = 'KBAUK';

    /**
     * HU: Áfakulcs: áfa tárgyi hatályán kívül
     */
    const VAT_ATK = 'ATK';

    /**
     * HU: Áfakulcs: EU-n belüli
     */
    const VAT_EUFAD37 = 'EUFAD37';

    /**
     * HU: Áfakulcs: EU-n belüli
     */
    const VAT_EUFADE = 'EUFADE';

    /**
     * HU: Áfakulcs: EU-n belüli
     */
    const VAT_EUE = 'EUE';

    /**
     * HU: Áfakulcs: EU-n kívüli
     */
    const VAT_HO = 'HO';

    /**
     * HU: Alapértelmezett ÁFA érték
     */
    const DEFAULT_VAT = '27';

    /**
     * HU: Alapértelmezett mennyiség
     */
    const DEFAULT_QUANTITY = 1.0;

    /**
     * HU: Alapértelmezett mennyiségi egység
     */
    const DEFAULT_QUANTITY_UNIT = 'db';

    protected string $id;

    protected string $name;

    protected float $quantity = self::DEFAULT_QUANTITY;

    protected string $quantityUnit = self::DEFAULT_QUANTITY_UNIT;

    protected float $netUnitPrice;

    /**
     * HU:
     *
     * Ugyanaz adható meg, mint a számlakészítés oldalon:
     * https://www.szamlazz.hu/szamla/szamlaszerkeszto
     *
     * Példa konkrét ÁFA értékre:
     * 0,5,7,18,19,20,25,27
     *
     * @var string
     */
    protected string $vat = self::DEFAULT_VAT;

    protected float $priceGapVatBase;

    protected float $netPrice;

    protected float $vatAmount;

    protected float $grossAmount;

    protected string $comment;

    protected array $requiredFields = ['name', 'quantity', 'quantityUnit', 'netUnitPrice', 'vat', 'netPrice', 'vatAmount', 'grossAmount'];

    public function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, string $vat = self::DEFAULT_VAT)
    {
        $this->setName($name);
        $this->setNetUnitPrice($netUnitPrice);
        $this->setQuantity($quantity);
        $this->setQuantityUnit($quantityUnit);
        $this->setVat($vat);
    }

    protected function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField(string $field, string $value): string
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'quantity':
                case 'netUnitPrice':
                case 'priceGapVatBase':
                case 'netPrice':
                case 'vatAmount':
                case 'grossAmount':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'name':
                case 'id':
                case 'quantityUnit':
                case 'vat':
                case 'comment':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
     * Ellenőrizzük a tulajdonságokat
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

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): void
    {
        $this->quantity = (float) $quantity;
    }

    public function getQuantityUnit(): string
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(string $quantityUnit): void
    {
        $this->quantityUnit = $quantityUnit;
    }

    public function getNetUnitPrice(): float
    {
        return $this->netUnitPrice;
    }

    public function setNetUnitPrice(float $netUnitPrice): void
    {
        $this->netUnitPrice = (float) $netUnitPrice;
    }

    public function getVat(): string
    {
        return $this->vat;
    }

    public function setVat(string $vat): void
    {
        $this->vat = $vat;
    }

    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    public function setNetPrice(float $netPrice): void
    {
        $this->netPrice = (float) $netPrice;
    }

    public function getVatAmount(): float
    {
        return $this->vatAmount;
    }

    public function setVatAmount(float $vatAmount): void
    {
        $this->vatAmount = (float) $vatAmount;
    }

    public function getGrossAmount(): float
    {
        return $this->grossAmount;
    }

    public function setGrossAmount(float $grossAmount): void
    {
        $this->grossAmount = (float) $grossAmount;
    }
}
