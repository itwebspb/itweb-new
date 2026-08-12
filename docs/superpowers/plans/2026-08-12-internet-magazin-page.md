# Интернет-магазин — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Создание интернет-магазина» (`internet-magazin`) в разделе `sozdanie-saytov` (158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML-фрагмент всех секций из `Internet-Mag (2).html` на существующих `dm-*` (паттерн визитки/корпоративного) → Bitrix DETAIL_TEXT + meta → remote sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-12-internet-magazin-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Internet-Mag (2).html`
- SVG only; якоря/`#form` → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `internet-magazin`; NAME «Создание интернет-магазина»; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только **remote**; local не трогать
- Commit только по «задеплой»
- Только существующие `dm-*` из `BLOCKS.md`
- Не трогать элемент `#1259` / `sozdanie-internet-magazina-na-bitriks`
- Не копировать сырые классы исходника

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-internet-magazin.html` | Create — page markup |
| `scripts/dm-pages.manifest.json` | Add `internet-magazin` entry |
| Remote Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML page fragment + manifest

**Files:**
- Create: `…/pages/uslugi-sozdanie-saytov-internet-magazin.html`
- Pattern: `uslugi-sozdanie-saytov-korporativnyy-sayt.html` / `sayt-vizitka.html`
- Source: `/Users/viktorgromov/Downloads/Internet-Mag (2).html`
- Update: `scripts/dm-pages.manifest.json`

- [ ] **Step 1:** Build `.dm-page` with all 19 sections in source order, verbatim copy
- [ ] **Step 2:** Market stats → `.dm-stat`; types → `.dm-card`; platforms → `.dm-table`; featured tariff «Бизнес»
- [ ] **Step 3:** Portfolio → `.dm-case` (+ metric); SVG; CALLBACK; `#dm-cases`
- [ ] **Step 4:** Add manifest entry for `internet-magazin` with meta from spec
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`, no raw source class names

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code internet-magazin`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/internet-magazin/` → 200 + dm-page + meta + featured «Бизнес»
- [ ] **Step 3:** Confirm `#1259` unchanged; report Element ID + URL; wait for «задеплой»

Do NOT commit.
