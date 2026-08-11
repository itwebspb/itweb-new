# SEO интернет-магазина — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу услуги «SEO интернет-магазина под ключ» в разделе «SEO продвижение» (iblock 21, секция 159) по дизайн-модели `.dm-page`, с текстами из `SEO интернет-магазина.html`.

**Architecture:** HTML-источник в `design-model/pages/` собирается из блоков `dm-*` по образцу `uslugi-seo-prodvizhenie-korporativnyy-sayt.html`; CSS уже готов; контент копируется в `DETAIL_TEXT` нового элемента. Раздел `seo-prodvizhenie` уже существует.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-11-seo-internet-magazin-page-design.md`

## Global Constraints

- Тексты (заголовки, лиды, списки, FAQ, кейсы, тарифы, отзывы) — **дословно** из `/Users/viktorgromov/Downloads/SEO интернет-магазина.html`; не переписывать смысл (включая опечатки исходника вроде «переилинковка» / «настрайваем»).
- Иконки — только тонкие SVG; эмодзи и префиксы `⏱`/`✓` в маркерах не копировать (маркеры даёт CSS).
- Порядок секций — как в исходнике (11 блоков, CTA без формы/телефонов).
- Новый CSS не добавлять, пока не вскроется баг; стили `.dm-solution ul`, `.dm-tool`, выравнивание кнопок уже есть.
- IBLOCK_ID = **21**; раздел ID **159**, CODE `seo-prodvizhenie`; элемент CODE `seo-internet-magazina`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Git commit / push / staging deploy — **только по явному запросу** («задеплой» = staging + commit + push).
- Не коммитить ядро Bitrix / `cache` / `upload` / `.env`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO интернет-магазина.html` | Source of truth for copy (read-only) |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html` | Markup/pattern reference (sibling page) |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/css/design-model.css` | Verify only (no required changes) |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html` | Create: page HTML source |
| Staging Bitrix DB iblock 21 | Create element + DETAIL_TEXT + IPROPERTY meta |
| `docs/superpowers/specs/2026-08-11-seo-internet-magazin-page-design.md` | Spec (already written) |
| `docs/superpowers/plans/2026-08-11-seo-internet-magazin-page.md` | This plan |

---

### Task 1: Проверить базу worktree и CSS

**Files:**
- Verify: `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/css/design-model.css`
- Verify: `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html`
- Verify: staging section 159 exists

**Interfaces:**
- Consumes: existing design-model branch/worktree
- Produces: confirmed ready environment for Tasks 2–3

- [ ] **Step 1: Проверить ветку и эталон**

```bash
WT=/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt
cd "$WT"
git status -sb
test -f bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html && echo HAS_REF
rg -n "dm-tool|\.dm-solution ul" bitrix/templates/aspro_max/css/design-model.css | head
```

Expected: ветка `feature/seo-korporativnyy-sayt`, эталон HTML есть, селекторы `dm-tool` и `.dm-solution ul` найдены.

- [ ] **Step 2: Подтвердить раздел на staging**

```bash
ssh -o BatchMode=yes itweb-new@itweb-new.acrobat.test-itweb.ru 'PASS=$(php -r '"'"'$a=include "www/itweb-new.acrobat.test-itweb.ru/bitrix/.settings.php"; echo $a["connections"]["value"]["default"]["password"];'"'"' 2>/dev/null); mysql -uitweb-new -p"$PASS" itweb-new --default-character-set=utf8mb4 -N -e "SELECT ID,NAME,CODE,ACTIVE FROM b_iblock_section WHERE IBLOCK_ID=21 AND CODE=\"seo-prodvizhenie\";" 2>&1 | grep -v Warning'
```

Expected: `159	SEO продвижение	seo-prodvizhenie	Y`

- [ ] **Step 3: Commit**

Пропустить (verify-only).

---

### Task 2: Собрать HTML-источник страницы

**Files:**
- Create: `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html`
- Read: `/Users/viktorgromov/Downloads/SEO интернет-магазина.html`
- Read: `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html`

**Interfaces:**
- Consumes: existing `dm-*` CSS; sibling page markup patterns
- Produces: complete `<div class="dm-page">…</div>` for DETAIL_TEXT sync in Task 3

- [ ] **Step 1: Создать файл-оболочку**

Рабочий каталог: `$WT` (см. Task 1).

Создать файл с единственной корневой обёрткой (без `<html>`/`<head>`/`<style>`/`<script>`):

```html
<div class="dm-page">

