<?php
use Aspro\Sku\Config;
use Aspro\Sku\General;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php';

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$moduleID = 'aspro.sku';
$moduleAssetsPath = str_replace('.', '/', $moduleID);
$langPrefix = 'ASPRO_SKU__';
Loader::includeModule($moduleID);

Bitrix\Main\UI\Extension::load('ui.hint');
Bitrix\Main\UI\Extension::load('ui.forms');
Bitrix\Main\UI\Extension::load('ui.buttons');

// title
$APPLICATION->SetTitle(Loc::getMessage($langPrefix.'PAGE_TITLE'));

// css & js
$APPLICATION->SetAdditionalCss('/bitrix/css/'.$moduleAssetsPath.'/settings.css');
$APPLICATION->AddHeadScript('/bitrix/js/'.$moduleAssetsPath.'/settings.js');

// rights
$RIGHT = $APPLICATION->GetGroupRight($moduleID);

try {
    if ($RIGHT < 'R') {
        throw new Exception(Loc::getMessage($langPrefix.'ERROR_ACCESS_DENIED'));
    }

    // tabs
    $arTabs = [];
    // $arTabs[] = [
    //     'DIV' => 'edit_general',
    //     'TAB' => Loc::getMessage($langPrefix.'GENERAL_TAB'),
    //     'TITLE' => Loc::getMessage($langPrefix.'GENERAL_TAB_TITLE'),
    //     'ICON' => 'settings',
    //     'PAGE_TYPE' => 'site_settings',
    //     'SITE_ID' => '',
    //     'SITE_DIR' => '',
    //     'SUB_TABS' => [
    //         [
    //             'DIV' => 'edit_main',
    //             'TAB' => Loc::getMessage($langPrefix.'MAIN_TAB'),
    //             'TITLE' => Loc::getMessage($langPrefix.'MAIN_TAB_TITLE'),
    //             'OPTIONS' => include_once('parameters/common/main.php'),
    //         ],
    //     ],
    // ];

    // sites
    $arSites = [];
    $dbRes = CSite::GetList($by = 'id', $sort = 'asc', ['ACTIVE' => 'Y']);
    while ($arSite = $dbRes->Fetch()) {
        $arSites[] = $arSite;
    }

    foreach ($arSites as $arSite) {
        $arTabs[] = [
            'DIV' => 'edit_'.$arSite['ID'],
            'TAB' => General::getMessage('SITE_TAB', ['#SITE_NAME#' => $arSite['NAME'], '#SITE_ID#' => $arSite['ID']]),
            'TITLE' => General::getMessage('SITE_TAB_TITLE', ['#SITE_NAME#' => $arSite['NAME'], '#SITE_ID#' => $arSite['ID']]),
            'ICON' => 'settings',
            'PAGE_TYPE' => 'site_settings',
            'SITE_ID' => $arSite['ID'],
            'SITE_DIR' => $arSite['DIR'],
            'OPTIONS' => include('parameters/sites.php'),
        ];
    }

    $tabSiteControl = new CAdminTabControl('tabSiteControl', $arTabs);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ajax action
        if (
            $RIGHT < 'W'
            || !check_bitrix_sessid()
        ) {
            throw new Exception(Loc::getMessage($langPrefix.'ERROR_ACTION_DENIED'));
        }

        if (isset($_POST['save'])) {
            Config\UI::saveOptions($arTabs);

            global $APPLICATION;
            $APPLICATION->RestartBuffer();

            if (strlen($_REQUEST['back_url_settings'] ?? '')) {
                LocalRedirect($_REQUEST['back_url_settings']);
            } else {
                LocalRedirect($APPLICATION->GetCurPageParam('lang='.urlencode(LANGUAGE_ID).'&'.$tabSiteControl->ActiveTabParam(), ['lang', 'tabSiteControl_active_tab']));
            }
        }
    }
} catch (Exception $e) {
    echo CAdminMessage::ShowMessage($e->getMessage());
}

if ($RIGHT >= 'R') {
    ?>
    <div class="aspro-sku__multi-form-detail">
        <form method="post" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID;?>">
            <?=bitrix_sessid_post();?>

            <?$tabSiteControl->Begin();?>

            <?foreach ($arTabs as $arTab):?>
                <?php
                $optionsSiteID = $arTab['SITE_ID'];

                $tabSiteControl->BeginNextTab();
                ?>

                <?if (!empty($arTab['SUB_TABS'])):?>
                    <tr>
                        <td width="100%" colspan="2">
                            <div class="aspro-sku__form-detail__body">
                                <table class="adm-detail-content-table edit-table">
                                    <tbody>
                                        <tr>
                                            <td width="100%" colspan="2">
                                                <?php
                                                $tabTypeControl = new CAdminViewTabControl('tabTypeControl', $arTab['SUB_TABS']);
                                                $tabTypeControl->Begin();
                                                ?>

                                                <?foreach ($arTab['SUB_TABS'] as $arSubTab):?>
                                                    <?$tabTypeControl->BeginNextTab(); ?>

                                                    <table class="adm-detail-content-table edit-table">
                                                        <tbody>
                                                            <?= Config\UI::showOptionsRow($arSubTab); ?>
                                                        </tbody>
                                                    </table>

                                                <?endforeach; ?>

                                                <?$tabTypeControl->End(); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?else:?>
                    <?= Config\UI::showOptionsRow($arTab); ?>
                <?endif; ?>
            <?endforeach; ?>

            <?if ($RIGHT >= 'W'):?>
                <?$tabSiteControl->Buttons();?>
                <div class="aspro-sku__form-detail__buttons">
                    <button type="submit" name="save" class="ui-btn ui-btn-success">
                        <?=Loc::getMessage($langPrefix.'BUTTON_SAVE');?>
                    </button>
                </div>
            <?endif;?>

            <?$tabSiteControl->End();?>
        </form>
    </div>
    <?php
}

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
