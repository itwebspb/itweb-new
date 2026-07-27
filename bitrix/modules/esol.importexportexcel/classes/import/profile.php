<?php
IncludeModuleLangFile(__FILE__);

/*
$storage = 'fs';
if(class_exists('\Bitrix\Main\Entity\DataManager'))
{
	$profileDB = new \Bitrix\KdaImportexcel\ProfileTable();
	$conn = $profileDB->getEntity()->getConnection();
	if($conn->getType()=='mysql')
	{
		$storage = 'db';
	}
}*/

$storage = 'db';

if($storage=='db')
{
	class CKDAImportProfile extends CKDAImportProfileDB {}
	if(is_callable(array($conn, 'queryExecute')))
	{
		$conn->queryExecute('SET wait_timeout=1800');
		$conn->queryExecute('SET sql_mode=""');
		$conn->queryExecute('SET SQL_BIG_SELECTS=1');
	}
	if(is_callable('\Bitrix\Main\Application', 'getInstance') && is_callable('\Bitrix\Main\Application', 'getExceptionHandler'))
	{
		$app = \Bitrix\Main\Application::getInstance();
		$app->getExceptionHandler()->setDebugMode(true);
	}
	if(is_callable(array($conn, 'stopTracker')))
	{
		$conn->stopTracker();
	}
	if(isset($GLOBALS['DB']) && is_object($GLOBALS['DB']))
	{
		$GLOBALS['DB']->debug = true;
		$GLOBALS['DB']->DebugToFile = false;
	}
}
else
{
	class CKDAImportProfile extends CKDAImportProfileFS {}
}

class CKDAImportProfileAll {
	protected static $moduleId = 'esol.importexportexcel';
	protected static $instance = array();
	protected static $arChangedCols = array();
	private $importTmpDir = null;
	private $pid = null;
	private $errors = array();
	private $params = array();
	
	public static function getInstance($suffix='iblock')
	{
		if (!isset(static::$instance[$suffix]))
			static::$instance[$suffix] = new static($suffix=='iblock' ? '' : $suffix);

		return static::$instance[$suffix];
	}
	
	public function GetErrors()
	{
		if(!isset($this->errors) || !is_array($this->errors)) $this->errors = array();
		return implode('<br>', array_unique($this->errors));
	}
	
	public function GetProfileListForMenu($PROFILE_ID)
	{
		return $this->GetList(is_numeric($PROFILE_ID) && strlen($PROFILE_ID) > 0 ? array('LOGIC'=>'OR', array(array('LOGIC'=>'OR', array('GROUP.ACTIVE'=>'Y'), array('GROUP.ID'=>false)), 'ACTIVE'=>'Y'), array('ID'=>$PROFILE_ID+1)) : array(), true);
	}
	
	public function ShowProfileList($fname, $PROFILE_ID='', $allowNew=true)
	{
		$arProfiles = $this->GetProfileListForMenu($PROFILE_ID);
		?><select name="<?echo $fname;?>" id="<?echo $fname;?>" onchange="EProfile.Choose(this)" style="max-width: 400px; padding-right: 30px;"><?
			?><option value=""><?echo GetMessage("KDA_IE_NO_PROFILE"); ?></option><?
			if($allowNew)
			{
				?><option value="new" <?if($_REQUEST[$fname]=='new'){echo 'selected';}?>><?echo GetMessage("KDA_IE_NEW_PROFILE"); ?></option><?
			}
			foreach($arProfiles as $groupId=>$arGroup)
			{
				if(strlen($arGroup['NAME']) > 0) echo '<optgroup label="'.htmlspecialcharsbx($arGroup['NAME']).'">';
				foreach($arGroup['LIST'] as $k=>$profile)
				{
					?><option value="<?echo $k;?>" <?if((strlen($PROFILE_ID)>0 && strval($PROFILE_ID)===strval($k)) || (strlen($_REQUEST[$fname])>0 && strval($_REQUEST[$fname])===strval($k))){echo 'selected';}?>><?echo '['.$k.'] '.$profile; ?></option><?
				}
				if(strlen($arGroup['NAME']) > 0) echo '</optgroup>';
			}
		?></select><?
	}
	
