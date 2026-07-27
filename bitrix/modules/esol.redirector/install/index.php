<?php

global $MESS;
$PathInstall = str_replace('\\', '/', __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen('/index.php'));
IncludeModuleLangFile($PathInstall.'/install.php');
include($PathInstall.'/version.php');

if (class_exists('esol_redirector')) return;

class esol_redirector extends CModule {

	var $MODULE_ID = 'esol.redirector';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $PARTNER_NAME;
	public $PARTNER_URI;
	public $MODULE_GROUP_RIGHTS = 'N';

	public function __construct() {

		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->PARTNER_NAME = GetMessage("ESOL_PARTNER_NAME");
		$this->PARTNER_URI = 'http://esolutions.su/';

		$this->MODULE_NAME = GetMessage('ESOL_REDIRECTOR_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('ESOL_REDIRECTOR_MODULE_DESCRIPTION');
	}

	public function DoInstall() {
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/js/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/panel/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/panel/', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/', true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/themes/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes/', true, true);
		
		$this->InstallDB();
	}
	
	function InstallDB()
	{
		include_once(dirname(__FILE__).'/../lib/redirect_table.php');
		$entity = new \Bitrix\EsolRedirector\RedirectTable();
		$tblName = $entity->getTableName();
		$conn = $entity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$entity->getEntity()->createDbTable();
			$conn->createIndex($tblName, 'ix_old_url', array('OLD_URL'), array('OLD_URL'=>255));
			$conn->createIndex($tblName, 'ix_new_url', array('NEW_URL'), array('NEW_URL'=>255));
			$conn->createIndex($tblName, 'ix_regexp', array('REGEXP'));
			$conn->createIndex($tblName, 'ix_main', array('ACTIVE', 'FOR404', 'OLD_URL', 'REGEXP'), array('OLD_URL'=>255));
			$this->CheckTableEncoding($conn, $tblName);
		}
		
		include_once(dirname(__FILE__).'/../lib/redirect_site_table.php');
		$entity = new \Bitrix\EsolRedirector\RedirectSiteTable();
		$tblName = $entity->getTableName();
		$conn = $entity->getEntity()->getConnection();
		if(!$conn->isTableExists($tblName))
		{
			$entity->getEntity()->createDbTable();		
			$conn->createIndex($tblName, 'ix_redirect_site', array('REDIRECT_ID', 'SITE_ID'));
			$this->CheckTableEncoding($conn, $tblName);
		}
		
		$eManager = \Bitrix\Main\EventManager::getInstance();
		$eManager->registerEventHandler("main", 'OnPageStart', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnPageStart');
		$eManager->registerEventHandler("main", 'OnEndBufferContent', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnEndBufferContent');
		$eManager->registerEventHandler("main", 'OnAfterEpilog', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');
		$eManager->registerEventHandler("main", 'OnEpilog', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnEpilog');
		
		$excludes = \Bitrix\Main\Config\Option::get($this->MODULE_ID, 'REDIRECT_EXCLUDE', '');
		if(strlen($excludes)==0) \Bitrix\Main\Config\Option::set($this->MODULE_ID, 'REDIRECT_EXCLUDE', "/bitrix/*\r\n/local/*\r\n*ajax=y*");
		
		RegisterModule($this->MODULE_ID);
		
		return true;
	}

	public function DoUninstall() {
		global $APPLICATION;
		$step = IntVal($_REQUEST['step']);
		
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("KDA_IE_UNINSTALL_TITLE"), dirname(__FILE__)."/unstep1.php");
		}
		elseif($step==2)
		{
			if($_REQUEST['remove_db_data']=='Y')
			{
				include_once(dirname(__FILE__).'/../lib/redirect_table.php');
				include_once(dirname(__FILE__).'/../lib/redirect_site_table.php');
				include_once(dirname(__FILE__).'/../lib/errors_table.php');
				$entity = new \Bitrix\EsolRedirector\RedirectTable();
				$tblName = $entity->getTableName();
				$conn = $entity->getEntity()->getConnection();
				if($conn->isTableExists($tblName))
				{
					$conn->query('DROP TABLE `'.$tblName.'`');
				}
				
				$entity = new \Bitrix\EsolRedirector\RedirectSiteTable();
				$tblName = $entity->getTableName();
				$conn = $entity->getEntity()->getConnection();
				if($conn->isTableExists($tblName))
				{
					$conn->query('DROP TABLE `'.$tblName.'`');
				}
				
				$entity = new \Bitrix\EsolRedirector\ErrorsTable();
				$tblName = $entity->getTableName();
				$conn = $entity->getEntity()->getConnection();
				if($conn->isTableExists($tblName))
				{
					$conn->query('DROP TABLE `'.$tblName.'`');
				}
				
				
				$eManager = \Bitrix\Main\EventManager::getInstance();
				$arEventModules = array(
					'iblock' => array(
						'OnBeforeIBlockElementUpdate',
						'OnAfterIBlockElementUpdate',
						'OnBeforeIBlockElementDelete',
						'OnBeforeIBlockSectionUpdate',
						'OnAfterIBlockSectionUpdate',
						'OnBeforeIBlockSectionDelete'
					)
				);
				foreach($arEventModules as $moduleId=>$arEvents)
				{
					foreach($arEvents as $eventName)
					{
						$eManager->unRegisterEventHandler($moduleId, $eventName, $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", $eventName);
					}
				}
				
				//exclude UPDATE_END
				\Bitrix\Main\Config\Option::delete($this->MODULE_ID);
			}
			
			DeleteDirFilesEx('/bitrix/js/'.$this->MODULE_ID.'/');
			DeleteDirFilesEx('/bitrix/panel/'.$this->MODULE_ID.'/');
			DeleteDirFilesEx('/bitrix/themes/.default/icons/'.$this->MODULE_ID.'/');
			
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/');
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/themes/.default/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes/.default/');
			
			$this->UnInstallDB();
		}
	}
	
	function UnInstallDB()
	{
		$eManager = \Bitrix\Main\EventManager::getInstance();
		$eManager->unRegisterEventHandler("main", 'OnPageStart', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnPageStart');
		$eManager->unRegisterEventHandler("main", 'OnEndBufferContent', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnEndBufferContent');
		$eManager->unRegisterEventHandler("main", 'OnAfterEpilog', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnAfterEpilog');
		$eManager->unRegisterEventHandler("main", 'OnEpilog', $this->MODULE_ID, "\Bitrix\EsolRedirector\Events", 'OnEpilog');
		
		UnRegisterModule($this->MODULE_ID);
		return true;
	}
	
	private function CheckTableEncoding($conn, $tblName)
	{
		$res = $conn->query('SHOW VARIABLES LIKE "character_set_database"');
		$f = $res->fetch();
		$charset = trim($f['Value']);

		$res = $conn->query('SHOW VARIABLES LIKE "collation_database"');
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
		elseif ($t_collation != $collation)
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
				$arFix[] = ' MODIFY `'.$f0['Field'].'` '.$f0['Type'].' CHARACTER SET '.$charset.($f0['Null'] == 'YES' ? ' NULL' : ' NOT NULL').
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
?>