<?
use Bitrix\Main\Entity\Query,
	Bitrix\Main\Entity\ExpressionField,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$moduleId = 'esol.redirector';
$moduleFilePrefix = str_replace('.', '_', $moduleId);
$moduleJsId = str_replace('.', '_', $moduleId);
$moduleDemoExpiredFunc = $moduleJsId.'_demo_expired';
$moduleShowDemoFunc = $moduleJsId.'_show_demo';
Loader::includeModule($moduleId);
CJSCore::Init(array($moduleJsId));
Loc::loadMessages(__FILE__);

include_once(dirname(__FILE__).'/../install/demo.php');
if (call_user_func($moduleDemoExpiredFunc)) {
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	call_user_func($moduleShowDemoFunc);
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if ($REQUEST_METHOD == "POST" && $MODE=='AJAX')
{
	if($ACTION=='SHOW_MODULE_MESSAGE')
	{
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		?><div><?
		call_user_func($moduleShowDemoFunc, true);
		?></div><?
		die();
	}
}

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT < "W") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
//if(!$USER->IsAdmin()) $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$sTableID = "tbl_esolredirector_redirect_list";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_old_url",
	"filter_new_url",
	"filter_status",
	"filter_auto",
	"filter_wsubsections",
	"filter_getparams",
	"filter_date_create_from",
	"filter_date_create_to",
	"filter_date_last_use_from",
	"filter_date_last_use_to",
	"filter_count_use_from",
	"filter_count_use_to"
);

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (strlen($filter_old_url) > 0)
	$filter["%OLD_URL"] = trim($filter_old_url);
if (strlen($filter_new_url) > 0)
	$filter["%NEW_URL"] = trim($filter_new_url);
if (strlen($filter_active) > 0)
	$filter["ACTIVE"] = trim($filter_active);
if (strlen($filter_status) > 0)
	$filter["STATUS"] = trim($filter_status);
if (strlen($filter_auto) > 0)
	$filter["AUTO"] = trim($filter_auto);
if (strlen($filter_wsubsections) > 0)
	$filter["WSUBSECTIONS"] = trim($filter_wsubsections);
if (strlen($filter_wgetparams) > 0)
	$filter["WGETPARAMS"] = trim($filter_wgetparams);
if (strlen($filter_date_create_from) > 0)
	$filter[">=DATE_CREATE"] = trim($filter_date_create_from);
if (strlen($filter_date_create_to) > 0)
	$filter["<=DATE_CREATE"] = (Loader::includeModule('iblock') && \CIBlock::isShortDate($filter_date_create_to)) ? ConvertTimeStamp(AddTime(MakeTimeStamp(trim($filter_date_create_to)), 1, "D"), "FULL"): trim($filter_date_create_to);
if (strlen($filter_date_last_use_from) > 0)
	$filter[">=DATE_LAST_USE"] = trim($filter_date_last_use_from);
if (strlen($filter_date_last_use_to) > 0)
	$filter["<=DATE_LAST_USE"] = (Loader::includeModule('iblock') && \CIBlock::isShortDate($filter_date_last_use_to)) ? ConvertTimeStamp(AddTime(MakeTimeStamp(trim($filter_date_last_use_to)), 1, "D"), "FULL"): trim($filter_date_last_use_to);
if (strlen($filter_count_use_from) > 0)
	$filter[">=COUNT_USE"] = (int)($filter_count_use_from);
if (strlen($filter_count_use_to) > 0)
	$filter["<=COUNT_USE"] = (int)($filter_count_use_to);

