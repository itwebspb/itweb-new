<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
require_once(dirname(__FILE__).'/PHPExcel/PHPExcel.php');

class Importer {
	protected static $moduleId = 'esol.redirector';
	protected static $moduleSubDir = '';
	protected static $cpSpecCharLetters = null;
	protected static $instances = array();
	var $notHaveTimeSetWorksheet = false;
	
	public static function getInstance($entity=0)
	{
		if (!isset(static::$instances[$entity]))
			static::$instances[$entity] = new static($entity);

		return static::$instances[$entity];
	}
	
	public function __construct($entity)
	{
		//$this->fl = \Bitrix\EsolRedirector\RedirectTable::GetImportedFields();
	}
	
	function setParams($filename, $params, $fparams, $stepparams, $pid = false)
	{
		$this->filename = $_SERVER['DOCUMENT_ROOT'].$filename;
		$this->params = $params;
		$this->fparams = $fparams;
		$this->maxReadRows = 500;
		$this->skipRows = 0;
		$this->errors = array();
		$this->breakWorksheet = false;
		$this->stepparams = $stepparams;
		$this->stepparams['total_read_line'] = intval($this->stepparams['total_read_line']);
		$this->stepparams['total_line'] = intval($this->stepparams['total_line']);
		$this->stepparams['correct_line'] = intval($this->stepparams['correct_line']);
		$this->stepparams['error_line'] = intval($this->stepparams['error_line']);
		$this->stepparams['element_added_line'] = intval($this->stepparams['element_added_line']);
		$this->stepparams['element_updated_line'] = intval($this->stepparams['element_updated_line']);
		$this->stepparams['element_removed_line'] = intval($this->stepparams['element_removed_line']);
		$this->stepparams['worksheetCurrentRow'] = intval($this->stepparams['worksheetCurrentRow']);
		if(!isset($this->stepparams['total_line_by_list'])) $this->stepparams['total_line_by_list'] = array();
		$this->stepparams['total_file_line'] = 0;
		if(is_array($this->params['LIST_LINES']))
		{
			foreach($this->params['LIST_ACTIVE'] as $k=>$v)
			{
				if($v=='Y')
				{
					$this->stepparams['total_file_line'] += $this->params['LIST_LINES'][$k];
				}
			}
		}
		
		$this->SetZipClass();
		
		/*Temp folders*/
		$this->filecnt = 0;
		$dir = $_SERVER["DOCUMENT_ROOT"].'/upload/tmp/'.static::$moduleId.'/'.static::$moduleSubDir;
		CheckDirPath($dir);
		if(!$this->stepparams['tmpdir'])
		{
			$i = 0;
			while(($tmpdir = $dir.$i.'/') && file_exists($tmpdir)){$i++;}
			$this->stepparams['tmpdir'] = $tmpdir;
			CheckDirPath($tmpdir);
		}
		$this->tmpdir = $this->stepparams['tmpdir'];
		$this->archivedir = $this->stepparams['tmpdir'].'archives/';
		CheckDirPath($this->archivedir);
		
		$this->tmpfile = $this->tmpdir.'params.txt';
		/*/Temp folders*/
		
		if(file_exists($this->tmpfile) && filesize($this->tmpfile) > 0)
		{
			$this->stepparams = array_merge($this->stepparams, \Bitrix\EsolRedirector\Utils::Unserialize(file_get_contents($this->tmpfile)));
		}
		
		if(!isset($this->stepparams['curstep'])) $this->stepparams['curstep'] = 'import';
		
		if(!isset($this->params['MAX_EXECUTION_TIME']) || $this->params['MAX_EXECUTION_TIME']!==0)
		{
			if(\Bitrix\Main\Config\Option::get(static::$moduleId, 'SET_MAX_EXECUTION_TIME')=='Y' && is_numeric(\Bitrix\Main\Config\Option::get(static::$moduleId, 'MAX_EXECUTION_TIME')))
			{
				$this->params['MAX_EXECUTION_TIME'] = intval(\Bitrix\Main\Config\Option::get(static::$moduleId, 'MAX_EXECUTION_TIME'));
				if(ini_get('max_execution_time') && $this->params['MAX_EXECUTION_TIME'] > ini_get('max_execution_time') - 5) $this->params['MAX_EXECUTION_TIME'] = ini_get('max_execution_time') - 5;
				if($this->params['MAX_EXECUTION_TIME'] < 5) $this->params['MAX_EXECUTION_TIME'] = 5;
				if($this->params['MAX_EXECUTION_TIME'] > 300) $this->params['MAX_EXECUTION_TIME'] = 300;
			}
			else
			{
				$this->params['MAX_EXECUTION_TIME'] = intval(ini_get('max_execution_time')) - 10;
				if($this->params['MAX_EXECUTION_TIME'] < 10) $this->params['MAX_EXECUTION_TIME'] = 15;
				if($this->params['MAX_EXECUTION_TIME'] > 50) $this->params['MAX_EXECUTION_TIME'] = 50;
			}
		}
		
		if($pid!==false)
		{
			$this->procfile = $dir.$pid.'.txt';
			$this->errorfile = $dir.$pid.'_error.txt';
			if((int)$this->stepparams['import_started'] < 1)
			{
				if(file_exists($this->procfile)) unlink($this->procfile);
				if(file_exists($this->errorfile)) unlink($this->errorfile);
			}
			$this->pid = $pid;
		}
	}

