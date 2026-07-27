<?php
namespace KdaIE;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Utils {
	public static $moduleId = 'esol.importexportexcel';
	public static $moduleSubDir = 'import/';

	protected static $rcurrencies = null;
	
	public static function GetModuleId()
	{
		return self::$moduleId;
	}
	
	public static function GetModuleSubDir()
	{
		return self::$moduleSubDir;
	}
	
	public static function GetSimplXmlFromString($s)
	{
		if(preg_match('/<!DOCTYPE|<!ENTITY/i', $s))
		{
			return new \SimpleXMLElement('<d></d>');
		}
		if (PHP_VERSION_ID < 80000) $old = libxml_disable_entity_loader(true);
		$sxml = simplexml_load_string($s, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NONET /*| LIBXML_PARSEHUGE*/);
		if (PHP_VERSION_ID < 80000) libxml_disable_entity_loader($old);
		return $sxml;
	}
	
	public static function GetSimplXmlFromFile($s)
	{
		if(preg_match('/<!DOCTYPE|<!ENTITY/i', file_get_contents($s)))
		{
			return new \SimpleXMLElement('<d></d>');
		}
		if (PHP_VERSION_ID < 80000) $old = libxml_disable_entity_loader(true);
		$sxml = simplexml_load_file($s, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NONET /*| LIBXML_PARSEHUGE*/);
		if (PHP_VERSION_ID < 80000) libxml_disable_entity_loader($old);
		return $sxml;
	}
	
	public static function IsSafePublicUrl($url)
	{
		if(!preg_match('#^\s*https?://#i', $url)) return false;
		$host = parse_url($url, PHP_URL_HOST);
		$ip = gethostbyname($host);
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) return false;
		return true;
	}
	
	public static function Unserialize($val, $allowedClasses=false)
	{
		/*
		if($allowedClasses===true)
		{
			if(preg_match_all('/O:\d+:"([^"]+)"/', $val, $m))
			{
				$allowedClasses = array_unique($m[1]);
			}
		}
		elseif
		*/
		
		if($allowedClasses === true) $allowedClasses = [];
		if($allowedClasses!==false && !is_array($allowedClasses))
		{
			$allowedClasses = array($allowedClasses);
		}
		if(!is_array($allowedClasses)) $allowedClasses = [];
		return unserialize($val, ['allowed_classes'=>$allowedClasses]);
	}
	
	public static function PhpToJSObject($arData)
	{
		$data = '';
		if(is_callable(array('\Bitrix\Main\Web\Json', 'encode')))
		{
			$data = \Bitrix\Main\Web\Json::encode($arData);
		}
		else
		{
			$data = \CUtil::PhpToJSObject($arData);
		}
		return $data;
	}
	
	public static function JsObjectToPhp($data)
	{
		if(strlen(trim($data))==0) return array();
		$arResult = null;
		if(is_callable(array('\Bitrix\Main\Web\Json', 'decode')))
		{
			try
			{
				$arResult = \Bitrix\Main\Web\Json::decode($data);
			}
			catch(\Throwable $exception)
			{
				//echo $exception->getMessage();
			}
		}
		if($arResult === null)
		{
			try
			{
				$arResult = \CUtil::JsObjectToPhp($data, true);
			}
			catch(\Throwable $exception)
			{
				//echo $exception->getMessage();
			}
		}
		if($arResult === null)
		{
			$arResult = array();
		}
		return $arResult;
	}
	
	public static function SortByNumStr($a, $b)
	{
		$a1 = preg_replace('/\.[\w\d]{2,5}$/', '', $a);
		$b1 = preg_replace('/\.[\w\d]{2,5}$/', '', $b);
		if($a1!=$b1)
		{
			$a = $a1;
			$b = $b1;
		}
		if(is_numeric($a) || is_numeric($b))
		{
			if(is_numeric($a) && is_numeric($b)) return (float)$a<(float)$b ? -1 : 1;
			else return is_numeric($a) ? -1 : 1;
		}
		return $a<$b ? -1 : 1;
	}
	
	public static function IsUtfMode()
	{
		if(is_callable(array('\Bitrix\Main\Application', 'isUtfMode')))
		{
			return \Bitrix\Main\Application::isUtfMode();
		}
		return (bool)(defined('BX_UTF') && BX_UTF);
	}
	
	public static function GetCurrencyVariables()
	{
		if(!isset(self::$rcurrencies))
		{
			$rcurrencies = array('#USD#', '#EUR#');
			if(\Bitrix\Main\Loader::includeModule('currency') && is_callable(array('\Bitrix\Currency\CurrencyTable', 'getList')))
			{
				$dbRes = \Bitrix\Currency\CurrencyTable::getList(array('select'=>array('CURRENCY')));
				while($arr = $dbRes->Fetch())
				{
					if(!in_array('#'.$arr['CURRENCY'].'#', $rcurrencies)) $rcurrencies[] = '#'.$arr['CURRENCY'].'#';
				}
			}
			self::$rcurrencies = $rcurrencies;
		}
		return self::$rcurrencies;
	}
	
	public static function AddNotifyConvertProfiles($type)
	{
		$arFilter = array('MODULE_ID'=>self::GetModuleId(), 'TAG'=>'CONVERT_PROFILES');
		$arMess = \CAdminNotify::GetList(array(), $arFilter)->Fetch();
		if(!$arMess)
		{
			if($type=='update' 
				&& (!class_exists('\CKDAImportProfile') || (\CKDAImportProfile::getInstance()->GetEntity()->getCount()==0 && \CKDAImportProfile::getInstance('highload')->GetEntity()->getCount()==0))
				&& (!class_exists('\CKDAExportProfile') || (\CKDAExportProfile::getInstance()->GetEntity()->getCount()==0 && \CKDAExportProfile::getInstance('highload')->GetEntity()->getCount()==0))) return;
			
			$link = '/bitrix/admin/settings.php?lang=ru&mid='.self::GetModuleId().'&mid_menu=1#profilesconv';
			if($type=='update') $mess = Loc::getMessage("KDA_IE_ERROR_EXP_CLASS_FILE_UPDATE"); 
			elseif($type=='error') $mess = Loc::getMessage("KDA_IE_ERROR_EXP_CLASS_FILE_ERROR");
			if(strlen($mess) > 0) \CAdminNotify::add(array_merge($arFilter, array('MESSAGE'=>sprintf($mess, self::GetModuleId(), $link))));
		}
	}
}
?>