	public function UpdateFileSettings(&$params, &$extraParams, $file, $ID, $bCron=false)
	{
		$arProfile = $this->GetByID($ID);
		if(!isset($arProfile['SETTINGS']) || !is_array($arProfile['SETTINGS'])) return false;
		$cronBreak = (bool)($bCron && \Bitrix\Main\Config\Option::get(static::$moduleId, 'CRON_BREAK_WITH_CHANGE_TITLES', 'N')=='Y');
		
		$titlesLine = array();
		$titlesLineForSave = array();
		if(isset($arProfile['SETTINGS']['LIST_SETTINGS']) && is_array($arProfile['SETTINGS']['LIST_SETTINGS']))
		{
			foreach($arProfile['SETTINGS']['LIST_SETTINGS'] as $lk=>$ls)
			{
				if($arProfile['SETTINGS']['LIST_ACTIVE'][$lk]!=='Y') continue;
				if(isset($ls['SET_TITLES']))
				{
					if($ls['BIND_FIELDS_TO_HEADERS']==1 || $cronBreak)
					{
						$titlesLine[$lk] = (int)$ls['SET_TITLES'];
					}
					if($ls['BIND_FIELDS_TO_HEADERS']==1)
					{
						$titlesLineForSave[$lk] = (int)$ls['SET_TITLES'];
					}
				}
			}
		}
		
		if(!empty($titlesLine))
		{
			if(!isset($arProfile['SETTINGS']['OLDBINDPARAMS']))
			{
				$arProfile['SETTINGS']['OLDBINDPARAMS'] = array(
					'TITLES_LIST' => $arProfile['SETTINGS']['TITLES_LIST'],
					'FIELDS_LIST' => $arProfile['SETTINGS']['FIELDS_LIST'],
					'EXTRASETTINGS' => $arProfile['EXTRASETTINGS'],
				);
			}
			$isChanges = false;
			self::$arChangedCols = array();
			$maxLine = max($titlesLine);
			if(is_array($file)) $arWorksheets = $file;
			else $arWorksheets = CKDAImportExcel::GetPreviewData($file, max(10, $maxLine+1), $arProfile['SETTINGS_DEFAULT'], $COUNT_COLUMNS, $ID);
			foreach($titlesLine as $listkey=>$lineKey)
			{
				if(!isset($arWorksheets[$listkey]['lines'][$lineKey])) continue;
				$arLine = $arWorksheets[$listkey]['lines'][$lineKey];
				/*$arOldTitles = array_map(array($this, 'Trim'), $arProfile['SETTINGS']['TITLES_LIST'][$listkey]);
				$arOldFields = $arProfile['SETTINGS']['FIELDS_LIST'][$listkey];
				$arOldExtra = $arProfile['EXTRASETTINGS'][$listkey];*/
				if(true /*isset($arProfile['SETTINGS']['OLDBINDPARAMS'])*/)
				{
					$arOldTitles = $arOldFields = $arOldExtra = array();
					if(isset($arProfile['SETTINGS']['OLDBINDPARAMS']['TITLES_LIST'][$listkey]) && is_array($arProfile['SETTINGS']['OLDBINDPARAMS']['TITLES_LIST'][$listkey]))
					{
						$arOldTitles = array_map(array($this, 'Trim'), $arProfile['SETTINGS']['OLDBINDPARAMS']['TITLES_LIST'][$listkey]);
					}
					if(isset($arProfile['SETTINGS']['OLDBINDPARAMS']['FIELDS_LIST'][$listkey]) && is_array($arProfile['SETTINGS']['OLDBINDPARAMS']['FIELDS_LIST'][$listkey]))
					{
						$arOldFields = $arProfile['SETTINGS']['OLDBINDPARAMS']['FIELDS_LIST'][$listkey];
					}
					if(isset($arProfile['SETTINGS']['OLDBINDPARAMS']['EXTRASETTINGS'][$listkey]) && is_array($arProfile['SETTINGS']['OLDBINDPARAMS']['EXTRASETTINGS'][$listkey]))
					{
						$arOldExtra = $arProfile['SETTINGS']['OLDBINDPARAMS']['EXTRASETTINGS'][$listkey];
					}
				}
				$IBLOCK_ID = $arProfile['SETTINGS']['IBLOCK_ID'][$listkey];
				$arTitles = array();
				$arTitlesOrig = array();
				foreach($arLine as $k=>$v)
				{
					$arTitles[$k] = $this->Trim(preg_replace('/[\r\n\s]+/', ' ', ToLower($v['VALUE'])));
					$arTitlesOrig[$k] = $v['VALUE'];
				}
				foreach(GetModuleEvents(static::$moduleId, "OnBeforeCheckTitles", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array(&$arTitles, &$arOldTitles));
				}
				$arFields = array();
				$arExtra = array();
				$arChangeKeys = array();
				foreach($arOldFields as $k=>$v)
				{
					$key = $k;
					if(strpos($k, '_')!==false) $key = current(explode('_', $k));
					if($arTitles[$key]===$arOldTitles[$key]) $newKey = $key;
					else $newKey = array_search($arOldTitles[$key], $arTitles);
					if(strlen($v) > 0 && ($newKey===false || $key!=$newKey))
					{
						$isChanges = true;
						self::$arChangedCols[$key + 1] = array('OLD'=>$arOldTitles[$key], 'NEW'=>$arTitles[$key]);
					}
					if($newKey===false) continue;
					$newKeyFull = $newKey.(strpos($k, '_')!==false ? '_'.end(explode('_', $k, 2)) : '');
					if(array_key_exists($newKeyFull, $arFields) && strlen($v)==0) continue;
					if($key!=$newKey) $arChangeKeys[$key] = $newKey;
					//if(strpos($k, '_')!==false) $newKey .= '_'.end(explode('_', $k, 2));
					$arFields[$newKeyFull] = $v;
					$arExtra[$newKeyFull] = $arOldExtra[$k];
				}
				foreach($arOldExtra as $k=>$v)
				{
					if(!isset($arExtra[$k])) $arExtra[$k] = $v;
				}

				/*update conversions*/
				if(count($arChangeKeys) > 0)
				{
					$arChangeKeys1 = array();
					$arChangeKeys2 = array();
					$arChangeKeys3 = array();
					foreach($arChangeKeys as $oldKey=>$newKey)
					{
						$arChangeKeys1[$oldKey + 1] = $newKey + 1;
						$arChangeKeys2['CELL'.($oldKey + 1)] = 'CELL'.($newKey + 1);
						$arChangeKeys3['#CELL'.($oldKey + 1).'#'] = '#CELL'.($newKey + 1).'#';
					}
					foreach($arExtra as $k=>$v)
					{
						if(isset($v['CONVERSION']) && is_array($v['CONVERSION']))
						{
							foreach($v['CONVERSION'] as $k2=>$v2)
							{
								if(isset($v2['CELL']) && !is_array($v2['CELL']) && array_key_exists($v2['CELL'], $arChangeKeys1)) $arExtra[$k]['CONVERSION'][$k2]['CELL'] = $arChangeKeys1[$v2['CELL']];
								if(isset($v2['FROM']) && !is_array($v2['FROM'])) $arExtra[$k]['CONVERSION'][$k2]['FROM'] = strtr($v2['FROM'], $arChangeKeys3);
								if(isset($v2['TO']) && !is_array($v2['TO'])) $arExtra[$k]['CONVERSION'][$k2]['TO'] = strtr($v2['TO'], $arChangeKeys3);
							}
						}
						if(isset($v['EXTRA_CONVERSION']) && is_array($v['EXTRA_CONVERSION']))
						{
							foreach($v['EXTRA_CONVERSION'] as $k2=>$v2)
							{
								if(isset($v2['CELL']) && !is_array($v2['CELL']) && array_key_exists($v2['CELL'], $arChangeKeys2)) $arExtra[$k]['EXTRA_CONVERSION'][$k2]['CELL'] = $arChangeKeys2[$v2['CELL']];
								if(isset($v2['FROM']) && !is_array($v2['FROM'])) $arExtra[$k]['EXTRA_CONVERSION'][$k2]['FROM'] = strtr($v2['FROM'], $arChangeKeys3);
								if(isset($v2['TO']) && !is_array($v2['TO'])) $arExtra[$k]['EXTRA_CONVERSION'][$k2]['TO'] = strtr($v2['TO'], $arChangeKeys3);
							}
						}
					}
				}
				/*/update conversions*/

				if(isset($titlesLineForSave[$listkey]))
				{
					if(count($arOldTitles)==0 && count($arFields)==0 && count($arOldFields) > 0)
					{
						$arFields = $arOldFields;
						$arExtra = $arOldExtra;
					}
					if($arProfile['SETTINGS_DEFAULT']['AUTO_CREATION_PROPERTIES']=='Y')
					{
						$this->AddNewPropsToFile($arFields, $arTitlesOrig, $IBLOCK_ID);
					}
					uksort($arFields, array(__CLASS__, 'SortFieldsByIndex'));
					uksort($arExtra, array(__CLASS__, 'SortFieldsByIndex'));
					$arProfile['SETTINGS']['TITLES_LIST'][$listkey] = $arTitles;
					$arProfile['SETTINGS']['FIELDS_LIST'][$listkey] = $arFields;
					$arProfile['EXTRASETTINGS'][$listkey] = $arExtra;
				}
			}
			$params = array_merge($params, $arProfile['SETTINGS']);
			$extraParams = $arProfile['EXTRASETTINGS'];
			
			if($isChanges && $cronBreak) return false;
			$this->Update($ID, $arProfile['SETTINGS_DEFAULT'], $arProfile['SETTINGS']);
			$this->UpdateExtra($ID, $arProfile['EXTRASETTINGS']);
		}
		return true;
	}
	
