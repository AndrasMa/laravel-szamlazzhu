<?php

namespace Omisai\Szamlazzhu;

/**
 * HU: Eladó
 */
class Seller
{
    protected string $bank;

    protected string $bankAccount;

    protected string $emailReplyTo;

    protected string $emailSubject;

    protected string $emailContent;

    protected string $signatoryName;

    public function __construct(string $bank = '', string $bankAccount = '')
    {
        $this->setBank($bank);
        $this->setBankAccount($bankAccount);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value): string
    {
        if (property_exists($this, $field)) {
            switch ($field) {
                case 'bank':
                case 'bankAccount':
                case 'emailReplyTo':
                case 'emailSubject':
                case 'emailContent':
                case 'signatoryName':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
                    break;
            }
        }

        return $value;
    }

    /**
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
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array
    {
        $data = [];

        $this->checkFields();

        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                if (SzamlaAgentUtil::isNotBlank($this->getBank())) {
                    $data['bank'] = $this->getBank();
                }
                if (SzamlaAgentUtil::isNotBlank($this->getBankAccount())) {
                    $data['bankszamlaszam'] = $this->getBankAccount();
                }

                $emailData = $this->getXmlEmailData();
                if (! empty($emailData)) {
                    $data = array_merge($data, $emailData);
                }
                if (SzamlaAgentUtil::isNotBlank($this->getSignatoryName())) {
                    $data['alairoNeve'] = $this->getSignatoryName();
                }
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                $data = $this->getXmlEmailData();
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS.": {$request->getXmlName()}");
        }

        return $data;
    }

    protected function getXmlEmailData(): array
    {
        $data = [];
        if (SzamlaAgentUtil::isNotBlank($this->getEmailReplyTo())) {
            $data['emailReplyto'] = $this->getEmailReplyTo();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getEmailSubject())) {
            $data['emailTargy'] = $this->getEmailSubject();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getEmailContent())) {
            $data['emailSzoveg'] = $this->getEmailContent();
        }

        return $data;
    }

    public function getBank(): string
    {
        return $this->bank;
    }

    public function setBank(string $bank): void
    {
        $this->bank = $bank;
    }

    public function getBankAccount(): string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(string $bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }

    public function getEmailReplyTo(): string
    {
        return $this->emailReplyTo;
    }

    public function setEmailReplyTo(string $emailReplyTo): void
    {
        $this->emailReplyTo = $emailReplyTo;
    }


    public function getEmailSubject(): string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
    }

    public function getEmailContent(): string
    {
        return $this->emailContent;
    }

    public function setEmailContent(string $emailContent): void
    {
        $this->emailContent = $emailContent;
    }

    public function getSignatoryName(): string
    {
        return $this->signatoryName;
    }

    public function setSignatoryName(string $signatoryName): void
    {
        $this->signatoryName = $signatoryName;
    }
}
