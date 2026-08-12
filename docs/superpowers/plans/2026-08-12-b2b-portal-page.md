# B2B-портал — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Создание B2B-портала под ключ» в `sozdanie-saytov` (158) на `.dm-page`.

**Architecture:** HTML-фрагмент `dm-*` в design-model → Bitrix iblock 21 DETAIL_TEXT + IPROPERTY meta → URL под секцией 158. Стили уже в `design-model.css`.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), staging SSH sync.

**Spec:** `docs/superpowers/specs/2026-08-12-b2b-portal-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/B2B-портал.html`
- SVG only; `#form` / якоря формы → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `b2b-portal`; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Commit только по «задеплой»
- Все `dm-tool` должны иметь `<p>` (не только h3)
- Meta title/description — дословно из spec

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-b2b-portal.html` | Create — page markup |
| `uslugi-sozdanie-saytov-sayty-dlya-stroitelstva.html` | Pattern only (read) |
| Staging Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-b2b-portal.html`
- Pattern: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-dlya-stroitelstva.html`
- Source: `/Users/viktorgromov/Downloads/B2B-портал.html`

**Interfaces:**
- Consumes: existing `dm-*` CSS classes from design-model
- Produces: self-contained `.dm-page` HTML fragment for DETAIL_TEXT

- [ ] **Step 1:** Copy structure from construction pattern; replace content from B2B source in order
- [ ] **Step 2:** Hero benefits: 50+ B2B-порталов / Интеграция с 1С и ЭДО / Запуск от 35 дней / Гарантия 12 месяцев (SVG icons)
- [ ] **Step 3:** Sections 2–10 per source; cases `#dm-cases`; integrations as `dm-tool` with `<p>` each
- [ ] **Step 4:** Tariffs: 150 000 / **250 000 `is-featured`** (Продвинутый B2B-портал, badge «Популярный») / 400 000; durations without emoji
- [ ] **Step 5:** CTA button text «Получить расчёт стоимости» + CALLBACK; guarantees from source
- [ ] **Step 6:** Verify no emoji, no `<form>`, no `tel:+` in page fragment; all tools have `<p>`

Do NOT commit.

Report: `.superpowers/sdd/2026-08-12-b2b-portal/task-1-report.md`

---

### Task 2: Bitrix sync

**Files:**
- Sync: HTML from Task 1 → staging docroot + element DETAIL_TEXT
- Staging: `itweb-new@itweb-new.acrobat.test-itweb.ru`, docroot `www/itweb-new.acrobat.test-itweb.ru`

**Interfaces:**
- Consumes: HTML fragment from Task 1
- Produces: ACTIVE element CODE `b2b-portal` in section 158

- [ ] **Step 1:** scp HTML to design-model/pages on staging
- [ ] **Step 2:** Create/update element: NAME `Создание B2B-портала под ключ`, CODE `b2b-portal`, IBLOCK 21, SECTION 158, ACTIVE=Y
- [ ] **Step 3:** Set DETAIL_TEXT = HTML; IPROPERTY ELEMENT_META_TITLE / DESCRIPTION from spec
- [ ] **Step 4:** Clear cache; delete temp scripts
- [ ] **Step 5:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/b2b-portal/` → 200 + dm-page + meta

Do NOT commit.

Report: `.superpowers/sdd/2026-08-12-b2b-portal/task-2-report.md`

---

### Task 3: Verify checklist

- [ ] HTML on `dm-*`, texts/order from source, SVG
- [ ] Element `b2b-portal` ACTIVE in 158
- [ ] DETAIL_TEXT + meta synced
- [ ] Live URL opens with featured 250k and CTA CALLBACK
- [ ] Report Element ID + URL; wait for user «задеплой» for git commit/push
