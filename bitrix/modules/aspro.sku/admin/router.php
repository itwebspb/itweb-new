<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

use Aspro\Sku\Admin\Page\Helper;
use Aspro\Sku\Admin\Page\Router;
use Aspro\Sku\General;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

global $APPLICATION;
IncludeModuleLangFile(__FILE__);

$APPLICATION->AddHeadString('<base href="/bitrix/admin/">', true);

$moduleID = 'aspro.sku';
Loader::includeModule($moduleID);

Bitrix\Main\UI\Extension::load('ui.forms');
Bitrix\Main\UI\Extension::load('ui.buttons');
Bitrix\Main\UI\Extension::load('ui.alerts');

CJSCore::Init([General::getExtensionName('popup')]);

$APPLICATION->SetTitle(General::getMessage('ERROR_TITLE'));

if ($APPLICATION->GetGroupRight($moduleID) < 'R') {
    Helper::throwException(General::getMessage('ERROR_ACCESS_DENIED'));
}

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new Bitrix\Main\Web\PostDecodeFilter);

if (Router::isNeedIncludeAdminVisual($request)) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
}

try {
    $router = new Router($request);
    $router->resolve();
} catch (Exception $e) {
    if ($request->isAjaxRequest()) {
        Helper::failureJsonResponse($e->getMessage());
    } else {
        CAdminMessage::ShowMessage($e->getMessage());
    }
}

if (Router::isNeedIncludeAdminVisual($request)) {
    require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
}
