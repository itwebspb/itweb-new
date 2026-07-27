<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class DbStructure
{
	protected static $moduleId = 'esol.redirector';

	public function __construct()
	{
		
	}
	
	public function CheckDB()
	{
		$optionName = 'DB_STRUCT_VERSION';
		$moduleVersion = false;
		if(is_callable(array('\Bitrix\Main\ModuleManager', 'getVersion')))
		{
			$moduleVersion = \Bitrix\Main\ModuleManager::getVersion(self::$moduleId);
			if($moduleVersion==\Bitrix\Main\Config\Option::get(self::$moduleId, $optionName)) return;
		}
		
		/*Security addon*/
		$arFiles = array(
			'/bitrix/admin/esol_allimportexport_cron_settings.php' => '/bitrix/modules/esol.allimportexport/admin/cron_settings.php',
			'/bitrix/admin/esol_export_excel_cron_settings.php' => '/bitrix/modules/esol.importexportexcel/admin/iblock_export_excel_cron_settings.php',
			'/bitrix/admin/esol_import_excel_cron_settings.php' => '/bitrix/modules/esol.importexportexcel/admin/iblock_import_excel_cron_settings.php',
			'/bitrix/admin/esol_import_xml_cron_settings.php' => '/bitrix/modules/esol.importxml/admin/import_xml_cron_settings.php',
			'/bitrix/admin/esol_export_xml_cron_settings.php' => '/bitrix/modules/esol.exportxml/admin/export_xml_cron_settings.php',
			'/bitrix/admin/esol_massedit_profile.php' => '/bitrix/modules/esol.massedit/admin/profile.php',
			'/bitrix/admin/kda_export_excel_cron_settings.php' => '/bitrix/modules/kda.exportexcel/admin/iblock_export_excel_cron_settings.php',
			'/bitrix/admin/kda_import_excel_cron_settings.php' => '/bitrix/modules/kda.importexcel/admin/iblock_import_excel_cron_settings.php'
		);
		foreach($arFiles as $f1=>$f2)
		{
			$secBlock = "<?php\r\n".
				"if(isset(\$_REQUEST['path']) && strlen(\$_REQUEST['path']) > 0 || !file_exists(\$_SERVER['DOCUMENT_ROOT'].'".$f2."'))\r\n".
				"{\r\n".
				"	header((stristr(php_sapi_name(), 'cgi') !== false ? 'Status: ' : \$_SERVER['SERVER_PROTOCOL'].' ').'403 Forbidden');\r\n".
				"	die();\r\n".
				"}\r\n".
				"?>";
			$fileBlock = "<?php\r\n".
					"require(\$_SERVER['DOCUMENT_ROOT'].'".$f2."');\r\n".
					"?>";
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$f1))
			{
				file_put_contents($_SERVER['DOCUMENT_ROOT'].$f1, $secBlock.$fileBlock);
			}
			elseif(($c = file_get_contents($_SERVER['DOCUMENT_ROOT'].$f1)) && str_replace("\r", '', trim($c))==str_replace("\r", '', $fileBlock))
			{
				file_put_contents($_SERVER['DOCUMENT_ROOT'].$f1, $secBlock.trim($c));
			}
		}
		/*/Security addon*/
		
		$redirectEntity = new \Bitrix\EsolRedirector\RedirectTable();
		$tblName = $redirectEntity->getTableName();
		$conn = $redirectEntity->getEntity()->getConnection();
		if(true)
		{			
			$isNewFields = false;
			$arDbFields = array();
			$arDbFieldVals = array();
			$dbRes = $conn->query("SHOW COLUMNS FROM `" . $tblName . "`");
			while($arr = $dbRes->Fetch())
			{
				$arDbFields[] = $arr['Field'];
				$arDbFieldVals[$arr['Field']] = $arr;
			}
			$fields = $redirectEntity->getEntity()->getScalarFields();
			$helper = $conn->getSqlHelper();
			$prevField = 'ID';
			foreach($fields as $columnName => $field)
			{
				$realColumnName = $field->getColumnName();
				$realType = ToLower(trim(preg_replace('/\([^\)]*\)/', '', $arDbFieldVals[$realColumnName]['Type'])));
				$bxType = ToLower(trim(preg_replace('/\([^\)]*\)/', '', $helper->getColumnTypeByField($field))));
				if(!in_array($realColumnName, $arDbFields))
				{
					$conn->query('ALTER TABLE '.$helper->quote($tblName).' ADD COLUMN '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT NULL AFTER '.$helper->quote($prevField));
					if($field->getDefaultValue())
					{
						$conn->query('ALTER TABLE '.$helper->quote($tblName).' CHANGE COLUMN '.$helper->quote($realColumnName).' '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT "'.$helper->forSql($field->getDefaultValue()).'"');
						$conn->query('UPDATE '.$helper->quote($tblName).' SET '.$helper->quote($realColumnName).'="'.$helper->forSql($field->getDefaultValue()).'"');
					}
					if($realColumnName=='REGEXP')
					{
						$conn->createIndex($tblName, 'ix_regexp', array('REGEXP'));
					}
					$isNewFields = true;
				}
				elseif(($bxType!=$realType || (ToUpper($arDbFieldVals[$realColumnName]['Null'])!='YES')) && $realColumnName!='ID')
				{
					if(in_array($realColumnName, array('OLD_URL', 'NEW_URL')))
					{
						$dbRes2 = $conn->query('SHOW INDEX FROM '.$helper->quote($tblName).' WHERE KEY_NAME="ix_'.ToLower($realColumnName).'"');
						if($arIndex = $dbRes2->Fetch())
						{
							$conn->query('DROP INDEX '.$helper->quote('ix_'.ToLower($realColumnName)).' ON '.$helper->quote($tblName));
						}
					}
					$conn->query('ALTER TABLE '.$helper->quote($tblName).' CHANGE COLUMN '.$helper->quote($realColumnName).' '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT NULL');
					if(in_array($realColumnName, array('OLD_URL', 'NEW_URL')))
					{
						$conn->createIndex($tblName, 'ix_'.ToLower($realColumnName), array($realColumnName), array($realColumnName=>255));
					}
				}
				$prevField = $realColumnName;
			}
			if($isNewFields)
			{
				$this->CheckTableEncoding($conn, $tblName);
			}
			
			
			/*$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `OLD_URL` `OLD_URL` text NOT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `NEW_URL` `NEW_URL` text NOT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_CREATE` `DATE_CREATE` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_LAST_USE` `DATE_LAST_USE` datetime DEFAULT NULL');*/
		}
		
		$errorsEntity = new \Bitrix\EsolRedirector\ErrorsTable();
		$tblName = $errorsEntity->getTableName();
		$conn = $errorsEntity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$errorsEntity->getEntity()->createDbTable();
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_FIRST` `DATE_FIRST` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `DATE_LAST` `DATE_LAST` datetime DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `LAST_USER_AGENT` `LAST_USER_AGENT` varchar(255) DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `LAST_REFERER` `LAST_REFERER` varchar(255) DEFAULT NULL');
			$conn->query('ALTER TABLE `'.$tblName.'` CHANGE COLUMN `LAST_IP` `LAST_IP` varchar(255) DEFAULT NULL');

			$dbRes2 = $conn->query('SHOW INDEX FROM '.$helper->quote($tblName).' WHERE KEY_NAME="ix_url_siteid"');
			if(!$dbRes2->Fetch())
			{
				$conn->createIndex($tblName, 'ix_url_siteid', array('URL', 'SITE_ID'), array('URL'=>255, 'SITE_ID'=>2));
			}
			
			$this->CheckTableEncoding($conn, $tblName);
		}
		else
		{
			$isNewFields = false;
			$arDbFields = array();
			$arDbFieldVals = array();
			$dbRes = $conn->query("SHOW COLUMNS FROM `" . $tblName . "`");
			while($arr = $dbRes->Fetch())
			{
				$arDbFields[] = $arr['Field'];
				$arDbFieldVals[$arr['Field']] = $arr;
			}
			$fields = $errorsEntity->getEntity()->getScalarFields();
			$helper = $conn->getSqlHelper();
			$prevField = 'ID';
			foreach($fields as $columnName => $field)
			{
				$realColumnName = $field->getColumnName();
				$realType = ToLower(trim(preg_replace('/\([^\)]*\)/', '', $arDbFieldVals[$realColumnName]['Type'])));
				$bxType = ToLower(trim(preg_replace('/\([^\)]*\)/', '', $helper->getColumnTypeByField($field))));
				if(!in_array($realColumnName, $arDbFields))
				{
					$conn->query('ALTER TABLE '.$helper->quote($tblName).' ADD COLUMN '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT NULL AFTER '.$helper->quote($prevField));
					if($field->getDefaultValue())
					{
						$conn->query('ALTER TABLE '.$helper->quote($tblName).' CHANGE COLUMN '.$helper->quote($realColumnName).' '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT "'.$helper->forSql($field->getDefaultValue()).'"');
						$conn->query('UPDATE '.$helper->quote($tblName).' SET '.$helper->quote($realColumnName).'="'.$helper->forSql($field->getDefaultValue()).'"');
					}
					$isNewFields = true;
				}
				elseif(($bxType!=$realType || (ToUpper($arDbFieldVals[$realColumnName]['Null'])!='YES')) && $realColumnName!='ID')
				{
					if($realColumnName=='URL')
					{
						$dbRes2 = $conn->query('SHOW INDEX FROM '.$helper->quote($tblName).' WHERE KEY_NAME="ix_url_siteid"');
						if($arIndex = $dbRes2->Fetch())
						{
							$conn->query('DROP INDEX '.$helper->quote('ix_url_siteid').' ON '.$helper->quote($tblName));
						}
					}
					$conn->query('ALTER TABLE '.$helper->quote($tblName).' CHANGE COLUMN '.$helper->quote($realColumnName).' '.$helper->quote($realColumnName).' '.$helper->getColumnTypeByField($field).' DEFAULT NULL');
					if($realColumnName=='URL')
					{
						$conn->createIndex($tblName, 'ix_url_siteid', array('URL', 'SITE_ID'), array('URL'=>255, 'SITE_ID'=>2));
					}
				}
				$prevField = $realColumnName;
			}
			if($isNewFields)
			{
				$this->CheckTableEncoding($conn, $tblName);
			}
		}
		
		/*After create module*/
		if(!\Bitrix\Main\Config\Option::get(self::$moduleId, $optionName))
		{
			$redirectEntity = new \Bitrix\EsolRedirector\RedirectTable();
			$tblName = $redirectEntity->getTableName();
			$conn = $redirectEntity->getEntity()->getConnection();
			$this->CheckTableEncoding($conn, $tblName);
			
			$redirectEntity = new \Bitrix\EsolRedirector\RedirectSiteTable();
			$tblName = $redirectEntity->getTableName();
			$conn = $redirectEntity->getEntity()->getConnection();
			$this->CheckTableEncoding($conn, $tblName);
		}
		/*/After create module*/
		
		if($moduleVersion)
		{
			\Bitrix\Main\Config\Option::set(self::$moduleId, $optionName, $moduleVersion);
		}
	}
	
	private function CheckTableEncoding($conn, $tblName)
	{
		$res = $conn->query('SHOW VARIABLES LIKE "character_set_connection"');
		$f = $res->fetch();
		$charset = trim($f['Value']);

		$res = $conn->query('SHOW VARIABLES LIKE "collation_connection"');
		$f = $res->fetch();
		$collation = trim($f['Value']);
		$charset2 = $this->GetCharsetByCollation($conn, $collation);
		
		$res0 = $conn->query('SHOW CREATE TABLE `' . $tblName . '`');
		$f0 = $res0->fetch();
		
		if (preg_match('/DEFAULT CHARSET=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
		{
			$t_charset = $regs[1];
			if (preg_match('/COLLATE=([a-z0-9\-_]+)/i', $f0['Create Table'], $regs))
				$t_collation = $regs[1];
			else
			{
				$res0 = $conn->query('SHOW CHARSET LIKE "' . $t_charset . '"');
				$f0 = $res0->fetch();
				$t_collation = $f0['Default collation'];
			}
		}
		else
		{
			$res0 = $conn->query('SHOW TABLE STATUS LIKE "' . $tblName . '"');
			$f0 = $res0->fetch();
			if (!$t_collation = $f0['Collation'])
				return;
			$t_charset = $this->GetCharsetByCollation($conn, $t_collation);
		}
		
		if ($charset != $t_charset)
		{
			$conn->query('ALTER TABLE `' . $tblName . '` CHARACTER SET ' . $charset);
		}
		if ($t_collation != $collation)
		{	// table collation differs
			$conn->query('ALTER TABLE `' . $tblName . '` COLLATE ' . $collation);
		}
		
		$arFix = array();
		$res0 = $conn->query("SHOW FULL COLUMNS FROM `" . $tblName . "`");
		while($f0 = $res0->fetch())
		{
			$f_collation = $f0['Collation'];
			if ($f_collation === NULL || $f_collation === "NULL")
				continue;

			$f_charset = $this->GetCharsetByCollation($conn, $f_collation);
			if ($charset != $f_charset && $charset2 != $f_charset)
			{
				$arFix[] = ' MODIFY `'.$f0['Field'].'` '.$f0['Type'].' CHARACTER SET '.$charset.($collation != $f_collation ? ' COLLATE '.$collation : '').($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL').
						($f0['Default'] === NULL ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT '.($f0['Type'] == 'timestamp' && $f0['Default'] == 'CURRENT_TIMESTAMP' ? $f0['Default'] : '"'.$conn->getSqlHelper()->forSql($f0['Default']).'"')).' '.$f0['Extra'];
			}
			elseif ($collation != $f_collation)
			{
				$arFix[] = ' MODIFY `'.$f0['Field'].'` '.$f0['Type'].' COLLATE '.$collation.($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL').
						($f0['Default'] === NULL ? ($f0['Null'] == 'YES' ? ' DEFAULT NULL ' : '') : ' DEFAULT '.($f0['Type'] == 'timestamp' && $f0['Default'] == 'CURRENT_TIMESTAMP' ? $f0['Default'] : '"'.$conn->getSqlHelper()->forSql($f0['Default']).'"')).' '.$f0['Extra'];
			}
		}

		if(count($arFix))
		{
			$conn->query('ALTER TABLE `'.$tblName.'` '.implode(",\n", $arFix));
		}
	}
	
	private function GetCharsetByCollation($conn, $collation)
	{
		static $CACHE;
		if (!$c = &$CACHE[$collation])
		{
			$res0 = $conn->query('SHOW COLLATION LIKE "' . $collation . '"');
			$f0 = $res0->Fetch();
			$c = $f0['Charset'];
		}
		return $c;
	}
}