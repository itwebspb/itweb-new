# SEO для B2B — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «SEO для B2B под ключ» в разделе seo-prodvizhenie (159) по `.dm-page` из `SEO для B2B.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-17-seo-dlya-b2b-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/SEO для B2B.html`, кроме «доподнительная» → «дополнительная».
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций; CTA без формы/телефонов.
- IBLOCK 21; секция 159; CODE `seo-dlya-b2b`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO для B2B.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-sayt-dlya-nedvizhimosti.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-dlya-b2b.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry |

---

### Task 1: HTML + manifest

- [ ] Создать HTML на `dm-*` с текстами из исходника
- [ ] «дополнительная услуга» (не «доподнительная»)
- [ ] Тарифы 40 / 80 featured / 140
- [ ] Добавить элемент в манифест
- [ ] Verify: no emoji/form/tel; 8 tool; 8 faq; keep `1С-Битрикс`

### Task 2: Remote sync

- [ ] `scripts/dm-sync-page.sh --env remote --code seo-dlya-b2b`
- [ ] URL `/services/seo-prodvizhenie/seo-dlya-b2b/` → 200 + dm-page
- [ ] No git commit
