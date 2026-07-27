<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$arOptions = $arConfig['PARAMS'];
?>

<div class="line-block__item sku-props__inner sku-props--pict visible-by-block-presence__condition" <?=$arOptions['STYLE']; ?> data-id="<?=$arOptions['ID']; ?>">
    <div class="sku-props__item">
        <?include 'partials/prop_title.php'; ?>
        <div class="line-block line-block--flex-wrap line-block--gap line-block--gap-6 sku-props__values ">
            <?foreach ($arOptions['VALUES'] as $key => $arItem):?>
                <?php
                    if ($arItem['MISSING']) {
                        continue;
                    }

                    $isShowLink = !$arItem['ACTIVE'] && $arItem['DETAIL_PAGE_URL'];

                    if (isset($arItem['CAN_BUY']) && !$arItem['CAN_BUY']) {
                        $arItem['STYLE'] = 'sku-props__value--missing';
                    }
                ?>
                <?if($isShowLink):?>
                    <a href="<?= $arItem['DETAIL_PAGE_URL']; ?>" data-value="<?= $arItem['ID']; ?>" title="<?= $arItem['NAME']; ?>">
                <?endif; ?>
                    <div class="line-block__item">
                        <button type="button"
                            class="sku-props__value sku-props__value--pict <?= $arItem['ACTIVE'] ? 'sku-props__value--active' : ''; ?> <?=$arItem['STYLE'];?>"
                            data-onevalue="<?= $arItem['ID']; ?>"
                            data-title="<?= $arItem['NAME']; ?>"
                            title="<?= $arItem['NAME']; ?>"
                            data-src=""
                            style="background-image: url(<?= $arItem['PICT']['SRC'] ?? $arItem['PICT']; ?>);">
                            <?= $arItem['NAME']; ?>
                        </button>
                    </div>
                <?if($isShowLink):?>
                    </a>
                <?endif; ?>
            <?endforeach; ?>
        </div>
    </div>
</div>
