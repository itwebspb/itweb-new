# Настройка Яндекс.Директ — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу услуги «Настройка Яндекс.Директ под ключ» в разделе «Контекстная реклама» (iblock 21, секция `kontekst`) по дизайн-модели `.dm-page`, с текстами из `Яндекс Директ.html`.

**Architecture:** HTML-источник в `design-model/pages/` → `DETAIL_TEXT` элемента.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-27-yandeks-direkt-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Яндекс Директ.html`
- SVG-иконки; без эмодзи и префиксов `⏱`/`✓`
- CTA = Aspro CALLBACK; без формы и телефонов
- Ссылки доп. услуг — как в исходнике
- Sync только на remote: `scripts/dm-sync-page.sh --env remote --code yandeks-direkt`

## File Map

| Файл | Действие |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst-yandeks-direkt.html` | создать |
| `scripts/dm-pages.manifest.json` | добавить element `yandeks-direkt` / section `kontekst` |
| `docs/superpowers/specs/2026-08-27-yandeks-direkt-page-design.md` | создать |
| `docs/superpowers/plans/2026-08-27-yandeks-direkt-page.md` | создать |

## Task 1: HTML + manifest

- [x] Создать `uslugi-kontekst-yandeks-direkt.html`
- [x] 10 секций; без отзывов
- [x] Добавить запись в manifest

## Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code yandeks-direkt`
- [x] Проверить `/services/kontekst/yandeks-direkt/`
