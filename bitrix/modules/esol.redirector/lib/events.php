<?php
namespace Bitrix\EsolRedirector;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Events
{
	protected static $moduleId = 'esol.redirector';
	protected static $instance = null;
	private static $isPageStart = false;
	private static $httpStatusCodes = array(
		200 => "200 OK",
		301 => "301 Moved Permanently",
		302 => "302 Found",
		/*303 => "303 See Other",
		307 => "307 Temporary Redirect"*/
		410 => '410 Gone'
	);
	private static $check404 = true;
	
	public static function getInstance()
	{
		if (!isset(static::$instance))
			static::$instance = new static();

		return static::$instance;
	}
	
	public static function getHttpStatusCodes()
	{
		$arStatuses = array();
		foreach(self::$httpStatusCodes as $k=>$v)
		{
			if($k==200 || !Loc::getMessage('ESOL_RR_TEXT_'.$k)) continue;
			$arStatuses[$k] = $k.' ('.Loc::getMessage('ESOL_RR_TEXT_'.$k).')';
		}
		return $arStatuses;
	}
	
	public static function OnEpilog()
	{
		if(!class_exists('\Bitrix\Main\Context')) return;
		$requestUri = self::GetRequestUri();
		if(!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI']) 
			|| self::isCrontab()
			|| mb_strpos($requestUri, '/bitrix/')===0
			|| self::IsExclude($requestUri)) return;
		
		$oldUri = $newUri = $requestUri;
		
		/*
		if(self::isCatalog() && !self::Is404())
		{
			$slashRedirect = self::GetOption(self::$moduleId, 'SLASH_AUTO_REDIRECT');
			if(mb_strpos($slashRedirect, 'SLASH')!==false 
				&& mb_strpos($slashRedirect, '404')==false
				&& (!isset($_POST) || empty($_POST)))
			{
				self::CheckSlashRedirects($newUri, $slashRedirect);
			}
		}
		*/
		
		if(self::$check404 && self::Is404())
		{
			self::CheckRedirects(array(array('LOGIC'=>'OR', array('=FOR404'=>'Y'), array('=STATUS'=>'410'))));
			
			$redirect404 = self::GetOption(self::$moduleId, 'REDIRECT_404', 'N');
			if(in_array($redirect404, array('MAIN', 'PARENT')))
			{
				if(mb_strlen($requestUri) > 1)
				{
					if($redirect404 == 'MAIN') $newUri = '/';
					elseif($redirect404 == 'PARENT')
					{
						$newUri = preg_replace('/\/[^\/]*$/', '/', rtrim($requestUri, '/'));
					}
				}
			}
		}
		
		if($newUri != $oldUri && self::AllowRedirect($oldUri, $newUri))
		{
			LocalRedirect($newUri, true, self::$httpStatusCodes[301]);
		}
	}
	
	public static function OnEndBufferContent()
	{
		if(!self::$isPageStart) self::OnPageStart();
	}
	