	public function SetZipClass()
	{
		if($this->params['OPTIMIZE_RAM']!='Y' && !isset($this->stepparams['optimizeRam']))
		{
			$this->stepparams['optimizeRam'] = 'N';
			$origFileSize = filesize($this->filename);
			if((class_exists('\XMLReader') && $origFileSize > 2*1024*1024) && ToLower(GetFileExtension($this->filename))=='xlsx')
			{
				$timeBegin = microtime(true);
				$needSize = $origFileSize*10;
				$tempPath = \CFile::GetTempName('', 'test_size.txt');
				CheckDirPath($tempPath);

				$fileSize = 0;
				$handle = fopen($tempPath, 'a');
				while($fileSize < $needSize && microtime(true) - $timeBegin < 3)
				{
					$partSize = min(5*1024*1024, $needSize - $fileSize);
					fwrite($handle, str_repeat('0', $partSize));
					$fileSize += $partSize;
				}
				fclose($handle);
				if($fileSize <= filesize($tempPath))
				{
					$this->stepparams['optimizeRam'] = 'Y';
				}
				unlink($tempPath);
				$dir = dirname($tempPath);
				if(count(array_diff(scandir($dir), array('.', '..')))==0)
				{
					rmdir($dir);
				}
			}
		}
		if($this->params['OPTIMIZE_RAM']=='Y' || $this->stepparams['optimizeRam']=='Y')
		{
			\KDAPHPExcel_Settings::setZipClass(KDAPHPExcel_Settings::KDAIEZIPARCHIVE);
		}
	}
	
	public function CheckTimeEnding($time = 0)
	{
		if($time==0) $time = $this->timeBeginImport;
		return ($this->params['MAX_EXECUTION_TIME'] && (time()-$time >= $this->params['MAX_EXECUTION_TIME']));
	}
	
	public function GetRemainingTime()
	{
		if(!$this->params['MAX_EXECUTION_TIME']) return 600;
		else return ($this->params['MAX_EXECUTION_TIME'] - (time() - $this->timeBeginImport));
	}
	
	public function HaveTimeSetWorksheet($time)
	{
		$this->notHaveTimeSetWorksheet = ($this->params['MAX_EXECUTION_TIME'] && $this->params['TIME_READ_FILE'] && (time()-$time+$this->params['TIME_READ_FILE'] >= $this->params['MAX_EXECUTION_TIME']));
		return !$this->notHaveTimeSetWorksheet;
	}
	
	public function Import()
	{
		register_shutdown_function(array($this, 'OnShutdown'));
		set_error_handler(array($this, "HandleError"));
		set_exception_handler(array($this, "HandleException"));
		$this->stepparams['import_started'] = 1;
		$this->SaveStatusImport();
		
		$time = $this->timeBeginImport = time();
		if($this->stepparams['curstep'] == 'import')
		{
			$this->InitImport();
			while($arItem = $this->GetNextRecord($time))
			{
				if(is_array($arItem)) $this->SaveRecord($arItem);
				if($this->CheckTimeEnding($time))
				{
					return $this->GetBreakParams();
				}
			}
			if($this->CheckTimeEnding($time) || $this->notHaveTimeSetWorksheet) return $this->GetBreakParams();
			$this->stepparams['curstep'] = 'import_end';
		}
		
		return $this->EndOfLoading($time);
	}
	
	public function EndOfLoading($time)
	{
		$this->SaveStatusImport(true);
		
		foreach(GetModuleEvents(static::$moduleId, "OnEndImport", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array($this->pid, $arEventData));
		}
		ZipArchive::RemoveFileDir($this->filename);
		
		return $this->GetBreakParams('finish');
	}	
	
	public function InitImport()
	{
		$this->objReader = \KDAPHPExcel_IOFactory::createReaderForFile($this->filename);
		$this->worksheetNames = array();
		if(is_callable(array($this->objReader, 'listWorksheetNames')))
		{
			$this->worksheetNames = $this->objReader->listWorksheetNames($this->filename);
		}		
		if($this->params['ELEMENT_NOT_LOAD_STYLES']=='Y' && $this->params['ELEMENT_NOT_LOAD_FORMATTING']=='Y')
		{
			$this->objReader->setReadDataOnly(true);
		}
		if(isset($this->params['CSV_PARAMS']))
		{
			$this->objReader->setCsvParams($this->params['CSV_PARAMS']);
		}
		$this->chunkFilter = new ImporterChunkReadFilter();
		$this->objReader->setReadFilter($this->chunkFilter);
		
		$this->worksheetNum = (isset($this->stepparams['worksheetNum']) ? intval($this->stepparams['worksheetNum']) : 0);
		$this->worksheetCurrentRow = intval($this->stepparams['worksheetCurrentRow']);
		$this->GetNextWorksheetNum();
	}
	
	public function GetBreakParams($action = 'continue')
	{
		$arStepParams = array(
			'params' => $this->GetStepParams(),
			'action' => $action,
			'errors' => $this->errors,
			'sessid' => bitrix_sessid()
		);
		
		if($action == 'continue')
		{
			file_put_contents($this->tmpfile, serialize($arStepParams['params']));
		}
		else
		{
			if(file_exists($this->procfile)) unlink($this->procfile);
			if(file_exists($this->tmpdir)) DeleteDirFilesEx(substr($this->tmpdir, strlen($_SERVER['DOCUMENT_ROOT'])));
		}
		
		unset($arStepParams['params']['currentelement']);
		unset($arStepParams['params']['currentelementitem']);
		return $arStepParams;
	}
	
	public function GetStepParams()
	{
		return array_merge($this->stepparams, array(
			'worksheetNum' => intval($this->worksheetNum),
			'worksheetCurrentRow' => $this->worksheetCurrentRow
		));
	}
	
