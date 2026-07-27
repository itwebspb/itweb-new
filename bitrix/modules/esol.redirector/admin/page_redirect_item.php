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

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT < "W") $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
//if(!$USER->IsAdmin()) $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(Loc::getMessage($ID ? "ESOL_RR_REDIRECT_PAGE_TITLE_EDIT" : "ESOL_RR_REDIRECT_PAGE_TITLE_NEW"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

$aTabs = array(
	array("DIV" => "edit0", "TAB" => Loc::getMessage("ESOL_RR_REDIRECT_TAB"), "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_REDIRECT_TAB")),
);
$tabControl = new CAdminTabControl("esolRedirectorTabControl", $aTabs, true, true);

$arErrors = array();

if($REDIRECT['REGEXP']!='Y')
{
	if(!empty($REDIRECT['OLD_URL'])) $REDIRECT['OLD_URL'] = urldecode($REDIRECT['OLD_URL']);
	if(!empty($REDIRECT['NEW_URL'])) $REDIRECT['NEW_URL'] = urldecode($REDIRECT['NEW_URL']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
{
	$listUrl = 'esol_redirector_redirect_list.php?lang='.LANGUAGE_ID;
	if($_POST['cancel']) LocalRedirect($listUrl);
	
	if(isset($_POST['Update']) && $_POST['Update'] === 'Y' && is_array($REDIRECT))
	{
		$REDIRECT['OLD_URL'] = trim($REDIRECT['OLD_URL']);
		if(strlen($REDIRECT['OLD_URL'])==0) $arErrors[] = Loc::getMessage('ESOL_RR_NOT_SET_OLD_URL');
		$REDIRECT['NEW_URL'] = trim($REDIRECT['NEW_URL']);
		if(strlen($REDIRECT['NEW_URL'])==0 && $REDIRECT['STATUS']!=410) $arErrors[] = Loc::getMessage('ESOL_RR_NOT_SET_NEW_URL');
		if(empty($REDIRECT['SITE_ID'])) $arErrors[] = Loc::getMessage('ESOL_RR_NOT_SET_SITE_ID');
		if(empty($arErrors) && \Bitrix\EsolRedirector\RedirectTable::getList(array('filter'=>array('=OLD_URL'=>$REDIRECT['OLD_URL'], '=ACTIVE'=>'Y', '!ID'=>(int)$REDIRECT['ID']), 'select'=>array('ID')))->Fetch()) $arErrors[] = Loc::getMessage('ESOL_RR_ERROR_DOUBLE');
		
		if(empty($arErrors))
		{
			$arSites = $REDIRECT['SITE_ID'];
			$arRedirect = $REDIRECT;
			$allowFields = array('ACTIVE','OLD_URL','NEW_URL','STATUS','WSUBSECTIONS','WGETPARAMS','REGEXP','FOR404','COMMENT','SITE_ID');
			$arRedirect2 = array_intersect_key($arRedirect, array_flip($allowFields));
			if(array_key_exists('ID', $arRedirect))
			{
				$dbRes = \Bitrix\EsolRedirector\RedirectTable::update($arRedirect['ID'], $arRedirect2);
				if(!$dbRes->isSuccess())
				{
					$arErrors = $dbRes->GetErrorMessages();
				}
			}
			else
			{
				$dbRes = \Bitrix\EsolRedirector\RedirectTable::add($arRedirect2);
				$redirectId = $dbRes->getId();
				if(!$dbRes->isSuccess())
				{
					$arErrors = $dbRes->GetErrorMessages();
				}
				elseif($redirectId > 0)
				{
					if($_POST['save']) LocalRedirect($listUrl);
					else LocalRedirect(\Bitrix\Main\Context::getCurrent()->getRequest()->getRequestedPage().'?ID='.$redirectId.'&lang='.LANGUAGE_ID);
				}
			}
		}
	}
	
	if($_POST['save'] && empty($arErrors)) LocalRedirect($listUrl);
}

if(empty($REDIRECT) && $ID)
{
	$REDIRECT = \Bitrix\EsolRedirector\RedirectTable::getList(array('filter'=>array('ID'=>$ID)))->Fetch();
	$REDIRECT['SITE_ID'] = array();
	$dbRes = \Bitrix\EsolRedirector\RedirectSiteTable::getList(array('filter'=>array('REDIRECT_ID' => $ID)));
	while($arr = $dbRes->Fetch())
	{
		$REDIRECT['SITE_ID'][] = $arr['SITE_ID'];
	}
}

if($ERROR_ID)
{
	$arError404 = \Bitrix\EsolRedirector\ErrorsTable::getList(array('filter'=>array('ID'=>$ERROR_ID)))->Fetch();
	if(!isset($REDIRECT['OLD_URL']) || strlen($REDIRECT['OLD_URL'])==0)
	{
		$arUrl404 = parse_url($arError404['URL']);
		$REDIRECT['OLD_URL'] = $arUrl404['path'].(strlen($arUrl404['query']) > 0 ? '?'.$arUrl404['query'] : '');
	}
	if(!isset($REDIRECT['SITE_ID']) || empty($REDIRECT['SITE_ID']))
	{
		$REDIRECT['SITE_ID'] = array($arError404['SITE_ID']);
	}
}

$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("ESOL_RR_TO_REDIRECT_LIST"),
		"ICON" => "btn_list",
		"LINK" => $moduleFilePrefix."_redirect_list.php?lang=".LANG
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

if($configErrors = \Bitrix\EsolRedirector\Utils::GetConfigErrors())
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'error',
		'MESSAGE' => $configErrors,
		'DETAILS' => '',
		'HTML' => true
	));
}

