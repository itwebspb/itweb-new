# Веб-портал — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Разработка веб-порталов» (`web-portal`) в разделе `sozdanie-saytov` (158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML-фрагмент всех секций из `Web-portal.html` на существующих `dm-*` (паттерн промо/лендинг) → Bitrix DETAIL_TEXT + meta → remote sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-web-portal-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Web-portal.html`
- SVG only; якоря/`#form` → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `web-portal`; NAME «Разработка веб-порталов»; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только **remote**; local не трогать
- Commit только по «задеплой»
- Только существующие `dm-*` из `BLOCKS.md`
- Не трогать элемент `#1329` / `b2b-portal`
- Не копировать сырые классы исходника

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-web-portal.html` | Create — page markup |
| `scripts/dm-pages.manifest.json` | Add `web-portal` entry |
| Remote Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML page fragment + manifest

**Files:**
- Create: `…/pages/uslugi-sozdanie-saytov-web-portal.html`
- Pattern: `uslugi-sozdanie-saytov-promo-sayt.html`
- Source: `/Users/viktorgromov/Downloads/Web-portal.html`
- Update: `scripts/dm-pages.manifest.json`

- [ ] **Step 1:** Build `.dm-page` with all 10 sections in source order, verbatim copy
- [ ] **Step 2:** Featured tariff «B2B-портал»; 8 timeline steps; tools with `<p>`
- [ ] **Step 3:** Cases `#dm-cases`; SVG; CALLBACK
- [ ] **Step 4:** Add manifest entry for `web-portal` with meta from spec
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`, no raw source class names

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code web-portal`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/web-portal/` → 200 + dm-page + meta + featured «B2B-портал»
- [ ] **Step 3:** Confirm `#1329` unchanged; report Element ID + URL; wait for «задеплой»

Do NOT commit.
