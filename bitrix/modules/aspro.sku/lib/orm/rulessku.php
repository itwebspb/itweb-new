<?php

namespace Aspro\Sku\ORM;

use Aspro\Sku\Config;
use Aspro\Sku\Enums\RulesSkuFilterType;
use Aspro\Sku\General;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class RulesSkuTable extends DataManager
{
    private const DEFAULT_CACHE_TTL = 0;

    public static function getTableName()
    {
        return 'b_'.(str_replace('.', '_', General::moduleId).'_rules_sku');
    }

    public static function getMap()
    {
        return [
            (new Fields\IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete()
                ->configureTitle(self::getLangByField('ID')),

            (new Fields\BooleanField('ACTIVE'))
                ->configureValues('N', 'Y')
                ->configureDefaultValue('Y')
                ->configureTitle(self::getLangByField('ACTIVE')),

            (new Fields\StringField('NAME'))
                ->configureRequired()
                ->configureSize(100)
                ->configureTitle(self::getLangByField('NAME')),

            (new Fields\IntegerField('SORT'))
                ->addValidator([Validators\Integer::class, 'positiveValue'])
                ->addValidator([Validators\Integer::class, 'greaterThanZero'])
                ->configureDefaultValue(500)
                ->configureTitle(self::getLangByField('SORT')),

            (new Fields\StringField('SITE_ID'))
                ->configureRequired()
                ->configureDefaultValue(null)
                ->configureSize(100)
                ->configureTitle(self::getLangByField('SITE_ID')),

            (new Fields\IntegerField('IBLOCK_ID'))
                ->configureRequired()
                ->addValidator([Validators\Integer::class, 'positiveValue'])
                ->addValidator([Validators\Integer::class, 'greaterThanZero'])
                ->configureDefaultValue(null)
                ->configureTitle(self::getLangByField('IBLOCK_ID')),

            (new Fields\EnumField('FILTER_TYPE'))
                ->configureValues(RulesSkuFilterType::getNames())
                ->configureDefaultValue(RulesSkuFilterType::MANUAL->getName())
                ->addValidator(new Validators\RulesSkuFilterType())
                ->configureTitle(self::getLangByField('FILTER_TYPE')),

            (new Fields\TextField('FILTER_ITEMS'))
                ->configureLong()
                ->addSaveDataModifier([self::class, 'filterTypeManualSaveModifier'])
                ->addSaveDataModifier([self::class, 'filterItemsFieldSaveModifier'])
                ->addSaveDataModifier([self::class, 'arrayFieldSaveModifier'])
                ->addFetchDataModifier([self::class, 'arrayFieldFetchModifier'])
                ->configureTitle(self::getLangByField('FILTER_ITEMS')),

            (new Fields\TextField('FILTER_SECTION_ID'))
                ->configureLong()
                ->addSaveDataModifier([self::class, 'filterTypeFilterSaveModifier'])
                ->addSaveDataModifier([self::class, 'arrayFieldSaveModifier'])
                ->addFetchDataModifier([self::class, 'arrayFieldFetchModifier'])
                ->configureTitle(self::getLangByField('FILTER_SECTION_ID')),

            (new Fields\TextField('FILTER_PROPERTY'))
                ->configureLong()
                ->addSaveDataModifier([self::class, 'filterTypeFilterSaveModifier'])
                ->addSaveDataModifier([self::class, 'arrayFieldSaveModifier'])
                ->addFetchDataModifier([self::class, 'arrayFieldFetchModifier'])
                ->configureTitle(self::getLangByField('FILTER_PROPERTY')),

            (new Fields\TextField('OFFERS_PROPERTY'))
                ->configureRequired()
                ->configureLong()
                ->addSaveDataModifier([self::class, 'arrayFieldSaveModifier'])
                ->addFetchDataModifier([self::class, 'arrayFieldFetchModifier'])
                ->configureTitle(self::getLangByField('OFFERS_PROPERTY')),
        ];
    }

    public static function filterTypeManualSaveModifier($value, $row)
    {
        if ($row['FILTER_TYPE'] === RulesSkuFilterType::FILTER->getName()) {
            $value = '';
        }

        return $value;
    }

    public static function filterTypeFilterSaveModifier($value, $row)
    {
        if ($row['FILTER_TYPE'] === RulesSkuFilterType::MANUAL->getName()) {
            $value = '';
        }

        return $value;
    }

    public static function filterItemsFieldSaveModifier($value, $row)
    {
        return static::getFilteredItems($value, $row);
    }

    public static function getFilteredItems($value, $row): array
    {
        $query = [
            'select' => ['ID'],
            'filter' => [
                '=ACTIVE' => 'Y',
                'IBLOCK_ID' => $row['IBLOCK_ID'],
                'ID' => $value,
            ],
            'cache' => [
                'ttl' => 3600,
            ],
        ];

        if (Loader::includeModule('catalog')) {
            $query['filter']['=CATALOG_PRODUCT.TYPE'] = ProductTable::TYPE_PRODUCT;
            $query['runtime'] = [
                new \Bitrix\Main\Entity\ReferenceField(
                    'CATALOG_PRODUCT',
                    ProductTable::class,
                    \Bitrix\Main\Entity\Query\Join::on('this.ID', 'ref.ID')
                ),
            ];
        }

        $items = \Bitrix\Iblock\ElementTable::getList($query)->fetchAll();

        return $items ? array_column($items, 'ID') : [];
    }

    public static function arrayFieldSaveModifier($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $json = self::normalizeArray($value);

            return Json::encode($json);
        } catch (\Throwable $th) {
            return '';
        }
    }

    public static function arrayFieldFetchModifier($value): array
    {
        if (empty($value)) {
            return [];
        }

        try {
            return Json::decode($value);
        } catch (\Throwable $th) {
            return [];
        }
    }

    public static function getLangByField(string $field): string
    {
        return Loc::getMessage('ENTITY_'.$field.'_FIELD');
    }

    public static function getMapFields()
    {
        $result = [];

        foreach (static::getMap() as $field) {
            $result[] = $field->getName();
        }

        return $result;
    }

    public static function hasMapField($code)
    {
        return in_array($code, static::getMapFields());
    }

    public static function getCacheTtl(): string
    {
        $ttl = Config::getCacheTable();

        return self::DEFAULT_CACHE_TTL ?: $ttl;
    }

    public static function onAfterAdd(Event $event): void
    {
        self::onRulesSkuChangeHandler($event);
    }

    public static function onAfterUpdate(Event $event): void
    {
        self::onRulesSkuChangeHandler($event);
    }

    public static function onAfterDelete(Event $event): void
    {
        self::onRulesSkuChangeHandler($event);
    }

    public static function onRulesSkuChangeHandler(Event $event): void
    {
        $siteId = self::getFieldFromEvent($event, 'SITE_ID');
        if (!$siteId) {
            return;
        }

        self::clearComponentCache($siteId);
    }

    public static function clearComponentCache(array|string $siteIdList = [])
    {
        $siteIdList = (array) $siteIdList;
        if (empty($siteIdList)) {
            return;
        }

        $cachedComponents = ['aspro:sku.list', 'aspro:sku.list.section'];
        foreach ($siteIdList as $siteId) {
            foreach ($cachedComponents as $component) {
                \CBitrixComponent::clearComponentCache($component, $siteId);
            }
        }
    }

    protected static function getFieldFromEvent(Event $event, string $field): ?string
    {
        $fields = $event->getParameter('fields');
        if (isset($fields[$field])) {
            return $fields[$field];
        }

        $primary = $event->getParameter('primary');
        if ($primary) {
            try {
                $rule = self::getByPrimary($primary)->fetch();

                return $rule ? $rule[$field] : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public static function normalizeArray(?array $array = []): array
    {
        if (!$array) {
            return [];
        }

        return array_diff(array_unique(array_values($array)), ['']);
    }
}
