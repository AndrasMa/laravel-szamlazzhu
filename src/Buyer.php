<?php

namespace Omisai\Szamlazzhu;

/**
 * HU: Vevő
 */
class Buyer
{
    protected string $id;

    protected string $name;

    protected string $country;

    protected string $zipCode;

    protected string $city;

    protected string $address;

    /**
     * If email address is given, the document will be sent to this email address by Számlázz.hu
     * In case of a test account, the system will not send an email for security reasons
     */
    protected string $email;

    protected bool $sendEmail = true;

    protected int $taxPayer; //TODO: Use the TaxPayer object instead

    protected string $taxNumber;

    protected string $groupIdentifier;

    protected string $taxNumberEU;

    /**
     * Postal data is optional
     */
    protected string $postalName;

    /**
     * Postal data is optional
     */
    protected string $postalCountry;

    /**
     * Postal data is optional
     */
    protected string $postalZip;

    /**
     * Postal data is optional
     */
    protected string $postalCity;

    /**
     * Postal data is optional
     */
    protected string $postalAddress;

    /**
     * HU: Vevő főkönyvi adatai
     */
    protected BuyerLedger $ledgerData;

    /**
     * If enabled on the settings page (https://www.szamlazz.hu/szamla/beallitasok)
     * this name will appear below the signature line.
     */
    protected string $signatoryName;

    protected string $phone;

    protected string $comment;

    protected array $requiredFields = ['name', 'zip', 'city', 'address'];

    public function __construct(string $name = '', string $zipCode = '', string $city = '', string $address = '')
    {
        $this->setName($name);
        $this->setZipCode($zipCode);
        $this->setCity($city);
        $this->setAddress($address);
    }

    protected function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    protected function setRequiredFields(array $requiredFields): void
    {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'taxPayer':
                    SzamlaAgentUtil::checkIntField($field, $value, $required, __CLASS__);
                    break;
                case 'sendEmail':
                    SzamlaAgentUtil::checkBoolField($field, $value, $required, __CLASS__);
                    break;
                case 'id':
                case 'email':
                case 'name':
                case 'country':
                case 'zipCode':
                case 'city':
                case 'address':
                case 'taxNumber':
                case 'groupIdentifier':
                case 'taxNumberEU':
                case 'postalName':
                case 'postalCountry':
                case 'postalZip':
                case 'postalCity':
                case 'postalAddress':
                case 'signatoryName':
                case 'phone':
                case 'comment':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
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
     * Generates the XML data of the customer, based on the XML schema defined in the request
     *
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array
    {
        $data = [];
        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                $data = [
                    'nev' => $this->getName(),
                    'orszag' => $this->getCountry(),
                    'irsz' => $this->getZipCode(),
                    'telepules' => $this->getCity(),
                    'cim' => $this->getAddress(),
                ];

                if (SzamlaAgentUtil::isNotBlank($this->getEmail())) {
                    $data['email'] = $this->getEmail();
                }

                $data['sendEmail'] = $this->isSendEmail() ? true : false;

                if (SzamlaAgentUtil::isNotBlank($this->getTaxPayer())) {
                    $data['adoalany'] = $this->getTaxPayer();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumber())) {
                    $data['adoszam'] = $this->getTaxNumber();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getGroupIdentifier())) {
                    $data['csoportazonosito'] = $this->getGroupIdentifier();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumberEU())) {
                    $data['adoszamEU'] = $this->getTaxNumberEU();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPostalName())) {
                    $data['postazasiNev'] = $this->getPostalName();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPostalCountry())) {
                    $data['postazasiOrszag'] = $this->getPostalCountry();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPostalZip())) {
                    $data['postazasiIrsz'] = $this->getPostalZip();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPostalCity())) {
                    $data['postazasiTelepules'] = $this->getPostalCity();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPostalAddress())) {
                    $data['postazasiCim'] = $this->getPostalAddress();
                }

                if (SzamlaAgentUtil::isNotNull($this->getLedgerData())) {
                    $data['vevoFokonyv'] = $this->getLedgerData()->getXmlData();
                }

                if (SzamlaAgentUtil::isNotBlank($this->getId())) {
                    $data['azonosito'] = $this->getId();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getSignatoryName())) {
                    $data['alairoNeve'] = $this->getSignatoryName();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getPhone())) {
                    $data['telefonszam'] = $this->getPhone();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getComment())) {
                    $data['megjegyzes'] = $this->getComment();
                }
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                if (SzamlaAgentUtil::isNotBlank($this->getEmail())) {
                    $data['email'] = $this->getEmail();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumber())) {
                    $data['adoszam'] = $this->getTaxNumber();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumberEU())) {
                    $data['adoszamEU'] = $this->getTaxNumberEU();
                }
                break;
            default:
                throw new SzamlaAgentException("Nincs ilyen XML séma definiálva: {$request->getXmlName()}");
        }
        $this->checkFields();

        return $data;
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

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    public function setSendEmail(bool $sendEmail): void
    {
        $this->sendEmail = $sendEmail;
    }

    public function getTaxPayer(): int
    {
        return $this->taxPayer;
    }

    public function setTaxPayer(int $taxPayer): void
    {
        $this->taxPayer = $taxPayer;
    }

    public function getTaxNumber(): string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    public function getGroupIdentifier(): string
    {
        return $this->groupIdentifier;
    }

    public function setGroupIdentifier(string $groupIdentifier): void
    {
        $this->groupIdentifier = $groupIdentifier;
    }

    public function getTaxNumberEU(): string
    {
        return $this->taxNumberEU;
    }

    public function setTaxNumberEU(string $taxNumberEU): void
    {
        $this->taxNumberEU = $taxNumberEU;
    }

    public function getPostalName(): string
    {
        return $this->postalName;
    }

    /**
     * Postal data is optional
     */
    public function setPostalName(string $postalName): void
    {
        $this->postalName = $postalName;
    }

    public function getPostalCountry(): string
    {
        return $this->postalCountry;
    }

    /**
     * Postal data is optional
     */
    public function setPostalCountry(string $postalCountry): void
    {
        $this->postalCountry = $postalCountry;
    }

    public function getPostalZip(): string
    {
        return $this->postalZip;
    }

    /**
     * Postal data is optional
     */
    public function setPostalZip(string $postalZip): void
    {
        $this->postalZip = $postalZip;
    }

    public function getPostalCity(): string
    {
        return $this->postalCity;
    }

    /**
     * Postal data is optional
     */
    public function setPostalCity(string $postalCity): void
    {
        $this->postalCity = $postalCity;
    }

    public function getPostalAddress(): string
    {
        return $this->postalAddress;
    }

    /**
     * Postal data is optional
     */
    public function setPostalAddress(string $postalAddress): void
    {
        $this->postalAddress = $postalAddress;
    }

    public function getLedgerData(): BuyerLedger
    {
        return $this->ledgerData;
    }

    public function setLedgerData(BuyerLedger $ledgerData): void
    {
        $this->ledgerData = $ledgerData;
    }

    public function getSignatoryName(): string
    {
        return $this->signatoryName;
    }

    /**
     * If enabled on the settings page (https://www.szamlazz.hu/szamla/beallitasok)
     * this name will appear below the signature line.
     */
    public function setSignatoryName(string $signatoryName): void
    {
        $this->signatoryName = $signatoryName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
