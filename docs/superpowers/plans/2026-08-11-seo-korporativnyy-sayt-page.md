# SEO корпоративного сайта — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу услуги «SEO корпоративного сайта под ключ» в корневом разделе «SEO продвижение» (iblock 21) по дизайн-модели `.dm-page`, с текстами из `SEO корпоративного сайта.html`.

**Architecture:** HTML-источник в `design-model/pages/` собирается из блоков `dm-*`; стили только в `design-model.css`; контент копируется в `DETAIL_TEXT` элемента инфоблока. База — ветка с уже подключённой дизайн-моделью.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-11-seo-korporativnyy-sayt-page-design.md`

## Global Constraints

- Тексты (заголовки, лиды, списки, FAQ, кейсы, тарифы, отзывы) — **дословно** из `SEO корпоративного сайта.html`; не переписывать смысл.
- Иконки — только тонкие SVG; эмодзи и префиксы `⏱`/`✓` в маркерах не копировать (маркеры даёт CSS).
- Порядок секций — как в исходнике (11 блоков, CTA без формы/телефонов).
- Стили — только `design-model.css` через уже существующий `SetAdditionalCSS`; не `@import` в `custom.css`.
- IBLOCK_ID = **21**; раздел CODE `seo-prodvizhenie`; элемент CODE `seo-korporativnogo-sayta`.
- Git commit — **только если пользователь явно попросил**; иначе пропускать шаги Commit.
- Не коммитить ядро Bitrix / `cache` / `upload` / `.env`.

## File map

| File | Role |
|---|---|
| `SEO корпоративного сайта.html` | Source of truth for copy (read-only) |
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html` | Markup/pattern reference |
| `bitrix/templates/aspro_max/css/design-model.css` | Modify: `.dm-solution` lists + `.dm-tool` |
| `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html` | Create: page HTML source |
| `local/php_interface/` or one-off PHP under `/tmp` | Create section + element + DETAIL_TEXT sync (do not leave permanent junk in repo unless useful) |
| `bitrix/templates/aspro_max/header.php` | Verify only: `SetAdditionalCSS(.../design-model.css)` already present on design-model branch |

---

### Task 1: Подключить ветку дизайн-модели

**Files:**
- Checkout / merge: `origin/cursor/bitrix-1c-services-page-e816` into working branch
- Verify: `bitrix/templates/aspro_max/css/design-model.css`
- Verify: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html`
- Verify: `header.php` contains `SetAdditionalCSS`

**Interfaces:**
- Consumes: remote branch with design-model v1
- Produces: local working tree with `design-model.css` and эталон HTML available for Tasks 2–4

- [ ] **Step 1: Проверить текущую ветку и наличие дизайн-модели**

```bash
cd /Users/viktorgromov/itweb-new/itweb-new
git status -sb
test -f bitrix/templates/aspro_max/css/design-model.css && echo HAS_CSS || echo NO_CSS
```

Expected: на `master` скорее всего `NO_CSS`.

- [ ] **Step 2: Получить ветку и создать рабочую ветку от неё**

```bash
git fetch origin cursor/bitrix-1c-services-page-e816
git checkout -B feature/seo-korporativnyy-sayt origin/cursor/bitrix-1c-services-page-e816
```

Если есть незакоммиченные нужные файлы (spec/plan/исходник SEO) — сначала убедиться, что они не потеряются (`git status`; при необходимости `git stash -u` только для лишнего, либо оставить untracked docs/SEO html как есть).

- [ ] **Step 3: Подтвердить ключевые артефакты**

```bash
rg -n "SetAdditionalCSS.*design-model" bitrix/templates/aspro_max/header.php
test -f bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html
wc -l bitrix/templates/aspro_max/css/design-model.css
```

Expected: совпадение `SetAdditionalCSS`, эталон HTML существует, CSS непустой.

- [ ] **Step 4: Commit (только по запросу пользователя)**

Пропустить, если коммит не просили. Иначе зафиксировать только checkout-ветку без лишних файлов.

---

### Task 2: Расширить `design-model.css` (solution lists + dm-tool)

**Files:**
- Modify: `bitrix/templates/aspro_max/css/design-model.css` (после блока `.dm-solution`)

**Interfaces:**
- Consumes: existing `.dm-solution`, `.dm-feature`, CSS variables (`--dm-accent`, `--dm-border`, `--dm-radius`, `--dm-shadow`, `--dm-muted`, `--dm-ink`)
- Produces: styles for `.dm-solution ul/li` and `.dm-tool` used by Task 3 HTML

- [ ] **Step 1: Найти якорь вставки**

```bash
rg -n "^\.dm-solution" bitrix/templates/aspro_max/css/design-model.css
```

- [ ] **Step 2: Добавить стили сразу после существующих правил `.dm-solution`**

```css
/* Solution feature lists (SEO directions etc.) */
.dm-page .dm-solution ul {
	list-style: none;
	margin: 16px 0 0;
	padding: 0;
	text-align: left;
}
.dm-page .dm-solution li {
	position: relative;
	padding: 8px 0 8px 26px;
	font-size: 14px;
	color: var(--dm-text);
	border-bottom: 1px solid var(--dm-border);
	line-height: 1.5;
}
.dm-page .dm-solution li:last-child { border-bottom: none; }
.dm-page .dm-solution li:before {
	content: '✓';
	position: absolute;
	left: 0;
	top: 8px;
	color: var(--dm-accent);
	font-weight: 700;
}

