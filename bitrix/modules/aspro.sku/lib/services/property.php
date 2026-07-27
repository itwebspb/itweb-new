<?php

namespace Aspro\Sku\Services;

use Aspro\Sku\Config;
use Aspro\Sku\DTO\SkuContext;
use Aspro\Sku\General;
use Aspro\Sku\Utils;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main;
use Bitrix\Main\Loader;

class Property extends Base
{
    private array $properties = [];
    private array $stringTable = [];
    private static $highLoadInclude;

    private const SORT_STEP = 100;
    private const DEFAULT_CACHE_TTL = 0;

    public function loadProperties(): void
    {
        $propertyCodes = Rule::getInstance()->getFilteredPropertyCodes();
        if (empty($propertyCodes)) {
            return;
        }

        $this->loadPropertiesInfo($propertyCodes);
        $this->loadPropertyValues();
    }

    private function loadPropertiesInfo(array $propertyCodes): void
    {
        $propertyIterator = PropertyTable::getList([
            'select' => [
                'ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT', 'LINK_IBLOCK_ID',
                'PROPERTY_TYPE', 'USER_TYPE', 'USER_TYPE_SETTINGS', 'HINT',
            ],
            'filter' => [
                '=IBLOCK_ID' => $this->getContext()->iblockId,
                '=CODE' => $propertyCodes,
                '=PROPERTY_TYPE' => [
                    PropertyTable::TYPE_LIST,
                    PropertyTable::TYPE_ELEMENT,
                    PropertyTable::TYPE_STRING,
                ],
                '=ACTIVE' => 'Y',
                '=MULTIPLE' => 'N',
            ],
            'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
        ]);

        while ($propInfo = $propertyIterator->fetch()) {
            $propInfo['ID'] = (int) $propInfo['ID'];
            $propInfo['CODE'] = (string) $propInfo['CODE'];
            if ($propInfo['CODE'] === '') {
                $propInfo['CODE'] = $propInfo['ID'];
            }
            $propInfo['SORT'] = (int) $propInfo['SORT'];
            $propInfo['USER_TYPE'] = (string) $propInfo['USER_TYPE'];

            if ($propInfo['PROPERTY_TYPE'] == PropertyTable::TYPE_STRING) {
                $this->modifyHighloadPropsInfo($propInfo);
            }

            $propInfo['SHOW_MODE'] = match ($propInfo['PROPERTY_TYPE']) {
                PropertyTable::TYPE_ELEMENT => 'pict',
                PropertyTable::TYPE_LIST => 'text',
                PropertyTable::TYPE_STRING => isset($propInfo['USER_TYPE_SETTINGS']['FIELDS_MAP']['UF_FILE']) ? 'pict' : 'text',
            };

            if ($propInfo['SHOW_MODE'] === 'pict') {
                $propInfo['DEFAULT_VALUES']['PICT'] = BX_ROOT.'/images/'.General::assetsPath.'/noimage.png';

                if (
                    is_array($this->getContext()->options['SKU_SHOW_PREVIEW_PICTURE_PROPS'])
                    && in_array(strtolower($propInfo['CODE']), $this->getContext()->options['SKU_SHOW_PREVIEW_PICTURE_PROPS'])
                ) {
                    $propInfo['SHOW_PREVIEW_PICTURE'] = true;
                }
            }

            $this->properties[$propInfo['CODE']] = $propInfo;
        }
    }

    private function loadPropertyValues(): void
    {
        $needPropValues = Item::getInstance()->getNeedPropertyValues();
        if (empty($needPropValues)) {
            return;
        }

        foreach ($this->properties as $code => &$property) {
            $values = [];
            $valuesExist = false;
            $pictMode = $property['SHOW_MODE'] === 'pict';

            $needValues = $needPropValues[$property['ID']] ?? [];
            if (empty($needValues)) {
                continue;
            }

            switch ($property['PROPERTY_TYPE']) {
                case PropertyTable::TYPE_ELEMENT:
                    $this->addValueEnum($property, $needValues, $values, $valuesExist);
                    break;
                case PropertyTable::TYPE_LIST:
                    $this->addValueList($property, $needValues, $values, $valuesExist);
                    break;
                case PropertyTable::TYPE_STRING:
                    $this->addValueString($pictMode, $property, $needValues, $values, $valuesExist);
                    break;
            }

            if (!$valuesExist) {
                continue;
            }

            if ($this->getContext()->options['SHOW_TEXT_FOR_EMPTY_PICTURES'] === 'Y'
                && $property['SHOW_MODE'] === 'pict'
                && !$pictMode
            ) {
                $property['SHOW_MODE'] = 'text';
            }

            $property['VALUES'] = $values;
            $property['VALUES_COUNT'] = count($values);
        }
    }