	public static function OnPageStart()
	{
		//Bitrix bug fix with session lazy start
		isset($_SESSION["SESS_NO_KEEP_STATISTIC"]);
		
		register_shutdown_function(array(self::getInstance(), 'OnShutdown'));
		
		self::$isPageStart = true;
		if(!class_exists('\Bitrix\Main\Context')) return;
		$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
		$requestUri = self::GetRequestUri();
		
		if(!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI']) 
			|| self::isCrontab()
			|| self::IsExclude($requestUri)) return;
			
		/*Check old static cache*/
		$oldJsCssRedirect = self::GetOption(self::$moduleId, 'OLD_JS_CSS_REDIRECT');
		if($oldJsCssRedirect=='Y' && (mb_strpos($requestUri, '/bitrix/cache/css/')===0 || mb_strpos($requestUri, '/bitrix/cache/js/')===0))
		{
			$arUrl = parse_url($requestUri);
			$ext = ToLower(end(explode('.', $arUrl['path'])));
			$path = preg_replace('/_[^_]*$/Uis', '_', dirname($arUrl['path']));
			$prefix = basename($path);
			$fullPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/').dirname($path).'/';
			$file = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$arUrl['path'];

			$timeBegin = time();
			while(time() - $timeBegin < 10)
			{
				if($_SERVER['HTTP_REFERER'] && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)==$obRequest->getHttpHost()) $link = preg_replace('/\?.*$/', '', $_SERVER['HTTP_REFERER']);
				else $link = ($obRequest->isHttps() ? 'https' : 'http').'://'.$obRequest->getHttpHost();
				$rand = mt_rand(1000000000000, 9999999999999);
				$client = new \Bitrix\Main\Web\HttpClient();
				$client->setHeader('BX-ACTION-TYPE', 'get_dynamic');
				$client->setHeader('BX-CACHE-MODE', 'HTMLCACHE');
				$res = $client->get($link.'?bxrand='.$rand);
				
				if(!file_exists($file) || filesize($file)==0)
				{
					$dir = dirname($file);
					$arFiles = glob(dirname($file).'/*.'.$ext);
					if(count($arFiles) > 1) $file = current($arFiles);
				}
			
				if(!file_exists($file) || filesize($file)==0)
				{
					$arTmpFiles = glob($fullPath.$prefix.'*/*.'.$ext);
					$arFiles = array();
					foreach($arTmpFiles as $tmpFile)
					{
						$arFiles[filemtime($tmpFile)] = $tmpFile;
					}
					if(count($arFiles) > 0)
					{
						ksort($arFiles);
						$file = end($arFiles);
					}
				}
								
				if(file_exists($file) && filesize($file)>0)
				{
					\CHTTP::SetStatus(self::$httpStatusCodes[200]);
					if($ext=='css') header('Content-Type: text/css');
					elseif($ext=='js') header('Content-Type: application/javascript');
					readfile($file);
					die();
				}
			}
		}
		/*/Check old static cache*/
		
		if(mb_strpos($requestUri, '/bitrix/')===0) return;
		
		$newScheme = $scheme = (self::isHttps() ? 'https' : 'http');
		$newDomain = $domain = trim(ToLower($obRequest->getHttpHost()));
		$newUri = $oldUri = $_SERVER['REQUEST_URI'];
		$newUri = $oldUri = self::CheckRedirectUrl($_SERVER['REQUEST_URI']);
		if(is_callable(array('\Bitrix\Main\Text\Encoding', 'convertEncodingToCurrent')))
		{
			$newUri = \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($newUri);
			$oldUri = \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($oldUri);
		}

		$httpRedirect = self::GetOption(self::$moduleId, 'HTTPS_AUTO_REDIRECT');
		if($httpRedirect=='HTTP' && self::isHttps()) $newScheme = 'http';
		elseif($httpRedirect=='HTTPS' && !self::isHttps()) $newScheme = 'https';
		
		$wwwRedirect = self::GetOption(self::$moduleId, 'WWW_AUTO_REDIRECT');
		if($wwwRedirect=='WITH_WWW' && mb_strpos($domain, 'www.')!==0) $newDomain = 'www.'.$domain;
		elseif($wwwRedirect=='WITHOUT_WWW' && mb_strpos($domain, 'www.')===0) $newDomain = mb_substr($domain, 4);

		$indexRedirect = self::GetOption(self::$moduleId, 'REDIRECT_INDEX_PHP');
		if(in_array($indexRedirect, array('PHP', 'PHP_HTML')) && (!isset($_POST) || empty($_POST)))
		{
			$arUrl = self::ParseUrl($newUri, true);
			if(preg_match('/index\.'.($indexRedirect=='PHP_HTML' ? '(php|html|htm)' : 'php').'/i', $arUrl['path']))
			{
				$newUri = str_replace('//', '/', preg_replace('/index\.'.($indexRedirect=='PHP_HTML' ? '(php|html|htm)' : 'php').'/i', '', $arUrl['path'])).(isset($arUrl['query']) && strlen($arUrl['query']) > 0 ? '?'.$arUrl['query'] : '');
			}
		}
		
		$slashRedirect = self::GetOption(self::$moduleId, 'SLASH_AUTO_REDIRECT');
		if(mb_strpos($slashRedirect, 'SLASH')!==false 
			&& (mb_strpos($slashRedirect, '404')!==false || (!self::Is404() /*&& !self::isCatalog()*/))
			&& (!isset($_POST) || empty($_POST)))
		{
			self::CheckSlashRedirects($newUri, $slashRedirect);
		}
		
