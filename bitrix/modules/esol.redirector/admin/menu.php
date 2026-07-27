<?
IncludeModuleLangFile(__FILE__);
$moduleId = 'esol.redirector';
$moduleFilePrefix = $moduleIdUl = str_replace('.', '_', $moduleId);

$aMenu = array();

global $USER;
$bUserIsAdmin = $USER->IsAdmin();
$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);

if($bUserIsAdmin || $MODULE_RIGHT>='W')
{
	$aSubMenu[] = array(
		"text" => GetMessage("ESOL_REDIRECTOR_MENU_REDIRECT_LIST"),
		"url" => $moduleFilePrefix."_redirect_list.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			$moduleFilePrefix."_redirect_item.php", 
			$moduleFilePrefix."_redirect_import.php", 
		),
		"title" => GetMessage("ESOL_REDIRECTOR_MENU_REDIRECT_LIST"),
		"module_id" => $moduleId,
		"items_id" => "menu_".$moduleIdUl,
		"sort" => 100,
		"section" => $moduleIdUl,
	);
	
	$aSubMenu[] = array(
		"text" => GetMessage("ESOL_REDIRECTOR_MENU_ERRORS_404"),
		"url" => $moduleFilePrefix."_error_list.php?lang=".LANGUAGE_ID,
		"title" => GetMessage("ESOL_REDIRECTOR_MENU_ERRORS_404"),
		"module_id" => $moduleId,
		"items_id" => "menu_".$moduleIdUl,
		"sort" => 200,
		"section" => $moduleIdUl,
	);
	
	$aSubMenu[] = array(
		"text" => GetMessage("ESOL_REDIRECTOR_MENU_SETTINGS"),
		"url" => $moduleFilePrefix."_settings.php?lang=".LANGUAGE_ID,
		"title" => GetMessage("ESOL_REDIRECTOR_MENU_SETTINGS"),
		"module_id" => $moduleId,
		"items_id" => "menu_".$moduleIdUl,
		"sort" => 300,
		"section" => $moduleIdUl,
	);
	
	$aMenu[] = array(
		"parent_menu" => "global_menu_marketing",
		"section" => $moduleIdUl,
		"sort" => 1400,
		"text" => GetMessage("ESOL_REDIRECTOR_MENU_PARENT"),
		"title" => GetMessage("ESOL_REDIRECTOR_MENU_PARENT"),
		"icon" => $moduleIdUl."_menu_icon",
		"items_id" => "menu_".$moduleIdUl."_parent",
		"module_id" => $moduleId,
		"items" => $aSubMenu,
	);
}

return $aMenu;
?>