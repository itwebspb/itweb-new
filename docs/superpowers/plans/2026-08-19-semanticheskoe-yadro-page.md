# Сбор семантического ядра — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «Сбор семантического ядра под ключ» в подразделе dopolnitelno (1225) раздела seo-prodvizhenie по `.dm-page` из `Сбор семантического ядра.html`.

**Architecture:** HTML в `design-model/pages/` по образцу `uslugi-seo-prodvizhenie-audit-sayta.html`; DETAIL_TEXT sync на staging. CSS готов. Подраздел `dopolnitelno` уже существует.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-19-semanticheskoe-yadro-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Сбор семантического ядра.html`.
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- Порядок секций как в исходнике (без отзывов — их нет в файле); CTA без формы/телефонов.
- IBLOCK 21; секция `dopolnitelno` (1225); CODE `semanticheskoe-yadro`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/Сбор семантического ядра.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-audit-sayta.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-semanticheskoe-yadro.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry (`section`: `dopolnitelno`) |

---

### Task 1: HTML + manifest

- [x] Создать HTML на `dm-*` с текстами из исходника
- [x] Тарифы 10 / 20 featured / 40
- [x] Добавить элемент в манифест с `section: dopolnitelno`
- [x] Verify: no emoji/form/tel; 8 tool; 8 faq; keep `1С-Битрикс`; no invented reviews

### Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code semanticheskoe-yadro` → ELEMENT_CREATED `#1875`
- [x] URL `/services/seo-prodvizhenie/dopolnitelno/semanticheskoe-yadro/` → 200 + dm-page
- [ ] No git commit
