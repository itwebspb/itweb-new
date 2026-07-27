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

if($_POST['action']=='enable_stat' && check_bitrix_sessid())
{
	$eManager = \Bitrix\Main\EventManager::getInstance();
	$eManager->registerEventHandler("main", 'OnAfterEpilog', $moduleId, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');
	\Bitrix\Main\Config\Option::set($moduleId, 'STAT_404_ERROR', 'Y');
	LocalRedirect(\Bitrix\Main\Context::getCurrent()->getRequest()->getRequestedPage().'?lang='.LANGUAGE_ID);
}

$sTableID = "tbl_esolredirector_error_list";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_url",
	"filter_views_from",
	"filter_views_to",
	"filter_date_first_from",
	"filter_date_first_to",
	"filter_date_last_from",
	"filter_date_last_to",
	"filter_last_user_agent",
	"filter_last_referer",
	"filter_last_ip"
);

$lAdmin->InitFilter($arFilterFields);

$filter = array();

if (strlen($filter_url) > 0)
	$filter["%URL"] = trim($filter_url);
if (strlen($filter_views_from) > 0)
	$filter[">=VIEWS"] = (int)($filter_views_from);
if (strlen($filter_views_to) > 0)
	$filter["<=VIEWS"] = (int)($filter_views_to);
if (strlen($filter_date_first_from) > 0)
	$filter[">=DATE_FIRST"] = trim($filter_date_first_from);
if (strlen($filter_date_first_to) > 0)
	$filter["<=DATE_FIRST"] = (Loader::includeModule('iblock') && \CIBlock::isShortDate($filter_date_first_to)) ? ConvertTimeStamp(AddTime(MakeTimeStamp(trim($filter_date_first_to)), 1, "D"), "FULL"): trim($filter_date_first_to);
if (strlen($filter_date_last_from) > 0)
	$filter[">=DATE_LAST"] = trim($filter_date_last_from);
if (strlen($filter_date_last_to) > 0)
	$filter["<=DATE_LAST"] = (Loader::includeModule('iblock') && \CIBlock::isShortDate($filter_date_last_to)) ? ConvertTimeStamp(AddTime(MakeTimeStamp(trim($filter_date_last_to)), 1, "D"), "FULL"): trim($filter_date_last_to);
if (strlen($filter_last_user_agent) > 0)
	$filter["%LAST_USER_AGENT"] = trim($filter_last_user_agent);
if (strlen($filter_last_referer) > 0)
	$filter["%LAST_REFERER"] = trim($filter_last_referer);
if (strlen($filter_last_ip) > 0)
	$filter["LAST_IP"] = trim($filter_last_ip);

/*
if($lAdmin->EditAction())
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;
		
		$dbRes = \Bitrix\EsolRedirector\ErrorsTable::update($ID, $arFields);
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
				$lAdmin->AddUpdateError(Loc::getMessage("ESOL_RR_ERROR_UPDATING_REC")." (".$arFields["ID"].", ".$arFields["URL"].", ".$arFields["SITE_ID"].")", $ID);
		}
	}
}
*/

if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = \Bitrix\EsolRedirector\ErrorsTable::getList(array('filter'=>$filter, 'select'=>array('ID')));
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
				$dbRes = \Bitrix\EsolRedirector\ErrorsTable::delete($ID);
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
if($_REQUEST['mode']=='excel')
{
	$navyParams['SHOW_ALL'] = true;
}
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
	'select' => array('ID', 'URL', 'STATUS', 'VIEWS', 'DATE_FIRST', 'DATE_LAST', 'LAST_USER_AGENT', 'LAST_REFERER', 'LAST_IP', 'SITE_ID', 'SITE_NAME'=>'SITE.NAME'),
	'filter' => $filter
);

