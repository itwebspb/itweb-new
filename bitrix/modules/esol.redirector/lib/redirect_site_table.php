<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class RedirectSiteTable extends Entity\DataManager
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
		return 'b_esolredirector_redirect_sites';
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
			'REDIRECT_ID' => new Entity\IntegerField('REDIRECT_ID', array(
				'required' => true
			)),
			'SITE_ID' => new Entity\StringField('SITE_ID', array(
				'required' => true,
				'size' => 2
			)),
			'SITE' => new Entity\ReferenceField(
				'SITE',
				'\Bitrix\Main\SiteTable',
				array('=this.SITE_ID' => 'ref.LID'),
				array('join_type' => 'LEFT')
			),
		);
	}
}