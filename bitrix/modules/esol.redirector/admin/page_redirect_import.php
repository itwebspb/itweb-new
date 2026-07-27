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
CJSCore::Init(array('fileinput', $moduleJsId));
Loc::loadMessages(__FILE__);
$settingsOptionName = 'IMPORT_OPTIONS';

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

$APPLICATION->SetTitle(Loc::getMessage("ESOL_RR_REDIRECT_IMPORT_PAGE_TITLE"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if (!call_user_func($moduleDemoExpiredFunc)) {
	call_user_func($moduleShowDemoFunc);
}

$STEP = intval($STEP);
if ($STEP <= 0)
	$STEP = 1;

if($_SERVER["REQUEST_METHOD"] == "POST")
{
	if(isset($_POST["backButton"]) && strlen($_POST["backButton"]) > 0) $STEP = $STEP - 2;
	if(isset($_POST["backButton2"]) && strlen($_POST["backButton2"]) > 0) $STEP = 1;
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_1"), "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_1")),
	array("DIV" => "edit2", "TAB" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_2"), "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_2")),
	array("DIV" => "edit3", "TAB" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_3"), "ICON" => "", "TITLE" => Loc::getMessage("ESOL_RR_REDIRECT_TAB_3")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$SHOW_FIRST_LINES = 10;

function ShowTblLine($data, $list, $line, $checked = true)
{
	?><tr><td class="line-settings">
			<input type="hidden" name="SETTINGS[IMPORT_LINE][<?echo $list;?>][<?echo $line;?>]" value="0">
			<input type="checkbox" name="SETTINGS[IMPORT_LINE][<?echo $list;?>][<?echo $line;?>]" value="1" <?if($checked){echo 'checked';}?>>
		</td><?
		foreach($data as $row)
		{
			$style = $parentStyle = $dataStyle = '';
			$parentStyle = '';
			if($row['STYLE'])
			{
				if($row['STYLE']['BACKGROUND'] && preg_match('/^[0-9a-fA-F]{3,8}$/', trim($row['STYLE']['BACKGROUND'])))
				{
					$style .= 'background-color:#'.$row['STYLE']['BACKGROUND'].';';
					$parentStyle .= 'background-color:#'.$row['STYLE']['BACKGROUND'].';';
				}
				if($row['STYLE']['COLOR'] && preg_match('/^[0-9a-fA-F]{3,8}$/', trim($row['STYLE']['COLOR']))) $style .= 'color:#'.$row['STYLE']['COLOR'].';';
				if($row['STYLE']['FONT-WEIGHT']) $style .= 'font-weight:bold;';
				if($row['STYLE']['FONT-STYLE']) $style .= 'font-style:italic;';
				if($row['STYLE']['TEXT-DECORATION']=='single') $style .= 'text-decoration:underline;';
			}
			$style = ($style ? 'style="'.$style.'"' : '');
			$parentStyle = ($parentStyle ? 'style="'.$parentStyle.'"' : '');
		?><td <?echo $parentStyle;?>><div class="cell" <?echo $parentStyle;?>><div class="cell_inner" <?echo $style;?>><?echo htmlspecialcharsbx($row['VALUE']);?></div></div></td><?
		}
	?></tr><?
}

$arErrors = array();

if(!isset($SETTINGS) || !is_array($SETTINGS))
{
	$SETTINGS = \Bitrix\Main\Config\Option::get($moduleId, $settingsOptionName);
	$SETTINGS = \Bitrix\EsolRedirector\Utils::Unserialize($SETTINGS);
	if(!is_array($SETTINGS)) $SETTINGS = array();
}
if ($REQUEST_METHOD == "POST" && $STEP > 1 && check_bitrix_sessid())
{
	if($ACTION) define('PUBLIC_AJAX_MODE', 'Y');
	
	//*****************************************************************//	
	if ($STEP > 1)
	{
		//*****************************************************************//	
		$sess = $_SESSION;
		session_write_close();
		$_SESSION = $sess;
		
		if (empty($arErrors))
		{
			if($STEP==2)
			{
				if(isset($_FILES["DATA_FILE"]) && $_FILES["DATA_FILE"]["error"]!=4)
				{
					if(is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
					{
						$fid = \CFile::SaveFile($_FILES["DATA_FILE"], $moduleId);
						if($fid && $_POST['DATA_FILE_OLD_ID'])
						{
							\CFile::Delete($_POST['DATA_FILE_OLD_ID']);
						}
					}
				}
				elseif(isset($_POST['DATA_FILE_OLD_ID']) && strlen($_POST['DATA_FILE_OLD_ID']) > 0)
				{
					if($arFile = \CFile::MakeFileArray($_POST['DATA_FILE_OLD_ID']))
					{
						$fid = (int)($_POST['DATA_FILE_OLD_ID']);
					}
				}
				
				if($fid)
				{
					$SETTINGS['DATA_FILE_ID'] = $fid;
				}
				else
				{
					$arErrors[] = Loc::getMessage("ESOL_RR_FILE_UPLOAD_ERROR")."<br>";
				}
				
				if(!empty($arErrors)) $STEP = 1;
			}
		}
	}
	
	$DATA_FILE_NAME = '';
	if($SETTINGS['DATA_FILE_ID'] > 0)
	{
		$arFile = \CFile::GetFileArray($SETTINGS['DATA_FILE_ID']);
		$DATA_FILE_NAME = \Bitrix\Main\IO\Path::convertLogicalToPhysical($arFile['SRC']);
	}
	
	if($ACTION == 'SHOW_PREVIEW_LIST')
	{
		try{
			$arWorksheets = \Bitrix\EsolRedirector\Importer::GetPreviewData($DATA_FILE_NAME, $SHOW_FIRST_LINES, $SETTINGS_DEFAULT);
		}catch(Exception $ex){
			$APPLICATION->RestartBuffer();
			ob_end_clean();
			echo Loc::getMessage("ESOL_RR_ERROR").$ex->getMessage();
			die();
		}
		$arFields = \Bitrix\EsolRedirector\RedirectTable::GetImportedFields();
		
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		
		foreach($SETTINGS as $k=>$v)
		{
			if(is_array($v)) continue;
			echo '<input type="hidden" name="SETTINGS['.htmlspecialcharsex($k).']" value="'.htmlspecialcharsex($v).'">';
		}
		
		if(!$arWorksheets) $arWorksheets = array();
		foreach($arWorksheets as $k=>$worksheet)
		{
			$columns = (count($worksheet['lines']) > 0 ? count($worksheet['lines'][0]) : 1) + 1;
			$bEmptyList = empty($worksheet['lines']);
		?>
			<table class="kda-ie-tbl <?if($bEmptyList){echo 'empty';}?>" data-list-index="<?echo $k;?>">
				<?/*?><tr class="heading">
					<td class="left"><?echo Loc::getMessage("ESOL_RR_LIST_TITLE"); ?> "<?echo $worksheet['title'];?>" <?if($bEmptyList){echo Loc::getMessage("ESOL_RR_EMPTY_LIST");}?></td>
					<td class="right list-settings">
						<?if(count($worksheet['lines']) > 0){?>
							<input type="hidden" name="SETTINGS[LIST_LINES][<?echo $k;?>]" value="<?echo $worksheet['lines_count'];?>">
							<?
						}?>
					</td>
				</tr><?*/?>
				<tr class="settings">
					<td colspan="2">
						<div class="set_scroll">
							<div></div>
						</div>
						<div class="set">						
						<table class="list">
						<?
						if(count($worksheet['lines']) > 0)
						{
							?>
								<tr>
									<td>
										<?if(count($worksheet['lines']) > 0){?>
											<input type="hidden" name="SETTINGS[LIST_LINES][<?echo $k;?>]" value="<?echo $worksheet['lines_count'];?>">
											<?
										}?>
										<input type="hidden" name="SETTINGS[CHECK_ALL][<?echo $k;?>]" value="0"> 
										<input type="checkbox" name="SETTINGS[CHECK_ALL][<?echo $k;?>]" id="check_all_<?echo $k;?>" value="1" <?if(!isset($SETTINGS['CHECK_ALL'][$k]) || $SETTINGS['CHECK_ALL'][$k]){echo 'checked';}?>> 
										<label for="check_all_<?echo $k;?>"><?echo Loc::getMessage("ESOL_RR_CHECK_ALL"); ?></label>
										<select name="FIELDS_LIST[<?echo $k;?>]"><option value=""><?echo Loc::getMessage("ESOL_RR_CHOOSE_FIELD");?></option>
										<?
										foreach($arFields as $fieldKey=>$fieldName)
										{
											echo '<option value="'.htmlspecialcharsex($fieldKey).'">'.$fieldName.'</option>';
										}
										?>
										</select>
									</td>
									<?
									$num_rows = count($worksheet['lines'][0]);
									for($i = 0; $i < $num_rows; $i++)
									{
										$arKeys = array($i);
										if(is_array($SETTINGS['FIELDS_LIST'][$k]))
											$arKeys = array_merge($arKeys, preg_grep('/^'.$i.'_\d+$/', array_keys($SETTINGS['FIELDS_LIST'][$k])));
										?>
										<td class="kda-ie-field-select">
											<?foreach($arKeys as $j){?>
												<div>
													<input type="hidden" name="SETTINGS[FIELDS_LIST][<?echo (int)$k?>][<?echo htmlspecialcharsbx($j)?>]" value="<?echo htmlspecialcharsbx($SETTINGS['FIELDS_LIST'][$k][$j])?>" >
													<span class="fieldval_wrap"><span class="fieldval" id="field-list-show-<?echo (int)$k?>-<?echo htmlspecialcharsbx($j)?>"></span></span>
													<a href="javascript:void(0)" class="field_settings"></a>
													<a href="javascript:void(0)" class="field_delete" title="<?echo Loc::getMessage("ESOL_RR_SETTINGS_DELETE_FIELD"); ?>" onclick="EList.DeleteUploadField(this);"></a>
												</div>
											<?}?>
											<div class="kda-ie-field-select-btns">
												<div class="kda-ie-field-select-btns-inner">
													<a href="javascript:void(0)" class="kda-ie-add-load-field" title="<?echo Loc::getMessage("ESOL_RR_SETTINGS_ADD_FIELD"); ?>" onclick="EList.AddUploadField(this);"></a>
												</div>
											</div>
										</td>
										<?
									}
									?>
								</tr>
							<?
							
						}			
						
						foreach($worksheet['lines'] as $line=>$arLine)
						{
							$checked = ((!isset($SETTINGS['IMPORT_LINE'][$k][$line]) && (!isset($SETTINGS['CHECK_ALL'][$k]) || $SETTINGS['CHECK_ALL'][$k])) || $SETTINGS['IMPORT_LINE'][$k][$line]);
							if($line==0 && !isset($SETTINGS['IMPORT_LINE']) && !isset($SETTINGS['CHECK_ALL']))
							{
								$checked = false;
							}
							ShowTblLine($arLine, $k, $line, $checked);
						}
						?>
						</table>
						</div>
						<br><br>
					</td>
				</tr>
			</table>
		<?
			break;
		}
		die();
	}
	
	if($ACTION == 'DO_IMPORT')
	{
		unset($EXTRASETTINGS);
		$EXTRASETTINGS = array();
		if(!is_array($SETTINGS_DEFAULT)) $SETTINGS_DEFAULT = array();
		$pid = 0;
		/*$oProfile = new CKDAImportProfile();
		$oProfile->ApplyExtra($EXTRASETTINGS, $PROFILE_ID);*/
		$params = array_merge($SETTINGS_DEFAULT, $SETTINGS);
		$stepparams = $_POST['stepparams'];
		$ee = \Bitrix\EsolRedirector\Importer::getInstance(0);
		$ee->setParams($DATA_FILE_NAME, $params, $EXTRASETTINGS, $stepparams, $pid);
		$arResult = $ee->Import();
		$APPLICATION->RestartBuffer();
		if(ob_get_contents()) ob_end_clean();
		echo \Bitrix\EsolRedirector\Utils::PhpToJSObject($arResult);
		
		//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
		die();
	}
}
\Bitrix\Main\Config\Option::set($moduleId, $settingsOptionName, serialize($SETTINGS));

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
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANGUAGE_ID?>" enctype="multipart/form-data" name="esol_redirect_item">
<? 
echo bitrix_sessid_post();

$tabControl->BeginNextTab();
if($STEP == 1)
{
?>

<tr>
	<td width="50%"><? echo Loc::getMessage('ESOL_RR_CHOOSE_FILE'); ?> (xls/xlsx/csv):</td>
	<td width="50%" class="esol-rr-file-choose">
		<input type="hidden" name="DATA_FILE_OLD_ID" value="<?echo htmlspecialcharsex($SETTINGS["DATA_FILE_ID"]);?>">
		<?/*?><input type="file" name="DATA_FILE" value=""><?*/?>
		<?
		$arFile = CFile::GetFileArray($SETTINGS["DATA_FILE_ID"]);
		if(!$arFile || !file_exists($_SERVER['DOCUMENT_ROOT'].$arFile['SRC'])) $SETTINGS["DATA_FILE_ID"] = '';
		?>
		<?echo \CFileInput::Show("DATA_FILE", $SETTINGS["DATA_FILE_ID"], array(
			"IMAGE" => "N",
			"PATH" => "Y",
			"FILE_SIZE" => "Y",
			"DIMENSIONS" => "N"
		), array(
			'upload' => true,
			'medialib' => false,
			'file_dialog' => false,
			'cloud' => false,
			'del' => false,
			'description' => false,
		));?>
	</td>
</tr>
<?/*?>
<tr>
	<td><?echo GetMessage("ESOL_RR_DELETE_OLD_REDIRECTS"); ?>:</td>
	<td>
		<input type="checkbox" name="SETTINGS[DELETE_OLD_REDIRECTS]" value="Y" <?if($SETTINGS['DELETE_OLD_REDIRECTS']=='Y'){echo 'checked';}?>>
	</td>
</tr>
<?*/?>
<tr>
	<td colspan="2">
	<?
	echo BeginNote();
	echo GetMessage("ESOL_RR_IMPORT1STEP_NOTE");
	echo EndNote();
	?>
	</td>
</tr>

<?
}
$tabControl->EndTab();
$tabControl->BeginNextTab();
if($STEP == 2)
{
?>
	<tr>
		<td colspan="2" id="preview_file">
			<div class="esol-rr-file-preloader">
				<?echo Loc::getMessage("ESOL_RR_PRELOADING"); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<?
		echo BeginNote();
		echo GetMessage("ESOL_RR_IMPORT2STEP_NOTE");
		echo EndNote();
		?>
		</td>
	</tr>
<?
}
$tabControl->EndTab();
$tabControl->BeginNextTab();
if ($STEP == 3)
{
?>
	<tr>
		<td id="resblock" class="kda-ie-result">
		 <table width="100%"><tr><td width="50%">
			<div id="progressbar"><span class="pline"></span><span class="presult load"><b>0%</b><span 
				data-import="<?echo Loc::getMessage("ESOL_RR_STATUS_IMPORT"); ?>" 
			><?echo Loc::getMessage("ESOL_RR_IMPORT_INIT"); ?></span></span></div>

			<div id="block_error_import" style="display: none;">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "ERROR",
					"MESSAGE" => Loc::getMessage("ESOL_RR_IMPORT_ERROR_CONNECT"),
					"DETAILS" => '<div><a href="javascript:void(0)" onclick="EProfile.ContinueProccess(this, '.$PROFILE_ID.');" id="kda_ie_continue_link">'.Loc::getMessage("ESOL_RR_PROCESSED_CONTINUE").'</a></div>',
					"HTML" => true,
				))?>
			</div>
			
			<div id="block_error" style="display: none;">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "ERROR",
					"MESSAGE" => Loc::getMessage("ESOL_RR_IMPORT_ERROR"),
					"DETAILS" => '<div id="res_error"></div>',
					"HTML" => true,
				))?>
			</div>
		 </td><td>
			<div class="detail_status">
				<?echo CAdminMessage::ShowMessage(array(
					"TYPE" => "PROGRESS",
					"MESSAGE" => '',
					"DETAILS" =>
					Loc::getMessage("ESOL_RR_SU_ALL").' <b id="total_line">0</b><br>'
					.Loc::getMessage("ESOL_RR_SU_CORR").' <b id="correct_line">0</b><br>'
					.Loc::getMessage("ESOL_RR_SU_ER").' <b id="error_line">0</b><br>'
					.Loc::getMessage("ESOL_RR_SU_RECORD_ADDED").' <b id="element_added_line">0</b><br>'
					.Loc::getMessage("ESOL_RR_SU_RECORD_UPDATED").' <b id="element_updated_line">0</b><br>',
					"HTML" => true,
				))?>
			</div>
		 </td></tr></table>
		</td>
	</tr>
<?
}
$tabControl->EndTab();
$tabControl->Buttons();

if($STEP == 2)
{
?>
<input type="submit" name="backButton" value="&lt;&lt; <?echo Loc::getMessage("ESOL_RR_BACK"); ?>">
<?
}

if($STEP < 3)
{
?>
	<input type="hidden" name="STEP" value="<?echo $STEP + 1; ?>">
	<input type="submit" value="<?echo ($STEP == 2) ? Loc::getMessage("ESOL_RR_NEXT_STEP_F") : Loc::getMessage("ESOL_RR_NEXT_STEP"); ?> &gt;&gt;" name="submit_btn" class="adm-btn-save">
<? 
}
else
{
?>
	<input type="hidden" name="STEP" value="1">
	<input type="submit" name="backButton2" value="&lt;&lt; <?echo GetMessage("ESOL_RR_BACK"); ?>" class="adm-btn-save">
<?
}

$tabControl->End();?>
</form>

<script language="JavaScript">
<?if ($STEP < 2): ?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
tabControl.DisableTab("edit3");
<?elseif ($STEP == 2): ?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit3");

<?elseif ($STEP > 2): ?>
tabControl.SelectTab("edit3");
tabControl.DisableTab("edit1");
tabControl.DisableTab("edit2");

<?
$arPost = $_POST;
$arPost['PROFILE_ID'] = 0;
unset($arPost['EXTRASETTINGS']);
if(COption::GetOptionString($moduleId, 'SET_MAX_EXECUTION_TIME')=='Y')
{
	$delay = (int)COption::GetOptionString($moduleId, 'EXECUTION_DELAY');
	$stepsTime = (int)COption::GetOptionString($moduleId, 'MAX_EXECUTION_TIME');
	if($delay > 0) $arPost['STEPS_DELAY'] = $delay;
	if($stepsTime > 0) $arPost['STEPS_TIME'] = $stepsTime;
}
else
{
	$stepsTime = intval(ini_get('max_execution_time'));
	if($stepsTime > 0) $arPost['STEPS_TIME'] = $stepsTime;
}
if($_POST['PROCESS_CONTINUE']=='Y'){
	/*$oProfile = new CKDAImportProfile();
?>
	EImport.Init(<?=\Bitrix\EsolRedirector\Utils::PhpToJSObject($arPost);?>, <?=\Bitrix\EsolRedirector\Utils::PhpToJSObject($oProfile->GetProccessParams($_POST['PROFILE_ID']));?>);
<?*/}else{?>
	EImport.Init(<?=\Bitrix\EsolRedirector\Utils::PhpToJSObject($arPost);?>);
<?}?>
<?endif; ?>
</script>

<?
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>