/* Compact tools row */
.dm-page .dm-tool {
	display: flex;
	align-items: center;
	gap: 14px;
	background: #fff;
	border: 1px solid var(--dm-border);
	border-radius: var(--dm-radius);
	padding: 18px 16px;
	box-shadow: var(--dm-shadow);
	transition: transform .2s ease, box-shadow .2s ease;
}
.dm-page .dm-tool:hover {
	transform: translateY(-3px);
	box-shadow: var(--dm-shadow-hover);
}
.dm-page .dm-tool .dm-ico {
	margin: 0;
	flex-shrink: 0;
}
.dm-page .dm-tool h3 {
	font-size: 16px;
	margin: 0 0 4px;
}
.dm-page .dm-tool p {
	margin: 0;
	font-size: 13px;
	color: var(--dm-muted);
	line-height: 1.45;
}
```

Если в файле нет обёртки `.dm-page` у соседних правил — сохранить тот же префикс/стиль, что у соседних селекторов (либо `.dm-solution ul` без `.dm-page`, если так принято в файле).

- [ ] **Step 3: Проверить синтаксис и наличие селекторов**

```bash
rg -n "dm-tool|\.dm-solution ul" bitrix/templates/aspro_max/css/design-model.css
```

Expected: оба блока найдены.

- [ ] **Step 4: Commit (только по запросу пользователя)**

```bash
git add bitrix/templates/aspro_max/css/design-model.css
git commit -m "$(cat <<'EOF'
Add dm-tool and solution list styles for SEO service pages.

EOF
)"
```

---

### Task 3: Собрать HTML-источник страницы

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html`
- Read: `SEO корпоративного сайта.html` (copy)
- Read: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html` (patterns)

**Interfaces:**
- Consumes: CSS from Task 2; `dm-*` patterns from эталон
- Produces: complete `<div class="dm-page">…</div>` for DETAIL_TEXT sync in Task 4

- [ ] **Step 1: Создать файл-оболочку**

Создать файл с единственной корневой обёрткой (без `<html>`/`<head>`/`<style>`/`<script>`):

```html
<div class="dm-page">

<!-- sections go here -->

