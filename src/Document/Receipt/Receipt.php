<?php

namespace Omisai\SzamlazzhuAgent\Document\Receipt;

use Omisai\SzamlazzhuAgent\Buyer;
use Omisai\SzamlazzhuAgent\CreditNote\ReceiptCreditNote;
use Omisai\SzamlazzhuAgent\Document\Document;
use Omisai\SzamlazzhuAgent\Header\ReceiptHeader;
use Omisai\SzamlazzhuAgent\Item\ReceiptItem;
use Omisai\SzamlazzhuAgent\Seller;
use Omisai\SzamlazzhuAgent\SzamlaAgentException;
use Omisai\SzamlazzhuAgent\SzamlaAgentRequest;
use Omisai\SzamlazzhuAgent\SzamlaAgentUtil;

/**
 * Nyugta
 */
class Receipt extends Document
{
    /**
     * Jóváírások maximális száma
     * a nyugta kifizetettségének beállításakor
     */
    const CREDIT_NOTES_LIMIT = 5;

    /**
     * A nyugta fejléc
     *
     * @var ReceiptHeader
     */
    private $header;

    /**
     * Nyugta tételek
     *
     * @var ReceiptItem[]
     */
    protected $items = [];

    /**
     * Nyugta jóváírások
     * A kifizetesek nem kötelező, de ha meg van adva, akkor az összegeknek meg kell egyezniük a nyugta végösszegével.
     *
     * @var ReceiptCreditNote[]
     */
    protected $creditNotes = [];

    /**
     * Eladói adatok
     *
     * @var Seller
     */
    protected $seller;

    /**
     * Vevő adatok
     *
     * @var Buyer
     */
    protected $buyer;

    /**
     * Nyugta létrehozása alapértelmezett fejléc adatokkal
     * (fizetési mód: átutalás, pénznem: Ft)
     *
     * @param  string  $receiptNumber nyugtaszám
     */
    public function __construct($receiptNumber = '')
    {
        if (! empty($receiptNumber)) {
            $this->setHeader(new ReceiptHeader($receiptNumber));
        }
    }

    /**
     * @return ReceiptHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader(ReceiptHeader $header)
    {
        $this->header = $header;
    }

    /**
     * Tétel hozzáadása a nyugtához
     */
    public function addItem(ReceiptItem $item)
    {
        array_push($this->items, $item);
    }

    /**
     * @return ReceiptItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param  ReceiptItem[]  $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * Jóváírás hozzáadása a nyugtához
     */
    public function addCreditNote(ReceiptCreditNote $creditNote)
    {
        if (count($this->creditNotes) < self::CREDIT_NOTES_LIMIT) {
            array_push($this->creditNotes, $creditNote);
        }
    }

    /**
     * @return ReceiptCreditNote[]
     */
    public function getCreditNotes()
    {
        return $this->creditNotes;
    }

    /**
     * @param  ReceiptCreditNote[]  $creditNotes
     */
    public function setCreditNotes(array $creditNotes)
    {
        $this->creditNotes = $creditNotes;
    }

    /**
     * @return Seller
     */
    public function getSeller()
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller)
    {
        $this->seller = $seller;
    }

    /**
     * @return Buyer
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    public function setBuyer(Buyer $buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * Összeállítja a nyugta XML adatait
     *
     *
     * @return array
     *
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request)
    {
        $fields = ['beallitasok', 'fejlec'];

        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_RECEIPT:
                $data = $this->buildFieldsData($request, array_merge($fields, ['tetelek', 'kifizetesek']));
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_RECEIPT:
            case $request::XML_SCHEMA_GET_RECEIPT:
                $data = $this->buildFieldsData($request, $fields);
                break;
            case $request::XML_SCHEMA_SEND_RECEIPT:
                $data = $this->buildFieldsData($request, array_merge($fields, ['emailKuldes']));
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS.": {$request->getXmlName()}");
        }

        return $data;
    }

    /**
     * Összeállítja és visszaadja az adott mezőkhöz tartozó adatokat
     *
     *
     * @return array
     *
     * @throws SzamlaAgentException
     */
    private function buildFieldsData(SzamlaAgentRequest $request, array $fields)
    {
        $data = [];

        if (! empty($fields)) {
            $emailSendingData = $this->buildXmlEmailSendingData();
            foreach ($fields as $key) {
                switch ($key) {
                    case 'beallitasok': $value = $request->getAgent()->getSetting()->buildXmlData($request);
                    break;
                    case 'fejlec':      $value = $this->getHeader()->buildXmlData($request);
                    break;
                    case 'tetelek':     $value = $this->buildXmlItemsData();
                    break;
                    case 'kifizetesek': $value = (! empty($this->getCreditNotes())) ? $this->buildCreditsXmlData() : null;
                    break;
                    case 'emailKuldes': $value = (! empty($emailSendingData)) ? $emailSendingData : null;
                    break;
                    default:
                        throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS.": {$key}");
                }

                if (isset($value)) {
                    $data[$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó tételek adatait
     *
     * @return array
     *
     * @throws SzamlaAgentException
     */
    protected function buildXmlItemsData()
    {
        $data = [];

        if (! empty($this->getItems())) {
            foreach ($this->getItems() as $key => $item) {
                $data["item{$key}"] = $item->buildXmlData();
            }
        }

        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó jóváírások adatait
     *
     * @return array
     *
     * @throws SzamlaAgentException
     */
    protected function buildCreditsXmlData()
    {
        $data = [];
        if (! empty($this->getCreditNotes())) {
            foreach ($this->getCreditNotes() as $key => $note) {
                $data["note{$key}"] = $note->buildXmlData();
            }
        }

        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó e-mail kiküldési adatokat
     *
     * @return array
     */
    protected function buildXmlEmailSendingData()
    {
        $data = [];

        if (SzamlaAgentUtil::isNotNull($this->getBuyer()) && SzamlaAgentUtil::isNotBlank($this->getBuyer()->getEmail())) {
            $data['email'] = $this->getBuyer()->getEmail();
        }

        if (SzamlaAgentUtil::isNotNull($this->getSeller())) {
            if (SzamlaAgentUtil::isNotBlank($this->getSeller()->getEmailReplyTo())) {
                $data['emailReplyto'] = $this->getSeller()->getEmailReplyTo();
            }
            if (SzamlaAgentUtil::isNotBlank($this->getSeller()->getEmailSubject())) {
                $data['emailTargy'] = $this->getSeller()->getEmailSubject();
            }
            if (SzamlaAgentUtil::isNotBlank($this->getSeller()->getEmailContent())) {
                $data['emailSzoveg'] = $this->getSeller()->getEmailContent();
            }
        }

        return $data;
    }
}
