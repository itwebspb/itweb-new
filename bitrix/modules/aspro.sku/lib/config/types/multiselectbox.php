<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?php

use Aspro\Sku\Config\UI;

if (!empty($arOption['CUSTOM_VALUES'])) {
    $optionList = include("custom_values/{$arOption['CUSTOM_VALUES']}.php");
}

Ui::loadUiSelect2($optionCode, $arOption['PLACEHOLDER']);
?>
<div class="uce-first-select-container ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-sm <?=$arOption['CUSTOM_CLASS'];?>">
    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
    <input type="hidden" name="<?=$optionName;?>" value="">
    <select class="ui-ctl-element" size="<?= $optionSize; ?>" <?=$optionDisabled;?> multiple name="<?= $optionName; ?>[]">
        <option></option>
        <?foreach ($optionList as $listKey => $listOptions):?>
            <?php
            $title = is_array($listOptions) ? $listOptions['TITLE'] : $listOptions;
            $isGroup = is_array($listOptions) && array_key_exists('ITEMS', $listOptions);
            $bDisabled = is_array($value) ? ($value['DISABLED'] ?? false) : false;
            ?>

            <?if (!empty($listOptions['SITE_ID']) && $optionsSiteID) {
                if (!in_array($optionsSiteID, $listOptions['SITE_ID'])) {
                    continue;
                }
            }?>

            <?if ($isGroup):?>
                <optgroup label="<?=$title;?>">
                    <?foreach ($listOptions['ITEMS'] as $itemId => $item):?>
                        <?$bSelected =in_array($itemId, (array)$optionVal);?>
                        <option value="<?=$itemId; ?>"<?=$bSelected ? ' selected' : '';?>><?=htmlspecialcharsbx($item);?></option>
                    <?endforeach;?>
                </optgroup>
            <?else:?>
                <?$bSelected = in_array($listKey, (array)$optionVal);?>
                <option value="<?=$listKey;?>"<?=$bSelected ? ' selected' : '';?>><?=htmlspecialcharsbx($title);?></option>
            <?endif;?>
        <?endforeach; ?>
    </select>
</div>