</div>
```

- [ ] **Step 2: Hero**

Скопировать H1 и subtitle дословно из исходника. 4 benefit: тексты «100+ корпоративных сайтов», «Рост заявок», «Результат за 3-6 мес», «Гарантия 12 месяцев» + SVG в `.ico`. Кнопки:

```html
<span class="dm-btn dm-btn-primary dm-btn-lg" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Заказать звонок</span>
<a href="#dm-cases" class="dm-btn dm-btn-secondary dm-btn-lg">Смотреть кейсы</a>
```

Структура — как эталонный `dm-hero` / `dm-hero-benefits` / `dm-hero-benefit`.

- [ ] **Step 3: «Что мы делаем»**

`dm-section` + `dm-h2` + `dm-lead` (тексты из исходника) + `dm-grid dm-grid-3`. Три `dm-solution`: Техническое SEO / Контент и структура / Репутация и ссылки — каждый с SVG `.dm-ico`, H3, `<p>`, `<ul>` из `direction-features` (без `✓` в тексте li).

- [ ] **Step 4: «Почему выбирают»**

`dm-section dm-section--alt` + сетка `dm-card` (6 карточек из исходника) + SVG. Тексты дословно.

- [ ] **Step 5: Кейсы**

`id="dm-cases"`. Три `dm-case` с `dm-case-head` / `dm-case-body` / `dm-case-stats` / `dm-case-stat` — цифры и подписи из исходника. Кнопки «Смотреть кейс» — `href="#"` как в исходнике (или `/projects/` только если исходник так указывает; в исходнике `#` — оставить `#`). Нижняя кнопка: текст «Смотреть все 100+ кейсов».

- [ ] **Step 6: Тарифы**

Три `dm-tariff`; средний с `is-featured` + `<span class="dm-tariff-badge">Популярный</span>`. Duration без `⏱` («от 3 месяцев» / «от 6 месяцев»). Цены и li — дословно. Кнопки CALLBACK с текстом «Заказать SEO». Нижняя CTA: «Нужен индивидуальный тариф? Сделаем».

- [ ] **Step 7: Доп. услуги**

`dm-grid` + `dm-card` (6 шт.): SVG, H3, p, `<a class="dm-btn dm-btn-outline" href="…">` с URL **как в исходнике** (`/uslugi/prodvizhenie/...`). Нижняя кнопка CALLBACK: «Нужны другие услуги? Мы поможем».

- [ ] **Step 8: Этапы**

`dm-timeline` с 6× `dm-step` по образцу эталона (`dm-step-media` / `dm-step-circle`+SVG / `dm-step-num` / `dm-step-body`).  
H3: `Этап N: …` как в исходнике (можно `<span class="n">Этап N:</span>` + остаток заголовка).  
В `<p>` — пункты исходника через `<br>`, **без эмодзи**, слова сохранить (пример этапа 1: `Технический и SEO-аудит<br>Анализ конкурентов<br>…`).  
Нижняя кнопка: текст из исходника «Хотите узнать сроки для вашего проекта? Оставьте заявку» → CALLBACK.

- [ ] **Step 9: Инструменты**

`dm-grid dm-grid-4` (или auto-fit) из 8×:

```html
<div class="dm-tool">
	<span class="dm-ico" aria-hidden="true"><svg viewBox="0 0 24 24">…</svg></span>
	<div>
		<h3>Яндекс.Вебмастер</h3>
		<p>Мониторинг индексации</p>
	</div>
</div>
```

Названия/подписи — из исходника. Нижняя кнопка CALLBACK: «Нужен нестандартный подход? Обсудим».

- [ ] **Step 10: Отзывы + FAQ + CTA**

Отзывы: `dm-review` + `dm-review-stars` (5 SVG star path как в эталоне); тексты/авторы/компании дословно; кнопка «Смотреть все отзывы на Яндекс.Картах» — `href="#"` как в исходнике.

FAQ: 8× `<details class="dm-faq-item"><summary>…</summary><div class="dm-faq-answer">…</div></details>` — вопросы/ответы дословно, включая нумерацию в summary если она есть в исходнике («1. Сколько стоит…»).

CTA (`dm-section dm-cta` `id="dm-form"`):

```html
<h2 class="dm-h2">Готовы обсудить SEO корпоративного сайта под ключ?</h2>
<p class="dm-lead">Оставьте заявку — менеджер свяжется в течение 30 минут, проведёт бесплатный аудит и рассчитает стоимость. Работаем по всей России, общение онлайн.</p>
<div class="dm-center">
	<span class="dm-btn dm-btn-primary dm-btn-lg" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Получить бесплатный аудит и расчёт стоимости</span>
</div>
```

