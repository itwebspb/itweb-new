# SEO сайта для недвижимости — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «SEO сайта для недвижимости под ключ» в разделе seo-prodvizhenie (159) по `.dm-page` из `SEO сайта для недвижимости.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-14-seo-sayta-dlya-nedvizhimosti-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/SEO сайта для недвижимости.html`.
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций; CTA без формы/телефонов.
- IBLOCK 21; секция 159; CODE `seo-sayta-dlya-nedvizhimosti`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO сайта для недвижимости.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-sayt-dlya-stroitelstva.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-sayt-dlya-nedvizhimosti.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry |

---

### Task 1: HTML + manifest

- [ ] Создать HTML на `dm-*` с текстами из исходника
- [ ] Добавить элемент в манифест
- [ ] Verify: no emoji/form/tel; 8 tool; 8 faq; keep `1С-Битрикс` and `РОстова`

### Task 2: Remote sync

- [ ] `scripts/dm-sync-page.sh --env remote --code seo-sayta-dlya-nedvizhimosti`
- [ ] URL `/services/seo-prodvizhenie/seo-sayta-dlya-nedvizhimosti/` → 200 + dm-page
- [ ] No git commit
