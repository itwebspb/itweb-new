# Сайт-каталог — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент «Создание сайта-каталога» (`sayt-katalog`) в разделе `sozdanie-saytov` (158) на `.dm-page`; sync только на remote.

**Architecture:** HTML из `Sait-katalog.html` на существующих `dm-*` → Bitrix DETAIL_TEXT + meta на staging.

**Tech Stack:** Bitrix Aspro Max, design-model, `scripts/dm-sync-page.sh --env remote`.

**Spec:** `docs/superpowers/specs/2026-08-12-sayt-katalog-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Sait-katalog.html`
- SVG only; CALLBACK; без формы/телефонов в CTA
- CODE `sayt-katalog`; NAME «Создание сайта-каталога»; section **158**
- Sync: **remote only** (local — только по явной команде пользователя)
- Commit только по «задеплой»
- Только существующие `dm-*`

## File map

| File | Role |
|---|---|
| `…/pages/uslugi-sozdanie-saytov-sayt-katalog.html` | Create |
| `scripts/dm-pages.manifest.json` | Add entry |
| Remote Bitrix element | Create/update |

---

### Task 1: HTML + manifest

- [ ] Build `.dm-page` verbatim; featured tariff 120k «Популярный»
- [ ] Add manifest entry for `sayt-katalog`
- [ ] No emoji / form / tel / raw source classes

### Task 2: Remote sync + verify

- [ ] `scripts/dm-sync-page.sh --env remote --code sayt-katalog`
- [ ] Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/sayt-katalog/` → 200 + dm-page + meta
- [ ] Report Element ID + URL; wait for «задеплой»
