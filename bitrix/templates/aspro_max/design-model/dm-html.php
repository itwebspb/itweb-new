<?php
/**
 * Restore inline SVG icons for dm-page DETAIL_TEXT.
 *
 * Bitrix visual editor / CBXSanitizer drops <svg>/<path>/<circle>/<rect>
 * from infoblock element HTML, leaving empty .dm-ico / .ico circles.
 * Section DESCRIPTION is not sanitized the same way, which is why
 * /services/podderzhka/ still shows icons.
 */
declare(strict_types=1);

function dm_icons_are_stripped(string $html): bool
{
	if ($html === '' || strpos($html, '<svg') !== false) {
		return false;
	}
	return strpos($html, 'dm-ico') !== false || strpos($html, 'class="ico"') !== false;
}

function dm_page_html_for_code(string $code, string $pagesDir): ?string
{
	$code = trim($code);
	$pagesDir = rtrim($pagesDir, '/');
	if ($code === '' || $pagesDir === '' || !is_dir($pagesDir)) {
		return null;
	}

	$suffix = '-' . $code . '.html';
	$exact = 'uslugi-' . $code . '.html';
	$entries = scandir($pagesDir);
	if ($entries === false) {
		return null;
	}

	$matches = [];
	foreach ($entries as $name) {
		if ($name === '.' || $name === '..') {
			continue;
		}
		if (substr($name, -5) !== '.html') {
			continue;
		}
		if ($name === $exact || substr($name, -strlen($suffix)) === $suffix) {
			$matches[] = $pagesDir . '/' . $name;
		}
	}

	$best = null;
	foreach ($matches as $path) {
		if (!is_readable($path)) {
			continue;
		}
		$html = file_get_contents($path);
		if (!is_string($html) || strpos($html, 'dm-page') === false) {
			continue;
		}
		if (strpos($html, '<svg') !== false) {
			return $html;
		}
		$best = $html;
	}

	return $best;
}

function dm_restore_detail_html(string $detailText, string $code, string $pagesDir): string
{
	if (!dm_icons_are_stripped($detailText)) {
		return $detailText;
	}
	$fromFile = dm_page_html_for_code($code, $pagesDir);
	return $fromFile !== null ? $fromFile : $detailText;
}