if(!empty($arErrors))
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'error',
		'MESSAGE' => implode('<br>', $arErrors),
		'DETAILS' => '',
		'HTML' => true
	));
}

$tabControl->Begin();
?>
<form method="POST" action="<?echo htmlspecialcharsbx($APPLICATION->GetCurPage())?>?<?if($ID){echo 'ID='.(int)$ID.'&';}?><?if($ERROR_ID){echo 'ERROR_ID='.(int)$ERROR_ID.'&';}?>lang=<?echo LANGUAGE_ID?>" name="esol_redirect_item">
<? echo bitrix_sessid_post();

$tabControl->BeginNextTab();
?>

<?if($REDIRECT['ID']){?>
<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_ID'); ?>:</td>
	<td width="50%">
		<?echo htmlspecialcharsbx($REDIRECT['ID']);?>
		<input type="hidden" name="REDIRECT[ID]" value="<?echo htmlspecialcharsbx($REDIRECT['ID'])?>">
	</td>
</tr>
<?}?>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_ACTIVE'); ?>:</td>
	<td width="50%">
		<input type="hidden" name="REDIRECT[ACTIVE]" value="N">
		<input type="checkbox" name="REDIRECT[ACTIVE]" value="Y" <?if($REDIRECT['ACTIVE']!='N'){echo 'checked';}?>>
	</td>
</tr>