if($lAdmin->EditAction())
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;
		
		$allowFields = array('ACTIVE','OLD_URL','NEW_URL','STATUS','WSUBSECTIONS','WGETPARAMS','REGEXP','FOR404','COMMENT','SITE_ID');
		$arFields2 = array_intersect_key($arFields, array_flip($allowFields));
		
		$dbRes = \Bitrix\EsolRedirector\RedirectTable::update($ID, $arFields2);
		if(!$dbRes->isSuccess())
		{
			$error = '';
			if($dbRes->getErrors())
			{
				foreach($dbRes->getErrors() as $errorObj)
				{
					$error .= $errorObj->getMessage().'. ';
				}
			}
			if($error)
				$lAdmin->AddUpdateError($error, $ID);
			else
				$lAdmin->AddUpdateError(Loc::getMessage("ESOL_RR_ERROR_UPDATING_REC")." (".$arFields["ID"].", ".htmlspecialcharsbx($arFields["ORL_URL"]).", ".htmlspecialcharsbx($arFields["NEW_URL"]).")", $ID);
		}
	}
}

if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = \Bitrix\EsolRedirector\RedirectTable::getList(array('filter'=>$filter, 'select'=>array('ID')));
		while($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if(strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$dbRes = \Bitrix\EsolRedirector\RedirectTable::delete($ID);
				if(!$dbRes->isSuccess())
				{
					$error = '';
					if($dbRes->getErrors())
					{
						foreach($dbRes->getErrors() as $errorObj)
						{
							$error .= $errorObj->getMessage().'. ';
						}
					}
					if($error)
						$lAdmin->AddGroupError($error, $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("ESOL_RR_ERROR_DELETING_TYPE"), $ID);
				}
				break;
		}
	}
}

$usePageNavigation = true;
$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
	$sTableID,
	array('nPageSize' => 20, 'sNavID' => $APPLICATION->GetCurPage())
));
if ($navyParams['SHOW_ALL'])
{
	$usePageNavigation = false;
}
else
{
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}

$getListParams = array(
	'select' => array(
		'ID', 
		'OLD_URL', 
		'NEW_URL', 
		'STATUS',
		'AUTO',
		'WSUBSECTIONS',
		'WGETPARAMS',
		'DATE_CREATE',
		'DATE_LAST_USE',
		'COUNT_USE',
		'ACTIVE',
		'COMMENT',
		'REGEXP',
		'CUSER_NAME' => 'CREATED_BY_USER.LOGIN',
		'CUSER_ID' => 'CREATED_BY_USER.ID'
	),
	'filter' => $filter
);

if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

if ($usePageNavigation)
{
	$countQuery = new Query(\Bitrix\EsolRedirector\RedirectTable::getEntity());
	$countQuery->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($getListParams['filter']);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];
	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);
		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$getListParams['limit'] = $navyParams['SIZEN'];
		$getListParams['offset'] = 0;
	}
}

$getListParams['order'] = array(ToUpper($by) => ToUpper($order));

