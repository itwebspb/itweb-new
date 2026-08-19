# Аудит сайта — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «Аудит сайта под ключ» в подразделе dopolnitelno (1225) раздела seo-prodvizhenie по `.dm-page` из `Аудит сайта.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов. Подраздел `dopolnitelno` уже существует.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-19-audit-sayta-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Аудит сайта.html`.
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций в порядке исходника; CTA без формы/телефонов.
- IBLOCK 21; секция `dopolnitelno` (1225); CODE `audit-sayta`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/Аудит сайта.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-dlya-obshchepita.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-audit-sayta.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry (`section`: `dopolnitelno`) |

---

### Task 1: HTML + manifest

- [x] Создать HTML на `dm-*` с текстами из исходника
- [x] Тарифы 15 / 30 featured / 60
- [x] Добавить элемент в манифест с `section: dopolnitelno`
- [x] Verify: no emoji/form/tel; 8 tool; 8 faq; keep `1С-Битрикс`

### Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code audit-sayta` → ELEMENT_CREATED `#1864`
- [x] URL `/services/seo-prodvizhenie/dopolnitelno/audit-sayta/` → 200 + dm-page
- [ ] No git commit
