<?php

namespace Aspro\Smartseo\Condition\Entities\Iblock;

use Aspro\Smartseo\General\Smartseo,
	Bitrix\Main\Localization\Loc;

class Helper
{
	/** = (equal) **/
	const LOGIC_EQ = 'Equal';
	/** != (not equal)  */
	const LOGIC_NOT_EQ = 'Not';
	/** > (great) */
	const LOGIC_GR = 'Great';
	/** < (less) */
	const LOGIC_LS = 'Less';
	/** >= (great or equal) */
	const LOGIC_EGR = 'EqGr';
	/** <= (less or equal) */
	const LOGIC_ELS = 'EqLs';
	/** contain */
	const LOGIC_CONT = 'Contain';
	/** not contain */
	const LOGIC_NOT_CONT = 'NotCont';
	/** AND */
	const LOGIC_AND = 'AND';
	/** OR */
	const LOGIC_OR = 'OR';

	static public function appendSelect(&$query, $field, $alias = '')
	{
		$query->addSelect($field, $alias);
	}

	static public function appendOrderBy(&$query, $field)
	{
		$query->addOrder($field);
	}

	static public function appendGroupBy(&$query, $field)
	{
		$query->addGroup($field);
	}

	static public function appendWhereNumberProperty(&$queryWhere, $property, $logics, $alias = '')
	{
		$whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

		$egrValue = 0;
		$elsValue = 0;

		foreach ($logics as $logic) {
			if($logic['OPERATOR'] == self::LOGIC_EGR) {
				$egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
			} elseif($logic['OPERATOR'] == self::LOGIC_ELS) {
				$elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
			}

			self::appendPropertyCondition($whereRangeNumber, $property, $logic, $alias);
		}

		if($egrValue > $elsValue) {
			$whereRangeNumber->logic('or');
		}

		$queryWhere->where($whereRangeNumber);
	}

	static public function appendWhereDefaultProperty(&$queryWhere, $propertyId, $logics, $alias = '')
	{
		foreach ($logics as $logic) {
			self::appendPropertyCondition($queryWhere, $propertyId, $logic, $alias);
		}
	}

	static public function appendWherePrice(&$queryWhere, $catalogGroupId, $logics, $alias = '', $siteId = '')
	{
		$whereRangeNumber = \Bitrix\Main\Entity\Query::filter();

		$egrValue = 0;
		$elsValue = 0;

		foreach ($logics as $logic) {
			if($logic['OPERATOR'] == self::LOGIC_EGR) {
				$egrValue = is_array($logic['VALUE']) ? min($logic['VALUE']) : $logic['VALUE'];
			} elseif($logic['OPERATOR'] == self::LOGIC_ELS) {
				$elsValue = is_array($logic['VALUE']) ? max($logic['VALUE']) : $logic['VALUE'];
			}

			self::appendPriceCondition($whereRangeNumber, $catalogGroupId, $logic, $alias, $siteId);
		}

		if($egrValue > $elsValue) {
			$whereRangeNumber->logic('or');
		}

		$queryWhere->where($whereRangeNumber);
	}

	static public function appendWhereSectionMargin(&$queryWhere, array $sectionMargins, $isIncludeSubsection = true, $alias = 'section')
	{
		if(!$sectionMargins) {
			return;
		}

		$whereMargin = \Bitrix\Main\Entity\Query::filter();
		$whereMargin->logic('or');

		foreach ($sectionMargins as $margin) {
			$whereMargin->where(
				\Bitrix\Main\Entity\Query::filter()->where([
					[$alias . '.IBLOCK_SECTION.LEFT_MARGIN', $isIncludeSubsection ? '>=' : '=', $margin['LEFT_MARGIN']],
					[$alias . '.IBLOCK_SECTION.RIGHT_MARGIN', $isIncludeSubsection ? '<=' : '=', $margin['RIGHT_MARGIN']],
				])
			);
		}

		$queryWhere->where($whereMargin);

		$whereActive = \Bitrix\Main\Entity\Query::filter();
		$whereActive->where($alias . '.IBLOCK_SECTION.ACTIVE', 'Y');
		$whereActive->where($alias . '.IBLOCK_SECTION.GLOBAL_ACTIVE', 'Y');

		if ($whereActive->hasConditions()) {
			$queryWhere->where($whereActive);
		}
	}

