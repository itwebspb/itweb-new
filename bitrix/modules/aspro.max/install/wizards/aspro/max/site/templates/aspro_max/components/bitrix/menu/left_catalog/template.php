<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$this->setFrameMode(true);

if (empty($arResult)) {
    return;
}

global $arTheme;

$bRightSide = $arTheme['SHOW_RIGHT_SIDE']['VALUE'] == 'Y';
$RightContent = $arTheme['SHOW_RIGHT_SIDE']['DEPENDENT_PARAMS']['RIGHT_CONTENT']['VALUE'];
$bRightBanner = $bRightSide && $RightContent == 'BANNER';
$bRightBrand = $bRightSide && $RightContent == 'BRANDS';
$classMenuPosition = $arTheme['MENU_POSITION']['VALUE'] == 'COMBINED' ? 'm_line' : 'm_'.strtolower($arTheme['MENU_POSITION']['VALUE']);
$bShowOverlay = $arTheme['DARK_HOVER_OVERLAY']['VALUE'] === 'Y' ?: false;
$iVisibleItemsMenu = ($arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] ? $arTheme['MAX_VISIBLE_ITEMS_MENU']['VALUE'] : 10);
$templateData['USE_AJAX_SUBMENU'] = ($arParams['AJAX_CATALOG_SUBMENU'] === 'Y') && ((int)$arParams['MAX_LEVEL'] >= 4);

