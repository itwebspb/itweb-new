# Сайт-визитка — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Создание сайта-визитки» в `sozdanie-saytov` (158) на `.dm-page`, с расширением design-model для блоков, которых ещё нет.

**Architecture:** Сначала недостающие `dm-*` в `design-model.css` → HTML-фрагмент всех секций Visitka → Bitrix DETAIL_TEXT + IPROPERTY meta.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page` + CSS), staging SSH sync.

**Spec:** `docs/superpowers/specs/2026-08-12-sayt-vizitka-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Visitka.html`
- SVG only; `#form` / якоря → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `sayt-vizitka`; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Commit только по «задеплой»
- Все `dm-tool` должны иметь `<p>`
- Meta title/description — дословно из spec
- **Нельзя** копировать сырые классы исходника (`geo-stat`, `industry-card`, …) в DETAIL_TEXT
- Если блок явно не похож на существующие `dm-*` — **добавить новый блок в** `bitrix/templates/aspro_max/css/design-model.css` в том же визуальном языке (токены `--dm-*`, радиусы, тени, иконки `.dm-ico`)
- Уже есть и можно переиспользовать: `dm-hero`, `dm-card`, `dm-feature`, `dm-case`, `dm-tariff`, `dm-timeline`/`dm-step`, `dm-tool`, `dm-faq`, `dm-review`, `dm-solution`, `dm-cta`, grids
- Вероятно нужны новые (имена зафиксировать при добавлении): geo/stats split, SEO items, industry/profession chips, platforms table, deliverables/results list, team, certificates — если нельзя честно собрать из существующих без потери layout

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/css/design-model.css` | Extend — new `dm-*` blocks |
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayt-vizitka.html` | Create — page markup |
| Staging Bitrix element | Create/update DETAIL_TEXT + meta; ensure CSS deployed |

---

### Task 1: Extend design-model CSS for Visitka-only layouts

**Files:**
- Modify: `bitrix/templates/aspro_max/css/design-model.css`
- Reference source layouts in `/Users/viktorgromov/Downloads/Visitka.html` (geo, seo-grid, industries, platforms-table, solutions, results, team, certificates)
- Pattern language: existing rules in the same CSS file (`dm-card`, `dm-solution`, `dm-case-stat`, …)

**Interfaces:**
- Produces: new reusable classes documented in a short comment block at the top of each new section in CSS, e.g. `dm-geo`, `dm-stats`, `dm-seo-item`, `dm-industry`, `dm-table`, `dm-deliverable`, `dm-team`, `dm-cert` (exact names chosen to match existing `dm-*` naming; avoid colliding with source class names)

- [ ] **Step 1:** Inventory Visitka section layouts vs existing `dm-*`; list which reuse vs need new CSS
- [ ] **Step 2:** Add only the missing blocks to `design-model.css` (desktop + mobile breakpoints consistent with file)
- [ ] **Step 3:** Keep visual language: `--dm-*` tokens, 55px icons via `.dm-ico` / hero `.ico`, no emoji in CSS content except existing tariff checkmarks
- [ ] **Step 4:** scp updated CSS to staging docroot `www/itweb-new.acrobat.test-itweb.ru/bitrix/templates/aspro_max/css/design-model.css` (or clear CDN/cache later in Task 3)

Do NOT commit.

Report: `.superpowers/sdd/2026-08-12-sayt-vizitka/task-1-report.md` — list new class names added

---

### Task 2: HTML page fragment

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayt-vizitka.html`
- Pattern: `uslugi-sozdanie-saytov-b2b-portal.html` + new classes from Task 1
- Source: `/Users/viktorgromov/Downloads/Visitka.html`

**Interfaces:**
- Consumes: existing + Task 1 `dm-*` classes
- Produces: self-contained `.dm-page` HTML for DETAIL_TEXT

- [ ] **Step 1:** Build all ~20 sections in source order with verbatim copy
- [ ] **Step 2:** Hero: 5 benefits (7 дней / От 25 000 ₽ / Адаптивный / Для старта / Гарантия 12 мес), SVG
- [ ] **Step 3:** Tariffs: 25 / **40 `is-featured` Стандарт** / 60; no emoji in durations
- [ ] **Step 4:** Integrations as `dm-tool` with `<p>` each; cases `#dm-cases`; CTA «Получить расчёт стоимости» + CALLBACK
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`; no raw source class names; all tools have `<p>`

Do NOT commit.

Report: `.superpowers/sdd/2026-08-12-sayt-vizitka/task-2-report.md`

---

### Task 3: Bitrix sync + verify

**Files:**
- Sync HTML + CSS to staging
- Staging: `itweb-new@itweb-new.acrobat.test-itweb.ru`, docroot `www/itweb-new.acrobat.test-itweb.ru`

**Interfaces:**
- Produces: ACTIVE element CODE `sayt-vizitka` in section 158

- [ ] **Step 1:** scp HTML + CSS
- [ ] **Step 2:** Create/update element: NAME `Создание сайта-визитки`, CODE `sayt-vizitka`, IBLOCK 21, SECTION 158, ACTIVE=Y
- [ ] **Step 3:** DETAIL_TEXT = HTML; IPROPERTY meta from spec
- [ ] **Step 4:** Clear cache; delete temp scripts
- [ ] **Step 5:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/sayt-vizitka/` → 200 + dm-page + meta + featured 40k + new blocks render (not unstyled)
- [ ] **Step 6:** Report Element ID + URL; wait for «задеплой»

Do NOT commit.

Report: `.superpowers/sdd/2026-08-12-sayt-vizitka/task-3-report.md`
