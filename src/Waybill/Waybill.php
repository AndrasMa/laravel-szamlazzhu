<?php

namespace Omisai\Szamlazzhu\Waybill;

use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\SzamlaAgentRequest;
use Omisai\Szamlazzhu\SzamlaAgentUtil;

/**
 * HU: Fuvarlevél
 */
class Waybill
{
    // Transoflex
    public const WAYBILL_TYPE_TRANSOFLEX = 'Transoflex';

    // Sprinter
    public const WAYBILL_TYPE_SPRINTER = 'Sprinter';

    // Pick-Pack-Pont
    public const WAYBILL_TYPE_PPP = 'PPP';

    // Magyar Posta
    public const WAYBILL_TYPE_MPL = 'MPL';

    protected string $destination;

    /**
     * @example TOF, PPP, SPRINTER, MPL, FOXPOST, GLS, EMPTY
     */
    protected string $parcel;

    /**
     * If no specified delivery data, then the barcode will be used
     */
    protected string $barcode;

    protected string $comment;

    protected function __construct(string $destination = '', string  $parcel = '', string  $barcode = '', string  $comment = '')
    {
        $this->setDestination($destination);
        $this->setParcel($parcel);
        $this->setBarcode($barcode);
        $this->setComment($comment);
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function checkFields(): void
    {
        SzamlaAgentUtil::checkStrField('destination', $this->destination, false, self::class);
        SzamlaAgentUtil::checkStrField('parcel', $this->parcel, false, self::class);
        SzamlaAgentUtil::checkStrField('barcode', $this->barcode, false, self::class);
        SzamlaAgentUtil::checkStrField('comment', $this->comment, false, self::class);
    }

    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array
    {
        $data = [];
        $this->checkFields();

        if (SzamlaAgentUtil::isNotBlank($this->getDestination())) {
            $data['uticel'] = $this->getDestination();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getParcel())) {
            $data['futarSzolgalat'] = $this->getParcel();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getBarcode())) {
            $data['vonalkod'] = $this->getBarcode();
        }
        if (SzamlaAgentUtil::isNotBlank($this->getComment())) {
            $data['megjegyzes'] = $this->getComment();
        }

        return $data;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    public function getParcel(): string
    {
        return $this->parcel;
    }

    public function setParcel(string $parcel): void
    {
        $this->parcel = $parcel;
    }

    public function getBarcode(): string
    {
        return $this->barcode;
    }

    public function setBarcode(string $barcode): void
    {
        $this->barcode = $barcode;
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
