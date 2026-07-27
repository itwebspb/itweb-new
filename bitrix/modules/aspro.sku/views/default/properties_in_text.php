<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Bitrix\Main\Localization\Loc;

$arOptions = $arConfig['PARAMS'];
?>

<div class="sku-props__group sku-props-group visible-by-block-presence__condition" <?=$arOptions['STYLE'];?>>
    <?include 'partials/prop_title.php';?>
    <div class="sku-props-group__list sku-props-list">
        <?foreach ($arOptions['VALUES'] as $key => $arItem):?>
            <?php
            if ($arItem['MISSING']) {
                continue;
            }

            $itemTitle = $arOptions['NAME'].': '.($arItem['NAME'] ?: Loc::getMessage('PROPERTY_VALUE_UNDEFINED'));

            $isShowLink = !$arItem['ACTIVE'] && $arItem['DETAIL_PAGE_URL'];

            $itemClassList = ['sku-props-list__value sku-props-value sku-props-value--text'];
            if ($isShowLink) {
                $itemClassList[] = 'sku-props-value--link';
            }
            if (isset($arItem['CAN_BUY']) && !$arItem['CAN_BUY']) {
                $itemClassList[] = 'sku-props-value--missing';
            }
            if ($arItem['ACTIVE']) {
                $itemClassList[] = 'active';
            }
            $itemClass = trim(implode(' ', $itemClassList));
            ?>
            <?if ($isShowLink):?>
                <a href="<?=$arItem['DETAIL_PAGE_URL'];?>" class="<?=$itemClass;?>" data-value="<?=$arItem['ID'];?>" title="<?=$itemTitle;?>">
                    <?=$arItem['NAME'];?>
                </a>
            <?else:?>
                <div class="<?=$itemClass;?>" data-value="<?=$arItem['ID'];?>" title="<?=$itemTitle;?>">
                    <?=$arItem['NAME'];?>
                </div>
            <?endif;?>
        <?endforeach; ?>
    </div>
</div>
