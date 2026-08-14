<?php
/**
 * Upsert dm-page HTML into Bitrix (element DETAIL_TEXT or section DESCRIPTION) + meta.
 * No Bitrix prolog — plain mysqli.
 *
 * Env:
 *   DM_DB_HOST, DM_DB_USER, DM_DB_PASS, DM_DB_NAME
 *   DM_DOCROOT — absolute site document root
 *   DM_MANIFEST — path to pages.manifest.json
 *   DM_CODE — optional single code; empty = all
 *   DM_PAGES_DIR — optional override for HTML source dir (default: DOCROOT/.../pages)
 */
declare(strict_types=1);

$host = getenv('DM_DB_HOST') ?: 'localhost';
$user = getenv('DM_DB_USER') ?: '';
$pass = getenv('DM_DB_PASS') ?: '';
$name = getenv('DM_DB_NAME') ?: '';
$docroot = rtrim(getenv('DM_DOCROOT') ?: '', '/');
$manifestPath = getenv('DM_MANIFEST') ?: '';
$onlyCode = getenv('DM_CODE') ?: '';
$pagesDir = getenv('DM_PAGES_DIR') ?: ($docroot . '/bitrix/templates/aspro_max/design-model/pages');

if ($user === '' || $name === '' || $docroot === '' || $manifestPath === '') {
	fwrite(STDERR, "Missing DM_DB_* / DM_DOCROOT / DM_MANIFEST\n");
	exit(1);
}

$manifest = json_decode((string)file_get_contents($manifestPath), true);
if (!is_array($manifest) || empty($manifest['pages'])) {
	fwrite(STDERR, "Bad manifest\n");
	exit(1);
}
$iblockId = (int)($manifest['iblock_id'] ?? 21);

$m = new mysqli($host, $user, $pass, $name);
$m->set_charset('utf8mb4');
if ($m->connect_errno) {
	fwrite(STDERR, "DB connect failed: {$m->connect_error}\n");
	exit(1);
}

function q(mysqli $m, string $sql): mysqli_result|bool
{
	$r = $m->query($sql);
	if ($r === false) {
		throw new RuntimeException($m->error . " | " . $sql);
	}
	return $r;
}

function sectionIdByCode(mysqli $m, int $iblockId, string $code): ?int
{
	$codeEsc = $m->real_escape_string($code);
	$r = q($m, "SELECT ID FROM b_iblock_section WHERE IBLOCK_ID={$iblockId} AND CODE='{$codeEsc}' LIMIT 1");
	$row = $r->fetch_assoc();
	return $row ? (int)$row['ID'] : null;
}

