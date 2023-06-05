<?php

namespace Omisai\SzamlazzhuAgent;

/**
 * A Számla Agent-ben használható valuták
 */
class Currency
{
    public const CURRENCY_FT = 'Ft';

    public const CURRENCY_HUF = 'HUF';

    public const CURRENCY_EUR = 'EUR';

    public const CURRENCY_CHF = 'CHF';

    public const CURRENCY_USD = 'USD';

    public const CURRENCY_AED = 'AED';

    public const CURRENCY_AUD = 'AUD';

    public const CURRENCY_BGN = 'BGN';

    public const CURRENCY_BRL = 'BRL';

    public const CURRENCY_CAD = 'CAD';

    public const CURRENCY_CNY = 'CNY';

    public const CURRENCY_CZK = 'CZK';

    public const CURRENCY_DKK = 'DKK';

    public const CURRENCY_EEK = 'EEK';

    public const CURRENCY_GBP = 'GBP';

    public const CURRENCY_HKD = 'HKD';

    public const CURRENCY_HRK = 'HRK';

    public const CURRENCY_IDR = 'IDR';

    public const CURRENCY_ILS = 'ILS';

    public const CURRENCY_INR = 'INR';

    public const CURRENCY_ISK = 'ISK';

    public const CURRENCY_JPY = 'JPY';

    public const CURRENCY_KRW = 'KRW';

    public const CURRENCY_LTL = 'LTL';

    public const CURRENCY_LVL = 'LVL';

    public const CURRENCY_MXN = 'MXN';

    public const CURRENCY_MYR = 'MYR';

    public const CURRENCY_NOK = 'NOK';

    public const CURRENCY_NZD = 'NZD';

    public const CURRENCY_PHP = 'PHP';

    public const CURRENCY_PLN = 'PLN';

    public const CURRENCY_RON = 'RON';

    public const CURRENCY_RSD = 'RSD';

    public const CURRENCY_RUB = 'RUB';

    public const CURRENCY_SEK = 'SEK';

    public const CURRENCY_SGD = 'SGD';

    public const CURRENCY_THB = 'THB';

    public const CURRENCY_TRY = 'TRY';

    public const CURRENCY_UAH = 'UAH';

    public const CURRENCY_VND = 'VND';

    public const CURRENCY_ZAR = 'ZAR';

    public static function getDefault(): string
    {
        return self::CURRENCY_FT;
    }

    public static function getCurrencyStr($currency): string
    {
        if ($currency == null || $currency == '' || $currency === 'Ft' || $currency == 'HUF') {
            $result = 'forint';
        } else {
            switch ($currency) {
                case self::CURRENCY_EUR: $result = 'euró';
                break;
                case self::CURRENCY_USD: $result = 'amerikai dollár';
                break;
                case self::CURRENCY_AUD: $result = 'ausztrál dollár';
                break;
                case self::CURRENCY_AED: $result = 'Arab Emírségek dirham';
                break;
                case self::CURRENCY_BRL: $result = 'brazil real';
                break;
                case self::CURRENCY_CAD: $result = 'kanadai dollár';
                break;
                case self::CURRENCY_CHF: $result = 'svájci frank';
                break;
                case self::CURRENCY_CNY: $result = 'kínai jüan';
                break;
                case self::CURRENCY_CZK: $result = 'cseh korona';
                break;
                case self::CURRENCY_DKK: $result = 'dán korona';
                break;
                case self::CURRENCY_EEK: $result = 'észt korona';
                break;
                case self::CURRENCY_GBP: $result = 'angol font';
                break;
                case self::CURRENCY_HKD: $result = 'hongkongi dollár';
                break;
                case self::CURRENCY_HRK: $result = 'horvát kúna';
                break;
                case self::CURRENCY_ISK: $result = 'izlandi korona';
                break;
                case self::CURRENCY_JPY: $result = 'japán jen';
                break;
                case self::CURRENCY_LTL: $result = 'litván litas';
                break;
                case self::CURRENCY_LVL: $result = 'lett lat';
                break;
                case self::CURRENCY_MXN: $result = 'mexikói peso';
                break;
                case self::CURRENCY_NOK: $result = 'norvég koron';
                break;
                case self::CURRENCY_NZD: $result = 'új-zélandi dollár';
                break;
                case self::CURRENCY_PLN: $result = 'lengyel zloty';
                break;
                case self::CURRENCY_RON: $result = 'új román lej';
                break;
                case self::CURRENCY_RUB: $result = 'orosz rubel';
                break;
                case self::CURRENCY_SEK: $result = 'svéd koron';
                break;
                case self::CURRENCY_UAH: $result = 'ukrán hryvna';
                break;
                case self::CURRENCY_BGN: $result = 'bolgár leva';
                break;
                case self::CURRENCY_RSD: $result = 'szerb dínár';
                break;
                case self::CURRENCY_ILS: $result = 'izraeli sékel';
                break;
                case self::CURRENCY_IDR: $result = 'indonéz rúpia';
                break;
                case self::CURRENCY_INR: $result = 'indiai rúpia';
                break;
                case self::CURRENCY_TRY: $result = 'török líra';
                break;
                case self::CURRENCY_VND: $result = 'vietnámi dong';
                break;
                case self::CURRENCY_SGD: $result = 'szingapúri dollár';
                break;
                case self::CURRENCY_THB: $result = 'thai bát';
                break;
                case self::CURRENCY_KRW: $result = 'dél-koreai won';
                break;
                case self::CURRENCY_MYR: $result = 'maláj ringgit';
                break;
                case self::CURRENCY_PHP: $result = 'fülöp-szigeteki peso';
                break;
                case self::CURRENCY_ZAR: $result = 'dél-afrikai rand';
                break;
                default:
                    $result = 'ismeretlen';
                    break;
            }
        }

        return $result;
    }
}
