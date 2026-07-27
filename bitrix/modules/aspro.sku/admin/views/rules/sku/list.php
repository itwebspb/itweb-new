<?php
/**
 * @var CAdminUiList $adminUiList
 * @var \Admin\Page\UI\::getFilterFields $filterFields
 */

use Aspro\Sku\General;

global $APPLICATION;
$APPLICATION->SetTitle(General::getMessage('MENU__RULES_TEXT'));
?>

<div id="<?=$controllerName;?>-<?=$controllerAction;?>">
    <div class="<?=$moduleClassList;?>__<?=$controllerAction;?>-wrapper-filter">
        <?$adminUiList->DisplayFilter($filterFields);?>
    </div>

    <div class="<?=$moduleClassList;?>__<?=$controllerAction;?>-wrapper-grid">
        <?$adminUiList->DisplayList();?>
    </div>

    <script>
        BX.loadExt(["<?=General::getExtensionName('grid');?>"]).then(() => {
            BX.Aspro.Sku.GridManager.id = '<?=$adminUiList->table_id;?>';
        })
    </script>
</div>