<!-- sections go here -->

</div>
```

Рекомендуемый способ: скопировать sibling `uslugi-seo-prodvizhenie-korporativnyy-sayt.html` и заменить **все** тексты/цифры/ссылки на контент интернет-магазина (структура `dm-*` уже верная).

- [ ] **Step 2: Hero**

H1: `SEO интернет-магазина под ключ`  
Subtitle: дословно из исходника (про каталог/карточки/фильтры, от 50 000 ₽/мес).  
4 benefit: `100+ магазинов`, `Рост продаж`, `Результат за 3-6 мес`, `Гарантия 12 месяцев` + SVG в `.ico`.

```html
<span class="dm-btn dm-btn-primary dm-btn-lg" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Заказать звонок</span>
<a href="#dm-cases" class="dm-btn dm-btn-secondary dm-btn-lg">Смотреть кейсы</a>
```

- [ ] **Step 3: «Что мы делаем»**

`dm-section` + `dm-h2` + `dm-lead` + `dm-grid dm-grid-3`. Три `dm-solution`:
1. Техническое SEO — Schema.org/Product + 5 li из исходника
2. Оптимизация каталога — 5 li
3. Контент и ссылки — 6 li (включая «Внутренняя переилинковка» как в исходнике)

Каждый: SVG `.dm-ico`, H3, `<p>`, `<ul>` без `✓` в тексте li.

- [ ] **Step 4: «Почему выбирают»**

`dm-section dm-section--alt` + 6× `dm-card` + SVG. Тексты дословно (e-commerce, фокус на продажи, техподдержка от 5 000 ₽/мес).

- [ ] **Step 5: Кейсы**

`id="dm-cases"`. Три `dm-case`:
1. стройматериалы — +450% / 280 заказов / ТОП-3 / 180 000 ₽
2. одежды — +380% / 420 / ТОП-5 / 150 000 ₽
3. электроники — +520% / 350 / ТОП-3 / 220 000 ₽

Кнопки «Смотреть кейс» — `href="#"`; низ: «Смотреть все 100+ кейсов».

- [ ] **Step 6: Тарифы**

Три `dm-tariff`; средний `is-featured` + badge «Популярный».  
Duration без `⏱`: «от 3 месяцев» / «от 6 месяцев».  
Цены: от 50 000 / от 80 000 / от 150 000 ₽/мес.  
Кнопки CALLBACK «Заказать SEO»; низ: «Нужен индивидуальный тариф? Сделаем».

- [ ] **Step 7: Доп. услуги**

6× `dm-card` с URL **как в исходнике**:
- `/uslugi/prodvizhenie/audit/`
- `/uslugi/prodvizhenie/semanticheskoe-yadro/`
- `/uslugi/prodvizhenie/seo/regionalnoe/`
- `/uslugi/prodvizhenie/seo/serm/`
- `/uslugi/prodvizhenie/seo/novyy-sayt/`
- `/uslugi/prodvizhenie/kontekst/`

Нижняя CALLBACK: «Нужны другие услуги? Мы поможем».

- [ ] **Step 8: Этапы**

6× `dm-step`. H3: `Этап 1: Аудит магазина` … `Этап 4: Оптимизация каталога` … `Этап 5: Контент и ссылки` …  
В `<p>` — пункты через `<br>`, **без эмодзи**.  
Низ CALLBACK: «Хотите узнать сроки для вашего магазина? Оставьте заявку».

- [ ] **Step 9: Инструменты**

8× `dm-tool` (те же названия, что у корпоративной: Вебмастер, Метрика, GSC, Arsenkin, Keys.so, GoGetLinks, PageSpeed, Text.ru / Advego). Lead — про e-commerce из исходника.  
Низ CALLBACK: «Нужен нестандартный подход? Обсудим».

- [ ] **Step 10: Отзывы + FAQ + CTA**

Отзывы: 3× `dm-review` с SVG-звёздами; тексты Владислав/Константин/Ирина дословно.  
FAQ: 8× `<details class="dm-faq-item">` — вопросы/ответы дословно (включая нумерацию «1. …»).  
CTA:

```html
<section class="dm-section dm-cta" id="dm-form">
	<div class="dm-container">
		<h2 class="dm-h2">Готовы обсудить SEO интернет-магазина под ключ?</h2>
		<p class="dm-lead">Оставьте заявку — менеджер свяжется в течение 30 минут и рассчитает стоимость. Работаем по всей России, общение онлайн.</p>
		<div class="dm-center">
			<span class="dm-btn dm-btn-primary dm-btn-lg" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Получить бесплатный аудит и расчёт стоимости</span>
		</div>
	</div>