	public function SetWorksheet($worksheetNum, $worksheetCurrentRow)
	{
		$this->skipRows = 0;
		
		$timeBegin = microtime(true);
		$this->chunkFilter->setRows($worksheetCurrentRow, $this->maxReadRows);
		if($this->efile) $this->efile->__destruct();
		if($this->worksheetNames[$worksheetNum]) $this->objReader->setLoadSheetsOnly($this->worksheetNames[$worksheetNum]);
		if($this->stepparams['csv_position'] && is_callable(array($this->objReader, 'setStartFilePosRow')))
		{
			$this->objReader->setStartFilePosRow($this->stepparams['csv_position']);
		}
		$this->efile = $this->objReader->load($this->filename);
		$this->worksheetIterator = $this->efile->getWorksheetIterator();
		$this->worksheet = $this->worksheetIterator->current();
		$timeEnd = microtime(true);
		$this->params['TIME_READ_FILE'] = ceil($timeEnd - $timeBegin);
		
		$this->params['CURRENT_ELEMENT_UID'] = array('OLD_URL');
		if($this->params['CHANGE_ELEMENT_UID'][$this->worksheetNum]=='Y')
		{
			$this->params['CURRENT_ELEMENT_UID'] = $this->params['LIST_ELEMENT_UID'][$this->worksheetNum];
		}
		
		$filedList = $this->params['FIELDS_LIST'][$this->worksheetNum];
		
		$arEntityFields = \Bitrix\EsolRedirector\RedirectTable::GetImportedFields();
		$notSetUid = (bool)((is_array($this->params['CURRENT_ELEMENT_UID']) && count(array_diff($this->params['CURRENT_ELEMENT_UID'], $filedList)) > 0) || (!is_array($this->params['CURRENT_ELEMENT_UID']) && !in_array($this->params['CURRENT_ELEMENT_UID'], $filedList)));
		
		/*$arRequiredFields = $this->fl->GetRequiredField();
		$notSetRequired = (bool)(count(array_diff(array_keys($arRequiredFields), $filedList)) > 0);*/
		if($notSetUid)
		{
			if($this->worksheet->getHighestDataRow() > 0)
			{
				if($notSetUid)
				{
					$nofields = (is_array($this->params['CURRENT_ELEMENT_UID']) ? array_diff($this->params['CURRENT_ELEMENT_UID'], $filedList) : array($this->params['CURRENT_ELEMENT_UID']));
					foreach($nofields as $k=>$field)
					{
						$nofields[$k] = '"'.$arEntityFields[$field].'"';
					}
					$nofields = implode(', ', $nofields);
					//$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_UID"), $this->worksheetNum+1, $nofields);
					$this->errors[] = Loc::getMessage("ESOL_RDR_NOT_SET_UID");
				}
				/*elseif($notSetRequired)
				{
					$nofields = (array_diff(array_keys($arRequiredFields), $filedList));
					foreach($nofields as $k=>$field)
					{
						$nofields[$k] = '"'.$arEntityFields[$field].'"';
					}
					$nofields = implode(', ', $nofields);
					$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_REQUIRED"), $this->worksheetNum+1, $nofields);
				}*/
			}
			if(!$this->GetNextWorksheetNum(true))
			{
				$this->worksheet = false;
				return false;
			}
			$pos = $this->GetNextLoadRow(1, $this->worksheetNum);
			$this->SetWorksheet($this->worksheetNum, $pos);
			return;
		}
		
		$this->fieldSettings = array();
		$this->fieldSettingsExtra = array();
		$this->fieldOnlyNew = array();
		foreach($filedList as $k=>$field)
		{
			$fieldParams = $this->fparams[$this->worksheetNum][$k];
			$this->fieldSettings[$field] = $fieldParams;
			if(strpos($field, '|')!==false) $this->fieldSettings[substr($field, 0, strpos($field, '|'))] = $fieldParams;
			$this->fieldSettingsExtra[$k] = $fieldParams;
			if($this->fieldSettings[$field]['SET_NEW_ONLY']=='Y')
			{
				$this->fieldOnlyNew[] = $field;
			}
		}
		
		if(!isset($this->stepparams['ELEMENT_NOT_LOAD_STYLES_ORIG']))
		{
			$this->stepparams['ELEMENT_NOT_LOAD_STYLES_ORIG'] = ($this->params['ELEMENT_NOT_LOAD_STYLES']=='Y' ? 'Y' : 'N');
		}
		else
		{
			$this->params['ELEMENT_NOT_LOAD_STYLES'] = $this->stepparams['ELEMENT_NOT_LOAD_STYLES_ORIG'];
		}
		$listSettings = $this->params['LIST_SETTINGS'][$this->worksheetNum];
		$this->titlesRow = (isset($listSettings['SET_TITLES']) ? $listSettings['SET_TITLES'] : false);
		
		$this->worksheetColumns = \KDAPHPExcel_Cell::columnIndexFromString($this->worksheet->getHighestDataColumn());
		$this->worksheetRows = min($this->maxReadRows, $this->worksheet->getHighestDataRow());
		$this->worksheetCurrentRow = $worksheetCurrentRow;
		if($this->worksheet)
		{
			$this->worksheetRows = min($worksheetCurrentRow+$this->maxReadRows, $this->worksheet->getHighestDataRow());
		}
	}
	
	public function SetFilePosition($pos, $time)
	{
		if($this->breakWorksheet)
		{
			$this->breakWorksheet = false;
			if(!$this->GetNextWorksheetNum(true)) return;
			if(!$this->HaveTimeSetWorksheet($time)) return false;
			$pos = $this->GetNextLoadRow(1, $this->worksheetNum);
			$this->SetWorksheet($this->worksheetNum, $pos);
		}
		else
		{
			$pos = $this->GetNextLoadRow($pos, $this->worksheetNum);
			if(($pos >= $this->worksheetRows) || !$this->worksheet)
			{
				if(!$this->HaveTimeSetWorksheet($time)) return false;
				if(!$this->GetNextWorksheetNum()) return;
				$this->SetWorksheet($this->worksheetNum, $pos);
				if($this->worksheetCurrentRow > $this->worksheetRows)
				{
					if(!$this->GetNextWorksheetNum(true)) return;
					if(!$this->HaveTimeSetWorksheet($time)) return false;
					$pos = $this->GetNextLoadRow(1, $this->worksheetNum);
					$this->SetWorksheet($this->worksheetNum, $pos);
				}
				$this->SaveStatusImport();
			}
			else
			{
				$this->worksheetCurrentRow = $pos;
			}
		}
		$this->stepparams['csv_position'] = $this->chunkFilter->getFilePosRow($this->worksheetCurrentRow);
	}
	
	public function GetNextWorksheetNum($inc = false)
	{
		if($inc) $this->worksheetNum++;
		//$arLists = $this->params['LIST_ACTIVE'];
		$arLists = array('Y');
		while(isset($arLists[$this->worksheetNum]) && $arLists[$this->worksheetNum]!='Y')
		{
			$this->worksheetNum++;
		}
		if(!isset($arLists[$this->worksheetNum]))
		{
			$this->worksheet = false;
			return false;
		}
		return true;
	}
	
