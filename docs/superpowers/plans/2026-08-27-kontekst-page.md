# Контекстная реклама (раздел) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Собрать посадочную страницу раздела «Контекстная реклама» (`kontekst`) на `.dm-page` из `Раздел Контекстная реклама.html`.

**Architecture:** HTML в `design-model/pages/` → sync в `DESCRIPTION` раздела iblock 21.

**Tech Stack:** Bitrix iblock 21, Aspro Max, `design-model.css`, CALLBACK jqm.

**Spec:** `docs/superpowers/specs/2026-08-27-kontekst-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Раздел Контекстная реклама.html`
- SVG-иконки; без эмодзи и префиксов `⏱`/`✓`
- CTA = Aspro CALLBACK (`data-event="jqm" data-param-form_id="CALLBACK"`); без формы и телефонов
- H1 Aspro `#pagetitle` не трогаем — в HTML свой hero H1
- Ссылки направлений и доп. услуг — как в исходнике
- Sync только на remote: `scripts/dm-sync-page.sh --env remote --code kontekst`

## File Map

| Файл | Действие |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst.html` | создать |
| `scripts/dm-pages.manifest.json` | добавить section `kontekst` |
| `docs/superpowers/specs/2026-08-27-kontekst-page-design.md` | создать |
| `docs/superpowers/plans/2026-08-27-kontekst-page.md` | создать |

## Task 1: HTML + manifest

- [x] Создать `uslugi-kontekst.html` по образцу `uslugi-seo-prodvizhenie.html`
- [x] 10 секций; без отзывов
- [x] Добавить запись `kind: section`, `code: kontekst` в manifest

## Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code kontekst`
- [x] Проверить `/services/kontekst/` на staging
