<?
if(!defined('NO_AGENT_CHECK')) define('NO_AGENT_CHECK', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
$moduleId = 'esol.importexportexcel';
CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');
CModule::IncludeModule($moduleId);
IncludeModuleLangFile(__FILE__);

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT <= "T") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$arGet = $_GET;
$HIGHLOADBLOCK_ID = (int)$arGet['HIGHLOADBLOCK_ID'];
$PROFILE_ID = (int)$arGet['PROFILE_ID'];

if($_POST && $_POST['action']=='save' /*&& isset($_POST['FILTER'])*/)
{
	\CUtil::JSPostUnescape();
	$arFilter = array();
	if(isset($_POST['FILTER']))
	{
		$arFilterKeys = preg_grep('/^filter1_/', array_keys($_POST));
		if(!empty($arFilterKeys))
		{
			if(!is_array($_POST['FILTER']))
			{
				$_POST['FILTER'] = array();
			}
			foreach($arFilterKeys as $key)
			{
				$arKey = explode('_', $key, 2);
				$_POST['FILTER'][$arKey[1]] = $_POST[$key];
			}
		}
		$arFilter = $_POST['FILTER'];
	}
	elseif(isset($_POST['EFILTER']))
	{
		$arFilter = $_POST['EFILTER'];
	}
	
	$APPLICATION->RestartBuffer();
	ob_end_clean();
	if(count($arFilter) > 0) echo base64_encode(serialize($arFilter));
	die();
}

if($OLDFILTER) $FILTER = \KdaIE\Utils::Unserialize(base64_decode($OLDFILTER));
if(!is_array($FILTER)) $FILTER = array();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");

$oldTypeFilter = false;
if(count($FILTER) > 0 && count(preg_grep('/^find_/', array_keys($FILTER))) > 0)
{
	foreach($FILTER as $filterItem)
	{
		if((!is_array($filterItem) && strlen($filterItem) > 0) || (is_array($filterItem) && !empty($filterItem)))
		{
			$oldTypeFilter = true;
		}
	}
	if(!$oldTypeFilter) $FILTER = array();
}
?>
<form action="" method="post" enctype="multipart/form-data" name="filter_form" id="kda-ie-filter" class="kda-ie-filter">
	<input type="hidden" name="action" value="save">
	<?
	if($oldTypeFilter)
	{
		CKDAImportUtils::ShowFilterHighload('kda_importexcel_hl_'.$PROFILE_ID, $HIGHLOADBLOCK_ID, $FILTER);
	}
	else
	{
		$fl = new CKDAFieldList();
		$eFilter = new CKDAIEFilter($HIGHLOADBLOCK_ID, 'hl');
		$eFilter->ShowFilterBlock('kda-ee-sheet-efilter', $FILTER, $fl);
	}
	?>
</form>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>