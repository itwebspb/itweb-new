<?php

if (empty($arOptions['NAME'])) {
    return;
}

$hasValue = isset($arOptions['VALUE']) && $arOptions['VALUE'];
?>
<div class="sku-props-group__title sku-props__title">
    <div class="">
        <?
            echo $arOptions['NAME'];
            if (TSolution::GetFrontParametrValue('SHOW_HINTS') !== 'N' && $arOptions['HINT']) {
                echo '<div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip">'.$arOptions['HINT'].'</div></div>';
            }
            echo ': <span class="sku-props__js-size">' . ($hasValue ? $arOptions['VALUE'] : '&mdash;') . '</span>';
        ?>
    </div>
</div>