function ensureSection(
	mysqli $m,
	int $iblockId,
	string $code,
	string $name,
	?string $parentCode,
	?string $html,
	?string $metaTitle,
	?string $metaDescription
): int {
	$parentId = null;
	if ($parentCode) {
		$parentId = sectionIdByCode($m, $iblockId, $parentCode);
		if ($parentId === null) {
			throw new RuntimeException("Parent section '{$parentCode}' missing for '{$code}'");
		}
	}

	$id = sectionIdByCode($m, $iblockId, $code);
	$now = date('Y-m-d H:i:s');
	$nameEsc = $m->real_escape_string($name);
	$codeEsc = $m->real_escape_string($code);
	$parentSql = $parentId === null ? 'NULL' : (string)$parentId;

	if ($id === null) {
		q($m, "INSERT INTO b_iblock_section
			(TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE, GLOBAL_ACTIVE, SORT, NAME, CODE, DESCRIPTION, DESCRIPTION_TYPE, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, XML_ID)
			VALUES ('{$now}',0,'{$now}',0,{$iblockId},{$parentSql},'Y','Y',500,'{$nameEsc}','{$codeEsc}','','html',1,2,1,'{$codeEsc}')");
		$id = (int)$m->insert_id;
		echo "SECTION_CREATED {$code}#{$id}\n";
	} else {
		q($m, "UPDATE b_iblock_section SET NAME='{$nameEsc}', ACTIVE='Y', IBLOCK_SECTION_ID={$parentSql}, TIMESTAMP_X='{$now}' WHERE ID={$id}");
		echo "SECTION_UPDATED {$code}#{$id}\n";
	}

	if ($html !== null) {
		$st = $m->prepare('UPDATE b_iblock_section SET DESCRIPTION=?, DESCRIPTION_TYPE="html" WHERE ID=?');
		$st->bind_param('si', $html, $id);
		$st->execute() || throw new RuntimeException($st->error);
	}

	if ($metaTitle || $metaDescription) {
		setSectionMeta($m, $iblockId, $id, $metaTitle, $metaDescription);
	}

	return $id;
}

function setSectionMeta(mysqli $m, int $iblockId, int $sectionId, ?string $title, ?string $desc): void
{
	q($m, "DELETE FROM b_iblock_section_iprop WHERE SECTION_ID={$sectionId}");
	q($m, "DELETE FROM b_iblock_iproperty WHERE ENTITY_TYPE='S' AND ENTITY_ID={$sectionId} AND CODE IN ('SECTION_META_TITLE','SECTION_META_DESCRIPTION')");

	$pairs = [];
	if ($title) {
		$pairs['SECTION_META_TITLE'] = $title;
	}
	if ($desc) {
		$pairs['SECTION_META_DESCRIPTION'] = $desc;
	}
	foreach ($pairs as $code => $tpl) {
		$st = $m->prepare("INSERT INTO b_iblock_iproperty (IBLOCK_ID, CODE, ENTITY_TYPE, ENTITY_ID, TEMPLATE) VALUES (?, ?, 'S', ?, ?)");
		$st->bind_param('isis', $iblockId, $code, $sectionId, $tpl);
		$st->execute() || throw new RuntimeException($st->error);
		$ipropId = (int)$m->insert_id;
		$st2 = $m->prepare('INSERT INTO b_iblock_section_iprop (IBLOCK_ID, SECTION_ID, IPROP_ID, VALUE) VALUES (?,?,?,?)');
		$st2->bind_param('iiis', $iblockId, $sectionId, $ipropId, $tpl);
		$st2->execute() || throw new RuntimeException($st2->error);
	}
}

function setElementMeta(mysqli $m, int $iblockId, int $sectionId, int $elementId, string $name, ?string $title, ?string $desc): void
{
	q($m, "DELETE FROM b_iblock_element_iprop WHERE ELEMENT_ID={$elementId}");
	q($m, "DELETE FROM b_iblock_iproperty WHERE ENTITY_TYPE='E' AND ENTITY_ID={$elementId}");

	// Mirror visitka pattern: inherit-ish page title slots 45/46/47 when present
	foreach ([45, 46, 47] as $ip) {
		$st = $m->prepare('INSERT INTO b_iblock_element_iprop (IBLOCK_ID, SECTION_ID, ELEMENT_ID, IPROP_ID, VALUE) VALUES (?,?,?,?,?)');
		$st->bind_param('iiiis', $iblockId, $sectionId, $elementId, $ip, $name);
		$st->execute(); // best-effort; ignore if FK fails on some envs
	}

	$pairs = [];
	if ($title) {
		$pairs['ELEMENT_META_TITLE'] = $title;
	}
	if ($desc) {
		$pairs['ELEMENT_META_DESCRIPTION'] = $desc;
	}
	foreach ($pairs as $code => $tpl) {
		$st = $m->prepare("INSERT INTO b_iblock_iproperty (IBLOCK_ID, CODE, ENTITY_TYPE, ENTITY_ID, TEMPLATE) VALUES (?, ?, 'E', ?, ?)");
		$st->bind_param('isis', $iblockId, $code, $elementId, $tpl);
		$st->execute() || throw new RuntimeException($st->error);
		$ipropId = (int)$m->insert_id;
		$st2 = $m->prepare('INSERT INTO b_iblock_element_iprop (IBLOCK_ID, SECTION_ID, ELEMENT_ID, IPROP_ID, VALUE) VALUES (?,?,?,?,?)');
		$st2->bind_param('iiiis', $iblockId, $sectionId, $elementId, $ipropId, $tpl);
		$st2->execute() || throw new RuntimeException($st2->error);
	}
}

function upsertElement(
	mysqli $m,
	int $iblockId,
	string $code,
	string $sectionCode,
	string $name,
	string $html,
	?string $metaTitle,
	?string $metaDescription
): int {
	$sectionId = sectionIdByCode($m, $iblockId, $sectionCode);
	if ($sectionId === null) {
		throw new RuntimeException("Section '{$sectionCode}' missing for element '{$code}'");
	}

	$codeEsc = $m->real_escape_string($code);
	$r = q($m, "SELECT ID FROM b_iblock_element WHERE IBLOCK_ID={$iblockId} AND CODE='{$codeEsc}' LIMIT 1");
	$row = $r->fetch_assoc();
	$now = date('Y-m-d H:i:s');

	if ($row) {
		$eid = (int)$row['ID'];
		$st = $m->prepare('UPDATE b_iblock_element SET NAME=?, ACTIVE="Y", IBLOCK_SECTION_ID=?, DETAIL_TEXT=?, DETAIL_TEXT_TYPE="html", TIMESTAMP_X=?, MODIFIED_BY=0, IN_SECTIONS="Y" WHERE ID=?');
		$st->bind_param('sissi', $name, $sectionId, $html, $now, $eid);
		$st->execute() || throw new RuntimeException($st->error);
		echo "ELEMENT_UPDATED {$code}#{$eid}\n";
	} else {
		$search = mb_strtoupper($name, 'UTF-8');
		$st = $m->prepare('INSERT INTO b_iblock_element (TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE, SORT, NAME, PREVIEW_TEXT, PREVIEW_TEXT_TYPE, DETAIL_TEXT, DETAIL_TEXT_TYPE, SEARCHABLE_CONTENT, WF_STATUS_ID, IN_SECTIONS, CODE, XML_ID) VALUES (?,0,?,0,?,?, "Y",500,?,"","text",?,"html",?,1,"Y",?,?)');
		$st->bind_param('ssiisssss', $now, $now, $iblockId, $sectionId, $name, $html, $search, $code, $code);
		$st->execute() || throw new RuntimeException($st->error);
		$eid = (int)$m->insert_id;
		echo "ELEMENT_CREATED {$code}#{$eid}\n";
	}

	q($m, "DELETE FROM b_iblock_section_element WHERE IBLOCK_ELEMENT_ID={$eid}");
	q($m, "INSERT INTO b_iblock_section_element (IBLOCK_SECTION_ID, IBLOCK_ELEMENT_ID, ADDITIONAL_PROPERTY_ID) VALUES ({$sectionId}, {$eid}, NULL)");

	setElementMeta($m, $iblockId, $sectionId, $eid, $name, $metaTitle, $metaDescription);
	return $eid;
}

function rebuildSectionTree(mysqli $m, int $iblockId): void
{
	$r = q($m, "SELECT ID, IBLOCK_SECTION_ID, SORT, ACTIVE FROM b_iblock_section WHERE IBLOCK_ID={$iblockId} ORDER BY SORT ASC, ID ASC");
	$byParent = [];
	while ($row = $r->fetch_assoc()) {
		$pid = $row['IBLOCK_SECTION_ID'] === null || $row['IBLOCK_SECTION_ID'] === '' ? 0 : (int)$row['IBLOCK_SECTION_ID'];
		$byParent[$pid][] = $row;
	}
	$margin = 1;
	$walk = function (int $parentId, int $depth, bool $parentActive) use (&$walk, &$margin, $byParent, $m): void {
		foreach ($byParent[$parentId] ?? [] as $row) {
			$id = (int)$row['ID'];
			$left = $margin++;
			$active = ($row['ACTIVE'] === 'Y') && $parentActive;
			$walk($id, $depth + 1, $active);
			$right = $margin++;
			$ga = $active ? 'Y' : 'N';
			q($m, "UPDATE b_iblock_section SET LEFT_MARGIN={$left}, RIGHT_MARGIN={$right}, DEPTH_LEVEL={$depth}, GLOBAL_ACTIVE='{$ga}' WHERE ID={$id}");
		}
	};
	$walk(0, 1, true);
	echo "RESORT_OK\n";
}

// Process sections first (parents before children), then elements
$pages = $manifest['pages'];
if ($onlyCode !== '') {
	$pages = array_values(array_filter($pages, static fn($p) => ($p['code'] ?? '') === $onlyCode));
	if (!$pages) {
		fwrite(STDERR, "Code not in manifest: {$onlyCode}\n");
		exit(1);
	}
}

$sections = array_values(array_filter($pages, static fn($p) => ($p['kind'] ?? '') === 'section'));
$elements = array_values(array_filter($pages, static fn($p) => ($p['kind'] ?? 'element') === 'element'));

// parents (no parent) first
usort($sections, static function ($a, $b) {
	$ap = $a['parent'] ?? null;
	$bp = $b['parent'] ?? null;
	if ($ap === $bp) {
		return 0;
	}
	if ($ap === null) {
		return -1;
	}
	if ($bp === null) {
		return 1;
	}
	return 0;
});

try {
	foreach ($sections as $p) {
		$htmlFile = $pagesDir . '/' . $p['html'];
		if (!is_file($htmlFile)) {
			throw new RuntimeException("Missing HTML: {$htmlFile}");
		}
		$html = file_get_contents($htmlFile);
		if ($html === false || strpos($html, 'dm-page') === false) {
			throw new RuntimeException("Bad HTML (no dm-page): {$htmlFile}");
		}
		ensureSection(
			$m,
			$iblockId,
			$p['code'],
			$p['name'],
			$p['parent'] ?? null,
			$html,
			$p['meta_title'] ?? null,
			$p['meta_description'] ?? null
		);
	}

	foreach ($elements as $p) {
		$htmlFile = $pagesDir . '/' . $p['html'];
		if (!is_file($htmlFile)) {
			throw new RuntimeException("Missing HTML: {$htmlFile}");
		}
		$html = file_get_contents($htmlFile);
		if ($html === false || strpos($html, 'dm-page') === false) {
			throw new RuntimeException("Bad HTML (no dm-page): {$htmlFile}");
		}
		upsertElement(
			$m,
			$iblockId,
			$p['code'],
			$p['section'],
			$p['name'],
			$html,
			$p['meta_title'] ?? null,
			$p['meta_description'] ?? null
		);
	}
	echo "OK\n";
} catch (Throwable $e) {
	fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
	exit(1);
}

rebuildSectionTree($m, $iblockId);
