<?php

if (empty($arOptions['NAME'])) {
    return;
}

$hasValue = isset($arOptions['VALUE']) && $arOptions['VALUE'];
$hasHint = $arConfig['SHOW_HINT'] && $arOptions['HINT'];
?>
<div class="sku-props-group__header">
    <div class="sku-props-group__title">
        <?=$arOptions['NAME'];?>
    </div>

    <?if ($hasHint):?>
        <div class="sku-props-group__hint sku-props-hint">
            <button type="button" class="sku-props-hint__icon">?</button>
            <div class="sku-props-hint__tooltip">
                <div class="sku-props-hint__tooltip-container">
                    <div class="sku-props-hint__tooltip-content">
                        <?=$arOptions['HINT'];?>
                    </div>
                </div>
            </div>
        </div>
    <?endif; ?>

    <div class="sku-props-group__title-delimeter">—</div>

    <div class="sku-props-group__title-value">
        <?=$hasValue ? $arOptions['VALUE'] : '&mdash;';?>
    </div>
</div>
