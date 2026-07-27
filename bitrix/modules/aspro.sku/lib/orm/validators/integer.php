<?php

namespace Aspro\Sku\Orm\Validators;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\FieldError;

class Integer
{
    public static function positiveValue($value, $primary, $row, Field $field): bool|string
    {
        if ((int) $value < 0) {
            return new FieldError($field, Loc::getMessage('ERROR_VALUE_MUST_BE_POSITIVE', ['#FIELD_NAME#' => $field->getTitle()]), 'ERROR_POSITIVE_VALUE');
        }

        return true;
    }

    public static function greaterThanZero($value, $primary, $row, Field $field): bool|string
    {
        if ((int) $value <= 0) {
            return new FieldError($field, Loc::getMessage('ERROR_VALUE_MUST_BE_GREATER_THAN_ZERO', ['#FIELD_NAME#' => $field->getTitle()]), 'ERROR_GREATER_THAN_ZERO');
        }

        return true;
    }
}