Без `dm-cta-guarantees`, без телефонов/email/мессенджеров, без HTML `<form>`.

- [ ] **Step 11: Проверки файла**

```bash
PAGE=bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html
rg -n "dm-hero|Что мы делаем|Почему выбирают|Кейсы|Тарифы|Дополнительные услуги|Этапы|Инструменты|Что говорят|частые вопросы|Готовы обсудить" "$PAGE"
rg -n "emoji|🏢|📈|🔍|⭐|⏱" "$PAGE" || true
rg -n "<form|tel:\+|WhatsApp|Telegram" "$PAGE" || true
rg -c "dm-tool" "$PAGE"
rg -c "dm-faq-item" "$PAGE"
```

Expected: все секции на месте; нет эмодзи/формы/телефонов; 8 `dm-tool`; 8 FAQ.

- [ ] **Step 12: Commit (только по запросу пользователя)**

```bash
git add bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html
git commit -m "$(cat <<'EOF'
Add SEO corporate site service page HTML source for dm-page.

EOF
)"
```

---

### Task 4: Раздел + элемент Bitrix и sync DETAIL_TEXT

**Files:**
- Bitrix DB iblock 21 (section + element)
- One-off PHP runner (через `php -f` в document root или MCP `user-itweb-bitrix-delight` `eval`/`sql` если локальный LAMP недоступен)

**Interfaces:**
- Consumes: HTML file from Task 3
- Produces: ACTIVE section `seo-prodvizhenie`, ACTIVE element `seo-korporativnogo-sayta` with `DETAIL_TEXT` = file contents, type `html`

- [ ] **Step 1: Убедиться, что Bitrix/окружение доступно**

Варианты (в порядке предпочтения):
1. Локальный LAMP: `bash .cursor/start.sh`, затем PHP CLI с `DOCUMENT_ROOT`.
2. MCP `user-itweb-bitrix-delight` (`eval` / `sql`).

Проверка SQL:

```sql
SELECT ID, NAME, CODE FROM b_iblock_section WHERE IBLOCK_ID=21 AND CODE='sozdanie-saytov';
SELECT ID, NAME, CODE FROM b_iblock_section WHERE IBLOCK_ID=21 AND CODE='seo-prodvizhenie';
SELECT ID, NAME, CODE FROM b_iblock_element WHERE IBLOCK_ID=21 AND CODE='seo-korporativnogo-sayta';
```

- [ ] **Step 2: Создать раздел, если нет**

Через Bitrix API (предпочтительно), не сырой INSERT без UF/дерева:

```php
<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = 21;
$code = 'seo-prodvizhenie';
$existing = CIBlockSection::GetList([], ['IBLOCK_ID'=>$iblockId,'CODE'=>$code], false, ['ID'])->Fetch();
if ($existing) {
	echo 'SECTION_EXISTS '.$existing['ID'];
} else {
	$bs = new CIBlockSection;
	$id = $bs->Add([
		'IBLOCK_ID' => $iblockId,
		'IBLOCK_SECTION_ID' => false, // корень, как sozdanie-saytov
		'NAME' => 'SEO продвижение',
		'CODE' => $code,
		'ACTIVE' => 'Y',
	]);
	if (!$id) { fwrite(STDERR, $bs->LAST_ERROR."\n"); exit(1); }
	echo 'SECTION_CREATED '.$id;
}
```

Если у `sozdanie-saytov` есть родитель — повторить тот же `IBLOCK_SECTION_ID` (корень услуг), не invent другой уровень.

- [ ] **Step 3: Создать/обновить элемент и DETAIL_TEXT**