	public function CheckSkipLine($currentRow, $worksheetNum, $checkValue = true)
	{
		$load = true;
		
		if($this->breakWorksheet ||
			(!$this->params['CHECK_ALL'][$worksheetNum] && !isset($this->params['IMPORT_LINE'][$worksheetNum][$currentRow - 1])) || 
			(isset($this->params['IMPORT_LINE'][$worksheetNum][$currentRow - 1]) && !$this->params['IMPORT_LINE'][$worksheetNum][$currentRow - 1]))
		{
			$load = false;
		}
		
		return !$load;
	}
	
	public function SaveStatusImport($end = false)
	{
		if($this->procfile)
		{
			$writeParams = $this->GetStepParams();
			$writeParams['action'] = ($end ? 'finish' : 'continue');
			file_put_contents($this->procfile, \Bitrix\EsolRedirector\Utils::PhpToJSObject($writeParams));
		}
	}
	
	public function GetNextLoadRow($row, $worksheetNum)
	{
		$nextRow = $row;
		if(isset($this->params['LIST_ACTIVE'][$worksheetNum]))
		{
			while($this->CheckSkipLine($nextRow, $worksheetNum, false))
			{
				$nextRow++;
				if($nextRow - $row > 30000)
				{
					return $nextRow;
				}
			}
		}
		return $nextRow;
	}
	
	public function GetNextRecord($time)
	{
		if($this->SetFilePosition($this->worksheetCurrentRow + 1, $time)===false) return false;
		while($this->worksheet && $this->CheckSkipLine($this->worksheetCurrentRow, $this->worksheetNum))
		{
			if($this->CheckTimeEnding($time)) return false;
			if($this->SetFilePosition($this->worksheetCurrentRow + 1, $time)===false) return false;
		}

		if(!$this->worksheet)
		{
			return false;
		}
	
		$arItem = array();
		$this->hyperlinks = array();
		$this->notes = array();
		for($column = 0; $column < $this->worksheetColumns; $column++) 
		{
			$val = $this->worksheet->getCellByColumnAndRow($column, $this->worksheetCurrentRow);
			$valText = $this->GetCalculatedValue($val);			
			$arItem[$column] = trim($valText);
			$arItem['~'.$column] = $valText;
			if($this->params['ELEMENT_NOT_LOAD_STYLES']!='Y' && !isset($arItem['STYLE']) && strlen(trim($valText))>0)
			{
				$arItem['STYLE'] = md5(\Bitrix\EsolRedirector\Utils::PhpToJSObject(self::GetCellStyle($val)));
			}
		}

		$this->worksheetNumForSave = $this->worksheetNum;

		return $arItem;
	}
	
