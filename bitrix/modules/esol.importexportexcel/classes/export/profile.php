<?php
IncludeModuleLangFile(__FILE__);

/*
$storage = 'fs';
if(class_exists('\Bitrix\Main\Entity\DataManager'))
{
	$profileDB = new \Bitrix\KdaExportexcel\ProfileTable();
	$conn = $profileDB->getEntity()->getConnection();
	if($conn->getType()=='mysql')
	{
		$storage = 'db';
	}
}
*/
$storage = 'db';

if($storage=='db')
{
	class CKDAExportProfile extends CKDAExportProfileDB {}
	if(is_callable(array($conn, 'queryExecute')))
	{
		$conn->queryExecute('SET wait_timeout=900');
		$conn->queryExecute('SET sql_mode=""');
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
	class CKDAExportProfile extends CKDAExportProfileFS {}
}

class CKDAExportProfileAll {
	protected static $instance = array();
	private $pid = null;
	private $errors = array();
	
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
			?><option value=""><?echo GetMessage("KDA_EE_NO_PROFILE"); ?></option><?
			if($allowNew)
			{
				?><option value="new" <?if($_REQUEST[$fname]=='new'){echo 'selected';}?>><?echo GetMessage("KDA_EE_NEW_PROFILE"); ?></option><?
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
		if(is_array($settings))
		{
			if($settings['DISPLAY_PARAMS'])
			{
				foreach($settings['DISPLAY_PARAMS'] as $k=>$v)
				{
					if($v && !is_array($v))
					{
						$v = \KdaIE\Utils::JsObjectToPhp($v);
					}
					if(!is_array($v)) $v = array();
					$settings['DISPLAY_PARAMS'][$k] = $v;
				}
			}
		}
		
		if(!is_array($settigs_default)) $settigs_default = array();
		if(!is_array($settings)) $settings = array();
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
									/*
									$index = (array_key_exists('INDEX', $v3) && strlen($v3['INDEX']) > 0 ? $v3['INDEX'] : $k3);
									if(isset($arOldExtra[$convKey][$index]['TO']))
									{
										$extrasettings[$k][$k2][$convKey][$k3]['TO'] = $arOldExtra[$convKey][$index]['TO'];
									}
									*/
								}
								if(array_key_exists('INDEX', $v3)) unset($extrasettings[$k][$k2][$convKey][$k3]['INDEX']);
							}
						}
					}
				}
			}
		}
	}
	
	public function UpdateFields($ID, $arFields)
	{
		return false;
	}
	
	public function GetLastImportProfiles($limit=10)
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
	
	public function SetExportParams($pid)
	{
		$this->pid = $pid;
	}
	
	public static function PrepareFieldExpression($exp, $convKey)
	{
		$pattern = '(#[A-Za-z0-9\_|=]+#)';
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
				}
			}
		}		
		
		$arFields['EXPRESSIONS'] = (empty($arExpressions) ? 'N' : 'Y');
	
		if($GLOBALS['USER']->IsAdmin())
		{
			$dir = __DIR__ .'/profiles_ext/';
			CheckDirPath($dir);
			
			$fn = $dir.'exporter'.$profileId.(strlen($type) > 0 ? '_'.$type : '').'.php';
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
					
					list($k1, $k2, $k3, $k4) = explode('|', $k);
					$arExp[] = "\t".'public function ExpSheet'.$k1.'Field'.$k2.($k4=='EXTRA_CONVERSION' ? 'Extra' : '').'Conv'.$k3.'($val){'."\r\n".
						"\t\t".'if(isset($this->convParams) && is_array($this->convParams)){foreach($this->convParams as $k=>$v){${$k} = $v;}}'."\r\n".
						"\t\t".$command."\r\n".
						"\t}";
				}
				file_put_contents($fn, '<?php'."\r\n".
					'class CKDAExportExcel'.(strlen($type) > 0 ? ucfirst($type) : '').$profileId.' extends CKDAExportExcel'.(strpos($type, 'hl')!==false ? 'Highload' : '').' {'."\r\n".
					implode("\r\n", $arExp)."\r\n".
					'}');
			}
			elseif(file_exists($fn)) unlink($fn);
		}
		
		if($GLOBALS['USER']->IsAdmin()) return;
		
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
			if(strlen($arProfile['EXPRESSIONS'])==0 || ($arProfile['EXPRESSIONS']=='Y' && preg_match('#CKDAExportExcel'.($this->suffix=='highload' ? 'Highload' : '').'$#', $className)))
			{
				return false;
			}
		}
		return true;
	}
	
	public function GetErrorsOnIncludeExpClass($pid, $type='', $returnFieldIndex=false)
	{
		$arError = array();
		if(strlen($pid)==0 || !is_numeric($pid)) return $arError;
		$fnType = ($this->suffix=='highload' ? 'hl' : '').$type;
		$fn = __DIR__ .'/profiles_ext/exporter'.(int)$pid.(strlen($fnType) > 0 ? '_'.$fnType : '').'.php';
		if(file_exists($fn))
		{
			try{
				include_once($fn);
				$ie = new ('\CKDAExportExcel'.UcFirst($fnType).(int)$pid)();
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
							if($returnFieldIndex) $arError['FIELD_INDEX'] = $m[1].'|'.$m[2];
							if(!$returnFieldIndex && strpos($type, 'tmp')===false && ($fieldName = $this->GetFieldNameByNumber($pid, $m[1], $m[2])))
							{
								$arError[] = sprintf(GetMessage("KDA_EE_EXP_PHP_CONV_ERROR_IN_FIELD"), $fieldName, (int)$m[1]+1);
							}
							else $arError[] = GetMessage("KDA_EE_EXP_PHP_CONV_ERROR");
							$arError[] = GetMessage("KDA_EE_EXP_PHP_CONV_NUMBER").': '.$m[3] + 1;
						}
						elseif(preg_match('/ExpSheet(\d+)Field([_\d]+)FilterExpression/', $method, $m))
						{
							if(!$returnFieldIndex && $returnFieldIndex) $arError['FIELD_INDEX'] = $m[1].'|'.$m[2];
							if(strpos($type, 'tmp')===false && ($fieldName = $this->GetFieldNameByNumber($pid, $m[1], $m[2])))
							{
								$arError[] = sprintf(GetMessage("KDA_EE_EXP_FILTEREXP_ERROR_IN_FIELD"), $fieldName, (int)$m[1]+1);
							}
							else $arError[] = GetMessage("KDA_EE_EXP_FILTEREXP_ERROR");
						}
						
						$errorLine = $lineNum + 1 - $methodLine - (preg_match('/(ExpSheet\d+Field[_\d]+FilterExpression)/', $method) ? 1 : 2);
						if($errorLine) $arError[] = GetMessage("KDA_EE_EXP_PHP_CONV_LINE_NUMBER").': '.$errorLine;						
					}
					$arError[] = GetMessage("KDA_EE_EXP_ERROR_TEXT").': '.$ex->getMessage();
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
			$fl = new \CKDAEEFieldList();
			$arFields = $fl->GetFieldNames($iblockId, $this->suffix);
			if(isset($arFields[$field]))
			{
				$fieldName = $arFields[$field];
			}
		}
		return $fieldName;
	}
	
	public static function EncodeProfileParams($arParams)
	{
		return '='.base64_encode(serialize($arParams));
	}
	
	public static function DecodeProfileParams($paramStr)
	{
		$paramStr = trim($paramStr);
		if(substr($paramStr, 0, 1)=='=') $paramStr = base64_decode(substr($paramStr, 1));
		$arParams = \KdaIE\Utils::Unserialize($paramStr);
		if(!is_array($arParams)) $arParams = array();
		return $arParams;
	}
	
	public function OnStartExport()
	{
		return  false;
	}
	
	public function OnEndExport($file, $arParams, $arErrors=array())
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
?>