	public function GetChangedColsTbl()
	{
		if(!is_array(self::$arChangedCols) || empty(self::$arChangedCols)) return '';
		$tbl = '<table border="1"><tr><th colspan="3">'.GetMessage("KDA_IE_CHANGE_FILE").'</th></tr><tr><th>'.GetMessage("KDA_IE_CHANGE_COLUMN_NUMBER").'</th><th>'.GetMessage("KDA_IE_CHANGE_COLUMN_OLD_VAL").'</th><th>'.GetMessage("KDA_IE_CHANGE_COLUMN_NEW_VAL").'</th></tr>';
		foreach(self::$arChangedCols as $k=>$v)
		{
			$tbl .= '<tr><td>'.htmlspecialcharsbx($k).'</td><td>'.htmlspecialcharsbx($v['OLD']).'</td><td>'.htmlspecialcharsbx($v['NEW']).'</td></tr>';
		}
		$tbl .= '</table>';
		return $tbl;
	}
	
	public function Trim($str)
	{
		$str = trim($str);
		$str = preg_replace('/(^(\xC2\xA0|\s)+|(\xC2\xA0|\s)+$)/s', '', $str);
		return $str;
	}
	
	public static function SortFieldsByIndex($a, $b)
	{
		$a1=current(explode("_", $a));
		$b1=current(explode("_", $b)); 
		if($a1==$b1)
		{
			$a2=(int)substr($a, strlen($a1)+1);
			$b2=(int)substr($b, strlen($b1)+1); 
			return ($a2 < $b2 ? -1 : 1);
		}
		return ($a1 < $b1 ? -1 : 1);
	}
	
	public function AddNewPropsToFile(&$arFields, $arTitles, $IBLOCK_ID)
	{
		$arPropNames = array();
		$arPropCodes = array();
		$dbRes = \CIBlockProperty::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
		while($arr = $dbRes->Fetch())
		{
			$arPropNames[ToLower($arr['NAME'])] = $arr['ID'];
			$arPropCodes[ToLower($arr['CODE'])] = $arr['ID'];
		}
		
		foreach($arTitles as $k=>$v)
		{
			$arKeys = preg_grep('/^'.$k.'(_|$)/', array_keys($arFields));
			$isField = false;
			foreach($arKeys as $k2)
			{
				if(strlen(trim($arFields[$k2])) > 0) $isField = true;
			}
			if(!$isField)
			{
				$maxLen = 50;
				$name = trim($v);
				$name = trim(preg_replace('/\{[^\{]*\}\s*$/Uis', '', $name));
				$code = '';
				if(preg_match('/\[([^\[]*)\]\s*$/Uis', $name, $m))
				{
					$code = trim($m[1]);
					$lowerCode = ToLower($code);
					if(isset($arPropCodes[$lowerCode])) $propId = $arPropCodes[$lowerCode];
					$name = trim(substr($name, 0, -strlen($m[0])));
				}
				$lowerName = ToLower($name);
				$propId = 0;
				if(isset($arPropNames[$lowerName])) $propId = $arPropNames[$lowerName];
				
				if($propId==0 && strlen($code)==0)
				{
					$arParams = array(
						'max_len' => $maxLen,
						'change_case' => 'U',
						'replace_space' => '_',
						'replace_other' => '_',
						'delete_repeat_replace' => 'Y',
					);
					$code = \CUtil::translit($name, LANGUAGE_ID, $arParams);
					$code = preg_replace('/[^a-zA-Z0-9_]/', '', $code);
					$code = preg_replace('/^[0-9_]+/', '', $code);
					$lowerCode = ToLower($code);
					if(isset($arPropCodes[$lowerCode])) $propId = $arPropCodes[$lowerCode];
				}
				
				if($propId==0)
				{			
					$arPropFields = Array(
						"NAME" => $name,
						"ACTIVE" => "Y",
						"CODE" => $code,
						"PROPERTY_TYPE" => "S",
						"IBLOCK_ID" => $IBLOCK_ID
					);
					$ibp = new \CIBlockProperty;
					$newPropId = $ibp->Add($arPropFields);
					if($newPropId > 0)
					{
						$propId = $newPropId;
						$arPropCodes[$lowerCode] = $propId;
						$arPropNames[$lowerName] = $propId;
					}
				}
				
				if($propId > 0)
				{
					$arFields[$k] = 'IP_PROP'.$propId;
				}
			}
		}
	}
	
	public function Apply(&$settigs_default, &$settings, $ID)
	{
		$arProfile = $this->GetByID($ID);
		if(!is_array($settigs_default) && is_array($arProfile['SETTINGS_DEFAULT']))
		{
			$settigs_default = $arProfile['SETTINGS_DEFAULT'];
		}
		if(!is_array($settings) && is_array($arProfile['SETTINGS']))
		{
			$settings = $arProfile['SETTINGS'];
		}
		
		$this->CheckUserSettings($settigs_default, $arProfile);
		
		if(is_array($settings))
		{
			if(is_array($settings['FIELDS_LIST']))
			{
				foreach($settings['FIELDS_LIST'] as $listkey=>$arFields)
				{
					uksort($arFields, array(__CLASS__, 'SortFieldsByIndex'));
					$settings['FIELDS_LIST'][$listkey] = $arFields;
				}
			}
			if($settings['ADDITIONAL_SETTINGS'])
			{
				foreach($settings['ADDITIONAL_SETTINGS'] as $k=>$v)
				{
					if($v && !is_array($v))
					{
						$v = \KdaIE\Utils::JsObjectToPhp($v);
					}
					if(!is_array($v)) $v = array();
					$settings['ADDITIONAL_SETTINGS'][$k] = $v;
				}
			}
		}
		if(!is_array($settigs_default)) $settigs_default = array();
		if(!is_array($settings)) $settings = array();
		
		$instance = static::getInstance();
		$instance->SetParams($settigs_default);
	}
	
	public function CheckUserSettings(&$settigs_default, $arProfile=array())
	{
		if(!$GLOBALS['USER']->IsAdmin())
		{
			if(is_array($settigs_default))
			{
				$settigs_default['ONAFTERSAVE_HANDLER'] = '';
				if(is_array($arProfile['SETTINGS_DEFAULT']) && strlen($arProfile['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER']) > 0)
				{
					$settigs_default['ONAFTERSAVE_HANDLER'] = $arProfile['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER'];
				}
			}
		}
	}
	
