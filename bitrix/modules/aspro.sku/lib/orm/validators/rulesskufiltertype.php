<?php

namespace Aspro\Sku\Orm\Validators;

use Aspro\Sku\Enums;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\FieldError;
use Bitrix\Main\ORM\Fields\Validators\Validator;
use Aspro\Sku\Orm\RulesSkuTable;

class RulesSkuFilterType extends Validator
{
    public function validate($value, $primary, array $row, Field $field): bool|string
    {
        foreach (Enums\RulesSkuFilterType::cases() as $filterType) {
            if ($filterType->getName() !== $value) {
                continue;
            }

            $method = 'check'.ucfirst($filterType->getNameLowerCase()).'Field';
            if (method_exists($this, $method)) {
                return call_user_func_array([$this, $method], [...func_get_args()]);
            }
        }

        return true;
    }

    protected function checkManualField($value, $primary, array $row, Field $field): bool|string
    {
        $filterItems = empty($row['FILTER_ITEMS']) ? [] : RulesSkuTable::normalizeArray($row['FILTER_ITEMS']);
        if (empty($filterItems)) {
            return new FieldError(
                $field,
                Loc::getMessage('ERROR_FILTER_TYPE_MANUAL', [
                    '#FIELD_NAME#' => Enums\RulesSkuFilterType::getNamesWithLang()[$value]
                ]),
                'ERROR_FILTER_TYPE_MANUAL'
            );
        }

        $filterItems = RulesSkuTable::getFilteredItems($filterItems, $row);
        if (empty($filterItems)) {
            return new FieldError(
                $field,
                Loc::getMessage('ERROR_FILTER_TYPE_MANUAL_INVALID_ITEMS'),
                'ERROR_FILTER_TYPE_MANUAL_INVALID_ITEMS'
            );
        }

        return true;
    }

    protected function checkFilterField($value, $primary, array $row, Field $field): bool|string
    {
        if (empty($row['FILTER_SECTION_ID']) && empty($row['FILTER_PROPERTY'])) {
            return new FieldError(
                $field,
                Loc::getMessage('ERROR_FILTER_TYPE_FILTER', [
                    '#FIELD_NAME#' => Enums\RulesSkuFilterType::getNamesWithLang()[$value]
                ]),
                'ERROR_FILTER_TYPE_FILTER'
            );
        }

        return true;
    }
}