    static protected function appendPropertyCondition(&$queryWhere, $propertyId, $logic, $alias = '')
    {
        $prefix = $alias ? $alias . '.' : '';
        $valField = $prefix . 'VALUE';
        $numField = $prefix . 'VALUE_NUM';

        $values = [];

		if($logic['VALUE']) {
			if(is_array($logic['VALUE'])) {
                $values = array_filter($logic['VALUE']);
            } else {
                $values = [$logic['VALUE']];
            }
        }

        $queryFilter = \Bitrix\Main\Entity\Query::filter();

        switch ($logic['OPERATOR']) {
            case self::LOGIC_EQ:
                if ($values) {
                    $queryFilter->whereIn($valField, $values);
                } else {
                    $queryFilter->whereNotNull($valField);
                }

                break;
            case self::LOGIC_NOT_EQ:

                if($values) {
                    $queryFilter->whereNotIn($valField, $values);
                } else {
                    $queryFilter->whereNull($valField);
                }

                break;
            case self::LOGIC_CONT:
                if ($values) {
                    $subFilter = \Bitrix\Main\Entity\Query::filter()->logic('OR');
                    foreach ($values as $val) {
                        $subFilter->whereLike($valField, '%' . $val . '%');
                    }
                    $queryFilter->where($subFilter);
                } else {
                    $queryFilter->whereNotNull($valField);
                }
                break;

            case self::LOGIC_NOT_CONT:
                $subFilter = \Bitrix\Main\Entity\Query::filter()->logic('OR');
                if (!empty($values)) {
                    foreach ($values as $val) {
                        $subFilter->whereNotLike($valField, '%' . $val . '%');
                    }
                }
                $subFilter->whereNull($valField);
                $queryFilter->where($subFilter);
                break;

            case self::LOGIC_EGR:
                if($values) {
                    $queryFilter->where($numField, '>=', min($values));
                }
                break;

            case self::LOGIC_ELS:
                if($values) {
                    $queryFilter->where($numField, '<=', max($values));
                }
                break;

            default:
                if ($values) {
                    $queryFilter->whereIn($valField, $values);
                }
                break;
        }

        if ($queryFilter->hasConditions()) {
            $queryWhere->where($queryFilter);
        }
	}


	static protected function appendPriceCondition(&$queryWhere, $catalogGroupId, $logic, $alias = '', $siteId = '')
	{
		if ($alias) {
			$alias = $alias . '.';
		}

		if(is_array($logic['VALUE'])) {
			$values = array_filter($logic['VALUE']);
		} else {
			$values = [$logic['VALUE']];
		}

        $currencyModuleLoaded = \Bitrix\Main\Loader::includeModule('currency');

        $baseCurrency = $currencyModuleLoaded ? \Bitrix\Currency\CurrencyManager::getBaseCurrency() : 'RUB';
        $setting = \Aspro\Smartseo\Admin\Settings\SettingSmartseo::getInstance();
        $currenctSettings = $setting->getSite($siteId)->getParametersCurrency();
        $needConvert = $currenctSettings['CONVERT_CURRENCY'] !== 'N';
        $currencyId = $currenctSettings['CURRENCY_ID'] ?? $baseCurrency;

        $priceField = $needConvert ? 'PRICE_SCALE' : 'PRICE';
        $minVal = min($values);
        $maxVal = min($values);

        if($needConvert && $currencyModuleLoaded){
            $minVal = \CCurrencyRates::ConvertCurrency($minVal, $currencyId, $baseCurrency);
            $maxVal = \CCurrencyRates::ConvertCurrency($maxVal, $currencyId, $baseCurrency);
        }

		switch ($logic['OPERATOR']) {
			case self::LOGIC_EGR :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . $priceField, '>=',  $minVal);

				break;
			case self::LOGIC_ELS :
				$queryFilter = \Bitrix\Main\Entity\Query::filter();
				$queryFilter
					->where($alias . $priceField, '<=', $maxVal);

				break;
		}

		$queryWhere->where($queryFilter);
	}
}