		$lowerRedirect = self::GetOption(self::$moduleId, 'TOLOWER_REDIRECT');
		if($lowerRedirect=='Y' && (!isset($_POST) || empty($_POST)))
		{
			$arParts = explode('?', $newUri);
			if(isset($arParts[0]) && $arParts[0] != ToLower($arParts[0]) && (!file_exists($_SERVER['DOCUMENT_ROOT'].$arParts[0]) || file_exists($_SERVER['DOCUMENT_ROOT'].ToLower($arParts[0]))))
			{
				$arParts[0] = ToLower($arParts[0]);
				self::$check404 = false;
			}
			$newUri = implode('?', $arParts);
		}
		
		$replaceMultiSlashes = self::GetOption(self::$moduleId, 'REPLACE_MULTI_SLASH');
		if($replaceMultiSlashes=='Y' && (!isset($_POST) || empty($_POST)))
		{
			$arParts = explode('?', $newUri);
			if(isset($arParts[0])) $arParts[0] = preg_replace('/[\/]+/', '/', $arParts[0]);
			$newUri = implode('?', $arParts);
		}
		
		if($newScheme!=$scheme || $newDomain!=$domain || $newUri != $oldUri)
		{
			if(self::AllowRedirect($oldUri, $newUri))
			{
				if(!defined("NO_KEEP_STATISTIC")) define("NO_KEEP_STATISTIC", "Y"); //fix bug with using $USER variable in statistics module
				LocalRedirect($newScheme.'://'.$newDomain.$newUri, true, self::$httpStatusCodes[301]);
			}
			else
			{
				self::CheckRedirects(array('=FOR404'=>'N'));
				return;
			}
			/*\CHTTP::SetStatus(self::$httpStatusCodes[301]);
			header("Location: ".$newScheme.'://'.$newDomain.$newUri);
			exit;*/
		}
		
