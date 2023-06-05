<?php

namespace Omisai\Szamlazzhu;

class Language
{
    public const LANGUAGE_HU = 'hu';

    public const LANGUAGE_EN = 'en';

    public const LANGUAGE_DE = 'de';

    public const LANGUAGE_IT = 'it';

    public const LANGUAGE_RO = 'ro';

    public const LANGUAGE_SK = 'sk';

    public const LANGUAGE_HR = 'hr';

    public const LANGUAGE_FR = 'fr';

    public const LANGUAGE_ES = 'es';

    public const LANGUAGE_CZ = 'cz';

    public const LANGUAGE_PL = 'pl';

    /**
     * Számlázz.hu rendszerében használható nyelvek
     *
     * @var array
     */
    protected static $availableLanguages = [
        self::LANGUAGE_HU, self::LANGUAGE_EN, self::LANGUAGE_DE, self::LANGUAGE_IT,
        self::LANGUAGE_RO, self::LANGUAGE_SK, self::LANGUAGE_HR, self::LANGUAGE_FR,
        self::LANGUAGE_ES, self::LANGUAGE_CZ, self::LANGUAGE_PL,
    ];

    public static function getDefault(): string
    {
        return self::LANGUAGE_HU;
    }

    /**
     * @throws \ReflectionException
     */
    public static function getAll(): array
    {
        $reflector = new \ReflectionClass(new Language());
        $languageConstants = $reflector->getConstants();

        $languages = [];
        foreach ($languageConstants as $languageSymbol) {
            $languages[] = $languageSymbol;
        }

        return $languages;
    }

    /**
     * @return string
     */
    public static function getLanguageStr($language): string
    {
        if ($language == null || $language == '' || $language === self::LANGUAGE_HU) {
            $result = 'magyar';
        } else {
            switch ($language) {
                case self::LANGUAGE_EN: $result = 'angol';
                    break;
                case self::LANGUAGE_DE: $result = 'német';
                    break;
                case self::LANGUAGE_IT: $result = 'olasz';
                    break;
                case self::LANGUAGE_RO: $result = 'román';
                    break;
                case self::LANGUAGE_SK: $result = 'szlovák';
                    break;
                case self::LANGUAGE_HR: $result = 'horvát';
                    break;
                case self::LANGUAGE_FR: $result = 'francia';
                    break;
                case self::LANGUAGE_ES: $result = 'spanyol';
                    break;
                case self::LANGUAGE_CZ: $result = 'cseh';
                    break;
                case self::LANGUAGE_PL: $result = 'lengyel';
                    break;
                default:
                    $result = 'ismeretlen';
                    break;
            }
        }

        return $result;
    }

    public function getAvailableLanguages(): array
    {
        return self::$availableLanguages;
    }
}
