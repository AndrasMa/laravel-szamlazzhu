<?php

namespace Omisai\Szamlazzhu;

enum Currency: string
{
    case FT = 'Ft';

    case HUF = 'HUF';

    case EUR = 'EUR';

    case CHF = 'CHF';

    case USD = 'USD';

    case AED = 'AED';

    case AUD = 'AUD';

    case BGN = 'BGN';

    case BRL = 'BRL';

    case CAD = 'CAD';

    case CNY = 'CNY';

    case CZK = 'CZK';

    case DKK = 'DKK';

    case EEK = 'EEK';

    case GBP = 'GBP';

    case HKD = 'HKD';

    case HRK = 'HRK';

    case IDR = 'IDR';

    case ILS = 'ILS';

    case INR = 'INR';

    case ISK = 'ISK';

    case JPY = 'JPY';

    case KRW = 'KRW';

    case LTL = 'LTL';

    case LVL = 'LVL';

    case MXN = 'MXN';

    case MYR = 'MYR';

    case NOK = 'NOK';

    case NZD = 'NZD';

    case PHP = 'PHP';

    case PLN = 'PLN';

    case RON = 'RON';

    case RSD = 'RSD';

    case RUB = 'RUB';

    case SEK = 'SEK';

    case SGD = 'SGD';

    case THB = 'THB';

    case TRY = 'TRY';

    case UAH = 'UAH';

    case VND = 'VND';

    case ZAR = 'ZAR';

    public static function getDefault(): self
    {
        return self::FT;
    }

    public static function getCurrencyName(Currency $currency): string
    {
        if ($currency == null || $currency == '' || $currency === 'Ft' || $currency == 'HUF') {
            $result = 'forint';
        } else {
            switch ($currency) {
                case self::FT:
                case self::HUF:
                    $result = 'forint';
                    break;
                case self::EUR:
                    $result = 'euró';
                    break;
                case self::USD:
                    $result = 'amerikai dollár';
                    break;
                case self::AUD:
                    $result = 'ausztrál dollár';
                    break;
                case self::AED:
                    $result = 'Arab Emírségek dirham';
                    break;
                case self::BRL:
                    $result = 'brazil real';
                    break;
                case self::CAD:
                    $result = 'kanadai dollár';
                    break;
                case self::CHF:
                    $result = 'svájci frank';
                    break;
                case self::CNY:
                    $result = 'kínai jüan';
                    break;
                case self::CZK:
                    $result = 'cseh korona';
                    break;
                case self::DKK:
                    $result = 'dán korona';
                    break;
                case self::EEK:
                    $result = 'észt korona';
                    break;
                case self::GBP:
                    $result = 'angol font';
                    break;
                case self::HKD:
                    $result = 'hongkongi dollár';
                    break;
                case self::HRK:
                    $result = 'horvát kúna';
                    break;
                case self::ISK:
                    $result = 'izlandi korona';
                    break;
                case self::JPY:
                    $result = 'japán jen';
                    break;
                case self::LTL:
                    $result = 'litván litas';
                    break;
                case self::LVL:
                    $result = 'lett lat';
                    break;
                case self::MXN:
                    $result = 'mexikói peso';
                    break;
                case self::NOK:
                    $result = 'norvég koron';
                    break;
                case self::NZD:
                    $result = 'új-zélandi dollár';
                    break;
                case self::PLN:
                    $result = 'lengyel zloty';
                    break;
                case self::RON:
                    $result = 'új román lej';
                    break;
                case self::RUB:
                    $result = 'orosz rubel';
                    break;
                case self::SEK:
                    $result = 'svéd koron';
                    break;
                case self::UAH:
                    $result = 'ukrán hryvna';
                    break;
                case self::BGN:
                    $result = 'bolgár leva';
                    break;
                case self::RSD:
                    $result = 'szerb dínár';
                    break;
                case self::ILS:
                    $result = 'izraeli sékel';
                    break;
                case self::IDR:
                    $result = 'indonéz rúpia';
                    break;
                case self::INR:
                    $result = 'indiai rúpia';
                    break;
                case self::TRY:
                    $result = 'török líra';
                    break;
                case self::VND:
                    $result = 'vietnámi dong';
                    break;
                case self::SGD:
                    $result = 'szingapúri dollár';
                    break;
                case self::THB:
                    $result = 'thai bát';
                    break;
                case self::KRW:
                    $result = 'dél-koreai won';
                    break;
                case self::MYR:
                    $result = 'maláj ringgit';
                    break;
                case self::PHP:
                    $result = 'fülöp-szigeteki peso';
                    break;
                case self::ZAR:
                    $result = 'dél-afrikai rand';
                    break;
                default:
                    $result = 'ismeretlen';
                    break;
            }
        }

        return $result;
    }
}