```php
<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = 21;
$sectionCode = 'seo-prodvizhenie';
$elementCode = 'seo-korporativnogo-sayta';
$htmlPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html';
$html = file_get_contents($htmlPath);
if ($html === false || $html === '') { fwrite(STDERR, "empty html\n"); exit(1); }

$section = CIBlockSection::GetList([], ['IBLOCK_ID'=>$iblockId,'CODE'=>$sectionCode], false, ['ID'])->Fetch();
if (!$section) { fwrite(STDERR, "section missing\n"); exit(1); }

$fields = [
	'IBLOCK_ID' => $iblockId,
	'IBLOCK_SECTION_ID' => (int)$section['ID'],
	'NAME' => 'SEO корпоративного сайта под ключ',
	'CODE' => $elementCode,
	'ACTIVE' => 'Y',
	'DETAIL_TEXT' => $html,
	'DETAIL_TEXT_TYPE' => 'html',
];

$el = new CIBlockElement;
$existing = CIBlockElement::GetList([], ['IBLOCK_ID'=>$iblockId,'CODE'=>$elementCode], false, false, ['ID'])->Fetch();
if ($existing) {
	if (!$el->Update($existing['ID'], $fields)) { fwrite(STDERR, $el->LAST_ERROR."\n"); exit(1); }
	echo 'ELEMENT_UPDATED '.$existing['ID'];
} else {
	$id = $el->Add($fields);
	if (!$id) { fwrite(STDERR, $el->LAST_ERROR."\n"); exit(1); }
	echo 'ELEMENT_CREATED '.$id;
}
```

- [ ] **Step 4: Очистить кеш**

```bash
rm -rf bitrix/cache/css/* bitrix/managed_cache/* bitrix/cache/* 2>/dev/null || true
```

- [ ] **Step 5: Проверить URL**

```bash
# внутри VM:
curl -sI "http://itweb-new.local/services/seo-prodvizhenie/seo-korporativnogo-sayta/" | head -20
# или через port-forward / MCP / browser
```

Expected: HTTP 200 (не 404). В теле страницы есть `dm-page`, H1 из исходника, классы `dm-tool` / `dm-faq-item`.

- [ ] **Step 6: Commit файлов шаблона (только по запросу пользователя)**

В git попадают HTML + CSS (+ spec/plan если просили). DETAIL_TEXT в git не коммитится.

---

### Task 5: Финальная сверка со spec

**Files:**
- Spec checklist vs live page / source file

- [ ] **Step 1: Чеклист готовности из spec**

Пройти каждый пункт:

1. HTML-источник на `dm-*`, порядок секций как в исходнике.
2. SVG вместо эмодзи; отзывы со звёздами SVG.
3. Раздел `seo-prodvizhenie` и элемент `seo-korporativnogo-sayta` ACTIVE.
4. `DETAIL_TEXT` = содержимое HTML-файла.
5. Стили `.dm-tool` и списков solution в `design-model.css`.
6. URL `/services/seo-prodvizhenie/seo-korporativnogo-sayta/` открывается, визуально в духе эталона `.dm-page`.

- [ ] **Step 2: Diff текстов (выборочно)**

Сверить минимум: H1, 3 названия тарифов + цены, 1 FAQ Q/A, 1 отзыв — с `SEO корпоративного сайта.html`. Расхождений по словам быть не должно (кроме снятых эмодзи-префиксов).

- [ ] **Step 3: Сообщить пользователю URL и что сделано**

Указать путь к HTML-источнику, ID раздела/элемента, URL для проверки. Staging-деплой — только если отдельно попросят.

---

## Spec coverage (self-review)

| Spec requirement | Task |
|---|---|
| Раздел `seo-prodvizhenie` корень | Task 4 |
| Элемент CODE/NAME | Task 4 |
| HTML path `uslugi-seo-prodvizhenie-korporativnyy-sayt.html` | Task 3 |
| Порядок 11 секций + тексты | Task 3 |
| SVG / no emoji | Task 3 Step 11 |
| CTA without form/phones | Task 3 Step 10 |
| `.dm-solution ul` + `.dm-tool` CSS | Task 2 |
| DETAIL_TEXT sync | Task 4 |
| Cache clear + URL check | Task 4–5 |
| Base = design-model branch | Task 1 |
| No staging auto-deploy | Task 5 (explicit out of scope) |
