<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Aspro\Sku\Config\UI;
use Aspro\Sku\General;

$hash = md5($optionCode);

$jsFuncNameAdd = 'AsproUIAdd'.$hash;
$jsFuncNameDelete = 'AsproUIDelete'.$hash;

$options = [
    'TYPE' => 'selectbox',
    'MULTIPLE' => $arOption['MULTIPLE'],
    'VALUE' => $optionVal,
    'LIST' => $optionList,
    'HIDE_TITLE' => true,
    'SCRIPT' => $arOption['SCRIPT'],
];
?>
<input type="hidden" name="<?=$optionName;?>" value="">
<template class="aspro-sku__ofers-property-template" id="<?=$hash;?>">
    <tr class="aspro-sku__ofers-property-table-row">
        <td class="aspro-sku__ofers-property-table-cell">
            <?UI::showOptionValue($optionCode, $options, $arTab);?>
            <button class="aspro-sku__delete-btn" type="button" onclick="<?=$jsFuncNameDelete;?>(this)" title="<?=General::getMessage('OFFERS_PROPS_ACTION_DELETE');?>"></button>
        </td>
    </tr>
</template>

<table class="aspro-sku__offers-property-table">
    <?if ($optionVal):?>
        <?$listCounter = 0;?>
        <?foreach ($optionVal as $value):?>
            <tr class="aspro-sku__ofers-property-table-row">
                <td class="aspro-sku__ofers-property-table-cell">
                    <?UI::showOptionValue($optionCode, array_merge($options, ['VALUE' => $value]), $arTab);?>
                    <button class="aspro-sku__delete-btn" type="button" onclick="<?=$jsFuncNameDelete;?>(this)" title="<?=General::getMessage('OFFERS_PROPS_ACTION_ADD');?>"></button>
                </td>
            </tr>
        <?endforeach;?>
    <?else:?>
        <tr class="aspro-sku__ofers-property-table-row">
            <td><?UI::showOptionValue($optionCode, array_merge($options, ['DISABLED' => $arOption['DISABLED']]), $arTab);?></td>
        </tr>
    <?endif;?>
</table>
<input type="button" value="<?=General::getMessage('OFFERS_PROPS_ACTION_ADD');?>" onclick="<?=$jsFuncNameAdd;?>(this)" <?=empty($arOption['LIST']) ? 'disabled' : '';?>>

<script>
    const <?=$jsFuncNameAdd;?> = (nodeButton) => {
        const nodeTemplate = document.getElementById('<?=$hash;?>');
        if (!nodeTemplate) {
            return;
        }

        const nodeTableRow = nodeTemplate.content.cloneNode(true);
        nodeButton.previousElementSibling.querySelector('tr:last-child').insertAdjacentElement('afterend', nodeTableRow.children[0]);
    }

    const <?=$jsFuncNameDelete;?> = (nodeButton) => {
        const nodeTableRow = nodeButton.closest('tr');
        if (nodeTableRow.previousElementSibling || nodeTableRow.nextElementSibling) {
            nodeButton.closest('tr').remove();
        }
    }
</script>