	public function SaveRecord($arItem)
	{
		$saveReadRecord = (bool)(!isset($this->stepparams['lastoffergenkey']));
		
		if($saveReadRecord) $this->stepparams['total_read_line']++;
		if(count(array_diff(array_map('trim', $arItem), array('')))==0)
		{
			$this->skipRows++;
			if($this->params['ADDITIONAL_SETTINGS'][$this->worksheetNum]['BREAK_LOADING']=='Y' || ($this->skipRows>=$this->maxReadRows - 1))
			{
				$this->breakWorksheet = true;
			}
			return false;
		}
		if($saveReadRecord)
		{
			$this->stepparams['total_line']++;
			$this->stepparams['total_line_by_list'][$this->worksheetNum]++;
		}		
		
		
		$filedList = $this->params['FIELDS_LIST'][$this->worksheetNumForSave];
		$arEntityFields = \Bitrix\EsolRedirector\RedirectTable::GetImportedFields();
		$entityDataClass = '\Bitrix\EsolRedirector\RedirectTable';
		$primaryField = 'ID';
		$this->currentItemValues = $arItem;

		$arFieldsElement = array();
		$arFieldsElementOrig = array();
		foreach($filedList as $key=>$field)
		{
			if(!array_key_exists($field, $arEntityFields)) continue;
			$k = $key;
			if(strpos($k, '_')!==false) $k = substr($k, 0, strpos($k, '_'));
			$value = $arItem[$k];
			if($this->fieldSettings[$field]['NOT_TRIM']=='Y') $value = $arItem['~'.$k];
			$origValue = $arItem['~'.$k];
			
			/*$conversions = (isset($this->fieldSettingsExtra[$key]) ? $this->fieldSettingsExtra[$key]['CONVERSION'] : $this->fieldSettings[$field]['CONVERSION']);
			if(!empty($conversions))
			{
				$value = $this->ApplyConversions($value, $conversions, $arItem, array('KEY'=>$k, 'NAME'=>$field), $iblockFields);
				$origValue = $this->ApplyConversions($origValue, $conversions, $arItem, array('KEY'=>$k, 'NAME'=>$field), $iblockFields);
				if($value===false) continue;
			}*/
			
			$arFieldsElement[$field] = $value;
			$arFieldsElementOrig[$field] = $origValue;
		}

		$arUid = array();
		if(!is_array($this->params['CURRENT_ELEMENT_UID'])) $this->params['CURRENT_ELEMENT_UID'] = array($this->params['CURRENT_ELEMENT_UID']);
		foreach($this->params['CURRENT_ELEMENT_UID'] as $uid)
		{
			$nameUid = $arEntityFields[$tuid]['title'];
			$valUid = $arFieldsElementOrig[$uid];
			$valUid2 = $arFieldsElement[$uid];
			
			$arUid[] = array(
				'uid' => $uid,
				'nameUid' => $nameUid,
				'valUid' => $valUid,
				'valUid2' => $valUid2
			);
		}
		
		$emptyFields = array();
		foreach($arUid as $k=>$v)
		{
			if((is_array($v['valUid']) && count(array_diff($v['valUid'], array('')))==0)
				|| (!is_array($v['valUid']) && strlen(trim($v['valUid']))==0)) $emptyFields[] = $v['nameUid'];
		}
		
		if(!empty($emptyFields) || empty($arUid))
		{
			//$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_FIELD"), implode(', ', $emptyFields), $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
			$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_FIELD"), implode(', ', $emptyFields), $this->worksheetCurrentRow);
			$this->stepparams['error_line']++;
			return false;
		}
		
		$arFilter = array();
		foreach($arUid as $v)
		{
			if(is_array($v['valUid'])) $arSubfilter = array_map(array($this, 'Trim'), $v['valUid']);
			else 
			{
				$arSubfilter = array($this->Trim($v['valUid']));
				if($this->Trim($v['valUid']) != $v['valUid2'])
				{
					$arSubfilter[] = $this->Trim($v['valUid2']);
					if(strlen($v['valUid2']) != strlen($this->Trim($v['valUid2'])))
					{
						$arSubfilter[] = $v['valUid2'];
					}
				}
				if(strlen($v['valUid']) != strlen($this->Trim($v['valUid'])))
				{
					$arSubfilter[] = $v['valUid'];
				}
			}
			
			if(count($arSubfilter) == 1)
			{
				$arSubfilter = $arSubfilter[0];
			}
			$arFilter['='.$v['uid']] = $arSubfilter;
		}
		
		$isError = false;
		$arKeys = array_diff(array_keys($arFieldsElement), array('SITE_ID'));
		//if($primaryField && !in_array($primaryField, $arKeys)) $arKeys[] = $primaryField;
		if($primaryField)
		{
			$arPrimaryFields = $primaryField;
			if(!is_array($arPrimaryFields)) $arPrimaryFields = array($arPrimaryFields);
			foreach($arPrimaryFields as $pfKey)
			{
				if(!in_array($pfKey, $arKeys)) $arKeys[] = $pfKey;
			}
		}
		
		$dbRes = $entityDataClass::getList(array('filter'=>$arFilter, 'select'=>$arKeys));
		while($arElement = $dbRes->Fetch())
		{
			if(is_array($primaryField))
			{
				$ID = array();
				foreach($primaryField as $pfKey)
				{
					$ID[$pfKey] = $arElement[$pfKey];
				}
			}
			else
			{
				$ID = $arElement[$primaryField];
			}
			/*if($this->params['ONLY_DELETE_MODE']=='Y')
			{
				if(!empty($ID))
				{
					$entityDataClass::delete($ID);
					$this->stepparams['element_removed_line']++;
				}
				continue;
			}*/
			
			$arFieldsElement2 = $arFieldsElement;
			if($this->params['ONLY_CREATE_MODE_ELEMENT']!='Y')
			{
				/*$this->UnsetUidFields($arFieldsElement2, $this->params['CURRENT_ELEMENT_UID']);
				
				if(!empty($this->fieldOnlyNew))
				{
					$this->UnsetExcessFields($this->fieldOnlyNew, $arFieldsElement2);
				}*/
				
				$this->PrepareFields($arFieldsElement2);
				if(empty($arFieldsElement2))
				{
					$this->stepparams['element_updated_line']++;
					continue;
				}
				$dbRes2 = $entityDataClass::update($ID, $arFieldsElement2);
				
				if($dbRes2->isSuccess())
				{
					$this->stepparams['element_updated_line']++;
				}
				else
				{
					$isError = true;
					$this->stepparams['error_line']++;
					//$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_UPDATE_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetNumForSave+1, $this->worksheetCurrentRow, (is_array($ID) ? print_r($ID, true) : $ID));
					$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_UPDATE_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetCurrentRow, (is_array($ID) ? print_r($ID, true) : $ID));
				}
			}
		}
		
		$allowCreate = (bool)($dbRes->getSelectedRowsCount()==0 && $this->params['ONLY_DELETE_MODE']!='Y' && $this->params['ONLY_UPDATE_MODE_ELEMENT']!='Y');
		/*if($allowCreate)
		{
			if(is_callable(array($entityDataClass, 'PrepareFieldsForAddImport')))
			{
				$entityDataClass::PrepareFieldsForAddImport($arFieldsElement);
			}
			$arRequiredFields = $this->fl->GetRequiredField();
			$arRequiredKeys = array_diff(array_keys($arRequiredFields), array_keys($arFieldsElement));
			if(count($arRequiredKeys) > 0)
			{
				foreach($arRequiredKeys as $k=>$field)
				{
					$arRequiredKeys[$k] = '"'.$arEntityFields[$field]['title'].'"';
				}
				$arRequiredKeys = implode(', ', $arRequiredKeys);
				//$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_REQUIRED_ADD"), $arRequiredKeys, $this->worksheetNum+1, $this->worksheetCurrentRow);
				$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_NOT_SET_REQUIRED_ADD"), $arRequiredKeys, $this->worksheetCurrentRow);
				$isError = true;
				$allowCreate = false;
			}
		}*/
		
		if($allowCreate)
		{
			if($this->params['ONLY_UPDATE_MODE_ELEMENT']!='Y')
			{
				/*$this->UnsetUidFields($arFieldsElement, $this->params['CURRENT_ELEMENT_UID'], true);*/

				$this->PrepareFields($arFieldsElement, true);
				$dbRes2 = $entityDataClass::add($arFieldsElement);
				if($dbRes2->isSuccess())
				{
					$ID = $dbRes2->getId();
					$this->stepparams['element_added_line']++;
				}
				else
				{
					$this->stepparams['error_line']++;
					//$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_ADD_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
					$this->errors[] = sprintf(Loc::getMessage("ESOL_RDR_ADD_ELEMENT_ERROR"), implode(', ',$dbRes2->GetErrorMessages()), $this->worksheetCurrentRow);
					$isError = true;
				}
			}
		}
		