		self::CheckRedirects(array('=FOR404'=>'N'));
		self::RemoveOldRedirect($oldUri);
	}
	
	public function OnShutdown()
	{
		self::OnEpilog();
	}
	
	public static function OnAfterEpilog()
	{
		if(!class_exists('\Bitrix\Main\Context')) return;
		if(!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI']) 
			|| self::isCrontab()) return;
		$requestUri = self::GetRequestUri();
		if(mb_strpos($requestUri, '/bitrix/')===0) return;
		
		if(self::GetOption(self::$moduleId, 'STAT_404_ERROR')=='Y' && self::Is404())
		{
			$isRedirect = false;
			$stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($stack as $item)
			{
				if(ToLower($item['function'])=='localredirect') $isRedirect = true;
			}
			
			if(!$isRedirect)
			{
				$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
				$url = ($obRequest->isHttps() ? 'https://' : 'http://').$obRequest->getHttpHost().trim($obRequest->getRequestUri());
				$userAgent = $obRequest->getUserAgent();
				if(!self::IsExclude404Url($url) && !self::IsExclude404Uagent($userAgent))
				{
					$dbRes = \Bitrix\EsolRedirector\ErrorsTable::getList(array('filter'=>array('URL'=>$url, 'SITE_ID'=>SITE_ID), 'select'=>array('ID', 'VIEWS')));
					if($arError = $dbRes->Fetch())
					{
						\Bitrix\EsolRedirector\ErrorsTable::update($arError['ID'], array(
							'VIEWS' => (int)$arError['VIEWS'] + 1, 
							'DATE_LAST' => new \Bitrix\Main\Type\DateTime(),
							'LAST_USER_AGENT' => $userAgent,
							'LAST_REFERER' => $_SERVER['HTTP_REFERER'],
							'LAST_IP' => $obRequest->getRemoteAddress()
						));
					}
					else
					{
						\Bitrix\EsolRedirector\ErrorsTable::add(array(
							'URL' => $url,
							'SITE_ID' => SITE_ID,
							'STATUS' => 404,
							'VIEWS' => 1, 
							'DATE_FIRST' => new \Bitrix\Main\Type\DateTime(),
							'DATE_LAST' => new \Bitrix\Main\Type\DateTime(),
							'LAST_USER_AGENT' => $userAgent,
							'LAST_REFERER' => $_SERVER['HTTP_REFERER'],
							'LAST_IP' => $obRequest->getRemoteAddress()
						));
					}
				}
			}
		}
		self::RemoveCurrentUrl();
	}
	
	public static function GetRequestUri()
	{
		$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
		$requestUri = self::CheckRedirectUrl(trim($obRequest->getRequestUri()));
		if(is_callable(array('\Bitrix\Main\Text\Encoding', 'convertEncodingToCurrent')))
		{
			$requestUri = \Bitrix\Main\Text\Encoding::convertEncodingToCurrent($requestUri);
		}
		return $requestUri;
	}
	
	public static function CheckRedirectUrl($uri)
	{
		//$uri = rawurldecode($uri);
		$uri = implode('%23', array_map('rawurldecode', explode('%23', $uri)));
		$arVars = array('WD_SEO_PRETTY_URL', 'REDIRECT_SCRIPT_URL', 'REDIRECT_URL');
		foreach($arVars as $var)
		{
			if(isset($_SERVER[$var]) && strlen($_SERVER[$var]) > 0 && stripos($_SERVER[$var], 'urlrewrite.php')===false && stripos($_SERVER[$var], 'index.php')===false && strpos($uri, $_SERVER[$var].'?')!==0 && !preg_match('#'.str_replace('/', '/+', preg_quote($_SERVER[$var], '#')).'#', $uri))
			{
				$uri = str_replace('#', urlencode('#'), $_SERVER[$var]);
				break;
			}
		}
		if(strpos($uri, '?')===false && isset($_SERVER['REDIRECT_QUERY_STRING']) && strlen($_SERVER['REDIRECT_QUERY_STRING']) > 0)
		{
			$uri = $uri.'?'.$_SERVER['REDIRECT_QUERY_STRING'];
		}
		//if(isset($_SERVER['WD_SEO_PRETTY_URL']) && strlen($_SERVER['WD_SEO_PRETTY_URL']) > 0) $uri = $_SERVER['WD_SEO_PRETTY_URL'];
		return $uri;
	}
	
	public static function GetEncodedUrl($uri)
	{
		return preg_replace_callback('/[^\/?=&#]+/', array(__CLASS__, 'GetEncodedUrlCallback'), $uri);
	}
	
	public static function GetEncodedUrlCallback($m)
	{
		return rawurlencode($m[0]);
	}
	
	public static function CheckSlashRedirects(&$newUri, $slashRedirect)
	{
		$slashRedirect = preg_replace('/_404$/', '', $slashRedirect);
		$arUrl = self::ParseUrl($newUri, true);
		$fileName = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$arUrl['path'];
		$isFile = false;
		/*$arExtensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'svg', 'js', 'css', 'mov', 'htm', 'html', 'swf', 'php');
		foreach($arExtensions as $ext1)
		{
			if(mb_substr($fileName, -mb_strlen($ext1)-1)=='.'.$ext1) $isFile = true;
		}*/
		if(preg_match('/\.[^\.]{2,4}$/', $fileName)) $isFile = true;
		if(!$isFile && (!file_exists($fileName) || !is_file($fileName)))
		{
			$newUrlPath = $urlPath = $arUrl['path'];
			if($slashRedirect=='WITH_SLASH' && mb_substr($urlPath, -1)!='/') $newUrlPath = $urlPath.'/';
			elseif($slashRedirect=='WITHOUT_SLASH' && mb_substr($urlPath, -1)=='/') $newUrlPath = mb_substr($urlPath, 0, -1);
			if($newUrlPath!=$urlPath)
			{
				$newUri = $newUrlPath.(isset($arUrl['query']) && strlen($arUrl['query']) > 0 ? '?'.$arUrl['query'] : '');
			}
		}
	}
	
	public static function RegexpErrorHandler()
	{
		return true;
	}
	
	public static function CheckRedirects($arExtFilter = array(), $requestUri = false, $loop = 0)
	{
		if($requestUri===false) $requestUri = self::GetRequestUri();
		if(mb_strlen($requestUri) < 1) return;
		$obRequest = \Bitrix\Main\Context::getCurrent()->getRequest();
		$domain = (self::isHttps() ? 'https' : 'http').'://'.$obRequest->getHttpHost();
		$arUri = parse_url($requestUri);
		if($arUri['host'] && $arUri['host']!=$obRequest->getHttpHost()) return;
		$requestPath = $requestUri;
		$requestQuery = '';
		if(($pos = mb_strpos($requestUri, '?'))!==false)
		{
			$requestPath = mb_substr($requestUri, 0, $pos);
			$requestQuery = mb_substr($requestUri, $pos);
		}
		
		$arUrls = array($domain, $domain.'/');
		if(strlen($requestQuery) > 0)
		{
			$arUrls[] = $requestUri;
			if(strpos($requestUri, '+')!==false) $arUrls[] = str_replace('+', ' ', $requestUri);
		}
		elseif(strlen($requestPath)==1)
		{
			$arUrls[] = $requestPath;
		}
		$uri = $requestPath;
		$cnt = 1;
		while(mb_strlen($uri) > 1 && $cnt < 100)
		{
			$uri = rtrim($uri, '/');
			$arUrls[] = $uri;
			$arUrls[] = $uri.'/';
			if(($pos = mb_strrpos($uri, '/'))!==false) $uri = mb_substr($uri, 0, $pos);
			else $uri = '';
			$cnt++;
		}
		
		$arEncodeUrls = array();
		foreach($arUrls as $url)
		{
			$arEncodeUrls[] = $domain.$url;
			$encodeUrl = self::GetEncodedUrl($url);
			if($encodeUrl!=$url)
			{
				$arEncodeUrls[] = $encodeUrl;
				$arEncodeUrls[] = $domain.$encodeUrl;
			}
		}
		$arUrls = array_merge($arUrls, $arEncodeUrls);

		if(!empty($arUrls))
		{
			$status = '301';
			$newUrl = $oldUrl = '';
			$arRedirect = array();
			$dbRes = RedirectTable::getList(array('filter'=>array_merge(array('=ACTIVE'=>'Y', '=SITE_REF.SITE_ID'=>SITE_ID, array('LOGIC'=>'OR', array('=OLD_URL'=>$arUrls), array('=REGEXP'=>'Y'))), $arExtFilter), 'order'=>array('REGEXP'=>'ASC', 'ID'=>'DESC'), 'select'=>array('ID', 'COUNT_USE', 'REGEXP', 'OLD_URL', 'NEW_URL', 'STATUS', 'WSUBSECTIONS', 'WGETPARAMS', 'AUTO', 'ENTITY')));
			while($arr = $dbRes->Fetch())
			{
				if(mb_strpos($arr['OLD_URL'], $domain)===0) $arr['OLD_URL'] = mb_substr($arr['OLD_URL'], mb_strlen($domain));
				if($arr['REGEXP']=='Y') 
				{
					set_error_handler(array(__CLASS__, 'RegexpErrorHandler'));
					$backtrack_limit = ini_get('pcre.backtrack_limit') ? : 100000;
					ini_set('pcre.backtrack_limit', '100000');
					$matched = (@preg_match('/'.str_replace('/', '\/', $arr['OLD_URL']).'/', $requestUri, $m)
						|| (mb_substr($requestUri, 0, 1)=='/' && @preg_match('/'.str_replace('/', '\/', $arr['OLD_URL']).'/', $domain.$requestUri, $m)));
					restore_error_handler();
					if($matched)
					{
						$arr['OLD_URL'] = $m[0];
						if(mb_strpos($arr['OLD_URL'], $domain)===0) $arr['OLD_URL'] = mb_substr($arr['OLD_URL'], mb_strlen($domain));
						if(count($m) > 0)
						{
							foreach($m as $mk=>$mv)
							{
								if($mk==0) continue;
								$arr['NEW_URL'] = str_replace('$'.$mk, $mv, $arr['NEW_URL']);
							}
						}
					}
					else continue;
				}
				
				if($arr['AUTO']=='Y' && $arr['ENTITY'] && !IblockRedirectWriter::getInstance()->CheckeRedirectByEntity($arr)) continue;

				$arr['NEW_URL'] = trim($arr['NEW_URL']);

				if($arr['STATUS']==410 && mb_strlen($arr['OLD_URL']) > mb_strlen($oldUrl)
					&& (($arr['WSUBSECTIONS']=='Y' || $arr['REGEXP']=='Y') || rtrim($arr['OLD_URL'], '/')==rtrim($requestUri, '/')))
				{
					$newUrl = $arr['OLD_URL'];
					$oldUrl = $arr['OLD_URL'];
					$status = $arr['STATUS'];
					$arRedirect = $arr;
				}
				elseif(mb_strlen($arr['OLD_URL']) > mb_strlen($oldUrl)
					&& (
							($arr['WSUBSECTIONS']=='Y' && !(mb_strpos(trim($requestUri, '/').'/', trim($arr['NEW_URL'], '/').'/')===0 && mb_strpos(trim($arr['NEW_URL'], '/').'/', trim($arr['OLD_URL'], '/').'/')===0))
							|| ($arr['WGETPARAMS']=='Y' && mb_strpos(str_replace('/?', '?', $requestUri), rtrim($arr['OLD_URL'], '/').'?')===0) 
							|| rtrim($arr['OLD_URL'], '/')==rtrim($requestUri, '/')
							//|| $arr['OLD_URL']==$requestUri
						)
					)
				{
					$newUrl = $arr['NEW_URL'];
					$oldUrl = $arr['OLD_URL'];
					$status = $arr['STATUS'];
					$arRedirect = $arr;
				}
			}

			if($status==410 && strlen($oldUrl) > 0)
			{
				\CHTTP::SetStatus(self::$httpStatusCodes[$status]);
				self::IncRedirectCountUse($arRedirect);
			}
			elseif(strlen($newUrl) > 0)
			{
				if(mb_strlen($requestUri) - mb_strlen($oldUrl) > 1)
				{
					$oldUrlPath = $oldUrl;
					$oldUrlQuery = '';
					if(($pos = mb_strpos($oldUrl, '?'))!==false)
					{
						$oldUrlPath = mb_substr($oldUrl, 0, $pos);
						$oldUrlQuery = mb_substr($oldUrl, $pos);
					}
					
					$newUrlPath = $newUrl;
					$newUrlQuery = '';
					if(($pos = mb_strpos($newUrl, '?'))!==false)
					{
						$newUrlPath = mb_substr($newUrl, 0, $pos);
						$newUrlQuery = mb_substr($newUrl, $pos);
					}
					
					$oldUrlPath = rtrim($oldUrlPath, '/').'/';
					$subPath = mb_substr($requestPath, mb_strlen($oldUrlPath));
					if(strlen($subPath) > 0) $newUrl = rtrim($newUrl, '/').'/'.$subPath;
					if(strlen($newUrlQuery) > 0) $newUrl .= $newUrlQuery;
					elseif(strlen($requestQuery) > 0) $newUrl .= $requestQuery;
				}
				if($newUrl!=$requestUri && self::AllowRedirect($requestUri, $newUrl, $loop))
				{
					self::IncRedirectCountUse($arRedirect);
					if(isset(self::$httpStatusCodes[$status])) $httpStatus = self::$httpStatusCodes[$status];
					else $httpStatus = $status;
					if($loop < 10) self::CheckRedirects(array_merge($arExtFilter, array('=FOR404'=>'N')), $newUrl, $loop+1);
					
					if($arExtFilter['=FOR404']=='N' && !defined("NO_KEEP_STATISTIC")) define("NO_KEEP_STATISTIC", "Y"); //fix bug with using $USER variable in statistics module
					LocalRedirect($newUrl, true, $httpStatus);
				}
			}
		}
	}
	
	public static function Is404()
	{
		//ERROR_404 not use
		//if(mb_strpos(\CHTTP::GetLastStatus(), '404')!==false || (defined('ERROR_404') && ERROR_404=='Y'))
		if(mb_strpos(\CHTTP::GetLastStatus(), '404')!==false
			|| count(preg_grep('/(Status:|'.preg_quote($_SERVER["SERVER_PROTOCOL"], '/').')\s*404/i', headers_list())) > 0)
		{
			return true;
		}
		return false;
	}
	
	public static function isCatalog()
	{
		$fn = \Bitrix\Main\Context::getCurrent()->getRequest()->getScriptFile();
		$fn = $_SERVER['DOCUMENT_ROOT'].$fn;
		if(file_exists($fn) && is_file($fn) && preg_match('/IncludeComponent\s*\(\s*["\'](\w+):catalog["\']/is', file_get_contents($fn, false, null, 0, 65535)))
		{
			return true;
		}
		return false;
	}
	
	public static function IncRedirectCountUse($arRedirect = array())
	{
		if($arRedirect['ID'] > 0)
		{
			$cnt = (array_key_exists('COUNT_USE', $arRedirect) ? (int)$arRedirect['COUNT_USE'] : 0);
			RedirectTable::update($arRedirect['ID'], array(
				'DATE_LAST_USE' => new \Bitrix\Main\Type\DateTime(),
				'COUNT_USE' => $cnt + 1
			));
		}
	}
	
	public static function RemoveCurrentUrl()
	{
		if(!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI']) 
			|| self::isCrontab()) return;
		$uri = self::CheckRedirectUrl($_SERVER['REQUEST_URI']);
		
		$arRedirects = array();
		$cookie = (isset($_COOKIE['ESR_REDIRECTS']) ? $_COOKIE['ESR_REDIRECTS'] : '');
		if(strlen($cookie) > 0)
		{
			$arRedirects = \Bitrix\EsolRedirector\Utils::Unserialize(base64_decode($cookie));
		}
		if(!is_array($arRedirects)) $arRedirects = array();
		
		$needSave = false;
		foreach($arRedirects as $k=>$v)
		{
			if(in_array($uri, $v['r']))
			{
				unset($arRedirects[$k]);
				$needSave = true;
			}
		}
		if($needSave)
		{
			$arRedirects = array_values($arRedirects);
			$encCookie = base64_encode(serialize($arRedirects));
			$_COOKIE['ESR_REDIRECTS'] = $encCookie;
			if(!headers_sent()) @setcookie("ESR_REDIRECTS", $encCookie, false, "/");
		}
	}
	
	public static function IsExclude($uri)
	{
		$arExcludes = explode("\n", self::GetOption(self::$moduleId, 'REDIRECT_EXCLUDE', ''));
		foreach($arExcludes as $exc)
		{
			$exc = trim($exc);
			if(strlen($exc)==0) continue;
			if(preg_match('/^'.strtr(preg_quote($exc, '/'), array('\*'=>'.*')).'$/', $uri))
			{
				return true;
			}
		}
		return false;
	}
	
	public static function IsExclude404Url($uri)
	{
		$arExcludes = explode("\n", self::GetOption(self::$moduleId, 'STAT404_URL_EXCLUDE', ''));
		foreach($arExcludes as $exc)
		{
			$exc = trim($exc);
			if(strlen($exc)==0) continue;
			if(preg_match('/^'.strtr(preg_quote($exc, '/'), array('\*'=>'.*')).'$/', $uri))
			{
				return true;
			}
		}
		return false;
	}
	
	public static function IsExclude404Uagent($agent)
	{
		$arExcludes = explode("\n", self::GetOption(self::$moduleId, 'STAT404_UAGENT_EXCLUDE', ''));
		foreach($arExcludes as $exc)
		{
			$exc = trim($exc);
			if(strlen($exc)==0) continue;
			if(preg_match('/^'.strtr(preg_quote($exc, '/'), array('\*'=>'.*')).'$/', $agent))
			{
				return true;
			}
		}
		return false;
	}
	
	public static function AllowRedirect($old, $new, $loop=0)
	{		
		$res = true;
		$arRedirects = array();
		$cookie = (isset($_COOKIE['ESR_REDIRECTS']) ? $_COOKIE['ESR_REDIRECTS'] : '');
		if(strlen($cookie) > 0)
		{
			$arRedirects = \Bitrix\EsolRedirector\Utils::Unserialize(base64_decode($cookie));
			if(!is_array($arRedirects)) $arRedirects = array();
		}
		$find = false;
		foreach($arRedirects as $k=>$v)
		{
			if(time() - $v['t'] > 10)
			{
				unset($arRedirects[$k]);
				continue;
			}
			if(is_array($v['r']) && in_array($old, $v['r']))
			{
				$find = true;
				if(in_array($new, $v['r']) || $v['c'] > 10)
				{
					$res = false;
					if($loop==0) unset($arRedirects[$k]);
				}
				else
				{
					$arRedirects[$k]['r'][] = $new;
					$arRedirects[$k]['c']++;
					$arRedirects[$k]['t'] = time();
				}
			}
		}
		if(!$find)
		{
			$arRedirects[] = array(
				'c' => 1,
				'r' => array($old, $new),
				't' => time()
			);
		}
		$arRedirects = array_values($arRedirects);
		$encCookie = base64_encode(serialize($arRedirects));
		$_COOKIE['ESR_REDIRECTS'] = $encCookie;
		if(!headers_sent()) @setcookie("ESR_REDIRECTS", $encCookie, false, "/");
		return $res;
	}
	
	public static function RemoveOldRedirect($old)
	{
		$arRedirects = array();
		$cookie = $_COOKIE['ESR_REDIRECTS'];
		if(strlen($cookie) > 0)
		{
			$arRedirects = \Bitrix\EsolRedirector\Utils::Unserialize(base64_decode($cookie));
			if(!is_array($arRedirects)) $arRedirects = array();
		}
		$find = false;

		foreach($arRedirects as $k=>$v)
		{
			if((is_array($v['r']) && in_array($old, $v['r'])) || $v['t'] - time() > 10)
			{
				unset($arRedirects[$k]);
				$find = true;
			}
		}
		if($find)
		{
			$arRedirects = array_values($arRedirects);
			$encCookie = base64_encode(serialize($arRedirects));
			$_COOKIE['ESR_REDIRECTS'] = $encCookie;
			if(!headers_sent()) @setcookie("ESR_REDIRECTS", $encCookie, false, "/");
		}
	}
	
	public static function ParseUrl($uri, $addGetParams=false)
	{
		$arParts = explode('?', $uri, 2);
		$arUrl = array('path'=>$arParts[0]);
		if(isset($arParts[1])) $arUrl['query'] = $arParts[1];
		elseif($addGetParams && $_GET) $arUrl['query'] = http_build_query($_GET);
		return $arUrl;
	}
	
	public static function isCrontab()
	{
		//return (bool)(defined('BX_CRONTAB') && BX_CRONTAB==true);
		return (bool)(PHP_SAPI === 'cli');
	}
	
	public static function isHttps()
	{
		if($_SERVER['SERVER_PORT'] == 443)
		{
			return true;
		}
		
		if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ToLower($_SERVER['HTTP_X_FORWARDED_PROTO'])=='https')
		{
			return true;
		}

		$https = $_SERVER['HTTPS'];
		if($https !== null && strtolower($https) == "on")
		{
			return true;
		}
		
		$https = $_SERVER['HTTP_HTTPS'];
		if($https !== null && strtolower($https) == "on")
		{
			return true;
		}

		return false;
	}
	
	public static function GetOption($moduleId, $optionName, $optionVal='')
	{
		if($moduleId===false) $moduleId = self::$moduleId;
		if($moduleId==self::$moduleId && \Bitrix\Main\Config\Option::get(self::$moduleId, 'SITE_SETTINGS_ENABLE_'.SITE_ID, 'N')=='Y')
		{
			$optionName = $optionName.'_'.SITE_ID;
		}
		return \Bitrix\Main\Config\Option::get($moduleId, $optionName, $optionVal);
	}
	
	public static function OnAfterIBlockElementAdd($arFields)
	{
		IblockRedirectWriter::getInstance()->RemoveNewElementUrl($arFields);
	}
	
	public static function OnBeforeIBlockElementUpdate($arFields)
	{
		IblockRedirectWriter::getInstance()->SaveOldElementUrl($arFields);
	}
	
	public static function OnAfterIBlockElementUpdate($arFields)
	{
		IblockRedirectWriter::getInstance()->SaveNewElementUrl($arFields);
	}
	
	public static function OnBeforeIBlockElementDelete($ID)
	{
		IblockRedirectWriter::getInstance()->RemoveElementUrl($ID);
	}
	
	public static function OnAfterIBlockSectionAdd($arFields)
	{
		IblockRedirectWriter::getInstance()->RemoveNewSectionUrl($arFields);
	}
	
	public static function OnBeforeIBlockSectionUpdate($arFields)
	{
		IblockRedirectWriter::getInstance()->SaveOldSectionUrl($arFields);
	}
	
	public static function OnAfterIBlockSectionUpdate($arFields)
	{
		IblockRedirectWriter::getInstance()->SaveNewSectionUrl($arFields);
	}
	
	public static function OnBeforeIBlockSectionDelete($ID)
	{
		IblockRedirectWriter::getInstance()->RemoveSectionUrl($ID);
	}
}