<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}?>

<?php

use Aspro\Sku\Config\Ui;

$optionVal = $optionVal ? explode(',', $optionVal) : [];

Ui::loadUiSelect2($optionCode, $arOption['PLACEHOLDER'], ['tags' => true]);
?>
<div class="ui-ctl ui-ctl-textbox ui-ctl-sm <?=$arOption['CUSTOM_CLASS'];?>">
    <select class="ui-ctl-element" name="<?=$optionName;?>[]" multiple="multiple" <?=$optionRequired;?> <?=$optionDisabled;?>>
        <?if ($optionVal):?>
            <?foreach ($optionVal as $value):?>
                <option value="<?=$value;?>" selected><?=$value;?></option>
            <?endforeach;?>
        <?endif;?>
    </select>
</div>