	public function CheckFileSettings(&$fileParams, $PROFILE_ID, $type = '')
	{
		if(preg_match('/^\s*\{.*\}\s*$/s', $fileParams))
		{
			if(ToLower($PROFILE_ID)=='new') $PROFILE_ID = '';
			$arParams = \KdaIE\Utils::JsObjectToPhp($fileParams);
			$ftype = ($this->suffix=='highload' ? 'hl' : '');
			if($GLOBALS['USER']->IsAdmin() && strlen($arParams['HANDLER_FOR_LINK'].$arParams['HANDLER_FOR_LINK_BASE64']) > 0)
			{
				if($type=='tmp' || strlen($PROFILE_ID)==0)
				{
					$ftype = $ftype.'tmp';
					$arTmpParams = array('PARAMS'=>self::EncodeProfileParams(array('SETTINGS_DEFAULT'=>array('EXT_DATA_FILE'=>$fileParams))));
					self::PrepareProfileFields($arTmpParams, array(), $PROFILE_ID, $ftype);
				}
				else 
				{
					$this->UpdatePartSettings($PROFILE_ID, array('EXT_DATA_FILE'=>$fileParams));
				}
			}
			\CKDAImportUtils::SetProfileIdForExp($PROFILE_ID.$ftype);
			
			if(!$GLOBALS['USER']->IsAdmin())
			{
				$arParams = \KdaIE\Utils::JsObjectToPhp($fileParams);
				$arParams['HANDLER_FOR_LINK'] = '';
				$arParams['HANDLER_FOR_LINK_BASE64'] = '';
				if(strlen($PROFILE_ID) > 0 && is_numeric($PROFILE_ID))
				{
					$arProfile = $this->GetByID($PROFILE_ID);
					if(isset($arProfile['SETTINGS_DEFAULT']['EXT_DATA_FILE']) && strlen($arProfile['SETTINGS_DEFAULT']['EXT_DATA_FILE']) > 0)
					{
						$arOldParams = \KdaIE\Utils::JsObjectToPhp($arProfile['SETTINGS_DEFAULT']['EXT_DATA_FILE']);
						if(isset($arOldParams['HANDLER_FOR_LINK']) && strlen($arOldParams['HANDLER_FOR_LINK']) > 0)
						{
							$arParams['HANDLER_FOR_LINK'] = $arOldParams['HANDLER_FOR_LINK'];
						}
						if(isset($arOldParams['HANDLER_FOR_LINK_BASE64']) && strlen($arOldParams['HANDLER_FOR_LINK_BASE64']) > 0)
						{
							$arParams['HANDLER_FOR_LINK_BASE64'] = $arOldParams['HANDLER_FOR_LINK_BASE64'];
						}
					}
				}
				$fileParams = \KdaIE\Utils::PhpToJSObject($arParams);
			}
		}
	}
	
	public function ApplyExtra(&$extrasettings, $ID)
	{
		$arProfile = $this->GetByID($ID);
		if(!is_array($extrasettings) && is_array($arProfile['EXTRASETTINGS']))
		{
			$extrasettings = $arProfile['EXTRASETTINGS'];
		}
		elseif(!$GLOBALS['USER']->IsAdmin())
		{
			if(is_array($extrasettings))
			{
				$arExpressions = array();
				$arFilterExpressions = array();
				$arPriceExpressions = array();
				if(isset($arProfile['EXTRASETTINGS']) && is_array($arProfile['EXTRASETTINGS']))
				{
					foreach($arProfile['EXTRASETTINGS'] as $k=>$v)
					{
						if(!is_array($v)) continue;
						foreach($v as $k2=>$v2)
						{
							if(!is_array($v2)) continue;
							$arKeys = array('CONVERSION', 'EXTRA_CONVERSION');
							foreach($arKeys as $convKey)
							{
								if(!array_key_exists($convKey, $v2) || !is_array($v2[$convKey])) continue;
								foreach($v2[$convKey] as $k3=>$v3)
								{
									if($v3['THEN']=='EXPRESSION' && strlen($v3['TO']) > 0 && !in_array($v3['TO'], $arExpressions))
									{
										$arExpressions[] = $v3['TO'];
									}
								}
							}
							if(array_key_exists('FILTER_EXPRESSION', $v2) && strlen($v2['FILTER_EXPRESSION']) > 0 && !in_array($v2['FILTER_EXPRESSION'], $arFilterExpressions))
							{
								$arFilterExpressions[] = $v2['FILTER_EXPRESSION'];
							}
							$arPriceKeys = array('PRICE_QUANTITY_FROM', 'PRICE_QUANTITY_TO');
							foreach($arPriceKeys as $k3=>$v3)
							{
								if(array_key_exists($v3, $v2) && strlen($v2[$v3]) > 0 && !in_array($v2[$v3], $arFilterExpressions))
								{
									$arPriceExpressions[] = $v2[$v3];
								}
							}
						}
					}
				}
				
				foreach($extrasettings as $k=>$v)
				{
					if(!is_array($v)) continue;
					foreach($v as $k2=>$v2)
					{
						if(!is_array($v2)) continue;
						$arOldExtra = $arProfile['EXTRASETTINGS'][$k][$k2];
						if(isset($_POST['COL_POSITIONS'][$k][$k2]) && ($arColKeys = explode('_', $_POST['COL_POSITIONS'][$k][$k2])) && count($arColKeys)==2)
						{
							$arOldExtra = $arProfile['EXTRASETTINGS'][$arColKeys[0]][$arColKeys[1]];
						}
						$arKeys = array('CONVERSION', 'EXTRA_CONVERSION');
						foreach($arKeys as $convKey)
						{
							if(!array_key_exists($convKey, $v2) || !is_array($v2[$convKey])) continue;
							foreach($v2[$convKey] as $k3=>$v3)
							{
								if($v3['THEN']=='EXPRESSION' && !in_array($v3['TO'], $arExpressions))
								{
									$extrasettings[$k][$k2][$convKey][$k3]['TO'] = '';
									$index = (array_key_exists('INDEX', $v3) && strlen($v3['INDEX']) > 0 ? $v3['INDEX'] : $k3);
									if(isset($arOldExtra[$convKey][$index]['TO']))
									{
										$extrasettings[$k][$k2][$convKey][$k3]['TO'] = $arOldExtra[$convKey][$index]['TO'];
									}
								}
								if(array_key_exists('INDEX', $v3)) unset($extrasettings[$k][$k2][$convKey][$k3]['INDEX']);
							}
						}
						
						if(array_key_exists('FILTER_EXPRESSION', $v2) && !in_array($v2['FILTER_EXPRESSION'], $arFilterExpressions))
						{
							unset($extrasettings[$k][$k2]['FILTER_EXPRESSION']);
							if(isset($arOldExtra['FILTER_EXPRESSION']))
							{
								$extrasettings[$k][$k2]['FILTER_EXPRESSION'] = $arOldExtra['FILTER_EXPRESSION'];
							}
						}
						
						$arPriceKeys = array('PRICE_QUANTITY_FROM', 'PRICE_QUANTITY_TO');
						foreach($arPriceKeys as $k3=>$v3)
						{
							if(array_key_exists($v3, $v2) && !in_array($v2[$v3], $arPriceExpressions))
							{
								$arParts1 = preg_split('/[+\-\*\/\?:\(\)]/', $v2[$v3]);
								$arParts2 = preg_grep('/^\s*(\s*|\d+|#CELL\d+#)\s*$/', $arParts1);
								if(count($arParts1)!=count($arParts2))
								{
									unset($extrasettings[$k][$k2][$v3]);
									if(isset($arOldExtra[$v3]))
									{
										$extrasettings[$k][$k2][$v3] = $arOldExtra[$v3];
									}
								}
							}
							
						}
					}
				}
			}
		}
	}
	
