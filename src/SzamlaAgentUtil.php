<?php

namespace Omisai\SzamlazzhuAgent;

use Illuminate\Support\Facades\Log;

class SzamlaAgentUtil
{
    public const DEFAULT_ADDED_DAYS = 8;

    public const DATE_FORMAT_DATE = 'date';

    public const DATE_FORMAT_DATETIME = 'datetime';

    public const DATE_FORMAT_TIMESTAMP = 'timestamp';

    /**
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public static function addDaysToDate(int $count, string|null $date = null)
    {
        if (empty($date)) {
            $newDate = new \DateTime('now');
        } else {
            $newDate = new \DateTime($date);
        }

        $newDate->modify("+{$count} day");

        return self::getDateStr($newDate);
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function getDateStr(\DateTime $date, string $format = self::DATE_FORMAT_DATE): string
    {
        switch ($format) {
            case self::DATE_FORMAT_DATE:
                $result = $date->format('Y-m-d');
                break;
            case self::DATE_FORMAT_DATETIME:
                $result = $date->format('Y-m-d H:i:s');
                break;
            case self::DATE_FORMAT_TIMESTAMP:
                $result = $date->getTimestamp();
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::DATE_FORMAT_NOT_EXISTS.': '.$format);
        }

        return $result;
    }

    /**
     * @throws \ReflectionException
     */
    public static function getXmlFileName(string $prefix, string $name, object $entity = null)
    {
        if (!empty($name) && !empty($entity)) {
            $name .= '-'.(new \ReflectionClass($entity))->getShortName();
        }

        return  $prefix.'-'.strtolower($name).'-'.self::getDateTimeWithMilliseconds().'.xml';
    }

    public static function getDateTimeWithMilliseconds(): string
    {
        return date('YmdHis').substr(microtime(false), 2, 5);
    }

    public static function formatXml(\SimpleXMLElement $simpleXMLElement): \DOMDocument
    {
        $xmlDocument = new \DOMDocument('1.0');
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        $xmlDocument->loadXML($simpleXMLElement->asXML());

        return $xmlDocument;
    }

    public static function formatResponseXml(string $response): \DOMDocument
    {
        $xmlDocument = new \DOMDocument('1.0');
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        $xmlDocument->loadXML($response);

        return $xmlDocument;
    }

    public static function checkValidXml($xmlContent): array
    {
        libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xmlContent);

        $result = libxml_get_errors();
        libxml_clear_errors();

        return $result;
    }

    public static function toJson($data): mixed
    {
        return json_encode($data);
    }

    public static function toArray($data): mixed
    {
        return json_decode(self::toJson($data), true);
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function doubleFormat($value): float
    {
        if (is_int($value)) {
            $value = floatval($value);
        }

        if (is_float($value)) {
            $decimals = strlen(preg_replace('/[\d]+[\.]?/', '', $value, 1));
            if ($decimals == 0) {
                $value = number_format((float) $value, 1, '.', '');
            }
        } else {
            Log::channel('szamlazzhu')->warning(sprintf('Invalid type! Instead of double got %s type at value of %s', gettype($value), $value));
        }

        return $value;
    }

    public static function isBlank(mixed $value): bool
    {
        return is_null($value) || (is_string($value) && $value !== '0' && (empty($value) || trim($value) == ''));
    }

    public static function isNotBlank(mixed $value): bool
    {
        return ! self::isBlank($value);
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkStrField(string $field, mixed $value, bool $required, string $class): void
    {
        $errorMessage = '';
        if (isset($value) && ! is_string($value)) {
            $errorMessage = "A(z) '{$field}' mező értéke nem szöveg!";
        } elseif ($required && self::isBlank($value)) {
            $errorMessage = self::getRequiredFieldErrMsg($field);
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkStrFieldWithRegExp(string $field, mixed $value, bool $required, string $class, string $pattern): void
    {
        $errorMessage = '';
        self::checkStrField($field, $value, $required, __CLASS__);

        if (! preg_match($pattern, $value)) {
            $errorMessage = "A(z) '{$field}' mező értéke nem megfelelő!";
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkIntField(string $field, mixed $value, bool $required, string $class): void
    {
        $errorMessage = '';
        if (isset($value) && ! is_int($value)) {
            $errorMessage = "A(z) '{$field}' mező értéke nem egész szám!";
        } elseif ($required && ! is_numeric($value)) {
            $errorMessage = self::getRequiredFieldErrMsg($field);
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkDoubleField(string $field, mixed $value, bool $required, string $class): void
    {
        $errorMessage = '';
        if (isset($value) && ! is_float($value)) {
            $errorMessage = "A(z) '{$field}' mező értéke nem double!";
        } elseif ($required && ! is_numeric($value)) {
            $errorMessage = self::getRequiredFieldErrMsg($field);
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkDateField(string $field, mixed $value, bool $required, string $class): void
    {
        $errorMessage = '';
        if (isset($value) && !self::isValidDate($value)) {
            if ($required) {
                $errorMessage = "A(z) '{$field}' kötelező mező, de nem érvényes dátumot tartalmaz!";
            } else {
                $errorMessage = "A(z) '{$field}' mező értéke nem dátum!";
            }
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    public static function isValidDate($date): bool
    {
        $parsedDate = \DateTime::createFromFormat('Y-m-d', $date);

        if (is_array(\DateTime::getLastErrors()) && \DateTime::getLastErrors()['warning_count'] > 0) {
            return false;
        }

        if (!checkdate($parsedDate->format('m'), $parsedDate->format('d'), $parsedDate->format('Y'))) {
            return false;
        }

        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $parsedDate->format('Y-m-d'))) {
            return false;
        }

        return true;
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function checkBoolField(string $field, mixed $value, bool $required, string $class): void
    {
        $errorMessage = '';
        if (isset($value) && is_bool($value) === false) {
            if ($required) {
                $errorMessage = "The '{$field}' field is required, but the value is not logical!";
            } else {
                $errorMessage = "The '{$field}' value is not logical!";
            }
        }

        if (! empty($errorMessage)) {
            throw new SzamlaAgentException(SzamlaAgentException::FIELDS_CHECK_ERROR.": {$errorMessage} (".$class.')');
        }
    }

    public static function getRequiredFieldErrMsg(string $field): string
    {
        return "The '{$field}' field is required, but it has no value!";
    }

    public static function isNotNull($value): bool
    {
        return null !== $value;
    }

    public static function addChildArray(\SimpleXMLElement $xmlNode, string $name, array $data): void
    {
        $node = $xmlNode->addChild($name);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                self::addChildArray($node, $key, $value);
            } else {
                $node->addChild($key, $value);
            }
        }
    }

    /**
     * @return \SimpleXMLElement $xmlNode
     */
    public static function removeNamespaces(\SimpleXMLElement $xmlNode)
    {
        $xmlString = $xmlNode->asXML();
        $cleanedXmlString = preg_replace('/(<\/|<)[a-z0-9]+:([a-z0-9]+[ =>])/i', '$1$2', $xmlString);
        $cleanedXmlNode = simplexml_load_string($cleanedXmlString);

        return $cleanedXmlNode;
    }

    /**
     * @throws SzamlaAgentException
     */
    public static function isValidJSON($string): mixed
    {
        // decode the JSON data
        $result = json_decode($string);
        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = '';
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
                // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            throw new SzamlaAgentException($error);
        }

        return $result;
    }
}
