# Интеграция сайта с 1С — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Интеграция сайта с 1С» (`integraciya-s-1s`) в разделе `sozdanie-saytov` (158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML-фрагмент всех секций из `Интеграция с 1С.html` на существующих `dm-*` (паттерн редизайн/промо) → Bitrix DETAIL_TEXT + meta → remote sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-integraciya-s-1s-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Интеграция с 1С.html`
- SVG only; якоря/`#form` → Aspro CALLBACK; без своей формы/телефонов в CTA
- CODE `integraciya-s-1s`; NAME «Интеграция сайта с 1С»; section **158**; iblock **21**
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только **remote**; local не трогать
- Commit только по «задеплой»
- Только существующие `dm-*` из `BLOCKS.md`
- Не копировать сырые классы исходника
- Не трогать `#1261` `sayty-na-1s-bitriks`
- Не вырезать «1С»; снять ⏱ и эмодзи-префиксы у этапов

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-integraciya-s-1s.html` | Create — page markup |
| `scripts/dm-pages.manifest.json` | Add `integraciya-s-1s` entry after `redizayn-sayta` |
| Remote Bitrix element | Create/update DETAIL_TEXT + meta |

---

### Task 1: HTML page fragment + manifest

- [ ] **Step 1:** Build `.dm-page` with all 10 sections in source order, verbatim copy
- [ ] **Step 2:** Featured tariff «Стандартная интеграция»; 6 timeline steps; конфигурации as `.dm-tool`
- [ ] **Step 3:** Cases `#dm-cases`; SVG; CALLBACK
- [ ] **Step 4:** Add manifest entry with meta from spec
- [ ] **Step 5:** Verify no emoji, no `<form>`, no `tel:+`, no raw source class names; «1С» preserved

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code integraciya-s-1s`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/integraciya-s-1s/` → 200 + dm-page + meta + featured «Стандартная интеграция»
- [ ] **Step 3:** Report Element ID + URL; wait for «задеплой»

Do NOT commit.
