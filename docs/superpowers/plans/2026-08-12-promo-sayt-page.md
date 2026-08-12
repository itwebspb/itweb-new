# Промо-сайт — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Создание промо-сайта» (`promo-sayt`) в разделе `sozdanie-saytov` (158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML-фрагмент всех секций из `Promo-sait.html` на существующих `dm-*` → Bitrix DETAIL_TEXT + meta → staging sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), staging SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-12-promo-sayt-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Promo-sait.html`
- SVG only; якоря/`#form` → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `promo-sayt`; NAME «Создание промо-сайта»; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Commit только по «задеплой»
- Все `dm-tool` с `<p>`
- Meta title/description — дословно из spec
- Только существующие `dm-*` из `BLOCKS.md` (hero, card, case, feature, tariff, timeline, tool, review, faq, cta)
- Не копировать сырые классы исходника (`benefit-card`, `tariff-card`, …)

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-promo-sayt.html` | Create — page markup |
| Staging Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML page fragment

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-promo-sayt.html`
- Pattern: `uslugi-sozdanie-saytov-b2b-portal.html`
- Source: `/Users/viktorgromov/Downloads/Promo-sait.html`
- Catalog: `bitrix/templates/aspro_max/design-model/BLOCKS.md`

- [ ] **Step 1:** Build `.dm-page` with all sections in source order, verbatim copy
- [ ] **Step 2:** Hero: 4 benefits (Яркий дизайн / Анимации и интерактив / Запуск от 14 дней / 100+ промо-сайтов), SVG, CALLBACK «Заказать звонок» + link to cases
- [ ] **Step 3:** Tariffs: 60 / **100 `is-featured` Популярный** / 150; CALLBACK on buttons
- [ ] **Step 4:** Timeline 6 steps; tools with `<p>`; cases `#dm-cases`; FAQ; CTA
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`, no raw source class names

Do NOT commit.

---

### Task 2: Bitrix sync + verify

**Files:**
- Sync HTML to staging
- Staging: `itweb-new@itweb-new.acrobat.test-itweb.ru`, docroot `www/itweb-new.acrobat.test-itweb.ru`

- [ ] **Step 1:** scp HTML to `design-model/pages/`
- [ ] **Step 2:** Create element if missing: NAME `Создание промо-сайта`, CODE `promo-sayt`, IBLOCK 21, SECTION 158, ACTIVE=Y
- [ ] **Step 3:** DETAIL_TEXT = HTML; IPROPERTY meta from spec
- [ ] **Step 4:** Clear Bitrix cache (`bitrix/cache`, managed_cache, css cache)
- [ ] **Step 5:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/promo-sayt/` → 200 + dm-page + meta + featured 100k
- [ ] **Step 6:** Report Element ID + URL; wait for «задеплой»

Do NOT commit.
