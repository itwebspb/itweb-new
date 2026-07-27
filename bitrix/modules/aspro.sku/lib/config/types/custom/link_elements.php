<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Aspro\Sku\General;

$uniqueId = md5($optionName);

$defaultParams = [
    'lang' => LANGUAGE_ID,
    'iblockfix' => 'y',
    'n' => $optionName,
];

$countValues = !empty($optionList) ? count($optionList) : 1;
?>

<table class="linked-property-table<?=$arOption['DISABLED'] ? ' aspro-control--disabled' : '';?>" id="tb<?=$uniqueId;?>" data-hash="<?=$uniqueId;?>">
    <?if ($optionList):?>
        <?$valuesCounter = 0;?>
        <?foreach ($optionList as $id => $name):?>
            <?php
            $params = array_merge($defaultParams, [
                'k' => ($valuesCounter++),
            ]);

            $url = '/bitrix/admin/iblock_element_search.php?'.http_build_query($params);
            ?>
            <tr class="linked-property-table__row">
                <td class="linked-property-table__cell">
                    <input name="<?=$params['n'];?>[<?=$params['k'];?>]" id="<?=$params['n'];?>[<?=$params['k'];?>]" value="<?=$id;?>" size="5" type="text">
                    <input type="button" onclick="openLinkedElementsWindow('<?=$url;?>')" value="...">&nbsp;<span id="sp_<?=$uniqueId;?>_<?=$params['k'];?>"><?=$name;?></span>
                </td>
            </tr>
        <?endforeach;?>
    <?else:?>
        <?php
        $params = array_merge($defaultParams, [
            'k' => '0',
        ]);

        $url = '/bitrix/admin/iblock_element_search.php?'.http_build_query($params);
        ?>
        <tr class="linked-property-table__row">
            <td class="linked-property-table__cell">
                <input name="<?=$params['n'];?>[<?=$params['k'];?>]" id="<?=$params['n'];?>[<?=$params['k'];?>]" value="<?=$item;?>" size="5" type="text">
                <input type="button" onclick="openLinkedElementsWindow('<?=$url;?>')" value="...">&nbsp;<span id="sp_<?=$uniqueId;?>_<?=$params['k'];?>"></span>
            </td>
        </tr>
    <?endif;?>

    <?php
    $params = array_merge($defaultParams, [
        'k' => $countValues,
        'm' => 'y',
    ]);

    $url = '/bitrix/admin/iblock_element_search.php?'.http_build_query($params);
    ?>
    <tr>
        <td>
            <input type="button" onclick="openLinkedElementsWindow('<?=$url;?>')" value="<?=General::getMessage('LINK_ELEMENT_ACTION_ADD');?>">&nbsp;<span id="sp_<?=$uniqueId;?>_<?=$params['k'];?>"></span>
        </td>
    </tr>
</table>

<script>
    window['MV_<?=$uniqueId;?>'] = <?=$countValues;?>;

    function InS<?=$uniqueId;?>(id, name) {
        const hash = '<?=$uniqueId;?>';
        const fieldName = '<?=$defaultParams['n'];?>';
        const iblockElementSearchActionURL = window.location.origin + '/bitrix/admin/iblock_element_search.php?<?=http_build_query($defaultParams);?>';

        const nodeTable = document.getElementById(`tb${hash}`);
        const nodeTableRow = nodeTable.insertRow(nodeTable.rows.length - 1);
        nodeTableRow.classList.add('linked-property-table__row')

        const nodeTableCell = nodeTableRow.insertCell(-1);
        nodeTableCell.classList.add('linked-property-table__cell');

        const inputName = `${fieldName}[${window[`MV_${hash}`]}]`;
        const inputSpanID = `sp_${hash}_${window[`MV_${hash}`]}`;

        const url = new URL(iblockElementSearchActionURL);
        url.searchParams.append('k', window[`MV_${hash}`]);

        const href = url.href.replace(window.location.origin, '');

        let resultHTML = `<input name="${inputName}" value="${id}" id="${inputName}" size="5" type="text">`;
            resultHTML += `<input type="button" value="..." onclick="openLinkedElementsWindow('${href}')">`
            resultHTML += `&nbsp;<span id="${inputSpanID}">${name}</span>`

        nodeTableCell.innerHTML = resultHTML;
        window[`MV_${hash}`]++;
    }
</script>
