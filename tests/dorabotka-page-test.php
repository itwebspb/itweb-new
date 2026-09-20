<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$pagePath = $root . '/services/podderzhka/dorabotka/index.php';
$sectionPath = $root . '/services/podderzhka/dorabotka/.section.php';
$designCssPath = $root . '/bitrix/templates/aspro_max/css/design-model.css';
$customCssPath = $root . '/bitrix/templates/aspro_max/css/custom.css';

$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$assert(is_file($pagePath), 'The dorabotka landing page must exist.');
$assert(is_file($sectionPath), 'The dorabotka section metadata must exist.');
$assert(is_file($designCssPath), 'The shared service-page design model must exist.');

if (is_file($pagePath)) {
    $page = (string) file_get_contents($pagePath);

    $assert(str_contains($page, 'Доработка и развитие сайта под ключ'), 'The page must contain the supplied H1.');
    $assert(str_contains($page, '1200+ доработок внедрено'), 'The page must contain the supplied proof point.');
    $assert(str_contains($page, 'id="dm-tariffs"'), 'The tariffs section must have an anchor.');
    $assert(substr_count($page, 'class="dm-tariff') >= 3, 'The page must contain all three tariffs.');
    $assert(substr_count($page, 'class="dm-step"') >= 6, 'The page must contain all six process steps.');
    $assert(substr_count($page, 'class="dm-faq-item"') >= 8, 'The page must contain all eight FAQ entries.');
    $assert(str_contains($page, 'itemtype="https://schema.org/FAQPage"'), 'The FAQ must retain schema.org markup.');
    $assert(str_contains($page, 'data-param-form_id="CALLBACK"'), 'Calls to action must open the site callback form.');
    $assert(!str_contains($page, 'href="#"'), 'The page must not contain placeholder links.');
}

if (is_file($customCssPath)) {
    $customCss = (string) file_get_contents($customCssPath);
    $assert(str_contains($customCss, 'design-model.css'), 'The shared design model must be loaded from custom.css.');
}

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, array_map(
        static fn (string $failure): string => 'FAIL: ' . $failure,
        $failures
    )) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "PASS: dorabotka landing page structure is complete.\n");
