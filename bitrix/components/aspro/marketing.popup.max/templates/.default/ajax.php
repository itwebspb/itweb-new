<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
?>

<?$frame = $this->createFrame()->begin('');?>
<?$arItem = (array)$arResult['ITEM'] ?? [];?>
<div class="form marketing-popup popup-text-info<?=$bPicture ? ' popup-text-info--has-img' : '';?> <?=$templateName;?>">
    <?if (!empty($arItem)):?>
        <?if ($arItem['PREVIEW_PICTURE']):?>
            <div class="popup-text-info__picture"><div style="background-image: url(<?=CFile::GetPath($arItem['PREVIEW_PICTURE']);?>)"></div></div>
        <?endif;?>

        <div class="popup-text-info__title font_exlg darken">
            <?=htmlspecialcharsbx($arItem['NAME']);?>
        </div>

        <div class="popup-text-info__text font_sm">
            <?$obParser = new CTextParser();?>
            <?=$obParser->html_cut($arItem['PREVIEW_TEXT'], 500);?>

            <?// print_r($arResult);?>
            <?if ($bBtn1 || $bBtn2):?>
                <div class="popup-text-info__btn">
                    <?php
                    if ($bBtn1) {
                        $btn1Link = SITE_DIR.ltrim((string)$arItem['PROPERTY_BTN1_LINK_VALUE'], '/');
                        $btn1Class = ($arItem['PROPERTY_BTN1_CLASS_INFO']['XML_ID'] ?? '') ?: 'btn-default';
                        ?>
                        <a class="btn <?=htmlspecialcharsbx($btn1Class);?> btn-lg"
                            href="<?=htmlspecialcharsbx($btn1Link);?>"
                            >
                            <?=htmlspecialcharsbx($arItem['PROPERTY_BTN1_TEXT_VALUE']);?>
                        </a>
                        <?php
                    }

                    if ($bBtn2) {
                        $btn2Link = SITE_DIR.ltrim((string)$arItem['PROPERTY_BTN2_LINK_VALUE'], '/');
                        $btn2Class = ($arItem['PROPERTY_BTN2_CLASS_INFO']['XML_ID'] ?? '') ?: 'btn-transparent-border-color';
                        ?>
                        <a class="btn <?=htmlspecialcharsbx($btn2Class);?> btn-lg"
                            href="<?=htmlspecialcharsbx($btn2Link)?>"
                            >
                            <?=htmlspecialcharsbx($arItem['PROPERTY_BTN2_TEXT_VALUE']);?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            <?endif;?>
        </div>
    <?else:?>
        ERROR
    <?endif;?>
</div>
<?$frame->end();?>
