<?
use Bitrix\Main\Loader,
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
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/settings.php");

include_once(dirname(__FILE__).'/../install/demo.php');
if (call_user_func($moduleDemoExpiredFunc)) {
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	call_user_func($moduleShowDemoFunc);
	require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$MODULE_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if($MODULE_RIGHT < "W") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(GetMessage("ESOL_RR_SETTINGS_PAGE_TITLE"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

if(1 /*$USER->IsAdmin()*/)
{
	$arSites = array();
	$dbRes = \Bitrix\Main\SiteTable::GetList(array('order'=>array('SORT'=>'ASC')));
	while($arr = $dbRes->Fetch())
	{
		$arSites[] = $arr;
	}
	
	$aTabs = array(
		array("DIV" => "edit0", "TAB" => Loc::getMessage("ESOL_RR_SETTINGS"), "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_SETTINGS_TITLE"))
	);
	if(count($arSites) > 1)
	{
		foreach($arSites as $arSite)
		{
			$aTabs[] = array("DIV" => "edit_".$arSite['LID'], "TAB" => $arSite['LID'], "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_SETTINGS_SITE_TITLE").' '.$arSite['LID']);
		}
	}
	if($USER->IsAdmin())
	{
		$aTabs[] = array("DIV" => "edit2", "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"), "ICON" => "form_settings", "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"));
	}
	$tabControl = new CAdminTabControl("esolRedirectorTabControl", $aTabs, true, true);

	/*if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['RestoreDefaults']) && !empty($_GET['RestoreDefaults']) && check_bitrix_sessid())
	{
		\Bitrix\Main\Config\Option::delete($moduleId);
		LocalRedirect($APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.'&mid_menu=1&mid='.$moduleId);
	}*/

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
	{
		if(isset($_POST['Update']) && $_POST['Update'] === 'Y' && is_array($_POST['SETTINGS']))
		{
			$eManager = \Bitrix\Main\EventManager::getInstance();
			$arEvents = array(
				'OnAfterIBlockElementAdd',
				'OnBeforeIBlockElementUpdate',
				'OnAfterIBlockElementUpdate',
				'OnBeforeIBlockElementDelete',
				'OnAfterIBlockSectionAdd',
				'OnBeforeIBlockSectionUpdate',
				'OnAfterIBlockSectionUpdate',
				'OnBeforeIBlockSectionDelete'
			);
			if($_POST['SETTINGS']['IBLOCK_AUTO_REDIRECT']=='Y' || strlen($_POST['SETTINGS']['REDIRECT_FROM_OLD_SE']) > 0)
			{
				foreach($arEvents as $eventName)
				{
					$eManager->registerEventHandler("iblock", $eventName, $moduleId, "\Bitrix\EsolRedirector\Events", $eventName, 1);
				}
			}
			else
			{
				foreach($arEvents as $eventName)
				{
					$eManager->unRegisterEventHandler("iblock", $eventName, $moduleId, "\Bitrix\EsolRedirector\Events", $eventName);
				}
			}
			
			if($_POST['SETTINGS']['SLASH_AUTO_REDIRECT'] && is_callable(array('\Bitrix\Main\Composite\Helper', 'getOptions')))
			{
				$slashMask = '*[^/]';
				$isUpdate = false;
				$compositeOptions = \Bitrix\Main\Composite\Helper::getOptions();
				$arMasks = array_diff(array_map('trim', explode(';', $compositeOptions['EXCLUDE_MASK'])), array(''));
				if(strpos($_POST['SETTINGS']['SLASH_AUTO_REDIRECT'], 'WITH_SLASH')!==false)
				{
					if(!in_array($slashMask, $arMasks))
					{
						$arMasks[] = $slashMask;
						$isUpdate = true;
					}
				}
				else
				{
					if(in_array($slashMask, $arMasks))
					{
						$arMasks = array_diff($arMasks, array($slashMask));
						$isUpdate = true;
					}
				}
				if($isUpdate)
				{
					$compositeOptions['EXCLUDE_MASK'] = implode('; ', $arMasks);
					\Bitrix\Main\Composite\Helper::setOptions($compositeOptions);
				}
			}
			
			$allowedKeys = array('HTTPS_AUTO_REDIRECT','WWW_AUTO_REDIRECT','SLASH_AUTO_REDIRECT','REDIRECT_404','REDIRECT_INDEX_PHP','REPLACE_MULTI_SLASH','TOLOWER_REDIRECT','OLD_JS_CSS_REDIRECT','STAT_404_ERROR','STAT_404_ERROR_LIMIT','IBLOCK_AUTO_REDIRECT','REDIRECT_FROM_OLD_SE','REDIRECT_EXCLUDE','REDIRECT_AUTO_EXCLUDE','STAT404_URL_EXCLUDE','STAT404_UAGENT_EXCLUDE', 'SITE_SETTINGS_ENABLE');
			foreach($_POST['SETTINGS'] as $k=>$v)
			{
				$baseKey = preg_replace('/_[a-z0-9]{2}$/i', '', $k);
				if(!in_array($k, $allowedKeys) && !in_array($baseKey, $allowedKeys)) continue;
				
				if($baseKey==='STAT_404_ERROR_LIMIT') $v = (int)$v;
				elseif(in_array($baseKey, array('REPLACE_MULTI_SLASH','TOLOWER_REDIRECT','OLD_JS_CSS_REDIRECT','STAT_404_ERROR','IBLOCK_AUTO_REDIRECT'))) $v = ($v=='Y' ? 'Y' : 'N');
				elseif($baseKey==='HTTPS_AUTO_REDIRECT' && !in_array($v, array('N','HTTPS','HTTP'))) $v = 'N';
				elseif($baseKey==='WWW_AUTO_REDIRECT' && !in_array($v, array('N','WITH_WWW','WITHOUT_WWW'))) $v = 'N';
				elseif($baseKey==='SLASH_AUTO_REDIRECT' && !in_array($v, array('N','WITH_SLASH','WITHOUT_SLASH','WITH_SLASH_404','WITHOUT_SLASH_404'))) $v = 'N';
				elseif($baseKey==='REDIRECT_404' && !in_array($v, array('N','MAIN','PARENT'))) $v = 'N';
				elseif($baseKey==='REDIRECT_INDEX_PHP' && !in_array($v, array('N','PHP','PHP_HTML'))) $v = 'N';
				elseif($baseKey==='REDIRECT_FROM_OLD_SE' && !in_array($v, array('N','PARENT','MAIN','410'))) $v = 'N';
				
				
				if($k=='IBLOCK_AUTO_REDIRECT')
				{
					/*$eManager = \Bitrix\Main\EventManager::getInstance();
					$arEvents = array(
						'OnBeforeIBlockElementUpdate',
						'OnAfterIBlockElementUpdate',
						'OnBeforeIBlockElementDelete',
						'OnBeforeIBlockSectionUpdate',
						'OnAfterIBlockSectionUpdate',
						'OnBeforeIBlockSectionDelete'
					);
					foreach($arEvents as $eventName)
					{
						if($v=='Y') $eManager->registerEventHandler("iblock", $eventName, $moduleId, "\Bitrix\EsolRedirector\Events", $eventName);
						else $eManager->unRegisterEventHandler("iblock", $eventName, $moduleId, "\Bitrix\EsolRedirector\Events", $eventName);
					}*/
				}
				elseif($k=='STAT_404_ERROR')
				{
					//$eManager = \Bitrix\Main\EventManager::getInstance();
					/*if($v=='Y') $eManager->registerEventHandler("main", 'OnAfterEpilog', $moduleId, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');
					else $eManager->unRegisterEventHandler("main", 'OnAfterEpilog', $moduleId, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');*/
				}
				\Bitrix\Main\Config\Option::set($moduleId, $k, $v);
			}
			$eManager->registerEventHandler("main", 'OnAfterEpilog', $moduleId, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');
			$eManager->registerEventHandler("main", 'OnEpilog', $moduleId, "\Bitrix\EsolRedirector\Events", 'OnEpilog');
		}
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


	$tabControl->Begin();
	?>
	<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANGUAGE_ID?>" name="esol_redirector_settings">
	<? echo bitrix_sessid_post();

	$tabControl->BeginNextTab();
	?>
	<?/*?>
	<tr class="heading">
		<td colspan="2"><? echo Loc::getMessage('ESOL_RR_OPTIONS_COMMON_SETTINGS'); ?></td>
	</tr>
	<?*/?>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_HTTPS_AUTO_REDIRECT'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'HTTPS_AUTO_REDIRECT');?>
			<select name="SETTINGS[HTTPS_AUTO_REDIRECT]">
				<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
				<option value="HTTPS" <?if($val=='HTTPS'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_HTTP_TO_HTTPS'); ?></option>
				<option value="HTTP" <?if($val=='HTTP'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_HTTPS_TO_HTTP'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_WWW_AUTO_REDIRECT'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'WWW_AUTO_REDIRECT');?>
			<select name="SETTINGS[WWW_AUTO_REDIRECT]">
				<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
				<option value="WITH_WWW" <?if($val=='WITH_WWW'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_WWW'); ?></option>
				<option value="WITHOUT_WWW" <?if($val=='WITHOUT_WWW'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_WWW'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_SLASH_AUTO_REDIRECT'); ?> <span id="hint_SLASH_AUTO_REDIRECT"></span><script>BX.hint_replace(BX('hint_SLASH_AUTO_REDIRECT'), '<?echo GetMessage("ESOL_RR_SLASH_AUTO_REDIRECT_HINT"); ?>');</script></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'SLASH_AUTO_REDIRECT');?>
			<select name="SETTINGS[SLASH_AUTO_REDIRECT]">
				<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
				<option value="WITH_SLASH" <?if($val=='WITH_SLASH'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_SLASH'); ?></option>
				<option value="WITHOUT_SLASH" <?if($val=='WITHOUT_SLASH'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_SLASH'); ?></option>
				<option value="WITH_SLASH_404" <?if($val=='WITH_SLASH_404'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_SLASH_404'); ?></option>
				<option value="WITHOUT_SLASH_404" <?if($val=='WITHOUT_SLASH_404'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_SLASH_404'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_404'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_404');?>
			<select name="SETTINGS[REDIRECT_404]">
				<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
				<option value="MAIN" <?if($val=='MAIN'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_404_MAIN'); ?></option>
				<option value="PARENT" <?if($val=='PARENT'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_404_PARENT'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_INDEX_PHP');?>
			<select name="SETTINGS[REDIRECT_INDEX_PHP]">
				<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
				<option value="PHP" <?if($val=='PHP'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX_PHP'); ?></option>
				<option value="PHP_HTML" <?if($val=='PHP_HTML'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX_PHP_HTML'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_REPLACE_MULTI_SLASH'); ?></td>
		<td width="50%">
			<input type="hidden" name="SETTINGS[REPLACE_MULTI_SLASH]" value="N">
			<input type="checkbox" name="SETTINGS[REPLACE_MULTI_SLASH]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'REPLACE_MULTI_SLASH')=='Y'){echo ' checked';}?>>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_TOLOWER_REDIRECT'); ?></td>
		<td width="50%">
			<input type="hidden" name="SETTINGS[TOLOWER_REDIRECT]" value="N">
			<input type="checkbox" name="SETTINGS[TOLOWER_REDIRECT]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'TOLOWER_REDIRECT')=='Y'){echo ' checked';}?>>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_OLD_JS_CSS_REDIRECT'); ?></td>
		<td width="50%">
			<input type="hidden" name="SETTINGS[OLD_JS_CSS_REDIRECT]" value="N">
			<input type="checkbox" name="SETTINGS[OLD_JS_CSS_REDIRECT]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'OLD_JS_CSS_REDIRECT')=='Y'){echo ' checked';}?>>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_STAT_404_ERROR'); ?></td>
		<td width="50%">
			<input type="hidden" name="SETTINGS[STAT_404_ERROR]" value="N">
			<input type="checkbox" name="SETTINGS[STAT_404_ERROR]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'STAT_404_ERROR')=='Y'){echo ' checked';}?>>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_STAT_404_ERROR_LIMIT'); ?></td>
		<td width="50%">
			<?
			$limitVal = \Bitrix\Main\Config\Option::get($moduleId, 'STAT_404_ERROR_LIMIT', '');
			if(strlen($limitVal)==0 && !is_numeric($limitVal)) $limitVal = 10000;
			$limitVal = (int)$limitVal;
			?>
			<input type="text" name="SETTINGS[STAT_404_ERROR_LIMIT]" value="<?echo $limitVal;?>">
		</td>
	</tr>
	
	<tr class="heading">
		<td colspan="2"><? echo Loc::getMessage('ESOL_RR_OPTIONS_IBLOCK_SETTINGS'); ?></td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_IBLOCK_AUTO_REDIRECT'); ?></td>
		<td width="50%">
			<input type="hidden" name="SETTINGS[IBLOCK_AUTO_REDIRECT]" value="N">
			<input type="checkbox" name="SETTINGS[IBLOCK_AUTO_REDIRECT]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'IBLOCK_AUTO_REDIRECT')=='Y'){echo ' checked';}?>>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_IBLOCK_REDIRECT_FROM_OLD_SE'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_FROM_OLD_SE');?>
			<select name="SETTINGS[REDIRECT_FROM_OLD_SE]">
				<option value=""><? echo Loc::getMessage('ESOL_RR_VAL_NO'); ?></option>
				<option value="PARENT" <?if($val=='PARENT'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_IBLOCK_REDIRECT_FROM_OLD_SE_PARENT'); ?></option>
				<option value="MAIN" <?if($val=='MAIN'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_IBLOCK_REDIRECT_FROM_OLD_SE_MAIN'); ?></option>
				<option value="410" <?if($val=='410'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_IBLOCK_REDIRECT_FROM_OLD_SE_410'); ?></option>
			</select>
		</td>
	</tr>
	
	<tr class="heading">
		<td colspan="2"><? echo Loc::getMessage('ESOL_RR_REDIRECT_EXCLUDE_TITLE'); ?></td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_EXCLUDE'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_EXCLUDE');?>
			<textarea name="SETTINGS[REDIRECT_EXCLUDE]" rows="5" cols="60"><?echo htmlspecialcharsbx($val)?></textarea>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_AUTO_EXCLUDE'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_AUTO_EXCLUDE');?>
			<textarea name="SETTINGS[REDIRECT_AUTO_EXCLUDE]" rows="5" cols="60"><?echo htmlspecialcharsbx($val)?></textarea>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_STAT404_URL_EXCLUDE'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'STAT404_URL_EXCLUDE');?>
			<textarea name="SETTINGS[STAT404_URL_EXCLUDE]" rows="5" cols="60"><?echo htmlspecialcharsbx($val)?></textarea>
		</td>
	</tr>
	
	<tr>
		<td width="50%"><? echo Loc::getMessage('ESOL_RR_STAT404_UAGENT_EXCLUDE'); ?></td>
		<td width="50%">
			<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'STAT404_UAGENT_EXCLUDE');?>
			<textarea name="SETTINGS[STAT404_UAGENT_EXCLUDE]" rows="5" cols="60"><?echo htmlspecialcharsbx($val)?></textarea>
		</td>
	</tr>

	<?
	if(count($arSites) > 1)
	{
		foreach($arSites as $arSite)
		{
			$siffix = '_'.$arSite['LID'];
			$tabControl->BeginNextTab();
			?>
			<tr>
			<td width="50%"><? echo Loc::getMessage('ESOL_RR_SITE_SETTINGS_ENABLE'); ?></td>
			<td width="50%">
				<?
					$val = \Bitrix\Main\Config\Option::get($moduleId, 'SITE_SETTINGS_ENABLE'.$siffix);
					$bEnable = (bool)($val=='Y');
					$sbId = 'esol_site_settings'.$siffix;
				?>
				<input type="hidden" name="SETTINGS[SITE_SETTINGS_ENABLE<?echo $siffix;?>]" value="N">
				<input type="checkbox" name="SETTINGS[SITE_SETTINGS_ENABLE<?echo $siffix;?>]" value="Y" <?if($val=='Y'){echo ' checked';}?> onchange="document.getElementById('<?echo $sbId;?>').style.display = (this.checked ? 'block' : 'none');">
			</td>
			</tr>
			<tr>
			<td colspan="2">
			<div id="<?echo $sbId;?>"<?if(!$bEnable){echo ' style="display: none;"';}?>>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_HTTPS_AUTO_REDIRECT'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'HTTPS_AUTO_REDIRECT'.$siffix);?>
						<select name="SETTINGS[HTTPS_AUTO_REDIRECT<?echo $siffix;?>]">
							<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
							<option value="HTTPS" <?if($val=='HTTPS'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_HTTP_TO_HTTPS'); ?></option>
							<option value="HTTP" <?if($val=='HTTP'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_HTTPS_TO_HTTP'); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_WWW_AUTO_REDIRECT'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'WWW_AUTO_REDIRECT'.$siffix);?>
						<select name="SETTINGS[WWW_AUTO_REDIRECT<?echo $siffix;?>]">
							<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
							<option value="WITH_WWW" <?if($val=='WITH_WWW'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_WWW'); ?></option>
							<option value="WITHOUT_WWW" <?if($val=='WITHOUT_WWW'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_WWW'); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_SLASH_AUTO_REDIRECT'); ?> <span id="hint_SLASH_AUTO_REDIRECT"></span><script>BX.hint_replace(BX('hint_SLASH_AUTO_REDIRECT'), '<?echo GetMessage("ESOL_RR_SLASH_AUTO_REDIRECT_HINT"); ?>');</script></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'SLASH_AUTO_REDIRECT'.$siffix);?>
						<select name="SETTINGS[SLASH_AUTO_REDIRECT<?echo $siffix;?>]">
							<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
							<option value="WITH_SLASH" <?if($val=='WITH_SLASH'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_SLASH'); ?></option>
							<option value="WITHOUT_SLASH" <?if($val=='WITHOUT_SLASH'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_SLASH'); ?></option>
							<option value="WITH_SLASH_404" <?if($val=='WITH_SLASH_404'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITH_SLASH_404'); ?></option>
							<option value="WITHOUT_SLASH_404" <?if($val=='WITHOUT_SLASH_404'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_WITHOUT_SLASH_404'); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_404'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_404'.$siffix);?>
						<select name="SETTINGS[REDIRECT_404<?echo $siffix;?>]">
							<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
							<option value="MAIN" <?if($val=='MAIN'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_404_MAIN'); ?></option>
							<option value="PARENT" <?if($val=='PARENT'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_404_PARENT'); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_INDEX_PHP'.$siffix);?>
						<select name="SETTINGS[REDIRECT_INDEX_PHP<?echo $siffix;?>]">
							<option value="N"><? echo Loc::getMessage('ESOL_RR_VAL_OFF'); ?></option>
							<option value="PHP" <?if($val=='PHP'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX_PHP'); ?></option>
							<option value="PHP_HTML" <?if($val=='PHP_HTML'){echo ' selected';}?>><? echo Loc::getMessage('ESOL_RR_REDIRECT_INDEX_PHP_HTML'); ?></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_REPLACE_MULTI_SLASH'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<input type="hidden" name="SETTINGS[REPLACE_MULTI_SLASH<?echo $siffix;?>]" value="N">
						<input type="checkbox" name="SETTINGS[REPLACE_MULTI_SLASH<?echo $siffix;?>]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'REPLACE_MULTI_SLASH'.$siffix)=='Y'){echo ' checked';}?>>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_TOLOWER_REDIRECT'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<input type="hidden" name="SETTINGS[TOLOWER_REDIRECT<?echo $siffix;?>]" value="N">
						<input type="checkbox" name="SETTINGS[TOLOWER_REDIRECT<?echo $siffix;?>]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'TOLOWER_REDIRECT'.$siffix)=='Y'){echo ' checked';}?>>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_OLD_JS_CSS_REDIRECT'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<input type="hidden" name="SETTINGS[OLD_JS_CSS_REDIRECT<?echo $siffix;?>]" value="N">
						<input type="checkbox" name="SETTINGS[OLD_JS_CSS_REDIRECT<?echo $siffix;?>]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'OLD_JS_CSS_REDIRECT'.$siffix)=='Y'){echo ' checked';}?>>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_STAT_404_ERROR'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<input type="hidden" name="SETTINGS[STAT_404_ERROR<?echo $siffix;?>]" value="N">
						<input type="checkbox" name="SETTINGS[STAT_404_ERROR<?echo $siffix;?>]" value="Y" <?if(\Bitrix\Main\Config\Option::get($moduleId, 'STAT_404_ERROR'.$siffix)=='Y'){echo ' checked';}?>>
					</td>
				</tr>
				
				<tr class="heading">
					<td colspan="2"><? echo Loc::getMessage('ESOL_RR_REDIRECT_EXCLUDE_TITLE'); ?></td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" width="50%"><? echo Loc::getMessage('ESOL_RR_REDIRECT_EXCLUDE'); ?></td>
					<td class="adm-detail-content-cell-r" width="50%">
						<?$val = \Bitrix\Main\Config\Option::get($moduleId, 'REDIRECT_EXCLUDE'.$siffix);?>
						<textarea name="SETTINGS[REDIRECT_EXCLUDE<?echo $siffix;?>]" rows="5" cols="60"><?echo htmlspecialcharsbx($val)?></textarea>
					</td>
				</tr>
			</table>
			</div>
			</td>
			</tr>
			<?
		}
	}
	
	if($USER->IsAdmin())
	{
		$tabControl->BeginNextTab();
		$module_id = $moduleId;
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	}

	$tabControl->Buttons();?>
<script type="text/javascript">
function RestoreDefaults()
{
	if (confirm('<? echo CUtil::JSEscape(Loc::getMessage("ESOL_RR_OPTIONS_BTN_HINT_RESTORE_DEFAULT_WARNING")); ?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?lang=<? echo LANGUAGE_ID; ?>&mid_menu=1&mid=<? echo $moduleId; ?>&RestoreDefaults=Y&<?=bitrix_sessid_get()?>";
}
</script>
	<input type="submit" name="Update" value="<?echo Loc::getMessage("ESOL_RR_OPTIONS_BTN_SAVE")?>">
	<input type="hidden" name="Update" value="Y">
	<?/*?><input type="reset" name="reset" value="<?echo Loc::getMessage("ESOL_RR_OPTIONS_BTN_RESET")?>">
	<input type="button" title="<?echo Loc::getMessage("ESOL_RR_OPTIONS_BTN_HINT_RESTORE_DEFAULT")?>" onclick="RestoreDefaults();" value="<?echo Loc::getMessage("ESOL_RR_OPTIONS_BTN_RESTORE_DEFAULT")?>"><?*/?>
	<?$tabControl->End();?>
	</form>
<?
}
?>

<?
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>