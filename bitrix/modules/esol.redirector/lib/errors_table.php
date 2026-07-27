<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ErrorsTable extends Entity\DataManager
{
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
		return 'b_esolredirector_errors';
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
			'URL' => new Entity\TextField('URL', array(
				'default_value' => ''
			)),
			'STATUS' => new Entity\IntegerField('STATUS', array(
				'default_value' => '404'
			)),
			'VIEWS' => new Entity\IntegerField('VIEWS', array(
				'default_value' => '0'
			)),
			'DATE_FIRST' => new Entity\DatetimeField('DATE_FIRST', array(
				'default_value' => ''
			)),
			'DATE_LAST' => new Entity\DatetimeField('DATE_LAST', array(
				'default_value' => ''
			)),
			'LAST_USER_AGENT' => new Entity\StringField('LAST_USER_AGENT', array(
				'default_value' => ''
			)),
			'LAST_REFERER' => new Entity\StringField('LAST_REFERER', array(
				'default_value' => ''
			)),
			'LAST_IP' => new Entity\StringField('LAST_IP', array(
				'default_value' => ''
			)),
			'SITE_ID' => new Entity\StringField('SITE_ID', array(
				'default_value' => ''
			)),
			'SITE' => new Entity\ReferenceField(
				'SITE',
				'\Bitrix\Main\SiteTable',
				array('=this.SITE_ID' => 'ref.LID'),
				array('join_type' => 'LEFT')
			),
		);
	}
	
	public static function add(array $arFields)
	{
		$dbResAdd = parent::add($arFields);
		
		/*remove old records*/
		$limit = Events::GetOption(false, 'STAT_404_ERROR_LIMIT');
		if(strlen($limit)==0 || !is_numeric($limit)) $limit = 10000;
		if((int)$limit > 0)
		{
			$removeCnt = min(1000, self::getCount() - (int)$limit);
			if($removeCnt > 0)
			{
				$dbRes = self::getList(array('order'=>array('ID'=>'ASC'), 'select'=>array('ID'), 'limit'=>$removeCnt));
				while($arr = $dbRes->Fetch())
				{
					self::delete($arr['ID']);
				}
			}
		}
		/*/remove old records*/
		
		return $dbResAdd;
	}
}