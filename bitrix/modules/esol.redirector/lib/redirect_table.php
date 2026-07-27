<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RedirectTable extends Entity\DataManager
{
	private static $compositDomains = null;
	
	/**
	 * Returns path to the file which contains definition of the class.
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_esolredirector_redirects';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			'ACTIVE' => new Entity\BooleanField('ACTIVE', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_ACTIVE"),
				'values' => array('Y', 'N'),
				'default_value' => 'Y'
			)),
			'OLD_URL' => new Entity\TextField('OLD_URL', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_OLD_URL"),
				'default_value' => ''
			)),
			'NEW_URL' => new Entity\TextField('NEW_URL', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_NEW_URL"),
				'default_value' => ''
			)),
			'STATUS' => new Entity\IntegerField('STATUS', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_STATUS"),
				'default_value' => '301'
			)),
			'AUTO' => new Entity\BooleanField('AUTO', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			'WSUBSECTIONS' => new Entity\BooleanField('WSUBSECTIONS', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_WSUBSECTIONS"),
				'values' => array('N', 'Y'),
				'default_value' => 'Y'
			)),
			'WGETPARAMS' => new Entity\BooleanField('WGETPARAMS', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_WGETPARAMS"),
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			'REGEXP' => new Entity\BooleanField('REGEXP', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_REGEXP"),
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			'FOR404' => new Entity\BooleanField('FOR404', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_FOR404"),
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			'DATE_CREATE' => new Entity\DatetimeField('DATE_CREATE', array(
				'default_value' => ''
			)),
			'CREATED_BY' => new Entity\IntegerField('CREATED_BY', array(
				'default_value' => ''
			)),
			'CREATED_BY_USER' => new Entity\ReferenceField(
				'CREATED_BY_USER',
				'\Bitrix\Main\UserTable',
				array('=this.CREATED_BY' => 'ref.ID'),
				array('join_type' => 'LEFT')
			),
			'DATE_LAST_USE' => new Entity\DatetimeField('DATE_LAST_USE', array(
				'default_value' => ''
			)),
			'COUNT_USE' => new Entity\IntegerField('COUNT_USE', array(
				'default_value' => '0'
			)),
			'COMMENT' => new Entity\TextField('COMMENT', array(
				'title' => Loc::getMessage("ESOL_RR_FIELD_COMMENT"),
				'default_value' => ''
			)),
			'ENTITY' => new Entity\StringField('ENTITY', array(
				'default_value' => ''
			)),
			'SITE_REF' => new Entity\ReferenceField(
				'SITE_REF',
				'\Bitrix\EsolRedirector\RedirectSiteTable',
				array('=this.ID' => 'ref.REDIRECT_ID'),
				array('join_type' => 'LEFT')
			),
		);
	}
	
	public static function AddRedirect($oldUrl, $newUrl, $arSites, $status = 301, $entity = false, $change = false)
	{
		$oldUrl = trim($oldUrl);
		$newUrl = trim($newUrl);
		if(strlen($oldUrl)==0 || (strlen($newUrl)==0 && $status!=410) || empty($arSites)) return;
		if(!preg_match('/^https?:/i', $oldUrl) && strpos($oldUrl, '/')!==0) $oldUrl = '/'.$oldUrl;
		
		if(strlen($newUrl) > 0)
		{
			if(!preg_match('/^https?:/i', $newUrl) && strpos($newUrl, '/')!==0) $newUrl = '/'.$newUrl;
			$dbRes = self::getList(array('filter'=>array('=NEW_URL'=>$oldUrl, '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID', 'OLD_URL')));
			while($arr = $dbRes->Fetch())
			{
				if($newUrl==$arr['OLD_URL']) self::delete($arr['ID']);
				else self::update($arr['ID'], array('NEW_URL'=>$newUrl));
			}
			if($change && $entity)
			{
				if(mb_substr($entity, 0, 1)=='E')
				{
					$dbRes = self::getList(array('filter'=>array('=OLD_URL'=>$newUrl, '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID')));
					while($arr = $dbRes->Fetch())
					{
						self::delete($arr['ID']);
					}
				}
				elseif(mb_substr($entity, 0, 1)=='S')
				{
					$dbRes = self::getList(array('filter'=>array('OLD_URL'=>$newUrl.'%', '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID')));
					while($arr = $dbRes->Fetch())
					{
						self::delete($arr['ID']);
					}
				}
			}
		}
		
		$dbRes = self::getList(array('filter'=>array('=OLD_URL'=>$oldUrl, '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID')));
		while($arr = $dbRes->Fetch())
		{
			self::delete($arr['ID']);
		}
		
		//self::ClearCompositeCache($oldUrl);
		$arFields = array(
			'OLD_URL' => $oldUrl,
			'NEW_URL' => $newUrl,
			'STATUS' => $status,
			'AUTO' => 'Y',
			'SITE_ID' => $arSites
		);
		if($entity) $arFields['ENTITY'] = $entity;
		$dbRes = self::add($arFields);
		$redirectId = $dbRes->getId();
	}
	
	public static function ClearCompositeCache($link='')
	{
		if(!class_exists('\Bitrix\Main\Composite\Helper')) return;
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/cache_files_cleaner.php");
		
		if(!isset(static::$compositDomains) || !is_array(static::$compositDomains))
		{
			$compositeOptions = \CHTMLPagesCache::getOptions();
			$compositDomains = $compositeOptions['DOMAINS'];
			if(!is_array($compositDomains)) $compositDomains = array();
			static::$compositDomains = $compositDomains;
		}
		
		if(strlen($link) > 0 && !empty(static::$compositDomains))
		{
			foreach(static::$compositDomains as $host)
			{
				if(is_callable(array('\Bitrix\Main\Composite\Internals\Model\PageTable', 'getList')) && is_callable(array('\Bitrix\Main\Composite\Page', 'createFromCacheKey')))
				{
					$pageList = \Bitrix\Main\Composite\Internals\Model\PageTable::getList(array("select" => array("ID", "CACHE_KEY"), "filter" => array("=URI" => $link, '=HOST'=>$host)));
					while ($record = $pageList->fetch())
					{
						$page = \Bitrix\Main\Composite\Page::createFromCacheKey($record["CACHE_KEY"]);
						$page->delete();
					}
				}
				else
				{					
					$page = new \Bitrix\Main\Composite\Page($link, $host);
					$page->delete();
				}	
			}
		}
	}
	
	public static function RemoveRedirectByOldUrl($url, $arSites)
	{
		$url = trim($url);
		
		$dbRes = self::getList(array('filter'=>array('=OLD_URL'=>$url, '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID')));
		while($arr = $dbRes->Fetch())
		{
			self::delete($arr['ID']);
		}
	}
	
	public static function RemoveRedirect($url, $arSites)
	{
		$url = trim($url);
		
		$dbRes = self::getList(array('filter'=>array('=NEW_URL'=>$url, '=SITE_REF.SITE_ID'=>$arSites), 'select'=>array('ID')));
		while($arr = $dbRes->Fetch())
		{
			self::delete($arr['ID']);
		}
	}
	
	public static function CheckRegexp($pattern)
	{
		return (preg_match('/'.str_replace('/', '\/', $pattern).'/', '0')!==false);
	}
	
	public static function add(array $arFields)
	{
		$arSites = $arFields['SITE_ID'];
		if(!is_array($arSites)) $arSites = array();
		unset($arFields['SITE_ID']);
		
		if($arFields['REGEXP']=='Y' && !self::CheckRegexp($arFields['OLD_URL']))
		{
			$result = new \Bitrix\Main\Entity\AddResult();
			$result->addError(new \Bitrix\Main\Error(Loc::getMessage("ESOL_RR_REGEXP_ERROR")));
			return $result;
		}
		
		$arFields['DATE_CREATE'] = new \Bitrix\Main\Type\DateTime();
		$arFields['CREATED_BY'] = (is_callable(array($GLOBALS['USER'], 'GetID')) && (int)$GLOBALS['USER']->GetID() > 0 ? (int)$GLOBALS['USER']->GetID() : false);
		$dbResAdd = parent::add($arFields);

		$redirectId = $dbResAdd->getId();
		if($redirectId > 0)
		{
			foreach($arSites as $siteId)
			{
				RedirectSiteTable::add(array(
					'REDIRECT_ID' => $redirectId,
					'SITE_ID' => $siteId
				));
			}
			
			/*Remove 404 records*/
			$dbRes = \Bitrix\EsolRedirector\ErrorsTable::getList(array('filter'=>array('%URL'=>$arFields['OLD_URL'], 'SITE_ID'=>$arSites), 'select'=>array('ID')));
			while($arr = $dbRes->Fetch())
			{
				\Bitrix\EsolRedirector\ErrorsTable::delete($arr['ID']);
			}
			/*/Remove 404 records*/
			
			if($arFields['OLD_URL']) self::ClearCompositeCache($arFields['OLD_URL']);
		}
		
		return $dbResAdd;
	}
	
	public static function update($ID, array $arFields)
	{
		$arSites = $arFields['SITE_ID'];
		if(!is_array($arSites)) $arSites = array();
		unset($arFields['SITE_ID']);
		
		if($ID && isset($arFields['OLD_URL']))
		{
			if(isset($arFields['REGEXP'])) $regexp = $arFields['REGEXP'];
			elseif($arr = self::getList(array('filter'=>array('ID'=>$ID), 'select'=>array('REGEXP')))->fetch()) $regexp = $arr['REGEXP'];
			else $regexp = 'N';
			
			if($regexp=='Y' && !self::CheckRegexp($arFields['OLD_URL']))
			{
				$result = new \Bitrix\Main\Entity\UpdateResult();
				$result->addError(new \Bitrix\Main\Error(Loc::getMessage("ESOL_RR_REGEXP_ERROR")));
				return $result;
			}
		}
		
		$dbResUpdate = parent::update($ID, $arFields);

		if($dbResUpdate->isSuccess())
		{
			if(!empty($arSites))
			{
				$dbRes = RedirectSiteTable::getList(array('filter'=>array('REDIRECT_ID' => $ID)));
				while($arr = $dbRes->Fetch())
				{
					if(($index = array_search($arr['SITE_ID'], $arSites))!==false)
					{
						unset($arSites[$index]);
					}
					else
					{
						RedirectSiteTable::delete($arr['ID']);
					}
				}
				foreach($arSites as $siteId)
				{
					RedirectSiteTable::add(array(
						'REDIRECT_ID' => $ID,
						'SITE_ID' => $siteId
					));
				}
			}
			
			if($arFields['OLD_URL']) self::ClearCompositeCache($arFields['OLD_URL']);
		}
		
		return $dbResUpdate;
	}
	
	public static function delete($ID)
	{		
		$dbResDelete = parent::delete($ID);

		if($dbResDelete->isSuccess())
		{
			$dbRes = RedirectSiteTable::getList(array('filter'=>array('REDIRECT_ID' => $ID)));
			while($arr = $dbRes->Fetch())
			{
				RedirectSiteTable::delete($arr['ID']);
			}
		}
		
		return $dbResDelete;
	}
	
	public static function GetImportedFields()
	{
		$redirectEntity = new static();
		$fields = $redirectEntity->getEntity()->getScalarFields();
		foreach($fields as $columnName => $field)
		{
			$fieldName = $field->getColumnName();
			if(!in_array($fieldName, array(
				'ACTIVE', 
				'OLD_URL',
				'NEW_URL',
				'STATUS',
				'WSUBSECTIONS',
				'WGETPARAMS',
				'REGEXP',
				'FOR404',
				'COMMENT'
			))) continue;
			$arFields[$fieldName] = $field->getTitle();
		}
		$arFields['SITE_ID'] = Loc::getMessage("ESOL_RR_FIELD_SITE_REF");
		return $arFields;
	}
}