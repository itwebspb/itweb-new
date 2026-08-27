# Продвижение нового сайта — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу услуги «Продвижение нового сайта под ключ» в подразделе «Дополнительно» раздела SEO-продвижение (iblock 21, секция `dopolnitelno`) по дизайн-модели `.dm-page`, с текстами из `Продвижение нового сайта.html`.

**Architecture:** HTML-источник в `design-model/pages/` → `DETAIL_TEXT` элемента.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-27-novyy-sayt-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Продвижение нового сайта.html`
- SVG-иконки; без эмодзи и префиксов `⏱`/`✓`
- CTA = Aspro CALLBACK; без формы и телефонов
- «1С-Битрикс» оставлять как в исходнике
- Sync только на remote: `scripts/dm-sync-page.sh --env remote --code novyy-sayt`

## File Map

| Файл | Действие |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-novyy-sayt.html` | создать |
| `scripts/dm-pages.manifest.json` | добавить element `novyy-sayt` / section `dopolnitelno` |
| `docs/superpowers/specs/2026-08-27-novyy-sayt-page-design.md` | создать |
| `docs/superpowers/plans/2026-08-27-novyy-sayt-page.md` | создать |

## Task 1: HTML + manifest

- [x] Создать `uslugi-seo-prodvizhenie-novyy-sayt.html`
- [x] 10 секций; без отзывов
- [x] Добавить запись в manifest

## Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code novyy-sayt`
- [x] Проверить `/services/seo-prodvizhenie/dopolnitelno/novyy-sayt/`