</section>
```

Без формы, телефонов, email, WhatsApp/Telegram, гарантий-галочек под формой.

- [ ] **Step 11: Проверки файла**

```bash
PAGE="$WT/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html"
rg -n "dm-hero|Что мы делаем|Почему выбирают|Кейсы|Тарифы|Дополнительные услуги|Этапы|Инструменты|Что говорят|частые вопросы|Готовы обсудить" "$PAGE"
rg -n "🛒|📈|🔍|⭐|⏱|📦|📝" "$PAGE" || true
rg -n "<form|tel:\+|WhatsApp|Telegram" "$PAGE" || true
rg -c "dm-tool" "$PAGE"
rg -c "dm-faq-item" "$PAGE"
rg -n "переилинковка|от 50 000|от 80 000|от 150 000" "$PAGE"
```

Expected: все секции на месте; нет эмодзи/формы/телефонов; 8 `dm-tool`; 8 FAQ; ключевые тексты магазина найдены.

- [ ] **Step 12: Commit (только по запросу пользователя)**

```bash
cd "$WT"
git add bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html
git commit -m "$(cat <<'EOF'
Add SEO internet store service page HTML source for dm-page.

EOF
)"
```

---

### Task 3: Элемент Bitrix + DETAIL_TEXT + meta (staging)

**Files:**
- Staging DB iblock 21 (element under section 159)
- HTML file from Task 2 (scp/rsync to staging document root, then sync via temporary web PHP)

**Interfaces:**
- Consumes: HTML file from Task 2
- Produces: ACTIVE element `seo-internet-magazina` with `DETAIL_TEXT` = file contents; meta title/description from source `<head>`

**Meta from source:**
- Title: `SEO интернет-магазина под ключ в СПб — вывод в ТОП | ITWEB`
- Description: `SEO-продвижение интернет-магазинов под ключ. Вывод в ТОП Яндекса и Google за 3-6 месяцев. Оптимизация каталога, карточек товаров, фильтров. От 50 000 ₽/мес. Бесплатный аудит.`

- [ ] **Step 1: Залить HTML на staging**

```bash
WT=/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt
SRC="$WT/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html"
REMOTE=itweb-new@itweb-new.acrobat.test-itweb.ru
ROOT=www/itweb-new.acrobat.test-itweb.ru
scp -o BatchMode=yes "$SRC" "$REMOTE:$ROOT/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html"
```

- [ ] **Step 2: Создать/обновить элемент через временный web PHP**

Положить в DOCUMENT_ROOT скрипт с секретным ключом (затем удалить). Полный bootstrap Bitrix CLI часто падает — предпочитать HTTP-вызов.

```php
<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
if ($_GET['k'] !== 'REPLACE_WITH_RANDOM') { http_response_code(403); die('forbidden'); }
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = 21;
$sectionId = 159;
$elementCode = 'seo-internet-magazina';
$htmlPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html';
$html = file_get_contents($htmlPath);
if ($html === false || $html === '') { http_response_code(500); die('empty html'); }

$fields = [
	'IBLOCK_ID' => $iblockId,
	'IBLOCK_SECTION_ID' => $sectionId,
	'NAME' => 'SEO интернет-магазина под ключ',
	'CODE' => $elementCode,
	'ACTIVE' => 'Y',
	'DETAIL_TEXT' => $html,
	'DETAIL_TEXT_TYPE' => 'html',
	'IPROPERTY_TEMPLATES' => [
		'ELEMENT_META_TITLE' => 'SEO интернет-магазина под ключ в СПб — вывод в ТОП | ITWEB',
		'ELEMENT_META_DESCRIPTION' => 'SEO-продвижение интернет-магазинов под ключ. Вывод в ТОП Яндекса и Google за 3-6 месяцев. Оптимизация каталога, карточек товаров, фильтров. От 50 000 ₽/мес. Бесплатный аудит.',
	],
];

