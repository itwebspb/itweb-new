<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
?>
<div class="brands-group-wrapper">
    <?foreach ($arItems as $letter => $arBrands):?>
        <div class="brand__wrapper bordered">
            <div class="brand__item">
                <div class="line-block line-block--flex-wrap line-block--align-normal line-block--gap line-block--gap-8">
                    <div class="line-block__item brand__item-letter">
                        <?=$letter;?>
                    </div>

                    <div class="line-block__item flex1">
                        <div class="brand__list">
                            <?foreach ($arBrands['ITEMS'] as $arItem):?>
                                <div class="brand__list-item">
                                    <a href="<?=$arItem['DETAIL_PAGE_URL'];?>" class="dark_link">
                                        <?=$arItem['NAME'];?>
                                    </a>
                                </div>
                            <?endforeach;?>

                            <?if (isset($arBrands['HAS_MORE'])):?>
                                <div class="brand__list-item">
                                    <span class="colored pointer filter-link"
                                        data-letter1="<?=urlencode($letter);?>"
                                        data-letter="<?=$arBrands['PREFIX'].$arBrands['CODE'];?>"
                                        >
                                        <?=GetMessage('ALL_BRANDS_ON_LETTER', ['#LETTER#' => $letter]);?>
                                    </span>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?endforeach;?>
</div>