    private function addValueEnum(array &$property, array $needValues, array &$values, bool &$valuesExist): void
    {
        $select = ['ID', 'NAME', 'SORT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'];
        $filter = ['IBLOCK_ID' => $property['LINK_IBLOCK_ID'], 'ID' => array_keys($needValues)];
        $cache = ['ttl' => self::getCacheTtl()];

        $collection = ElementTable::getList([
            'select' => $select,
            'filter' => $filter,
            'cache' => $cache,
        ])->fetchAll();

        foreach ($collection as $item) {
            $image = $property['DEFAULT_VALUES']['PICT'] ?? '';
            if ($item['PREVIEW_PICTURE'] || $item['DETAIL_PICTURE']) {
                $image = \CFile::ResizeImageGet(
                    $item['PREVIEW_PICTURE'] ?: $item['DETAIL_PICTURE'],
                    ['width' => 72, 'height' => 72],
                    BX_RESIZE_IMAGE_PROPORTIONAL_ALT
                )['src'];
            }

            $values[$item['ID']] = [
                'ID' => (int) $item['ID'],
                'NAME' => $item['NAME'],
                'SORT' => $item['SORT'],
                'PICT' => $image,
            ];
            $valuesExist = true;
        }
    }

    private function addValueList(array &$property, array $needValues, array &$values, bool &$valuesExist): void
    {
        foreach (array_chunk($needValues, 500) as $pageIds) {
            $iterator = Iblock\PropertyEnumerationTable::getList([
                'select' => ['ID', 'VALUE', 'SORT'],
                'filter' => ['=PROPERTY_ID' => $property['ID'], '@ID' => $pageIds],
                'order' => ['SORT' => 'ASC', 'VALUE' => 'ASC'],
            ]);
            while ($row = $iterator->fetch()) {
                $row['ID'] = (int) $row['ID'];
                $values[$row['ID']] = [
                    'ID' => $row['ID'],
                    'NAME' => $row['VALUE'],
                    'SORT' => (int) $row['SORT'],
                    'PICT' => false,
                ];
                $valuesExist = true;
            }
        }
    }

    private function addValueString(bool &$pictMode, array &$property, array $needValues, array &$values, bool &$valuesExist): void
    {
        if ($property['USER_TYPE'] === 'directory') {
            $this->addValueHighload($pictMode, $property, $needValues, $values, $valuesExist);
        } else {
            $this->addValueStringList($property, $needValues, $values, $valuesExist);
        }
    }

    private function addValueHighload(bool &$pictMode, array &$property, array $needValues, array &$values, bool &$valuesExist): void
    {
        if (self::$highLoadInclude === null) {
            self::$highLoadInclude = Loader::includeModule('highloadblock');
        }
        if (!self::$highLoadInclude) {
            return;
        }

        $xmlMap = [];
        $sortExist = isset($property['USER_TYPE_SETTINGS']['FIELDS_MAP']['UF_SORT']);

        $directorySelect = ['ID', 'UF_NAME', 'UF_XML_ID'];
        $directoryOrder = [];
        if ($pictMode) {
            $directorySelect[] = 'UF_FILE';
        }
        if ($sortExist) {
            $directorySelect[] = 'UF_SORT';
            $directoryOrder['UF_SORT'] = 'ASC';
        }
        $directoryOrder['UF_NAME'] = 'ASC';
        $sortValue = self::SORT_STEP;

        $entity = $property['USER_TYPE_SETTINGS']['ENTITY'] ?? null;
        if (!$entity instanceof Main\Entity\Base) {
            return;
        }

        $entityDataClass = $entity->getDataClass();
        $entityGetList = [
            'select' => $directorySelect,
            'order' => $directoryOrder,
        ];

        foreach (array_chunk($needValues, 500) as $pageIds) {
            $entityGetList['filter'] = ['=UF_XML_ID' => $pageIds];
            $iterator = $entityDataClass::getList($entityGetList);
            while ($row = $iterator->fetch()) {
                $row['ID'] = (int) $row['ID'];
                $row['UF_SORT'] = ($sortExist ? (int) $row['UF_SORT'] : $sortValue);
                $sortValue += self::SORT_STEP;

                if ($pictMode) {
                    if (!empty($row['UF_FILE'])) {
                        $arFile = \CFile::GetFileArray($row['UF_FILE']);
                        if (!empty($arFile)) {
                            $row['PICT'] = [
                                'ID' => (int) $arFile['ID'],
                                'SRC' => $arFile['SRC'],
                                'WIDTH' => (int) $arFile['WIDTH'],
                                'HEIGHT' => (int) $arFile['HEIGHT'],
                            ];
                        }
                    }
                    if (empty($row['PICT'])) {
                        $row['PICT'] = $property['DEFAULT_VALUES']['PICT'] ?? '';
                        if ($this->getContext()->options['SHOW_TEXT_FOR_EMPTY_PICTURES'] === 'Y') {
                            $pictMode = false;
                        }
                    }
                }
                $values[$row['ID']] = [
                    'ID' => $row['ID'],
                    'NAME' => $row['UF_NAME'],
                    'SORT' => (int) $row['UF_SORT'],
                    'XML_ID' => $row['UF_XML_ID'],
                    'PICT' => ($pictMode ? $row['PICT'] : false),
                ];
                $valuesExist = true;
                $xmlMap[$row['UF_XML_ID']] = $row['ID'];
            }
        }

        $values[0] = [
            'ID' => 0,
            'SORT' => PHP_INT_MAX,
            'NA' => true,
            'NAME' => $property['DEFAULT_VALUES']['NAME'] ?? '',
            'XML_ID' => '',
            'PICT' => ($pictMode ? ($property['DEFAULT_VALUES']['PICT'] ?? '') : false),
        ];

        if ($valuesExist) {
            $property['XML_MAP'] = $xmlMap;
        }
    }