?>
<div class="menu_top_block catalog_block <?=$bShowOverlay ? 'dark_overlay' : '';?>"<?=$templateData['USE_AJAX_SUBMENU'] ? ' data-visible-count="'.(int)$iVisibleItemsMenu.'"' : '';?>>
    <ul class="menu dropdown">
        <?foreach($arResult as $key => $arItem):?>
            <?php
            $arItem['IMAGES'] = (isset($arItem['PARAMS']['SECTION_ICON']) ? $arItem['PARAMS']['SECTION_ICON'] : ($arItem['PARAMS']['PICTURE'] ? $arItem['PARAMS']['PICTURE'] : $arItem['IMAGES']));

            if (!is_array($arItem['IMAGES'])) {
                $arItem['IMAGES'] = CFile::ResizeImageGet($arItem['IMAGES'], ['width' => 60, 'height' => 60], BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
            }
            ?>
            <li class="full <?=$arItem['CHILD'] ? 'has-child' : '';?> <?=$arItem['SELECTED'] ? 'current opened' : '';?> <?=$classMenuPosition;?> v_<?=strtolower($arTheme['MENU_TYPE_VIEW']['VALUE']);?><?=$templateData['USE_AJAX_SUBMENU'] && $arItem['CHILD'] ? ' ajax-submenu-parent' : '';?>"<?=$templateData['USE_AJAX_SUBMENU'] && $arItem['CHILD'] ? ' data-section-id="'.(int)$arItem['ID'].'"' : '';?>>
                <a class="icons_fa <?=$arItem['CHILD'] ? 'parent' : '';?>" href="<?=$arItem['SECTION_PAGE_URL'];?>" >
                    <?if ($arItem['CHILD']):?>
                        <?if (strtolower($arTheme['MENU_TYPE_VIEW']['VALUE']) == 'bottom'):?>
                            <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_sprite.svg#trianglearrow_down', 'svg-inline-down', ['WIDTH' => 5, 'HEIGHT' => 3, 'INLINE' => 'N']);?>
                        <?else:?>
                            <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_sprite.svg#trianglearrow_right', 'right', ['WIDTH' => 3, 'HEIGHT' => 5, 'INLINE' => 'N']);?>
                        <?endif;?>
                    <?endif;?>
                    <?if ($arItem['IMAGES'] && $arTheme['LEFT_BLOCK_CATALOG_ICONS']['VALUE'] == 'Y'):?>
                        <span class="image">
                            <?if (
                                strpos($arItem['IMAGES']['src'], '.svg') !== false
                                && TSolution::GetFrontParametrValue('COLORED_CATALOG_ICON') === 'Y'
                            ):?>
                                <?=TSolution\Functions::showSVG(['PATH' => $arItem['IMAGES']['src']]);?>
                            <?else:?>
                                <img class="lazy" data-src="<?=$arItem['IMAGES']['src'];?>" src="<?=TSolution\Functions::showBlankImg($arItem['IMAGES']['src']);?>" alt="<?=$arItem['NAME'];?>" title="<?=$arItem['NAME'];?>" /></span>
                            <?endif;?>
                        </span>
                    <?endif;?>
                    <span class="name"><?=$arItem['NAME'];?></span>
                    <div class="toggle_block"></div>
                    <div class="clearfix"></div>
                </a>
                <?if ($arItem['CHILD']):?>
                    <div class="dropdown-block <?=strtolower($arTheme['MENU_TYPE_VIEW']['VALUE']) == 'bottom' ? 'dropdown' : '';?>">
                        <div class="dropdown">
                            <ul class="left-menu-wrapper">
                                <?foreach($arItem['CHILD'] as $arChildItem):?>
                                    <?php
                                    $arChildItem['IMAGES'] = (isset($arChildItem['PARAMS']['SECTION_ICON']) ? $arChildItem['PARAMS']['SECTION_ICON'] : (isset($arChildItem['PARAMS']['PICTURE']) ? $arChildItem['PARAMS']['PICTURE'] : $arChildItem['IMAGES']));

                                    if (!is_array($arChildItem['IMAGES'])) {
                                        $arChildItem['IMAGES'] = CFile::ResizeImageGet($arChildItem['IMAGES'], ['width' => 60, 'height' => 60], BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
                                    }
                                    ?>
                                    <li class="<?=!$templateData['USE_AJAX_SUBMENU'] && $arChildItem['CHILD'] ? 'has-childs' : '';?> <?if ($arChildItem['SELECTED']) {?> current <?}?>"<?=$templateData['USE_AJAX_SUBMENU'] ? ' data-l2-id="'.(int)$arChildItem['ID'].'"' : '';?>>
                                        <?if (
                                            $arChildItem['IMAGES']
                                            && $arTheme['SHOW_CATALOG_SECTIONS_ICONS']['VALUE'] == 'Y'
                                            && $arTheme['MENU_TYPE_VIEW']['VALUE'] !== 'BOTTOM'
                                        ):?>
                                            <span class="image colored_theme_svg">
                                                <a href="<?=$arChildItem['SECTION_PAGE_URL'];?>">
                                                    <?if (strpos($arChildItem['IMAGES']['src'], '.svg') !== false && TSolution::GetFrontParametrValue('COLORED_CATALOG_ICON') === 'Y'):?>
                                                        <?=TSolution\Functions::showSVG(['PATH' => $arChildItem['IMAGES']['src']]);?>
                                                    <?else:?>
                                                        <img class="lazy" data-src="<?=$arChildItem['IMAGES']['src'];?>" src="<?=TSolution\Functions::showBlankImg($arChildItem['IMAGES']['src']);?>" alt="<?=$arChildItem['NAME'];?>" />
                                                    <?endif;?>
                                                </a>
                                            </span>
                                        <?endif;?>
                                        <a class="section option-font-bold" href="<?=$arChildItem['SECTION_PAGE_URL'];?>"><span><?=$arChildItem['NAME'];?></span></a>
                                        <?if (!$templateData['USE_AJAX_SUBMENU'] && $arChildItem['CHILD']):?>
                                            <?$iCountChilds = count($arChildItem['CHILD']);?>
                                            <ul class="dropdown toggle_menu">
                                                <?foreach($arChildItem['CHILD'] as $key => $arChildItem1):?>
                                                    <li class="menu_item <?if ($arChildItem1['SELECTED']) {?> current <?}?> <?=$key + 1 > $iVisibleItemsMenu ? 'collapsed' : '';?>">
                                                        <a class="parent1 section1" href="<?=$arChildItem1['SECTION_PAGE_URL'];?>"><span><?=$arChildItem1['NAME'];?></span></a>
                                                    </li>
                                                <?endforeach;?>
                                                <?if ($iCountChilds > $iVisibleItemsMenu):?>
                                                    <li><span class="more_items with_dropdown"><?=Bitrix\Main\Localization\Loc::getMessage('S_MORE_ITEMS').' '.($iCountChilds - $iVisibleItemsMenu);?></span></li>
                                                <?endif;?>
                                            </ul>
                                        <?endif;?>
                                        <div class="clearfix"></div>
                                    </li>
                                <?endforeach;?>
                            </ul>

                            <?if ($bRightSide):?>
                                <div class="right-side <?=$RightContent;?>">
                                    <div class="right-content">
                                        <?if ($bRightBanner && $arItem['UF_MENU_BANNER']):?>
                                            <?php
                                            if ($GLOBALS['arRegionLink']) {
                                                $GLOBALS['rightBannersFilter'] = array_merge($GLOBALS['arRegionLink'], ['ID' => $arItem['UF_MENU_BANNER']]);
                                            } else {
                                                $GLOBALS['rightBannersFilter'] = ['ID' => $arItem['UF_MENU_BANNER']];
                                            }

                                            $APPLICATION->IncludeComponent(
                                                'bitrix:news.list',
                                                'banners',
                                                [
                                                    'IBLOCK_TYPE' => 'aspro_max_adv',
                                                    'IBLOCK_ID' => TSolution\Cache::$arIBlocks[SITE_ID]['aspro_max_adv']['aspro_max_banners_inner'][0],
                                                    'PAGE' => $APPLICATION->GetCurPage(),
                                                    'MENU_BANNER' => true,
                                                    'SHOW_ALL_ELEMENTS' => 'N',
                                                    // 'MENU_LINK' => $arItem['link'],
                                                    'NEWS_COUNT' => '100',
                                                    'SORT_BY1' => 'SORT',
                                                    'SORT_ORDER1' => 'ASC',
                                                    'SORT_BY2' => 'ID',
                                                    'SORT_ORDER2' => 'ASC',
                                                    'FIELD_CODE' => [
                                                        0 => 'NAME',
                                                        2 => 'PREVIEW_PICTURE',
                                                    ],
                                                    'PROPERTY_CODE' => [
                                                        0 => 'LINK',
                                                        1 => 'TARGET',
                                                        2 => 'BGCOLOR',
                                                        3 => 'SHOW_SECTION',
                                                        4 => 'SHOW_PAGE',
                                                        5 => 'HIDDEN_XS',
                                                        6 => 'HIDDEN_SM',
                                                        7 => 'POSITION',
                                                        8 => 'SIZING',
                                                    ],
                                                    'CHECK_DATES' => 'Y',
                                                    'FILTER_NAME' => 'rightBannersFilter',
                                                    'DETAIL_URL' => '',
                                                    'AJAX_MODE' => 'N',
                                                    'AJAX_OPTION_JUMP' => 'N',
                                                    'AJAX_OPTION_STYLE' => 'Y',
                                                    'AJAX_OPTION_HISTORY' => 'N',
                                                    'CACHE_TYPE' => 'A',
                                                    'CACHE_TIME' => '3600000',
                                                    'CACHE_FILTER' => 'Y',
                                                    'CACHE_GROUPS' => 'N',
                                                    'PREVIEW_TRUNCATE_LEN' => '150',
                                                    'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                                                    'SET_TITLE' => 'N',
                                                    'SET_STATUS_404' => 'N',
                                                    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                                                    'ADD_SECTIONS_CHAIN' => 'N',
                                                    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                                                    'PARENT_SECTION' => '',
                                                    'PARENT_SECTION_CODE' => '',
                                                    'INCLUDE_SUBSECTIONS' => 'Y',
                                                    'PAGER_TEMPLATE' => '.default',
                                                    'DISPLAY_TOP_PAGER' => 'N',
                                                    'DISPLAY_BOTTOM_PAGER' => 'N',
                                                    'PAGER_TITLE' => '',
                                                    'PAGER_SHOW_ALWAYS' => 'N',
                                                    'PAGER_DESC_NUMBERING' => 'N',
                                                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '3600000',
                                                    'PAGER_SHOW_ALL' => 'N',
                                                    'AJAX_OPTION_ADDITIONAL' => '',
                                                    'SHOW_DETAIL_LINK' => 'N',
                                                    'SET_BROWSER_TITLE' => 'N',
                                                    'SET_META_KEYWORDS' => 'N',
                                                    'SET_META_DESCRIPTION' => 'N',
                                                    'COMPONENT_TEMPLATE' => 'banners',
                                                    'SET_LAST_MODIFIED' => 'N',
                                                    'COMPOSITE_FRAME_MODE' => 'A',
                                                    'COMPOSITE_FRAME_TYPE' => 'AUTO',
                                                    'PAGER_BASE_LINK_ENABLE' => 'N',
                                                    'SHOW_404' => 'N',
                                                    'MESSAGE_404' => '',
                                                ],
                                                false, ['ACTIVE_COMPONENT' => 'Y', 'HIDE_ICONS' => 'Y']
                                            );
                                            ?>
                                        <?elseif ($bRightBrand && is_array($arItem['UF_MENU_BRANDS'])):?>
                                            <div class="brands-wrapper">
                                                <?foreach ($arItem['UF_MENU_BRANDS'] as $brand):?>
                                                    <?if (is_array($brand)) :?>
                                                        <div class="brand-wrapper">
                                                            <?if ($brand['DETAIL_PAGE_URL']):?>
                                                                <a href="<?=$brand['DETAIL_PAGE_URL'];?>">
                                                            <?endif;?>
                                                                <img src="<?=CFile::GetPath($brand['PREVIEW_PICTURE']);?>" alt="<?=$brand['NAME'];?>" title="<?=$brand['NAME'];?>" />
                                                            <?if ($brand['DETAIL_PAGE_URL']):?>
                                                                </a>
                                                            <?endif;?>
                                                        </div>
                                                    <?endif;?>
                                                <?endforeach;?>
                                            </div>
                                        <?endif;?>
                                    </div>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                <?endif;?>
            </li>
        <?endforeach?>
    </ul>
</div>
<?if ($templateData['USE_AJAX_SUBMENU']):?>
<script>
    BX.message({
        'S_MORE_ITEMS': '<?=CUtil::JSEscape(GetMessage('S_MORE_ITEMS'))?>',
    });
</script>
<?endif;?>
