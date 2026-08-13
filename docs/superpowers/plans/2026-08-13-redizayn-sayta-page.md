# Редизайн сайта — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Редизайн сайта» (`redizayn-sayta`) в разделе `sozdanie-saytov` (158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML-фрагмент всех секций из `Редизайн сайта.html` на существующих `dm-*` (паттерн промо/портал) → Bitrix DETAIL_TEXT + meta → remote sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-redizayn-sayta-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Редизайн сайта.html`
- SVG only; якоря/`#form` → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `redizayn-sayta`; NAME «Редизайн сайта»; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только **remote**; local не трогать
- Commit только по «задеплой»
- Только существующие `dm-*` из `BLOCKS.md`
- Не копировать сырые классы исходника

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-redizayn-sayta.html` | Create — page markup |
| `scripts/dm-pages.manifest.json` | Add `redizayn-sayta` entry |
| Remote Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML page fragment + manifest

- [ ] **Step 1:** Build `.dm-page` with all 10 sections in source order, verbatim copy
- [ ] **Step 2:** Featured tariff «Комплексный редизайн»; 6 timeline steps; «Что сохраняем» as `.dm-tool`
- [ ] **Step 3:** Cases `#dm-cases`; SVG; CALLBACK
- [ ] **Step 4:** Add manifest entry with meta from spec
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`, no raw source class names

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code redizayn-sayta`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/redizayn-sayta/` → 200 + dm-page + meta + featured «Комплексный редизайн»
- [ ] **Step 3:** Report Element ID + URL; wait for «задеплой»

Do NOT commit.