if ($usePageNavigation)
{
	$getListParams['limit'] = $navyParams['SIZEN'];
	$getListParams['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

if ($usePageNavigation)
{
	$countQuery = new Query(\Bitrix\EsolRedirector\ErrorsTable::getEntity());
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

$rsData = new CAdminResult(\Bitrix\EsolRedirector\ErrorsTable::getList($getListParams), $sTableID);
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

$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("ESOL_RR_ERRORS_LIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"URL", "content"=>Loc::getMessage("ESOL_RR_PL_URL"), "sort"=>"URL", "default"=>true),
	array("id"=>"VIEWS", "content"=>Loc::getMessage("ESOL_RR_PL_VIEWS"), "sort"=>"VIEWS", "default"=>true),
	array("id"=>"DATE_FIRST", "content"=>Loc::getMessage("ESOL_RR_PL_DATE_FIRST"), "sort"=>"DATE_FIRST", "default"=>true),
	array("id"=>"DATE_LAST", "content"=>Loc::getMessage("ESOL_RR_PL_DATE_LAST"), "sort"=>"DATE_LAST", "default"=>true),
	array("id"=>"LAST_USER_AGENT", "content"=>Loc::getMessage("ESOL_RR_PL_LAST_USER_AGENT"), "sort"=>"LAST_USER_AGENT", "default"=>true),
	array("id"=>"LAST_REFERER", "content"=>Loc::getMessage("ESOL_RR_PL_LAST_REFERER"), "sort"=>"LAST_REFERER", "default"=>true),
	array("id"=>"LAST_IP", "content"=>Loc::getMessage("ESOL_RR_PL_LAST_IP"), "sort"=>"LAST_IP", "default"=>true),
	array("id"=>"SITE_NAME", "content"=>Loc::getMessage("ESOL_RR_PL_SITE_ID"), "sort"=>"SITE_NAME", "default"=>true)
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while($arError = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($arError['ID'], $arError/*, $moduleFilePrefix."_redirect_item.php?ID=".$arError['ID']."&lang=".LANG, GetMessage("ESOL_RR_TO_REDIRECT")*/);
	
	$row->AddField("ID", $arError['ID']);
	$row->AddField("URL", '<a href="'.htmlspecialcharsex($arError['URL']).'" target="_blank">'.htmlspecialcharsbx($arError['URL']).'</a>');
	$row->AddField("VIEWS", $arError['VIEWS']);
	$row->AddField("DATE_FIRST", $arError['DATE_FIRST']);
	$row->AddField("DATE_LAST", $arError['DATE_LAST']);
	$row->AddField("LAST_USER_AGENT", htmlspecialcharsbx($arError['LAST_USER_AGENT']));
	$row->AddField("LAST_REFERER", htmlspecialcharsbx($arError['LAST_REFERER']));
	$row->AddField("LAST_IP", htmlspecialcharsbx($arError['LAST_IP']));
	$row->AddField("SITE_NAME", $arError['SITE_NAME']);
	
	$arActions = array();
	$arActions[] = array("ICON"=>"move", "TEXT"=>Loc::getMessage("ESOL_RR_TO_NEW_REDIRECT"), "ACTION"=>$lAdmin->ActionRedirect($moduleFilePrefix."_redirect_item.php?ERROR_ID=".$arError['ID']."&lang=".LANG), "DEFAULT"=>true);

	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("ESOL_RR_ERROR_DELETE"), "ACTION"=>"if(confirm('".GetMessageJS('ESOL_RR_ERROR_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($arError['ID'], "delete"));

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

$aContext = array();
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("ESOL_RR_ERROR_LIST_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

if($configErrors = \Bitrix\EsolRedirector\Utils::GetConfigErrors())
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'error',
		'MESSAGE' => $configErrors,
		'DETAILS' => '',
		'HTML' => true
	));
}


if(\Bitrix\Main\Config\Option::get($moduleId, 'STAT_404_ERROR')!='Y')
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'error',
		'MESSAGE' => '<form method="post">'.Loc::getMessage("ESOL_RR_ERROR_DISABLED_STAT").' '.bitrix_sessid_post().'<input type="hidden"name="action" value="enable_stat"><input type="submit" value="'.Loc::getMessage("ESOL_RR_ERROR_ENABLE_STAT").'"></form>',
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
		Loc::getMessage("ESOL_RR_FILTER_URL"),
		Loc::getMessage("ESOL_RR_FILTER_VIEWS"),
		Loc::getMessage("ESOL_RR_FILTER_DATE_FIRST"),
		Loc::getMessage("ESOL_RR_FILTER_DATE_LAST"),
		Loc::getMessage("ESOL_RR_FILTER_LAST_USER_AGENT"),
		Loc::getMessage("ESOL_RR_FILTER_LAST_REFERER"),
		Loc::getMessage("ESOL_RR_FILTER_LAST_IP"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_URL")?>:</td>
		<td>
			<input type="text" name="filter_url" value="<?echo htmlspecialcharsex($filter_url)?>">
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_VIEWS")?>:</td>
		<td nowrap>
			<input type="text" name="filter_views_from" size="10" value="<?echo htmlspecialcharsex($filter_views_from)?>">
			...
			<input type="text" name="filter_views_to" size="10" value="<?echo htmlspecialcharsex($filter_views_to)?>">
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_DATE_FIRST")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_first_from", htmlspecialcharsex($filter_date_first_from), "filter_date_first_to", htmlspecialcharsex($filter_date_first_to), "find_form")?>
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_DATE_LAST")?>:</td>
		<td>
			<?echo CalendarPeriod("filter_date_last_from", htmlspecialcharsex($filter_date_last_from), "filter_date_last_to", htmlspecialcharsex($filter_date_last_to), "find_form")?>
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_LAST_USER_AGENT")?>:</td>
		<td>
			<input type="text" name="filter_last_user_agent" value="<?echo htmlspecialcharsex($filter_last_user_agent)?>">
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_LAST_REFERER")?>:</td>
		<td>
			<input type="text" name="filter_last_referer" value="<?echo htmlspecialcharsex($filter_last_referer)?>">
		</td>
	</tr>
	
	<tr>
		<td><?echo Loc::getMessage("ESOL_RR_FILTER_LAST_IP")?>:</td>
		<td>
			<input type="text" name="filter_last_ip" value="<?echo htmlspecialcharsex($filter_last_ip)?>">
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