<tr>
	<td width="50%"><b><? echo Loc::getMessage('ESOL_RR_OLD_URL'); ?></b>: <span id="hint_OLD_URL"></span><script>BX.hint_replace(BX('hint_OLD_URL'), '<?echo GetMessage("ESOL_RR_URL_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="text" name="REDIRECT[OLD_URL]" value="<?echo htmlspecialcharsbx($REDIRECT['OLD_URL'])?>" size="60">
		<br><? echo sprintf(Loc::getMessage('ESOL_RR_URL_EXAMPLE'), '<i>/catalog/bikes/</i>'); ?>
	</td>
</tr>

<tr id="esol_rr_new_url_wrap" <?if($REDIRECT['STATUS']==410){echo 'style="display: none;"';}?>>
	<td width="50%"><b><? echo Loc::getMessage('ESOL_RR_NEW_URL'); ?></b>: <span id="hint_NEW_URL"></span><script>BX.hint_replace(BX('hint_NEW_URL'), '<?echo GetMessage("ESOL_RR_URL_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="text" name="REDIRECT[NEW_URL]" value="<?echo htmlspecialcharsbx($REDIRECT['NEW_URL'])?>" size="60">
		<br><? echo sprintf(Loc::getMessage('ESOL_RR_URL_EXAMPLE'), '<i>/catalog/bikes/</i>'); ?>
	</td>
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
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_STATUS'); ?>:</td>
	<td width="50%">
		<?echo SelectBoxFromArray("REDIRECT[STATUS]", $arStatusDropdown, $REDIRECT['STATUS'], '', 'onchange="$(\'#esol_rr_new_url_wrap\').css(\'display\', this.value==410 ? \'none\' : \'\');"');?>
	</td>
</tr>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_WSUBSECTIONS'); ?>: <span id="hint_WSUBSECTIONS"></span><script>BX.hint_replace(BX('hint_WSUBSECTIONS'), '<?echo GetMessage("ESOL_RR_WSUBSECTIONS_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="hidden" name="REDIRECT[WSUBSECTIONS]" value="N">
		<input type="checkbox" name="REDIRECT[WSUBSECTIONS]" value="Y" <?if($REDIRECT['WSUBSECTIONS']!='N'){echo 'checked';}?>>
	</td>
</tr>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_WGETPARAMS'); ?>: <span id="hint_WGETPARAMS"></span><script>BX.hint_replace(BX('hint_WGETPARAMS'), '<?echo GetMessage("ESOL_RR_WGETPARAMS_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="hidden" name="REDIRECT[WGETPARAMS]" value="N">
		<input type="checkbox" name="REDIRECT[WGETPARAMS]" value="Y" <?if($REDIRECT['WGETPARAMS']=='Y'){echo 'checked';}?>>
	</td>
</tr>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_REGEXP'); ?>: <span id="hint_REGEXP"></span><script>BX.hint_replace(BX('hint_REGEXP'), '<?echo GetMessage("ESOL_RR_REGEXP_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="hidden" name="REDIRECT[REGEXP]" value="N">
		<input type="checkbox" name="REDIRECT[REGEXP]" value="Y" <?if($REDIRECT['REGEXP']=='Y'){echo 'checked';}?>>
	</td>
</tr>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_FOR404'); ?>: <span id="hint_FOR404"></span><script>BX.hint_replace(BX('hint_FOR404'), '<?echo GetMessage("ESOL_RR_FOR404_HINT"); ?>');</script></td>
	<td width="50%">
		<input type="hidden" name="REDIRECT[FOR404]" value="N">
		<input type="checkbox" name="REDIRECT[FOR404]" value="Y" <?if($REDIRECT['FOR404']=='Y'){echo 'checked';}?>>
	</td>
</tr>

<?
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
//if(!isset($REDIRECT['SITE_ID']) || empty($REDIRECT['SITE_ID']) && !empty($arSitesDropdown['reference_id']))
if(!$REDIRECT['ID'])
{
	$REDIRECT['SITE_ID'] = array(current($arSitesDropdown['reference_id']));
}
?>
<tr>
	<td width="50%"><b><? echo Loc::getMessage('ESOL_RR_SITE'); ?></b>:</td>
	<td width="50%">
		<?echo SelectBoxMFromArray("REDIRECT[SITE_ID][]", $arSitesDropdown, $REDIRECT['SITE_ID'], "", false, 4);?>
	</td>
</tr>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_COMMENT'); ?>:</td>
	<td width="50%">
		<textarea name="REDIRECT[COMMENT]" rows="3" cols="50"><?echo htmlspecialcharsbx($REDIRECT['COMMENT'])?></textarea>
	</td>
</tr>

<?
$tabControl->Buttons();?>
<input type="hidden" name="Update" value="Y">
<input type="submit" name="save" class="adm-btn-save" value="<?echo Loc::getMessage("ESOL_RR_BTN_SAVE")?>">
<input type="submit" name="apply" value="<?echo Loc::getMessage("ESOL_RR_BTN_APPLY")?>">
<input type="submit" name="cancel" value="<?echo Loc::getMessage("ESOL_RR_BTN_CANCEL")?>">
<?$tabControl->End();?>
</form>

<?
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>