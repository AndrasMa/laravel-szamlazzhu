<?php

namespace Omisai\Szamlazzhu;

enum PaymentMethod: string
{
    case PAYMENT_METHOD_TRANSFER = 'átutalás';

    case PAYMENT_METHOD_CASH = 'készpénz';

    case PAYMENT_METHOD_BANKCARD = 'bankkártya';

    case PAYMENT_METHOD_CHEQUE = 'csekk';

    case PAYMENT_METHOD_CASH_ON_DELIVERY = 'utánvét';

    case PAYMENT_METHOD_PAYPAL = 'PayPal';

    case PAYMENT_METHOD_SZEP_CARD = 'SZÉP kártya';

    case PAYMENT_METHOD_OTP_SIMPLE = 'OTP Simple';
}