    private function addValueStringList(array &$property, array $needValues, array &$values, bool &$valuesExist): void
    {
        $sort = self::SORT_STEP;
        foreach ($needValues as $value) {
            $propCode = $property['CODE'];

            if (!array_key_exists($propCode, $this->stringTable)) {
                $this->stringTable[$propCode] = [];
            }

            if (!array_key_exists($value, $this->stringTable[$propCode])) {
                $this->stringTable[$propCode][$value] = $this->generateUniqueId($value);
            }

            $values[$this->stringTable[$propCode][$value]] = [
                'ID' => $this->stringTable[$propCode][$value],
                'NAME' => $value,
                'SORT' => $sort,
                'PICT' => false,
            ];

            $sort += self::SORT_STEP;
        }

        $valuesExist = true;
    }

    private function generateUniqueId(string $value): int
    {
        return crc32(md5($value));
    }

    private function modifyHighloadPropsInfo(array &$propInfo): void
    {
        if ($propInfo['USER_TYPE'] != 'directory') {
            return;
        }

        $propInfo['USER_TYPE_SETTINGS'] = (string) $propInfo['USER_TYPE_SETTINGS'];
        if ($propInfo['USER_TYPE_SETTINGS'] == '') {
            return;
        }
        $propInfo['USER_TYPE_SETTINGS'] = Utils::unserialize($propInfo['USER_TYPE_SETTINGS']);
        if (!isset($propInfo['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($propInfo['USER_TYPE_SETTINGS']['TABLE_NAME'])) {
            return;
        }
        if (self::$highLoadInclude === null) {
            self::$highLoadInclude = Loader::includeModule('highloadblock');
        }
        if (!self::$highLoadInclude) {
            return;
        }

        $highBlock = HighloadBlockTable::getList([
            'filter' => ['=TABLE_NAME' => $propInfo['USER_TYPE_SETTINGS']['TABLE_NAME']],
        ])->fetch();
        if (!isset($highBlock['ID'])) {
            return;
        }

        $entity = HighloadBlockTable::compileEntity($highBlock);
        $fieldsList = $entity->getFields();
        if (empty($fieldsList)) {
            return;
        }

        $requireFields = ['ID', 'UF_XML_ID', 'UF_NAME'];
        $flag = true;
        foreach ($requireFields as $fieldCode) {
            if (!isset($fieldsList[$fieldCode]) || empty($fieldsList[$fieldCode])) {
                $flag = false;
                break;
            }
        }

        if (!$flag) {
            return;
        }

        $propInfo['USER_TYPE_SETTINGS']['FIELDS_MAP'] = $fieldsList;
        $propInfo['USER_TYPE_SETTINGS']['ENTITY'] = $entity;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getStringTable(): array
    {
        return $this->stringTable;
    }

    public static function getCacheTtl(): string
    {
        $ttl = Config::getCacheTable();

        return self::DEFAULT_CACHE_TTL ?: $ttl;
    }
}
