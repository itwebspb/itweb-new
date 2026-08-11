# SEO лендинга — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу услуги «SEO лендинга под ключ» в разделе «SEO продвижение» (iblock 21, секция 159) по дизайн-модели `.dm-page`, с текстами из `SEO лендинга.html`.

**Architecture:** HTML-источник в `design-model/pages/` собирается из блоков `dm-*` по образцу соседних SEO-страниц; CSS уже готов; контент копируется в `DETAIL_TEXT` нового элемента. Раздел `seo-prodvizhenie` уже существует.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-11-seo-lending-page-design.md`

## Global Constraints

- Тексты (заголовки, лиды, списки, FAQ, кейсы, тарифы, отзывы) — **дословно** из `/Users/viktorgromov/Downloads/SEO лендинга.html`; не переписывать смысл (опечатки исходника сохранять).
- Иконки — только тонкие SVG; эмодзи и префиксы `⏱`/`✓` в маркерах не копировать (маркеры даёт CSS).
- Порядок секций — как в исходнике (11 блоков, CTA без формы/телефонов).
- Новый CSS не добавлять, пока не вскроется баг.
- IBLOCK_ID = **21**; раздел ID **159**, CODE `seo-prodvizhenie`; элемент CODE `seo-lendinga`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Git commit / push / staging deploy — **только по явному запросу** («задеплой» = staging + commit + push).
- Не коммитить ядро Bitrix / `cache` / `upload` / `.env`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO лендинга.html` | Source of truth for copy (read-only) |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html` | Markup/pattern reference (sibling) |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/css/design-model.css` | Verify only |
| `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-lending.html` | Create: page HTML source |
| Staging Bitrix DB iblock 21 | Create element + DETAIL_TEXT + IPROPERTY meta |
| `docs/superpowers/specs/2026-08-11-seo-lending-page-design.md` | Spec |
| `docs/superpowers/plans/2026-08-11-seo-lending-page.md` | This plan |

---

### Task 1: Проверить базу worktree и CSS

**Files:**
- Verify worktree branch, sibling HTML, CSS selectors, staging section 159

**Interfaces:**
- Produces: confirmed ready environment for Tasks 2–3

- [ ] **Step 1: Проверить ветку и эталон**

```bash
WT=/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt
cd "$WT"
git status -sb
test -f bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html && echo HAS_REF
rg -n "dm-tool|\.dm-solution ul" bitrix/templates/aspro_max/css/design-model.css | head
```

Expected: ветка `feature/seo-korporativnyy-sayt`, эталон HTML есть, селекторы найдены.

- [ ] **Step 2: Подтвердить раздел на staging**

```bash
ssh -o BatchMode=yes itweb-new@itweb-new.acrobat.test-itweb.ru 'PASS=$(php -r '"'"'$a=include "www/itweb-new.acrobat.test-itweb.ru/bitrix/.settings.php"; echo $a["connections"]["value"]["default"]["password"];'"'"' 2>/dev/null); mysql -uitweb-new -p"$PASS" itweb-new --default-character-set=utf8mb4 -N -e "SELECT ID,NAME,CODE,ACTIVE FROM b_iblock_section WHERE IBLOCK_ID=21 AND CODE=\"seo-prodvizhenie\";" 2>&1 | grep -v Warning'
```

Expected: `159	SEO продвижение	seo-prodvizhenie	Y`

- [ ] **Step 3: Commit** — пропустить.

---

### Task 2: Собрать HTML-источник страницы

**Files:**
- Create: `.worktrees/seo-korporativnyy-sayt/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-lending.html`
- Read: `/Users/viktorgromov/Downloads/SEO лендинга.html`
- Read: sibling `uslugi-seo-prodvizhenie-internet-magazin.html` (or corporate)

**Interfaces:**
- Produces: complete `<div class="dm-page">…</div>` for Task 3

- [ ] **Step 1: Создать файл** — скопировать sibling и заменить все тексты на контент лендинга. Без `<html>`/`<head>`/`<style>`/`<script>`.

- [ ] **Step 2: Hero** — H1 `SEO лендинга под ключ`; subtitle дословно; benefits: `100+ лендингов`, `Конверсия до 15%`, `Результат за 2-4 мес`, `Гарантия 12 месяцев`; CALLBACK + `#dm-cases`.

- [ ] **Step 3: «Что мы делаем»** — 3× `dm-solution`: Техническое SEO / Контент и конверсия / Трафик и ссылки — списки из исходника без `✓`.

- [ ] **Step 4: «Почему выбирают»** — 6× `dm-card` дословно.

- [ ] **Step 5: Кейсы** — `id="dm-cases"`; стоматология (+260%/120/ТОП-5/40 000 ₽); ремонт (+310%/95/ТОП-3/45 000 ₽); онлайн-курс (+220%/240/ТОП-5/35 000 ₽). Ссылки `#` как в исходнике.

- [ ] **Step 6: Тарифы** — Старт 30 000 / Бизнес 50 000 (featured) / Премиум 90 000; duration без `⏱`; CALLBACK «Заказать SEO».

- [ ] **Step 7: Доп. услуги** — 6 карточек; URL из исходника без изменения; CALLBACK «Нужны другие услуги? Мы поможем».