$el = new CIBlockElement;
$existing = CIBlockElement::GetList([], ['IBLOCK_ID'=>$iblockId,'CODE'=>$elementCode], false, false, ['ID'])->Fetch();
if ($existing) {
	if (!$el->Update($existing['ID'], $fields)) { http_response_code(500); die($el->LAST_ERROR); }
	echo 'ELEMENT_UPDATED '.$existing['ID'];
} else {
	$id = $el->Add($fields);
	if (!$id) { http_response_code(500); die($el->LAST_ERROR); }
	echo 'ELEMENT_CREATED '.$id;
}
```

Вызов:

```bash
KEY=$(openssl rand -hex 12)
# scp script with KEY substituted, then:
curl -fsS "https://itweb-new.acrobat.test-itweb.ru/_tmp_seo_im_sync.php?k=$KEY"
# delete remote script after success
```

- [ ] **Step 3: Очистить кеш на staging**

```bash
ssh -o BatchMode=yes itweb-new@itweb-new.acrobat.test-itweb.ru 'rm -rf www/itweb-new.acrobat.test-itweb.ru/bitrix/cache/* www/itweb-new.acrobat.test-itweb.ru/bitrix/managed_cache/*'
```

- [ ] **Step 4: Проверить URL**

```bash
curl -sI "https://itweb-new.acrobat.test-itweb.ru/services/seo-prodvizhenie/seo-internet-magazina/" | head -20
curl -fsS "https://itweb-new.acrobat.test-itweb.ru/services/seo-prodvizhenie/seo-internet-magazina/" | rg -n "dm-page|SEO интернет-магазина под ключ|dm-tool|dm-faq-item" | head
```

Expected: HTTP 200; в теле `dm-page`, H1, `dm-tool`, FAQ.

- [ ] **Step 5: Commit (только по запросу)**

В git: HTML (+ spec/plan если ещё не закоммичены). DETAIL_TEXT в git не коммитится.

---

### Task 4: Финальная сверка со spec

**Files:**
- Spec checklist vs live page / source file

- [ ] **Step 1: Чеклист готовности из spec**

1. HTML-источник на `dm-*`, порядок секций как в исходнике.
2. SVG вместо эмодзи; отзывы со звёздами SVG.
3. Элемент `seo-internet-magazina` в разделе 159 ACTIVE.
4. `DETAIL_TEXT` = содержимое HTML-файла; meta title/description выставлены.
5. URL `/services/seo-prodvizhenie/seo-internet-magazina/` открывается, визуально в духе `.dm-page`.

- [ ] **Step 2: Diff текстов (выборочно)**

Сверить: H1, 3 названия тарифов + цены, 1 FAQ Q/A, 1 отзыв — с Downloads-исходником. Расхождений по словам быть не должно (кроме снятых эмодзи-префиксов).

- [ ] **Step 3: Сообщить пользователю**

Указать путь к HTML, ID элемента, URL. Commit/push — только если попросили «задеплой» (файлы на staging уже залиты в Task 3; «задеплой» дополнительно фиксирует git).

---

## Spec coverage (self-review)

| Spec requirement | Task |
|---|---|
| Раздел 159 уже есть | Task 1 |
| Элемент CODE/NAME `seo-internet-magazina` | Task 3 |
| HTML path `uslugi-seo-prodvizhenie-internet-magazin.html` | Task 2 |
| Порядок 11 секций + тексты | Task 2 |
| SVG / no emoji | Task 2 Step 11 |
| CTA without form/phones | Task 2 Step 10 |
| Meta from `<head>` | Task 3 |
| No new CSS required | Task 1 verify |
| DETAIL_TEXT sync | Task 3 |
| Cache clear + URL check | Task 3–4 |
| Commit/deploy only on request | Global + Task 3–4 |
