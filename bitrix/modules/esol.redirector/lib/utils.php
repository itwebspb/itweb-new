<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Utils {
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
		
		if($allowedClasses!==false && $allowedClasses!==true && !is_array($allowedClasses))
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
	
	public static function IsUtfMode()
	{
		if(is_callable(array('\Bitrix\Main\Application', 'isUtfMode')))
		{
			return \Bitrix\Main\Application::isUtfMode();
		}
		return (bool)(defined('BX_UTF') && BX_UTF);
	}
	
	public static function GetConfigErrors()
	{
		$errors = '';
		/*
		//check by PHP_SAPI
		if(defined('BX_CRONTAB') && BX_CRONTAB==true)
		{
			$errors .= Loc::getMessage("ESOL_RR_ERROR_BX_CRONTAB").'<br>';
		}
		*/
		return $errors;
	}
}
?>