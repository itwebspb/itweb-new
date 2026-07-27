<?php

namespace Aspro\Max;

use Bitrix\Main\IO\File;
use Bitrix\Main\Web\Uri;
use CMax as Solution;

class Utils
{
    public static function implodeClasses(array $arClasses, string $delimiter = ' '): string
    {
        return implode($delimiter, $arClasses);
    }

    public static function getPathWithTimestamp(string $path): string
    {
        $file = new File($_SERVER['DOCUMENT_ROOT'].$path);
        if (!$file->isExists()) {
            return $path;
        }

        return $path.'?'.$file->getModificationTime();
    }

    public static function checkShowSearchFilter(array $arItem, array $arParams): bool
    {
        $displayTypesAllowed = ['F', 'K', 'G', 'H'];
        $minValuesCount = 5;

        $filterProps = explode(',', Solution::GetFrontParametrValue('CATALOG_SEARCH_FILTER_PROP'));
        if ($arItem['IBLOCK_ID'] != $arParams['IBLOCK_ID']) {
            $filterProps = explode(',', Solution::GetFrontParametrValue('CATALOG_SKU_SEARCH_FILTER_PROP'));
        }

        $hasValues = !empty($arItem['VALUES']);
        $isAllowedDisplayType = in_array($arItem['DISPLAY_TYPE'], $displayTypesAllowed);
        $hasMoreThanMinimum = count($arItem['VALUES']) > $minValuesCount;
        $isInFilterProps = in_array($arItem['CODE'], $filterProps, true);

        return $hasValues && $isAllowedDisplayType && $hasMoreThanMinimum && $isInFilterProps;
    }

    public static function getNumVisibleCountFilter()
    {
        $numShowAllFromOption = Solution::GetFrontParametrValue('CATALOG_FILTER_SHOWALL_FROM');

        return $numShowAllFromOption > 0 ? $numShowAllFromOption : INF;
    }

    public static function getSiteURL(): string
    {
        return rtrim((new Uri(SITE_DIR))->toAbsolute()->getUri(), '/');
    }

    public static function getCurrentUrl(): string
    {
        global $APPLICATION;

        return self::getSiteURL().$APPLICATION->GetCurPage(false);
    }

    public static function getAbsolutePath(string $path): string
    {
        return self::getSiteURL().$path;
    }

    public static function sanitizeValue($value): string
    {
        return trim(htmlspecialcharsbx(strip_tags($value)));
    }

    public static function sanitizeHtml($value): string
    {
        $obSanitizer = new \CBXSanitizer();
        $obSanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_LOW);

        return $obSanitizer->SanitizeHtml((string) $value);
    }

    public static function getSiteInfo(): array
    {
        return \CSite::GetByID(SITE_ID)->Fetch();
    }

    public static function getIsoDate(array $element): ?string
    {
        $date = new \DateTime($element['DATE_CREATE']);

        return $date->format(\DateTime::ATOM);
    }

    public static function getAbsoluteExternalUrl(string $url): string
    {
        if (self::isHttpUrl($url)) {
            return preg_replace('#(?<!:)/+#', '/', $url);
        }

        $uri = new Uri('https://'.$url);
        if ($uri->getHost()) {
            return $uri->getUri();
        }

        return (new Uri($url))->toAbsolute()->getUri();
    }

    protected static function isHttpUrl(string $url): bool
    {
        return preg_match('#^https?://(?=\w)#', $url);
    }
}