	public function ProfileExists($ID)
	{
		return false;
	}
	
	public function UpdateFields($ID, $arFields)
	{
		return false;
	}
	
	public function GetProfilesCronPool()
	{
		return array();
	}
	
	public function GetLastImportProfiles($arParams = array())
	{
		return array();
	}
	
	public function GetFieldsByID($ID)
	{
		return array();
	}
	
	public function GetStatus($id)
	{
		return '';
	}
	
	public function UpdatePartSettings($ID, $settigs_default=array())
	{
		
	}
	
	public function SetImportParams($pid, $tmpdir, $arParams, $arImportParams=array())
	{
		$this->pid = $pid;
		$this->importTmpDir = $tmpdir;
		$this->fileElementsId = $this->importTmpDir.'elements_id.txt';
		$this->fileOffersId = $this->importTmpDir.'offers_id.txt';
		$this->importParams = $arImportParams;
	}
	
	public function GetImportParam($pname)
	{
		if(isset($this->importParams) && is_array($this->importParams) && array_key_exists($pname, $this->importParams)) return $this->importParams[$pname];
		else return false;
	}
	
	public function SaveElementId($ID, $type)
	{
		$fn = $this->fileElementsId;
		if($type=='O') $fn = $this->fileOffersId;
		$handle = fopen($fn, 'a');
		fwrite($handle, $ID."\r\n");
		fclose($handle);
		return true;
	}
	
	public function GetLastImportId($type)
	{
		if($type=='E') return CKDAImportUtils::SortFileIds($this->fileElementsId);
		elseif($type=='O') return CKDAImportUtils::SortFileIds($this->fileOffersId);
	}
	
	public function GetUpdatedIds($type, $first)
	{
		if($type=='E') return CKDAImportUtils::GetPartIdsFromFile($this->fileElementsId, $first);
		elseif($type=='O') return CKDAImportUtils::GetPartIdsFromFile($this->fileOffersId, $first);
	}
	
	public function IsAlreadyLoaded($ID, $type)
	{
		$fn = $this->fileElementsId;
		if($type=='O') $fn = $this->fileOffersId;
		
		$find = false;
		if($fn && file_exists($fn))
		{
			$handle = fopen($fn, 'r');
			while(!feof($handle) && !$find)
			{
				$buffer = trim(fgets($handle, 128));
				if($buffer && ($ID == (int)$buffer))
				{
					$find = true;
				}
			}
			fclose($handle);
		}
		
		return $find;
	}
	
	public static function PrepareFieldExpression($exp, $convKey)
	{
		$pattern = '(#CELL~*\d+#|#CELL\d+[\-\+]\d+#|#CELL_[A-Z]+\d+#|#VAL#|#CLINK#|#CNOTE#|#HASH#|#FILENAME#|#FILEDATE#|#SHEETNAME#|#ROWNUMBER#|#IMPORT_PROCESS_ID#|#SEP_SECTION#|#DATETIME#|#DATETIME[\+\-]\d+#|'.implode('|', \KdaIE\Utils::GetCurrencyVariables()).($convKey=='EXTRA_CONVERSION' ? '|#(OFFER_|PARENT_)?(IE_|IP_PROP|ICAT_|IPROP_TEMP_|ISECT|PROP_)[A-Z0-9_]+#' : '').')';
		if(preg_match_all('/(\$\{([\'"]).*(?<!\\\)\2\}|(?<!\\\)([\'"]).*(?<!\\\)\3)/U', $exp, $m))
		{
			foreach($m[0] as $vv)
			{
				if(preg_match('/^\$\{([\'"])/', $vv)) continue;
				$quot = mb_substr($vv, 0, 1);
				$vv2 = $vv;
				$vv2 = preg_replace('/(?<!\$\{[\'"])'.$pattern.'/', $quot.'.${\'$1\'}.'.$quot, $vv2);
				$exp = str_replace($vv, $vv2, $exp);


			}
		}
		$exp = preg_replace('/(?<![\'"])'.$pattern.'/', '${\'$1\'}', $exp);
		return $exp;
	}
	
