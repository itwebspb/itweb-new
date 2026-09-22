<?php
/**
 * One-shot browser migrator: upsert IBLOCK 21 elements geo and aeo only.
 *
 * Does not create/update the parent section, other services, or CSS.
 *
 * Run (logged in as Bitrix admin):
 *   https://<host>/scripts/migrate-geo-aeo-oneshot.php
 *
 * Or with token (set MIGRATE_GEO_AEO_TOKEN below or in the environment):
 *   https://<host>/scripts/migrate-geo-aeo-oneshot.php?token=...
 *
 * Copy this file under DOCUMENT_ROOT if /scripts/ is not web-accessible.
 * After a successful upsert the file deletes itself.
 */
const MIGRATE_GEO_AEO_TOKEN = '';
const MIGRATE_IBLOCK_ID = 21;
const MIGRATE_SECTION_CODE = 'prodvizhenie-v-ai-poiske';

if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
	fwrite(STDERR, "Browser-only. Open this file via HTTP on the Bitrix site.\n");
	exit(1);
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

$docroot = dmFindDocroot();
$_SERVER['DOCUMENT_ROOT'] = $docroot;

define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('NOT_CHECK_PERMISSIONS', true);
define('PUBLIC_AJAX_MODE', true);

$prolog = $docroot . '/bitrix/modules/main/include/prolog_before.php';
if (!is_file($prolog)) {
	http_response_code(500);
	dmOut('Bitrix prolog не найден: ' . htmlspecialchars($prolog, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
	exit(1);
}
require $prolog;

global $USER, $DB;

$isAdmin = is_object($USER) && $USER->IsAuthorized() && $USER->IsAdmin();
$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$expected = (string)(getenv('MIGRATE_GEO_AEO_TOKEN') ?: MIGRATE_GEO_AEO_TOKEN);
$tokenOk = ($expected !== '' && $token !== '' && hash_equals($expected, $token));
if (!$isAdmin && !$tokenOk) {
	http_response_code(403);
	dmOut('Forbidden. Войдите как администратор Bitrix или передайте ?token= (константа/env MIGRATE_GEO_AEO_TOKEN).');
	exit(1);
}

$iblockLoaded = class_exists('\\Bitrix\\Main\\Loader')
	? \Bitrix\Main\Loader::includeModule('iblock')
	: (class_exists('CModule') && CModule::IncludeModule('iblock'));
if (!$iblockLoaded) {
	http_response_code(500);
	dmOut('Модуль iblock не подключён.');
	exit(1);
}

$pagesDir = $docroot . '/bitrix/templates/aspro_max/design-model/pages';
$pages = [
	[
		'code' => 'geo',
		'name' => 'GEO-продвижение',
		'html' => 'uslugi-prodvizhenie-ai-geo.html',
		'meta_title' => 'GEO-продвижение в ИИ-поиске — Generative Engine Optimization | Ай Ти Веб',
		'meta_description' => 'GEO-продвижение (Generative Engine Optimization) под ключ: выводим бренд в ответы ChatGPT, Perplexity, Gemini и Алисы. Entity-профиль, контент под извлечение ИИ, мониторинг цитируемости. От 35 000 ₽/мес. Аудит ИИ-видимости.',
	],
	[
		'code' => 'aeo',
		'name' => 'AEO-продвижение',
		'html' => 'uslugi-prodvizhenie-ai-aeo.html',
		'meta_title' => 'AEO-продвижение сайтов — Answer Engine Optimization | Ай Ти Веб',
		'meta_description' => 'AEO-продвижение сайтов под ключ: оптимизация под прямые ответы Яндекса и Google, голосовой поиск, сниппеты и FAQ. Точные ответы, Schema.org, контент под вопросы, мониторинг Position 0. От 30 000 ₽/мес.',
	],
];

$log = [];
$ok = false;

try {
	$section = dmFindSection(MIGRATE_IBLOCK_ID, MIGRATE_SECTION_CODE);
	if ($section === null) {
		throw new RuntimeException('Родительский раздел CODE=' . MIGRATE_SECTION_CODE . ' (IBLOCK ' . MIGRATE_IBLOCK_ID . ') не найден. Раздел не создаём — создайте его вручную и повторите.');
	}
	$sectionId = (int)$section['ID'];
	$log[] = 'SECTION_OK ' . MIGRATE_SECTION_CODE . '#' . $sectionId . ' (не изменялся)';

	foreach ($pages as $page) {
		$htmlFile = $pagesDir . '/' . $page['html'];
		if (!is_file($htmlFile)) {
			throw new RuntimeException('Нет HTML-файла: ' . $htmlFile);
		}
		$html = file_get_contents($htmlFile);
		if ($html === false || strpos($html, 'dm-page') === false) {
			throw new RuntimeException('Плохой HTML (нет dm-page): ' . $htmlFile);
		}
		$result = dmUpsertElement(MIGRATE_IBLOCK_ID, $sectionId, $page, $html);
		$log[] = $result;
	}

	if (method_exists('CIBlock', 'clearIblockTagCache')) {
		CIBlock::clearIblockTagCache(MIGRATE_IBLOCK_ID);
		$log[] = 'CACHE_CLEARED iblock_' . MIGRATE_IBLOCK_ID;
	}

	$ok = true;
	$log[] = 'OK';
} catch (Throwable $e) {
	http_response_code(500);
	$log[] = 'ERROR: ' . $e->getMessage();
}

if ($ok) {
	if (@unlink(__FILE__)) {
		$log[] = 'SCRIPT_DELETED ' . __FILE__;
	} else {
		$log[] = 'SCRIPT_DELETE_FAILED удалите вручную: ' . __FILE__;
	}
} else {
	$log[] = 'SCRIPT_KEPT (ошибка — файл не удалён, можно повторить)';
}

dmOut(implode("\n", $log));
exit($ok ? 0 : 1);

function dmFindDocroot(): string
{
	$candidates = [];
	if (!empty($_SERVER['DOCUMENT_ROOT'])) {
		$candidates[] = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/');
	}
	$dir = __DIR__;
	for ($i = 0; $i < 6; $i++) {
		$candidates[] = $dir;
		$parent = dirname($dir);
		if ($parent === $dir) {
			break;
		}
		$dir = $parent;
	}
	foreach (array_unique($candidates) as $root) {
		if ($root !== '' && is_file($root . '/bitrix/modules/main/include/prolog_before.php')) {
			return $root;
		}
	}
	http_response_code(500);
	echo 'Cannot locate Bitrix DOCUMENT_ROOT';
	exit(1);
}

function dmOut(string $text): void
{
	echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>migrate geo/aeo</title></head><body><pre>';
	echo htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	echo '</pre></body></html>';
}

function dmFindSection(int $iblockId, string $code): ?array
{
	$rs = CIBlockSection::GetList(
		[],
		[
			'IBLOCK_ID' => $iblockId,
			'=CODE' => $code,
			'CHECK_PERMISSIONS' => 'N',
		],
		false,
		['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'ACTIVE']
	);
	$row = $rs ? $rs->Fetch() : false;
	return $row ?: null;
}

function dmFindElement(int $iblockId, int $sectionId, string $code): array
{
	$rs = CIBlockElement::GetList(
		['ID' => 'ASC'],
		[
			'IBLOCK_ID' => $iblockId,
			'=CODE' => $code,
			'CHECK_PERMISSIONS' => 'N',
			'SHOW_NEW' => 'Y',
			'SHOW_HISTORY' => 'N',
		],
		false,
		false,
		['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'CODE', 'NAME', 'ACTIVE']
	);
	$matches = [];
	while ($rs && ($row = $rs->Fetch())) {
		$matches[] = $row;
	}
	if (!$matches) {
		return [];
	}
	$inSection = array_values(array_filter(
		$matches,
		static fn($row) => (int)$row['IBLOCK_SECTION_ID'] === $sectionId
	));
	$pool = $inSection ?: $matches;
	if (count($pool) > 1) {
		$ids = implode(',', array_map(static fn($row) => (string)$row['ID'], $pool));
		throw new RuntimeException("Несколько элементов CODE={$code} в IBLOCK {$iblockId}: #{$ids}. Разрешите вручную.");
	}
	return $pool[0];
}

function dmUpsertElement(int $iblockId, int $sectionId, array $page, string $html): string
{
	global $DB;

	$code = $page['code'];
	$existing = dmFindElement($iblockId, $sectionId, $code);
	$el = new CIBlockElement();

	$fields = [
		'IBLOCK_ID' => $iblockId,
		'IBLOCK_SECTION_ID' => $sectionId,
		'IBLOCK_SECTION' => [$sectionId],
		'CODE' => $code,
		'ACTIVE' => 'Y',
		'DETAIL_TEXT' => $html,
		'DETAIL_TEXT_TYPE' => 'html',
	];

	if ($existing) {
		$id = (int)$existing['ID'];
		if (!$el->Update($id, $fields)) {
			throw new RuntimeException("Update {$code}#{$id}: " . $el->LAST_ERROR);
		}
		$action = 'ELEMENT_UPDATED';
	} else {
		$fields['NAME'] = $page['name'];
		$fields['XML_ID'] = $code;
		$fields['SORT'] = 500;
		$fields['IPROPERTY_TEMPLATES'] = [
			'ELEMENT_META_TITLE' => $page['meta_title'],
			'ELEMENT_META_DESCRIPTION' => $page['meta_description'],
		];
		$id = (int)$el->Add($fields);
		if ($id <= 0) {
			throw new RuntimeException("Add {$code}: " . $el->LAST_ERROR);
		}
		$action = 'ELEMENT_CREATED';
	}

	if (is_object($DB) && method_exists($DB, 'ForSql')) {
		$sql = "UPDATE b_iblock_element SET DETAIL_TEXT='" . $DB->ForSql($html) . "', DETAIL_TEXT_TYPE='html', ACTIVE='Y' WHERE ID=" . $id . ' AND IBLOCK_ID=' . $iblockId;
		$res = $DB->Query($sql);
		if (!$res) {
			throw new RuntimeException("Не удалось записать DETAIL_TEXT для {$code}#{$id}");
		}
	}

	return "{$action} {$code}#{$id}";
}
