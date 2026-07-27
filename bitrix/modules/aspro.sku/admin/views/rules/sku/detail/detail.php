<?php
/**
 * @var Aspro\Sku\Admin\Page\App\View   $this
 * @var CAdminUiContextMenu             $adminContextMenu
 * @var CAdminTabControl                $adminTabControl
 * @var string                          $tabButtons
 */

use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\Config;
use Aspro\Sku\General;

global $APPLICATION;
$APPLICATION->SetTitle($pageTitle);
$APPLICATION->SetAdditionalCss($this->getPathToModuleStyles().'/form_controls.css');
$APPLICATION->SetAdditionalCss($this->getPathToStyleByRoute().'.css');
$APPLICATION->AddHeadScript($this->getPathToScriptByRoute().'.js');
?>

<div id="<?=$controllerClassList;?>-<?=$controllerAction;?>" class="<?=$moduleClassList;?>__form-detail">

    <div class="<?=$moduleClassList;?>__form-toolbar">
        <?$adminContextMenu->Show();?>
    </div>

    <div class="<?=$moduleClassList;?>__form-detail">
        <form
            method="post"
            enctype="multipart/form-data"
            action="<?=$actionUrl;?>"
            data-detail-url="<?=Router::mkUrl($controllerName, $controllerAction);?>"
            data-list-url="<?=Router::mkUrl('rules.sku', 'list');?>"
        >
            <?=bitrix_sessid_post();?>

            <?$adminTabControl->Begin();?>

            <?foreach ($adminTabControl->tabs as $arTab):?>
                <?$adminTabControl->BeginNextTab();?>

                <?=Config\UI::showOptionsRow($arTab);?>
            <?endforeach;?>

            <?$adminTabControl->Buttons();?>
            <?=$tabButtons;?>

            <?$adminTabControl->End();?>
        </form>
    </div>

    <script>
        BX.loadExt(["<?=General::getExtensionName('detail');?>"]).then(() => {
            BX.Aspro.Sku.DetailManager.id = '<?=$controllerClassList;?>-<?=$controllerAction;?>';
        })
    </script>
</div>