	public static function PrepareProfileFields(&$arFields, $arOldFields, $profileId, $type='')
	{
		if(/*$GLOBALS['USER']->IsAdmin() ||*/ !array_key_exists('PARAMS', $arFields)) return;
		$oldParams = self::DecodeProfileParams($arOldFields['PARAMS']);
		$newParams = self::DecodeProfileParams($arFields['PARAMS']);
		
		if(strlen($profileId) > 0) $profileId = (int)$profileId;
		
		$arExpressions = array();
		if(is_array($newParams['SETTINGS_DEFAULT']))
		{
			if(strlen($newParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER']) > 0)
			{
				$arExpressions['ONAFTERSAVE'] = $newParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER'];
			}
			
			if(strlen($newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']) > 0 && preg_match('/^\s*\{.*\}\s*$/s', $newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']))
			{
				$arParams = \KdaIE\Utils::JsObjectToPhp($newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']);
				if(strlen($arParams['HANDLER_FOR_LINK_BASE64']) > 0) $handler = base64_decode(trim($arParams['HANDLER_FOR_LINK_BASE64']));
				else $handler = trim($arParams['HANDLER_FOR_LINK']);
				if(strlen($handler) > 0)
				{
					$arExpressions['LINKAUTH'] = $handler;
				}
			}
		}
		
		if(is_array($newParams['EXTRASETTINGS']))
		{
			foreach($newParams['EXTRASETTINGS'] as $k=>$v)
			{
				if(!is_array($v)) continue;
				foreach($v as $k2=>$v2)
				{
					if(!is_array($v2)) continue;
					$arKeys = array('CONVERSION', 'EXTRA_CONVERSION');
					foreach($arKeys as $convKey)
					{
						if(!array_key_exists($convKey, $v2) || !is_array($v2[$convKey])) continue;
						foreach($v2[$convKey] as $k3=>$v3)
						{
							if($v3['THEN']=='EXPRESSION')
							{
								$arExpressions[$k.'|'.$k2.'|'.$k3.'|'.$convKey] = self::PrepareFieldExpression($v3['TO'], $convKey);
							}
						}
					}
					
					if(array_key_exists('FILTER_EXPRESSION', $v2))
					{
						$arExpressions['FILTER_EXPRESSION_'.$k.'|'.$k2] = $v2['FILTER_EXPRESSION'];
					}
					
					/*
					$arPriceKeys = array('PRICE_QUANTITY_FROM', 'PRICE_QUANTITY_TO');
					foreach($arPriceKeys as $k3=>$v3)
					{
						if(array_key_exists($v3, $v2) && strlen($v2[$v3]) > 0 && !in_array($v2[$v3], $arFilterExpressions))
						{
							$arExpressions['PRICE_EXPRESSION_'.$k.'|'.$k2.'|'.$v3] = $v2[$v3];
						}
					}
					*/
				}
			}
		}		
		
		$arFields['EXPRESSIONS'] = (empty($arExpressions) ? 'N' : 'Y');
	
		if($GLOBALS['USER']->IsAdmin())
		{
			$dir = __DIR__ .'/profiles_ext/';
			CheckDirPath($dir);
			
			$fn = $dir.'importer'.$profileId.(strlen($type) > 0 ? '_'.$type : '').'.php';
			if(!empty($arExpressions))
			{
				$blacklist = array('shell_exec', 'exec', 'system', 'passthru', 'proc_open', 'popen', 'assert', 'create_function', 'extract', 'parse_str', 'eval', 'move_uploaded_file', 'symlink', 'file_get_contents', 'file_put_contents', 'curl_exec', 'fopen', 'fwrite', 'fputs', 'unlink', 'rmdir', 'mkdir', 'chmod', 'chown', 'link', 'include', 'require', 'include_once', 'require_once', 'call_user_func', 'call_user_func_array', 'array_map', 'array_filter', 'array_walk', 'array_reduce', 'register_shutdown_function', 'set_exception_handler', 'chr', 'ord', 'base64_decode', 'gzinflate', 'gzdecode', 'str_rot13', 'ReflectionFunction', 'ReflectionMethod', 'Closure', 'forward_static_call', 'forward_static_call_array');
				$arExp = array();
				foreach($arExpressions as $k=>$exp)
				{
					//if (preg_match('/[`]|\b(?:shell_exec|exec|system|passthru|proc_open|popen|assert|create_function|extract|parse_str|eval|move_uploaded_file|symlink|file_get_contents|file_put_contents|curl_exec|fopen|fwrite|fputs|unlink|rmdir|mkdir|chmod|chown|link|include|require|include_once|require_once)\b/', $exp)) continue;
					
					$allow = true;					
					$tokens = token_get_all('<?php '.$exp);
					$prevToken = null;
					foreach($tokens as $token) {
						if(!is_array($token)) {
							// Block variable functions: $func(...)
							if($token === '(' && is_array($prevToken) && $prevToken[0] === T_VARIABLE)
							{
								$allow = false;
							}
							elseif($token === '`')
							{
								$allow = false;
							}
							$prevToken = $token;
							continue;
						}
						if(token_name($token[0])=='T_STRING' && in_array(strtolower($token[1]), $blacklist)) {
							$allow = false;
						}
						$prevToken = $token;
					}
					if(!$allow) continue;
					
					
					if($k=='ONAFTERSAVE')
					{
						$arExp[] = "\t".'public function ExpOnAfterSave($ID){'."\r\n".
						"\t\t".$exp."\r\n".
						"\t}";
						continue;
					}
					
					if(preg_match('/(^|\n)[\r\t\s]*return/is', $exp))
					{
						$command = $exp.';';
					}
					elseif(preg_match('/\$val\s*=[^=]/', $exp))
					{
						$command = $exp.';'."\r\n".'return $val;';
					}
					else
					{
						$command = 'return '.$exp.';';
					}
					
					if($k=='LINKAUTH')
					{
						$arExp[] = "\t".'public function ExpFileLinkAuth($val, $arParams=array()){'."\r\n".
							"\t\t".'if(is_array($arParams)){foreach($arParams as $k=>$v){${$k} = $v;}}'."\r\n".
							"\t\t".$command."\r\n".
							"\t}";
						continue;
					}
					
					if(preg_match('/^FILTER_EXPRESSION_(\d+)\|([_\d]+)$/', $k, $m))
					{
						$arExp[] = "\t".'public function ExpSheet'.$m[1].'Field'.$m[2].'FilterExpression($val){'."\r\n".
							"\t\t".$command."\r\n".
							"\t}";
							continue;
					}
					
					list($k1, $k2, $k3, $k4) = explode('|', $k);
					$arExp[] = "\t".'public function ExpSheet'.$k1.'Field'.$k2.($k4=='EXTRA_CONVERSION' ? 'Extra' : '').'Conv'.$k3.'($val){'."\r\n".
						"\t\t".'if(isset($this->convParams) && is_array($this->convParams)){foreach($this->convParams as $k=>$v){${$k} = $v;}}'."\r\n".
						"\t\t".$command."\r\n".
						"\t}";
				}
				file_put_contents($fn, '<?php'."\r\n".
					'class CKDAImportExcel'.(strlen($type) > 0 ? ucfirst($type) : '').$profileId.' extends CKDAImportExcel'.($type=='hl' ? 'Highload' : '').' {'."\r\n".
					implode("\r\n", $arExp)."\r\n".
					'}');
			}
			elseif(file_exists($fn)) unlink($fn);
		}
		
		if($GLOBALS['USER']->IsAdmin()) return;
		
		
		if(is_array($newParams['SETTINGS_DEFAULT']))
		{
			$newParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER'] = '';
			if(is_array($oldParams['SETTINGS_DEFAULT']) && strlen($oldParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER']) > 0)
			{
				$newParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER'] = $oldParams['SETTINGS_DEFAULT']['ONAFTERSAVE_HANDLER'];
			}
		}
		
		if(isset($newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']) && preg_match('/^\s*\{.*\}\s*$/s', $newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']))
		{
			$arFileParams = \KdaIE\Utils::JsObjectToPhp($newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']);
			$arFileParams['HANDLER_FOR_LINK'] = '';
			$arFileParams['HANDLER_FOR_LINK_BASE64'] = '';
			if(isset($oldParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']) && strlen($oldParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']) > 0)
			{
				$arOldFileParams = \KdaIE\Utils::JsObjectToPhp($oldParams['SETTINGS_DEFAULT']['EXT_DATA_FILE']);
				if(isset($arOldFileParams['HANDLER_FOR_LINK']) && strlen($arOldFileParams['HANDLER_FOR_LINK']) > 0)
				{
					$arFileParams['HANDLER_FOR_LINK'] = $arOldFileParams['HANDLER_FOR_LINK'];
				}
				if(isset($arOldFileParams['HANDLER_FOR_LINK_BASE64']) && strlen($arOldFileParams['HANDLER_FOR_LINK_BASE64']) > 0)
				{
					$arFileParams['HANDLER_FOR_LINK_BASE64'] = $arOldFileParams['HANDLER_FOR_LINK_BASE64'];
				}
			}
			$newParams['SETTINGS_DEFAULT']['EXT_DATA_FILE'] = \KdaIE\Utils::PhpToJSObject($arFileParams);
		}
		
		if(is_array($newParams['EXTRASETTINGS']))
		{
			$arExpressions = array();
			$arFilterExpressions = array();
			$arPriceExpressions = array();
			if(isset($oldParams['EXTRASETTINGS']) && is_array($oldParams['EXTRASETTINGS']))
			{
				foreach($oldParams['EXTRASETTINGS'] as $k=>$v)
				{
					if(!is_array($v)) continue;
					foreach($v as $k2=>$v2)
					{
						if(!is_array($v2)) continue;
						$arKeys = array('CONVERSION', 'EXTRA_CONVERSION');
						foreach($arKeys as $convKey)
						{
							if(!array_key_exists($convKey, $v2) || !is_array($v2[$convKey])) continue;
							foreach($v2[$convKey] as $k3=>$v3)
							{
								if($v3['THEN']=='EXPRESSION' && strlen($v3['TO']) > 0 && !in_array($v3['TO'], $arExpressions))
								{
									$arExpressions[] = $v3['TO'];
								}
							}
						}
						if(array_key_exists('FILTER_EXPRESSION', $v2) && strlen($v2['FILTER_EXPRESSION']) > 0 && !in_array($v2['FILTER_EXPRESSION'], $arFilterExpressions))
						{
							$arFilterExpressions[] = $v2['FILTER_EXPRESSION'];
						}
						$arPriceKeys = array('PRICE_QUANTITY_FROM', 'PRICE_QUANTITY_TO');
						foreach($arPriceKeys as $k3=>$v3)
						{
							if(array_key_exists($v3, $v2) && strlen($v2[$v3]) > 0 && !in_array($v2[$v3], $arFilterExpressions))
							{
								$arPriceExpressions[] = $v2[$v3];
							}
						}
					}
				}
			}
			
			foreach($newParams['EXTRASETTINGS'] as $k=>$v)
			{
				if(!is_array($v)) continue;
				foreach($v as $k2=>$v2)
				{
					if(!is_array($v2)) continue;
					$arOldExtra = $oldParams['EXTRASETTINGS'][$k][$k2];
					/*
					if(isset($_POST['COL_POSITIONS'][$k][$k2]) && ($arColKeys = explode('_', $_POST['COL_POSITIONS'][$k][$k2])) && count($arColKeys)==2)
					{
						$arOldExtra = $oldParams['EXTRASETTINGS'][$arColKeys[0]][$arColKeys[1]];
					}
					*/
					$arKeys = array('CONVERSION', 'EXTRA_CONVERSION');
					foreach($arKeys as $convKey)
					{
						if(!array_key_exists($convKey, $v2) || !is_array($v2[$convKey])) continue;
						foreach($v2[$convKey] as $k3=>$v3)
						{
							if($v3['THEN']=='EXPRESSION' && !in_array($v3['TO'], $arExpressions))
							{
								$newParams['EXTRASETTINGS'][$k][$k2][$convKey][$k3]['TO'] = '';
								$index = (array_key_exists('INDEX', $v3) && strlen($v3['INDEX']) > 0 ? $v3['INDEX'] : $k3);
								if(isset($arOldExtra[$convKey][$index]['TO']))
								{
									$newParams['EXTRASETTINGS'][$k][$k2][$convKey][$k3]['TO'] = $arOldExtra[$convKey][$index]['TO'];
								}
							}
							if(array_key_exists('INDEX', $v3)) unset($newParams['EXTRASETTINGS'][$k][$k2][$convKey][$k3]['INDEX']);
						}
					}
					
					if(array_key_exists('FILTER_EXPRESSION', $v2) && !in_array($v2['FILTER_EXPRESSION'], $arFilterExpressions))
					{
						unset($newParams['EXTRASETTINGS'][$k][$k2]['FILTER_EXPRESSION']);
						if(isset($arOldExtra['FILTER_EXPRESSION']))
						{
							$newParams['EXTRASETTINGS'][$k][$k2]['FILTER_EXPRESSION'] = $arOldExtra['FILTER_EXPRESSION'];
						}
					}
					
					$arPriceKeys = array('PRICE_QUANTITY_FROM', 'PRICE_QUANTITY_TO');
					foreach($arPriceKeys as $k3=>$v3)
					{
						if(array_key_exists($v3, $v2) && !in_array($v2[$v3], $arPriceExpressions))
						{
							$arParts1 = preg_split('/[+\-\*\/\?:\(\)]/', $v2[$v3]);
							$arParts2 = preg_grep('/^\s*(\s*|\d+|#CELL\d+#)\s*$/', $arParts1);
							if(count($arParts1)!=count($arParts2))
							{
								unset($newParams['EXTRASETTINGS'][$k][$k2][$v3]);
								if(isset($arOldExtra[$v3]))
								{
									$newParams['EXTRASETTINGS'][$k][$k2][$v3] = $arOldExtra[$v3];
								}
							}
						}
						
					}
				}
			}
		}
	
		$arFields['PARAMS'] = self::EncodeProfileParams($newParams);
	}
	
	public function CheckExpClassFile($pid, $className)
	{
		if($pid!==false && strlen($pid) > 0)
		{
			$arProfile = $this->GetEntity()->getList(array('filter'=>array('ID'=>(int)$pid+1), 'select'=>array('EXPRESSIONS')))->Fetch();
			if(strlen($arProfile['EXPRESSIONS'])==0 || ($arProfile['EXPRESSIONS']=='Y' && preg_match('#CKDAImportExcel'.($this->suffix=='highload' ? 'Highload' : '').'$#', $className)))
			{
				return false;
			}
		}
		return true;
	}
	
	public function GetErrorsOnIncludeExpClass($pid, $type='')
	{
		$arError = array();
		if(strlen($pid)==0 || !is_numeric($pid)) return $arError;
		$fnType = ($this->suffix=='highload' ? 'hl' : '').$type;
		$fn = __DIR__ .'/profiles_ext/importer'.(int)$pid.(strlen($fnType) > 0 ? '_'.$fnType : '').'.php';
		if(file_exists($fn))
		{
			try{
				include_once($fn);
				$ie = new ('\CKDAImportExcel'.UcFirst($fnType).(int)$pid)();
			}catch(\Error $ex){
				if($ex instanceof \Error)
				{
					$lineNum = $ex->getLine();
					$method = '';
					$methodLine = 0;
					$arLines = file($fn);
					foreach($arLines as $k=>$v)
					{
						if(preg_match('/public\s+function\s+(\w+)\(/', $v, $m))
						{
							if($k+1 < $lineNum)
							{
								$method = $m[1];
								$methodLine = $k+1;
							}
							else break;
						}
					}
					
					if($method)
					{
						if(preg_match('/ExpSheet(\d+)Field([_\d]+)Conv(\d+)/', $method, $m) || preg_match('/ExpSheet(\d+)Field([_\d]+)ExtraConv(\d+)/', $method, $m))
						{
							if(strpos($type, 'tmp')===false && ($fieldName = $this->GetFieldNameByNumber($pid, $m[1], $m[2])))
							{
								$arError[] = sprintf(GetMessage("KDA_IE_EXP_PHP_CONV_ERROR_IN_FIELD"), $fieldName, (int)$m[1]+1);
							}
							else $arError[] = GetMessage("KDA_IE_EXP_PHP_CONV_ERROR");
							$arError[] = GetMessage("KDA_IE_EXP_PHP_CONV_NUMBER").': '.$m[3] + 1;
						}
						elseif(preg_match('/ExpSheet(\d+)Field([_\d]+)FilterExpression/', $method, $m))
						{
							if(strpos($type, 'tmp')===false && ($fieldName = $this->GetFieldNameByNumber($pid, $m[1], $m[2])))
							{
								$arError[] = sprintf(GetMessage("KDA_IE_EXP_FILTEREXP_ERROR_IN_FIELD"), $fieldName, (int)$m[1]+1);
							}
							else $arError[] = GetMessage("KDA_IE_EXP_FILTEREXP_ERROR");
						}
						elseif($method=="ExpOnAfterSave")
						{
							$arError[] = GetMessage("KDA_IE_EXP_ON_AFTER_SAVE_ERROR");
						}
						elseif($method=="ExpFileLinkAuth")
						{
							$arError[] = GetMessage("KDA_IE_EXP_FILE_AUTH_LINK");
						}
						
						$errorLine = $lineNum + 1 - $methodLine - (preg_match('/(ExpOnAfterSave|ExpSheet\d+Field[_\d]+FilterExpression)/', $method) ? 1 : 2);
						if($errorLine) $arError[] = GetMessage("KDA_IE_EXP_PHP_CONV_LINE_NUMBER").': '.$errorLine;						
					}
					$arError[] = GetMessage("KDA_IE_EXP_ERROR_TEXT").': '.$ex->getMessage();
				}
			}
		}
		return $arError;
	}
	
	public function GetFieldNameByNumber($pid, $sheetNum, $fieldNum)
	{
		$fieldName = '';
		$arParams = $this->GetByID($pid);
		$iblockParamName = ($this->suffix=='highload' ? 'HIGHLOADBLOCK_ID' : 'IBLOCK_ID');
		$iblockId = (isset($arParams['SETTINGS_DEFAULT'][$iblockParamName]) ? $arParams['SETTINGS_DEFAULT'][$iblockParamName] : 0);
		if(isset($arParams['SETTINGS']['IBLOCK_ID'][$sheetNum])) $iblockId = $arParams['SETTINGS']['IBLOCK_ID'][$sheetNum];
		if($iblockId > 0 && isset($arParams['SETTINGS']['FIELDS_LIST'][$sheetNum][$fieldNum]))
		{
			$field = $arParams['SETTINGS']['FIELDS_LIST'][$sheetNum][$fieldNum];
			$fl = new \CKDAFieldList();
			$arFields = $fl->GetFieldNames($iblockId, $this->suffix);
			if(isset($arFields[$field]))
			{
				$fieldName = $arFields[$field];
			}
		}
		return $fieldName;
	}
	
	public function GetCacheDataParams($profileId, $file, $arParams)
	{
		if(!is_array($arParams)) $arParams = array();
		return array(
			'FILE' => (string)$file,
			'FILESIZE' => file_exists($file) ? filesize($file) : 0,
			'FILEMTIME' => file_exists($file) ? filemtime($file) : 0,
			'XMLREADER' => (bool)class_exists('\XMLReader'),
			'COUNT_LINES_FOR_PREVIEW' => (string)$arParams['COUNT_LINES_FOR_PREVIEW'],
			'NUMBER_SEPARATOR' => (string)serialize($arParams['NUMBER_SEPARATOR']),
			'ELEMENT_LOAD_IMAGES' => (string)$arParams['ELEMENT_LOAD_IMAGES'],
			'ELEMENT_NOT_LOAD_FORMATTING' => (string)$arParams['ELEMENT_NOT_LOAD_FORMATTING'],
			'ELEMENT_NOT_LOAD_STYLES' => (string)$arParams['ELEMENT_NOT_LOAD_STYLES'],
			'ELEMENT_NOT_LOAD_STYLES_LIST' => (string)serialize($arParams['ELEMENT_NOT_LOAD_STYLES_LIST']),
		);
	}
	
	public function GetCacheData($profileId, $file, $arParams)
	{
		if(strlen($profileId)==0 || !$file || is_array($file)) return false;
		$fn = $this->tmpcachedir.$profileId.($this->suffix ? '_'.$this->suffix : '').'.txt';
		if(file_exists($fn))
		{
			$arData = array();
			$arFileData = array_map('trim', explode("\n", file_get_contents($fn)));
			foreach($arFileData as $line)
			{
				if(mb_strpos($line, ':') > 0)
				{
					$arLine = explode(':', $line, 2);
					$arData[trim($arLine[0])] = \KdaIE\Utils::Unserialize(base64_decode(trim($arLine[1])));
				}
			}
			$arCheckParams = $this->GetCacheDataParams($profileId, $file, $arParams);
			foreach($arCheckParams as $k=>$v)
			{
				if($v!==$arData[$k]) return false;
			}
			if(empty($arData['WORKSHEETS'])) return false;
			return $arData;
		}
		return false;
	}
	
	public function SetCacheData($profileId, $file, $arParams, $arNewData)
	{
		if(strlen($profileId)==0 || !$file || is_array($file)) return $arData;
		$fn = $this->tmpcachedir.$profileId.($this->suffix ? '_'.$this->suffix : '').'.txt';
		$arData = $this->GetCacheData($profileId, $file, $arParams);
		if(!$arData) $arData = $this->GetCacheDataParams($profileId, $file, $arParams);
		$arData = array_merge($arData, $arNewData);
		$arLines = array();
		foreach($arData as $k=>$v)
		{
			$arLines[] = $k.':'.base64_encode(serialize($v));
		}
		file_put_contents($fn, implode("\r\n", $arLines));
	}
	
	public function SetParams($params=array())
	{
		$this->params = $params;
	}
	
	public function GetParam($name)
	{
		if(isset($this->params[$name])) return $this->params[$name];
		return null;
	}
	
	public static function EncodeProfileParams($arParams)
	{
		return '='.base64_encode(serialize($arParams));
	}
	
	public static function DecodeProfileParams($paramStr, $checkEncoding=true)
	{
		$paramStr = trim($paramStr);
		if(substr($paramStr, 0, 1)=='=') $paramStr = base64_decode(substr($paramStr, 1));
		$arParams = \KdaIE\Utils::Unserialize($paramStr);
		if(!is_array($arParams)) $arParams = array();
		if($checkEncoding && isset($arParams['ENCODING']) && $arParams['ENCODING'] && $arParams['ENCODING'] != \CKDAImportUtils::getSiteEncoding())
		{
			$arParams = \Bitrix\Main\Text\Encoding::convertEncodingArray($arParams, $arParams['ENCODING'], \CKDAImportUtils::getSiteEncoding());
		}
		return $arParams;
	}
	
	public function SetMassMode($massMode, $arElementIds=array(), $arOfferIds=array(), $logger=false)
	{
		$this->isMassMode = $massMode;
	}
	
	public function GetMassMode()
	{
		return $this->isMassMode;
	}
	
	public function OnStartImport()
	{
		return false;
	}
	
	public function OnEndImport($filename, $arParams)
	{
		return array();
	}
	
	public function OutputBackup()
	{
		return false;
	}
	
	public function RestoreBackup($arFiles, $arParams)
	{
		return false;
	}
}