$rsData = new CAdminResult(\Bitrix\EsolRedirector\RedirectTable::getList($getListParams), $sTableID);
if ($usePageNavigation)
{
	$rsData->NavStart($getListParams['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$rsData->NavRecordCount = $totalCount;
	$rsData->NavPageCount = $totalPages;
	$rsData->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$rsData->NavStart();
}

$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("ESOL_RR_REDIRECTS_LIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"OLD_URL", "content"=>Loc::getMessage("ESOL_RR_PL_OLD_URL"), "sort"=>"OLD_URL", "default"=>true),
	array("id"=>"NEW_URL", "content"=>Loc::getMessage("ESOL_RR_PL_NEW_URL"), "sort"=>"NEW_URL", "default"=>true),
	array("id"=>"ACTIVE", "content"=>Loc::getMessage("ESOL_RR_PL_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"STATUS", "content"=>Loc::getMessage("ESOL_RR_PL_STATUS"), "sort"=>"STATUS", "default"=>true),
	array("id"=>"AUTO", "content"=>Loc::getMessage("ESOL_RR_PL_AUTO"), "sort"=>"STATUS", "default"=>true),
	array("id"=>"WSUBSECTIONS", "content"=>Loc::getMessage("ESOL_RR_PL_WSUBSECTIONS"), "sort"=>"WSUBSECTIONS", "default"=>true),
	array("id"=>"WGETPARAMS", "content"=>Loc::getMessage("ESOL_RR_PL_WGETPARAMS"), "sort"=>"WGETPARAMS", "default"=>true),
	array("id"=>"DATE_CREATE", "content"=>Loc::getMessage("ESOL_RR_PL_DATE_CREATE"), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"CREATED_BY_USER", "content"=>Loc::getMessage("ESOL_RR_PL_CREATED_BY_USER"), "sort"=>"CREATED_BY_USER", "default"=>false),
	array("id"=>"DATE_LAST_USE", "content"=>Loc::getMessage("ESOL_RR_PL_DATE_LAST_USE"), "sort"=>"DATE_LAST_USE", "default"=>true),
	array("id"=>"COUNT_USE", "content"=>Loc::getMessage("ESOL_RR_PL_COUNT_USE"), "sort"=>"COUNT_USE", "default"=>true),
	array("id"=>"SITE_NAME", "content"=>Loc::getMessage("ESOL_RR_PL_SITE_NAME"), "sort"=>"SITE_REF.SITE_ID", "default"=>true),
	array("id"=>"COMMENT", "content"=>Loc::getMessage("ESOL_RR_PL_COMMENT"), "sort"=>"COMMENT", "default"=>false)
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arRedirects = array();
while($arRedirect = $rsData->NavNext(true, "f_"))
{
	$arRedirects[$f_ID] = $arRedirect;
}
if(!empty($arRedirects))
{
	$dbRes = \Bitrix\EsolRedirector\RedirectTable::getList(array('filter'=>array('ID'=>array_keys($arRedirects)), 'select'=>array('ID', 'SITE_NAME'=>'SITE_REF.SITE.NAME', 'SITE_ID'=>'SITE_REF.SITE.LID')));
	while($arr = $dbRes->Fetch())
	{
		if(!isset($arRedirects[$arr['ID']]['SITE_ID'])) $arRedirects[$arr['ID']]['SITE_ID'] = array();
		if(!in_array($arr['SITE_ID'], $arRedirects[$arr['ID']]['SITE_ID'])) $arRedirects[$arr['ID']]['SITE_ID'][] = $arr['SITE_ID'];
		if(!isset($arRedirects[$arr['ID']]['SITE_NAME']) || strlen($arRedirects[$arr['ID']]['SITE_NAME'])==0) $arRedirects[$arr['ID']]['SITE_NAME'] = $arr['SITE_NAME'];
		else $arRedirects[$arr['ID']]['SITE_NAME'] .= ' / '.$arr['SITE_NAME'];
	}
}


$arSitesDropdown = array("reference" => array(), "reference_id" => array());
if(class_exists('\Bitrix\Main\SiteTable'))
{
	$dbRes = \Bitrix\Main\SiteTable::getList();
	while($arSite = $dbRes->Fetch())
	{
		if($arSite['DEF']=='Y')
		{
			array_unshift($arSitesDropdown['reference_id'], $arSite['LID']);
			array_unshift($arSitesDropdown['reference'], $arSite['NAME']);
		}
		else
		{
			array_push($arSitesDropdown['reference_id'], $arSite['LID']);
			array_push($arSitesDropdown['reference'], $arSite['NAME']);
		}
	}
}

$arStatuses = \Bitrix\EsolRedirector\Events::getHttpStatusCodes();
$arStatuses = array_combine(array_keys($arStatuses), array_keys($arStatuses));
$idRow = 0;
//while($arRedirect = $rsData->NavNext(true, "f_"))
foreach($arRedirects as $arRedirect)
{
	$idRow++;
	$row =& $lAdmin->AddRow($arRedirect['ID'], $arRedirect, $moduleFilePrefix."_redirect_item.php?ID=".$arRedirect['ID']."&lang=".LANG, GetMessage("ESOL_RR_TO_REDIRECT"));
	
	$row->AddField("ID", "<a href=\"".$moduleFilePrefix."_redirect_item.php?ID=".$arRedirect['ID']."&lang=".LANG."\">".$arRedirect['ID']."</a>");
	$row->AddField("OLD_URL", ($arRedirect['REGEXP']=='Y' ? htmlspecialcharsbx($arRedirect['OLD_URL']) : '<a href="'.htmlspecialcharsbx($arRedirect['OLD_URL']).'" target="_blank">'.htmlspecialcharsbx($arRedirect['OLD_URL']).'</a>'), true);
	$row->AddField("NEW_URL", ($arRedirect['REGEXP']=='Y' ? htmlspecialcharsbx($arRedirect['NEW_URL']) : '<a href="'.htmlspecialcharsbx($arRedirect['NEW_URL']).'" target="_blank">'.htmlspecialcharsbx($arRedirect['NEW_URL']).'</a>'), true);
	$row->AddCheckField("ACTIVE", ($arRedirect['ACTIVE']=='Y' ? Loc::getMessage("ESOL_RR_YES") : Loc::getMessage("ESOL_RR_NO")));
	$row->AddSelectField("STATUS", $arStatuses, $arRedirect['STATUS']);
	$row->AddField("AUTO", ($arRedirect['AUTO']=='Y' ? Loc::getMessage("ESOL_RR_YES") : Loc::getMessage("ESOL_RR_NO")));
	$row->AddCheckField("WSUBSECTIONS", ($arRedirect['WSUBSECTIONS']=='Y' ? Loc::getMessage("ESOL_RR_YES") : Loc::getMessage("ESOL_RR_NO")));
	$row->AddCheckField("WGETPARAMS", ($arRedirect['WGETPARAMS']=='Y' ? Loc::getMessage("ESOL_RR_YES") : Loc::getMessage("ESOL_RR_NO")));
	$row->AddField("SITE_NAME", $arRedirect['SITE_NAME']);
	$row->AddEditField("SITE_NAME", SelectBoxMFromArray('FIELDS['.(int)$arRedirect['ID'].'][SITE_ID][]', $arSitesDropdown, $arRedirect['SITE_ID'], "", false, 4));
	$row->AddField("COMMENT", nl2br(htmlspecialcharsbx($arRedirect['COMMENT'])));
	$row->AddEditField("COMMENT", '<textarea rows="3" cols="30" name="FIELDS['.(int)$arRedirect['ID'].'][COMMENT]">'.htmlspecialcharsbx($arRedirect['COMMENT']).'</textarea>');
	$row->AddViewField("CREATED_BY_USER", ($arRedirect['CUSER_ID'] ? '[<a href="user_edit.php?lang='.LANG.'&ID='.$arRedirect['CUSER_ID'].'">'.$arRedirect['CUSER_ID'].'</a>] '.$arRedirect['CUSER_NAME'] : ''));
	
	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("ESOL_RR_TO_REDIRECT"), "ACTION"=>$lAdmin->ActionRedirect($moduleFilePrefix."_redirect_item.php?ID=".$arRedirect['ID']."&lang=".LANG), "DEFAULT"=>true);

	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("ESOL_RR_REDIRECT_DELETE"), "ACTION"=>"if(confirm('".GetMessageJS('ESOL_RR_REDIRECT_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arRedirect['ID'], "delete"));

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsData->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT" => Loc::getMessage("ESOL_RR_NEW_REDIRECT"),
		"LINK" => $moduleFilePrefix."_redirect_item.php?lang=".LANG,
		"LINK_PARAM" => "",
		"TITLE" => Loc::getMessage("ESOL_RR_NEW_REDIRECT")
	),
	array(
		"TEXT" => Loc::getMessage("ESOL_RR_TO_IMPORT"),
		"LINK" => $moduleFilePrefix."_redirect_import.php?lang=".LANG,
		"LINK_PARAM" => "",
		"TITLE" => Loc::getMessage("ESOL_RR_TO_IMPORT")
	)
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("ESOL_RR_REDIRECT_LIST_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

/*$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("ESOL_RR_NEW_REDIRECT"),
		"ICON" => "btn_green",
		"LINK" => $moduleFilePrefix."_redirect_item.php?lang=".LANG
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();*/

if($configErrors = \Bitrix\EsolRedirector\Utils::GetConfigErrors())
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'error',
		'MESSAGE' => $configErrors,
		'DETAILS' => '',
		'HTML' => true
	));
}
?>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		Loc::getMessage("ESOL_RR_FILTER_OLD_URL"),
		Loc::getMessage("ESOL_RR_FILTER_NEW_URL"),
		Loc::getMessage("ESOL_RR_FILTER_ACTIVE"),
		Loc::getMessage("ESOL_RR_FILTER_STATUS"),
		Loc::getMessage("ESOL_RR_FILTER_AUTO"),
		Loc::getMessage("ESOL_RR_FILTER_WSUBSECTIONS"),
		Loc::getMessage("ESOL_RR_FILTER_WGETPARAMS"),
		Loc::getMessage("ESOL_RR_FILTER_DATE_CREATE"),
		Loc::getMessage("ESOL_RR_FILTER_DATE_LAST_USE"),
		Loc::getMessage("ESOL_RR_FILTER_COUNT_USE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_OLD_URL")?>:</td>
		<td>
			<input type="text" name="filter_old_url" value="<?echo htmlspecialcharsex($filter_old_url)?>">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_NEW_URL")?>:</td>
		<td>
			<input type="text" name="filter_new_url" value="<?echo htmlspecialcharsex($filter_new_url)?>">
		</td>
	</tr>
<?
$arBoolDropdown = array("reference" => array(
	Loc::getMessage("ESOL_RR_YES"),
	Loc::getMessage("ESOL_RR_NO")
), "reference_id" => array(
	'Y',
	'N'
));
?>
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_ACTIVE")?>:</td>
		<td><?echo SelectBoxFromArray("filter_active", $arBoolDropdown, $filter_active, Loc::getMessage("ESOL_RR_ALL"), "");?></td>
	</tr>
<?
$arStatuses = \Bitrix\EsolRedirector\Events::getHttpStatusCodes();
$arStatusDropdown = array("reference" => array(), "reference_id" => array());
foreach($arStatuses as $k=>$v)
{
	$arStatusDropdown['reference'][] = $v;
	$arStatusDropdown['reference_id'][] = $k;
}
?>
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_STATUS")?>:</td>
		<td><?echo SelectBoxFromArray("filter_status", $arStatusDropdown, $filter_status, Loc::getMessage("ESOL_RR_ALL"), "");?></td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_AUTO")?>:</td>
		<td><?echo SelectBoxFromArray("filter_auto", $arBoolDropdown, $filter_auto, Loc::getMessage("ESOL_RR_ALL"), "");?></td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_WSUBSECTIONS")?>:</td>
		<td><?echo SelectBoxFromArray("filter_wsubsections", $arBoolDropdown, $filter_wsubsections, Loc::getMessage("ESOL_RR_ALL"), "");?></td>
	</tr>

	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_WGETPARAMS")?>:</td>
		<td><?echo SelectBoxFromArray("filter_wgetparams", $arBoolDropdown, $filter_wgetparams, Loc::getMessage("ESOL_RR_ALL"), "");?></td>
	</tr>

	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_DATE_CREATE")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_create_from", htmlspecialcharsex($filter_date_create_from), "filter_date_create_to", htmlspecialcharsex($filter_date_create_to), "find_form")?>
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_DATE_LAST_USE")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_last_use_from", htmlspecialcharsex($filter_date_last_use_from), "filter_date_last_use_to", htmlspecialcharsex($filter_date_last_use_to), "find_form")?>
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_COUNT_USE")?>:</td>
		<td nowrap>
			<input type="text" name="filter_count_use_from" size="10" value="<?echo htmlspecialcharsex($filter_count_use_from)?>">
			...
			<input type="text" name="filter_count_use_to" size="10" value="<?echo htmlspecialcharsex($filter_count_use_to)?>">
		</td>
	</tr>
<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?
$lAdmin->DisplayList();
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