- [ ] **Step 8: Этапы** — 6× `dm-step` (Аудит лендинга … Контент и конверсия …); пункты через `<br>` без эмодзи.

- [ ] **Step 9: Инструменты** — 8× `dm-tool` (в исходнике 8-й — `A/B тесты`, не Text.ru); тексты дословно.

- [ ] **Step 10: Отзывы + FAQ + CTA** — 3 отзыва (Тимур/Виталий/Наталья); 8 FAQ; CTA:

```html
<section class="dm-section dm-cta" id="dm-form">
	<div class="dm-container">
		<h2 class="dm-h2">Готовы обсудить SEO лендинга под ключ?</h2>
		<p class="dm-lead">Оставьте заявку — менеджер свяжется в течение 30 минут, проведёт бесплатный аудит и рассчитает стоимость. Работаем по всей России, общение онлайн.</p>
		<div class="dm-center">
			<span class="dm-btn dm-btn-primary dm-btn-lg" data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Получить бесплатный аудит и расчёт стоимости</span>
		</div>
	</div>
</section>
```

Без формы/телефонов/мессенджеров.

- [ ] **Step 11: Проверки**

```bash
PAGE="$WT/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-lending.html"
rg -n "dm-hero|Что мы делаем|Почему выбирают|Кейсы|Тарифы|Дополнительные услуги|Этапы|Инструменты|Что говорят|частые вопросы|Готовы обсудить" "$PAGE"
rg -n "🎯|📈|🔍|⭐|⏱|📦|📝" "$PAGE" || true
rg -n "<form|tel:\+|WhatsApp|Telegram" "$PAGE" || true
rg -c "dm-tool" "$PAGE"
rg -c "dm-faq-item" "$PAGE"
rg -n "от 30 000|от 50 000|от 90 000|A/B тесты|100\+ лендингов" "$PAGE"
```

Expected: все секции; нет эмодзи/формы; 8 tool; 8 FAQ; ключевые тексты лендинга.

- [ ] **Step 12: Commit** — только по запросу пользователя.

---

### Task 3: Элемент Bitrix + DETAIL_TEXT + meta (staging)

**Files:** Staging DB + scp HTML

**Meta:**
- Title: `SEO лендинга под ключ — вывод в ТОП | ITWEB`
- Description: `SEO-продвижение лендингов под ключ. Вывод в ТОП Яндекса и Google за 2-4 месяца. Нишевые запросы, A/B тесты, рост конверсии до 15%. От 30 000 ₽/мес. Бесплатный аудит.`

- [ ] **Step 1: scp HTML** на `www/itweb-new.acrobat.test-itweb.ru/bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-lending.html`

- [ ] **Step 2: Создать/обновить элемент** через временный web PHP (секретный ключ), затем удалить скрипт:

```php
$elementCode = 'seo-lendinga';
$fields = [
	'IBLOCK_ID' => 21,
	'IBLOCK_SECTION_ID' => 159,
	'NAME' => 'SEO лендинга под ключ',
	'CODE' => $elementCode,
	'ACTIVE' => 'Y',
	'DETAIL_TEXT' => $html,
	'DETAIL_TEXT_TYPE' => 'html',
	'IPROPERTY_TEMPLATES' => [
		'ELEMENT_META_TITLE' => 'SEO лендинга под ключ — вывод в ТОП | ITWEB',
		'ELEMENT_META_DESCRIPTION' => 'SEO-продвижение лендингов под ключ. Вывод в ТОП Яндекса и Google за 2-4 месяца. Нишевые запросы, A/B тесты, рост конверсии до 15%. От 30 000 ₽/мес. Бесплатный аудит.',
	],
];
```

- [ ] **Step 3: Очистить кеш** staging.

- [ ] **Step 4: Проверить URL**

```bash
curl -sI "https://itweb-new.acrobat.test-itweb.ru/services/seo-prodvizhenie/seo-lendinga/" | head -20
curl -fsS "https://itweb-new.acrobat.test-itweb.ru/services/seo-prodvizhenie/seo-lendinga/" | rg -n "dm-page|SEO лендинга под ключ|dm-tool|dm-faq-item" | head
```

Expected: HTTP 200; `dm-page`, H1, tools, FAQ.

- [ ] **Step 5: Commit** — только по запросу.

---

### Task 4: Финальная сверка со spec

- [ ] **Step 1:** Чеклист готовности из spec (HTML dm-*, SVG, элемент ACTIVE, DETAIL_TEXT+meta, URL).
- [ ] **Step 2:** Spot-check H1, 3 тарифа+цены, 1 FAQ, 1 отзыв vs Downloads.
- [ ] **Step 3:** Сообщить URL, ID элемента, путь HTML. Commit/push — по «задеплой».

---

## Spec coverage (self-review)

| Spec requirement | Task |
|---|---|
| Раздел 159 | Task 1 |
| Элемент `seo-lendinga` | Task 3 |
| HTML `uslugi-seo-prodvizhenie-lending.html` | Task 2 |
| 11 секций + тексты | Task 2 |
| SVG / no form | Task 2 |
| Meta from head | Task 3 |
| DETAIL_TEXT sync | Task 3 |
| Final checklist | Task 4 |
