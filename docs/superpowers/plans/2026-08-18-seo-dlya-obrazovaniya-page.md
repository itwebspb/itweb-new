# SEO для образования — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «SEO для образования под ключ» в разделе seo-prodvizhenie (159) по `.dm-page` из `SEO для образования.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-18-seo-dlya-obrazovaniya-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/SEO для образования.html`.
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций; CTA без формы/телефонов.
- IBLOCK 21; секция 159; CODE `seo-dlya-obrazovaniya`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO для образования.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-dlya-b2b.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-dlya-obrazovaniya.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry |

---

### Task 1: HTML + manifest

- [x] Создать HTML на `dm-*` с текстами из исходника
- [x] Тарифы 40 / 70 featured / 120
- [x] Добавить элемент в манифест
- [x] Verify: no emoji/form/tel; 8 tool; 8 faq; keep `1С-Битрикс`

### Task 2: Remote sync

- [x] `scripts/dm-sync-page.sh --env remote --code seo-dlya-obrazovaniya` → ELEMENT_CREATED `#1862`
- [x] URL `/services/seo-prodvizhenie/seo-dlya-obrazovaniya/` → 200 + dm-page
- [ ] No git commit
