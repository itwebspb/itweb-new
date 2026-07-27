<?php

namespace Aspro\Max\Scheme;

use Aspro\Max\Utils as SolutionUtils;

class Gallery
{
    public static function show(array $arItems): string
    {
        $html = '';
        foreach ($arItems as $arImage) {
            $defaultImgPath = SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg';
            $imageSrc = $arImage['BIG']['src'] ? SolutionUtils::getAbsolutePath($arImage['BIG']['src']) : SolutionUtils::getAbsolutePath($defaultImgPath);

            $html .= '<span itemprop="image" itemscope itemtype="https://schema.org/ImageObject" style="display:none">';
            $html .= '<link itemprop="url" href="'.htmlspecialcharsbx($imageSrc).'">';
            $html .= '<link itemprop="contentUrl" href="'.htmlspecialcharsbx($imageSrc).'">';

            if (!empty($arImage['BIG']['width'])) {
                $html .= '<meta itemprop="width" content="'.(int) $arImage['BIG']['width'].'">';
            }

            if (!empty($arImage['BIG']['height'])) {
                $html .= '<meta itemprop="height" content="'.(int) $arImage['BIG']['height'].'">';
            }

            if (!empty($arImage['ALT'])) {
                $alt = htmlspecialcharsbx($arImage['ALT']);
                $html .= '<meta itemprop="description" content="'.$alt.'">';
            }

            if (!empty($arImage['TITLE'])) {
                $title = htmlspecialcharsbx($arImage['TITLE']);
                $html .= '<meta itemprop="name" content="'.$title.'">';
            }

            $html .= '</span>';
        }

        return $html;
    }
}
