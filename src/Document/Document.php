<?php

namespace Omisai\Szamlazzhu\Document;

use Omisai\Szamlazzhu\Currency;
use Omisai\Szamlazzhu\Language;

/**
 * Bizonylat
 */
class Document
{
    /**
     * Fizetési módok
     */
    public const PAYMENT_METHOD_TRANSFER = 'átutalás';

    public const PAYMENT_METHOD_CASH = 'készpénz';

    public const PAYMENT_METHOD_BANKCARD = 'bankkártya';

    public const PAYMENT_METHOD_CHEQUE = 'csekk';

    public const PAYMENT_METHOD_CASH_ON_DELIVERY = 'utánvét';

    public const PAYMENT_METHOD_PAYPAL = 'PayPal';

    public const PAYMENT_METHOD_SZEP_CARD = 'SZÉP kártya';

    public const PAYMENT_METHOD_OTP_SIMPLE = 'OTP Simple';

    /**
     * HU: Normál számla
     */
    public const DOCUMENT_TYPE_INVOICE = 'invoice';

    /**
     * HU: Normál számla kódja
     */
    public const DOCUMENT_TYPE_INVOICE_CODE = 'SZ';

    /**
     * HU: Sztornó számla
     */
    public const DOCUMENT_TYPE_REVERSE_INVOICE = 'reverseInvoice';

    /**
     * HU: Sztornó számla kódja
     */
    public const DOCUMENT_TYPE_REVERSE_INVOICE_CODE = 'SS';

    /**
     * HU: Jóváíró számla
     */
    public const DOCUMENT_TYPE_PAY_INVOICE = 'payInvoice';

    /**
     * HU: Jóváíró számla kódja
     */
    public const DOCUMENT_TYPE_PAY_INVOICE_CODE = 'JS';

    /**
     * HU: Helyesbítő számla
     */
    public const DOCUMENT_TYPE_CORRECTIVE_INVOICE = 'correctiveInvoice';

    /**
     * HU: Helyesbítő számla kódja
     */
    public const DOCUMENT_TYPE_CORRECTIVE_INVOICE_CODE = 'HS';

    /**
     * HU: Előlegszámla
     */
    public const DOCUMENT_TYPE_PREPAYMENT_INVOICE = 'prePaymentInvoice';

    /**
     * HU: Előlegszámla kódja
     */
    public const DOCUMENT_TYPE_PREPAYMENT_INVOICE_CODE = 'ES';

    /**
     * HU: Végszámla
     */
    public const DOCUMENT_TYPE_FINAL_INVOICE = 'finalInvoice';

    /**
     * HU: Végszámla kódja
     */
    public const DOCUMENT_TYPE_FINAL_INVOICE_CODE = 'VS';

    /**
     * HU: Díjbekérő
     */
    public const DOCUMENT_TYPE_PROFORMA = 'proforma';

    /**
     * HU: Díjbekérő kódja
     */
    public const DOCUMENT_TYPE_PROFORMA_CODE = 'D';

    /**
     * HU: Szállítólevél
     */
    public const DOCUMENT_TYPE_DELIVERY_NOTE = 'deliveryNote';

    /**
     * HU: Szállítólevél kódja
     */
    public const DOCUMENT_TYPE_DELIVERY_NOTE_CODE = 'SL';

    /**
     * HU: Nyugta
     */
    public const DOCUMENT_TYPE_RECEIPT = 'receipt';

    /**
     * HU: Nyugta kódja
     */
    public const DOCUMENT_TYPE_RECEIPT_CODE = 'NY';

    /**
     * HU: Nyugta sztornó
     */
    public const DOCUMENT_TYPE_RESERVE_RECEIPT = 'reserveReceipt';

    /**
     * HU: Nyugta sztornó kódja
     */
    public const DOCUMENT_TYPE_RESERVE_RECEIPT_CODE = 'SN';

    public static function getDefaultCurrency(): string
    {
        return Currency::getDefault();
    }

    public static function getDefaultLanguage(): string
    {
        return Language::getDefault();
    }
}