		if(!$isError) $this->stepparams['correct_line']++;
		$this->SaveStatusImport();
	}
	
	public function PrepareFields(&$arFieldsElement, $bAdd=false)
	{
		if(isset($arFieldsElement['ACTIVE']))
		{
			$arFieldsElement['ACTIVE'] = $this->GetBoolValue($arFieldsElement['ACTIVE'], false, false);
			if($arFieldsElement['ACTIVE']===false) unset($arFieldsElement['ACTIVE']);
		}
		if(isset($arFieldsElement['WSUBSECTIONS']))
		{
			$arFieldsElement['WSUBSECTIONS'] = $this->GetBoolValue($arFieldsElement['WSUBSECTIONS'], false, false);
			if($arFieldsElement['WSUBSECTIONS']===false) unset($arFieldsElement['WSUBSECTIONS']);
		}
		if(isset($arFieldsElement['WGETPARAMS']))
		{
			$arFieldsElement['WGETPARAMS'] = $this->GetBoolValue($arFieldsElement['WGETPARAMS'], false, false);
			if($arFieldsElement['WGETPARAMS']===false) unset($arFieldsElement['WGETPARAMS']);
		}
		if(isset($arFieldsElement['REGEXP']))
		{
			$arFieldsElement['REGEXP'] = $this->GetBoolValue($arFieldsElement['REGEXP'], false, false);
			if($arFieldsElement['REGEXP']===false) unset($arFieldsElement['REGEXP']);
		}
		if(isset($arFieldsElement['FOR404']))
		{
			$arFieldsElement['FOR404'] = $this->GetBoolValue($arFieldsElement['FOR404'], false, false);
			if($arFieldsElement['FOR404']===false) unset($arFieldsElement['FOR404']);
		}
		if(isset($arFieldsElement['STATUS']))
		{
			$arFieldsElement['STATUS'] = (int)$arFieldsElement['STATUS'];
		}
		if(isset($arFieldsElement['SITE_ID']))
		{
			if(strpos($arFieldsElement['SITE_ID'], '/')!==false) $arSites = explode('/', $arFieldsElement['SITE_ID']);
			elseif(strpos($arFieldsElement['SITE_ID'], ',')!==false) $arSites = explode(',', $arFieldsElement['SITE_ID']);
			elseif(strpos($arFieldsElement['SITE_ID'], ';')!==false) $arSites = explode(';', $arFieldsElement['SITE_ID']);
			else $arSites = array($arFieldsElement['SITE_ID']);
			foreach($arSites as $k=>$v)
			{
				$arSites[$k] = $this->GetSiteByName($v);
			}
			$arSites = array_diff($arSites, array(''));
			if(!empty($arSites)) $arFieldsElement['SITE_ID'] = $arSites;
			else unset($arFieldsElement['SITE_ID']);
		}
		if(!isset($arFieldsElement['SITE_ID']) && $bAdd)
		{
			$arFieldsElement['SITE_ID'] = array($this->GetDefaultSiteId());
		}
	}
	
	public function GetSiteByName($val)
	{
		if(!isset($this->siteList))
		{
			$this->siteList = array(
				'names' => array(),
				'ids' => array()
			);
			$dbRes = \CSite::GetList(($by='sort'), ($order='asc'), array());
			while($arr = $dbRes->Fetch())
			{
				$this->siteList['names'][ToLower($arr['NAME'])] = $arr['ID'];
				$this->siteList['ids'][ToLower($arr['ID'])] = $arr['ID'];
			}
		}
		$val = ToLower(trim($val));
		if(isset($this->siteList['ids'][$val])) return $this->siteList['ids'][$val];
		elseif(isset($this->siteList['names'][$val])) return $this->siteList['names'][$val];
		else return '';
	}
	
	public function GetDefaultSiteId()
	{
		if(!isset($this->defultSiteId))
		{
			if(!($arSite = \CSite::GetList(($by='sort'), ($order='asc'), array('DEFAULT'=>'Y'))->Fetch()))
				$arSite = \CSite::GetList(($by='sort'), ($order='asc'), array())->Fetch();
			$this->defultSiteId = $arSite['ID'];
		}
		return $this->defultSiteId;
	}
	
	public function GetBoolValue($val, $numReturn = false, $defaultValue = false)
	{
		$trueVals = array_map('trim', explode(',', Loc::getMessage("ESOL_RDR_FIELD_VAL_Y")));
		$falseVals = array_map('trim', explode(',', Loc::getMessage("ESOL_RDR_FIELD_VAL_N")));
		if(in_array(ToLower($val), $trueVals))
		{
			return ($numReturn ? 1 : 'Y');
		}
		elseif(in_array(ToLower($val), $falseVals))
		{
			return ($numReturn ? 0 : 'N');
		}
		else
		{
			return $defaultValue;
		}
	}
	
	public static function GetPreviewData($file, $showLines, $arParams = array(), $colsCount = false)
	{
		$selfobj = new ImporterStatic($arParams, $file);
		$file = $_SERVER['DOCUMENT_ROOT'].$file;
		$objReader = \KDAPHPExcel_IOFactory::createReaderForFile($file);		
		if($arParams['ELEMENT_NOT_LOAD_STYLES']=='Y' && $arParams['ELEMENT_NOT_LOAD_FORMATTING']=='Y')
		{
			$objReader->setReadDataOnly(true);
		}
		if(isset($arParams['CSV_PARAMS']))
		{
			$objReader->setCsvParams($arParams['CSV_PARAMS']);
		}
		$chunkFilter = new ImporterChunkReadFilter();
		$objReader->setReadFilter($chunkFilter);
		$maxLine = 1000;
		if(!$colsCount) $maxLine = max($showLines + 50, 50);
		$chunkFilter->setRows(1, $maxLine);

		$efile = $objReader->load($file);
		$arWorksheets = array();
		foreach($efile->getWorksheetIterator() as $worksheet) 
		{
			$maxDrawCol = 0;
			
			$columns_count = max(\KDAPHPExcel_Cell::columnIndexFromString($worksheet->getHighestDataColumn()), $maxDrawCol);
			$columns_count = min($columns_count, 5000);
			$rows_count = $worksheet->getHighestDataRow();

			$arLines = array();
			$cntLines = $emptyLines = 0;
			for ($row = 0; ($row < $rows_count && count($arLines) < min($showLines+$emptyLines, $maxLine)); $row++) 
			{
				$arLine = array();
				$bEmpty = true;
				for ($column = 0; $column < $columns_count; $column++) 
				{
					$val = $worksheet->getCellByColumnAndRow($column, $row+1);					
					$valText = $selfobj->GetCalculatedValue($val);
					if(strlen(trim($valText)) > 0) $bEmpty = false;
					
					$curLine = array('VALUE' => $valText);
					if($arParams['ELEMENT_NOT_LOAD_STYLES']!='Y')
					{
						$curLine['STYLE'] = $selfobj->GetCellStyle($val, true);
					}
					$arLine[] = $curLine;
				}

				$arLines[$row] = $arLine;
				if($bEmpty)
				{
					$emptyLines++;
				}
				$cntLines++;
			}
			
			if($colsCount)
			{
				$columns_count = $colsCount;
				$arLines = array();
				$lastEmptyLines = 0;
				for ($row = $cntLines; $row < $rows_count; $row++) 
				{
					$arLine = array();
					$bEmpty = true;
					for ($column = 0; $column < $columns_count; $column++) 
					{
						$val = $worksheet->getCellByColumnAndRow($column, $row+1);
						$valText = $selfobj->GetCalculatedValue($val);
						if(strlen(trim($valText)) > 0) $bEmpty = false;
						
						$curLine = array('VALUE' => $valText);
						if($arParams['ELEMENT_NOT_LOAD_STYLES']!='Y')
						{
							$curLine['STYLE'] = $selfobj->GetCellStyle($val, true);
						}
						$arLine[] = $curLine;
					}
					if($bEmpty) $lastEmptyLines++;
					else $lastEmptyLines = 0;
					$arLines[$row] = $arLine;
				}
				
				if($lastEmptyLines > 0)
				{
					$arLines = array_slice($arLines, 0, -$lastEmptyLines, true);
				}
			}
			
			$arCells = explode(':', $worksheet->getSelectedCells());
			$heghestRow = intval(preg_replace('/\D+/', '', end($arCells)));
			if(is_callable(array($worksheet, 'getRealHighestRow'))) $heghestRow = intval($worksheet->getRealHighestRow());
			elseif($worksheet->getHighestDataRow() > $heghestRow) $heghestRow = intval($worksheet->getHighestDataRow());
			if(stripos($file, '.csv'))
			{
				$heghestRow = self::GetFileLinesCount($file);
			}

			$arWorksheets[] = array(
				'title' => self::CorrectCalculatedValue($worksheet->GetTitle()),
				'show_more' => ($row < $rows_count - 1),
				'lines_count' => $heghestRow,
				'lines' => $arLines
			);
		}
		return $arWorksheets;
	}
	
	public static function GetFileLinesCount($fn)
	{
		if(!file_exists($fn)) return 0;
		
		$cnt = 0;
		$handle = fopen($fn, 'r');
		while (!feof($handle)) {
			$buffer = trim(fgets($handle));
			if($buffer) $cnt++;
		}
		fclose($handle);
		return $cnt;
	}
	
	public function GetCellStyle($val, $modify = false)
	{
		$style = $val->getStyle();
		$arStyle = array(
			'COLOR' => $style->getFont()->getColor()->getRGB(),
			'FONT-FAMILY' => $style->getFont()->getName(),
			'FONT-SIZE' => $style->getFont()->getSize(),
			'FONT-WEIGHT' => $style->getFont()->getBold(),
			'FONT-STYLE' => $style->getFont()->getItalic(),
			'TEXT-DECORATION' => $style->getFont()->getUnderline(),
			'BACKGROUND' => ($style->getFill()->getFillType()=='solid' ? $style->getFill()->getStartColor()->getRGB() : ''),
		);
		$outlineLevel = (int)$val->getWorksheet()->getRowDimension($val->getRow())->getOutlineLevel();
		if($outlineLevel > 0)
		{
			$arStyle['TEXT-INDENT'] = $outlineLevel;
		}
		if($modify)
		{
			$arStyle['EXT'] = array(
				'COLOR' => $style->getFont()->getColor()->getRealRGB(),
				'BACKGROUND' => ($style->getFill()->getFillType()=='solid' ? $style->getFill()->getStartColor()->getRealRGB() : ''),
			);
		}
		
		return $arStyle;
	}
	
	public function GetCalculatedValue($val)
	{
		try{
			if($this->params['ELEMENT_NOT_LOAD_FORMATTING']=='Y') $val = $val->getCalculatedValue();
			else $val = $val->getFormattedValue();
		}catch(\Exception $ex){}
		return self::CorrectCalculatedValue($val);
	}
	
	public function Trim($str)
	{
		$str = trim($str);
		$str = preg_replace('/(^(\xC2\xA0|\s)+|(\xC2\xA0|\s)+$)/s', '', $str);
		return $str;
	}
	
	public static function CorrectCalculatedValue($val)
	{
		$val = str_ireplace('_x000D_', '', $val);
		if(!\Bitrix\EsolRedirector\Utils::IsUtfMode() && \CUtil::DetectUTF8($val)/*function_exists('mb_detect_encoding') && (mb_detect_encoding($val) == 'UTF-8')*/)
		{
			$val = self::ReplaceCpSpecChars($val);
			if(function_exists('iconv'))
			{
				$newVal = iconv("UTF-8", "CP1251//IGNORE", $val);
				if(strlen(trim($newVal))==0 && strlen(trim($val))>0)
				{
					$newVal2 = \Bitrix\Main\Text\Encoding::convertEncoding($val, "UTF-8", "Windows-1251");
					if(strpos(trim($newVal2), '?')!==0) $newVal = $newVal2;
				}
				$val = $newVal;
			}
			else $val = \Bitrix\Main\Text\Encoding::convertEncoding($val, "UTF-8", "Windows-1251");
		}
		return $val;
	}
	
	public static function ReplaceCpSpecChars($val)
	{
		$specChars = array('Ø'=>'&#216;', '™'=>'&#153;', '®'=>'&#174;', '©'=>'&#169;');
		if(!isset(self::$cpSpecCharLetters))
		{
			$cpSpecCharLetters = array();
			foreach($specChars as $char=>$code)
			{
				$letter = false;
				$pos = 0;
				for($i=192; $i<255; $i++)
				{
					$tmpLetter = \Bitrix\Main\Text\Encoding::convertEncodingArray(chr($i), 'CP1251', 'UTF-8');
					$tmpPos = strpos($tmpLetter, $char);
					if($tmpPos!==false)
					{
						$letter = $tmpLetter;
						$pos = $tmpPos;
					}
				}
				$cpSpecCharLetters[$char] = array('letter'=>$letter, 'pos'=>$pos);
			}
			self::$cpSpecCharLetters = $cpSpecCharLetters;
		}
		
		foreach($specChars as $char=>$code)
		{
			if(strpos($val, $char)===false) continue;
			$letter = self::$cpSpecCharLetters[$char]['letter'];
			$pos = self::$cpSpecCharLetters[$char]['pos'];

			if($letter!==false)
			{
				if($pos==0) $val = preg_replace('/'.substr($letter, 0, 1).'(?!'.substr($letter, 1, 1).')/', $code, $val);
				elseif($pos==1) $val = preg_replace('/(?<!'.substr($letter, 0, 1).')'.substr($letter, 1, 1).'/', $code, $val);
			}
			else
			{
				$val = str_replace($char, $code, $val);
			}
		}
		return $val;
	}
	
	public function OnShutdown()
	{
		$arError = error_get_last();
		if(!is_array($arError) || !isset($arError['type']) || !in_array($arError['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR))) return;
		
		if($this->worksheetCurrentRow > 0)
		{
			$this->EndWithError(sprintf(Loc::getMessage("ESOL_RDR_FATAL_ERROR_IN_LINE"), $this->worksheetNumForSave+1, $this->worksheetCurrentRow, $arError['type'], $arError['message'], $arError['file'], $arError['line']));
		}
		else
		{
			$this->EndWithError(sprintf(Loc::getMessage("ESOL_RDR_FATAL_ERROR"), $arError['type'], $arError['message'], $arError['file'], $arError['line']));
		}
	}
	
	public function HandleError($code, $message, $file, $line)
	{
		return true;
	}
	
	public function HandleException($exception)
	{
		$error = '';
		if($this->worksheetCurrentRow > 0)
		{
			$error .= sprintf(Loc::getMessage("ESOL_RDR_ERROR_LINE"), $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
		}
		if(is_callable(array('\Bitrix\Main\Diag\ExceptionHandlerFormatter', 'format')))
		{
			$error .= \Bitrix\Main\Diag\ExceptionHandlerFormatter::format($exception);
		}
		else
		{
			$error .= sprintf(Loc::getMessage("ESOL_RDR_FATAL_ERROR"), '', $exception->getMessage(), $exception->getFile(), $exception->getLine());
		}
		$this->EndWithError($error);
	}
	
	public function EndWithError($error)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		ob_end_clean();
		$this->errors[] = $error;
		$this->SaveStatusImport();
		echo '<!--module_return_data-->'.\Bitrix\EsolRedirector\Utils::PhpToJSObject($this->GetBreakParams());
		die();
	}
}

class ImporterStatic extends Importer
{
	function __construct($params, $file='')
	{
		$this->params = $params;
		$this->filename = $_SERVER['DOCUMENT_ROOT'].$file;
		$this->SetZipClass();
	}
}

class ImporterChunkReadFilter implements \KDAPHPExcel_Reader_IReadFilter
{
	private $_startRow = 0;
	private $_endRow = 0;
	private $_arFilePos = array();
	private $_arMerge = array();
	private $_params = array();
	/**  Set the list of rows that we want to read  */
	
	public function setParams($arParams=array())
	{
		$this->_params = $arParams;
	}
	
	public function getParam($paramName)
	{
		return (array_key_exists($paramName, $this->_params) ? $this->_params[$paramName] : false);
	}

	public function setMergeCells($mergeRef)
	{
		if(preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', trim($mergeRef), $m) && $m[2]!=$m[4])
		{
			/*$this->_arMerge[$m[1]][$m[2].':'.$m[4]] = array($m[2], $m[4]);
			$this->_arMerge[$m[3]][$m[2].':'.$m[4]] = array($m[2], $m[4]);*/
			$this->_arMerge[$m[2].':'.$m[4]] = array($m[2], $m[4]);
		}
	}

	public function setRows($startRow, $chunkSize) {
		$this->_startRow = $startRow;
		$this->_endRow = $startRow + $chunkSize;
		$this->_arMerge = array();
	}

	public function readCell($column, $row, $worksheetName = '') {
		//  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
		if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)){
			return true;
		}
		elseif(count($this->_arMerge) > 0){
			foreach($this->_arMerge as $range){
				if($row >= $range[0] && $row <= $range[1] && (($this->_startRow >= $range[0] && $this->_startRow <= $range[1]) || ($this->_endRow >= $range[0] && $this->_endRow <= $range[1]))){
					return true;
				}
			}
		}
		return false;
	}
	
	public function getStartRow()
	{
		return $this->_startRow;
	}
	
	public function getEndRow()
	{
		return $this->_endRow;
	}
	
	public function setFilePosRow($row, $pos)
	{
		$this->_arFilePos[$row] = $pos;
	}
	
	public function getFilePosRow($row)
	{
		$nextRow = $row + 1;
		$pos = 0;
		if(!empty($this->_arFilePos))
		{
			if(isset($this->_arFilePos[$nextRow])) $pos = (int)$this->_arFilePos[$nextRow];
			else
			{
				$arKeys = array_keys($this->_arFilePos);
				if(!empty($arKeys))
				{
					$maxKey = max($arKeys);
					if($nextRow > $maxKey);
					{
						$nextRow = $maxKey;
						$pos = (int)$this->_arFilePos[$maxKey];
					}
				}
			}
		}
		return array(
			'row' => $nextRow,
			'pos' => $pos
		);
	}
}
?>