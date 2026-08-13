# Создание сайтов (раздел) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Посадочная страница раздела «Создание сайтов» (`sozdanie-saytov`, 158) на `.dm-page` без новых CSS-блоков.

**Architecture:** HTML из `index2 (1) (1).html` на существующих `dm-*` (паттерн визитка + SEO-раздел) → Bitrix `DESCRIPTION` + meta → remote sync.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-sozdanie-saytov-section-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/index2 (1) (1).html`
- SVG only; `#final-cta`/`#form` → CALLBACK; без формы/телефонов
- Раздел 158 уже есть; NAME не менять
- Список дочерних сверху оставляем
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только remote; commit по «задеплой»
- Не вырезать «1С»

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov.html` | Create |
| `scripts/dm-pages.manifest.json` | Add section `sozdanie-saytov` |
| Remote section 158 | DESCRIPTION + meta |

---

### Task 1: HTML + manifest

- [ ] **Step 1:** Build `.dm-page` with all 19 sections
- [ ] **Step 2:** Featured tariff «Корпоративный сайт»; geo map; offers → child URLs
- [ ] **Step 3:** Manifest `kind: section`, parent null
- [ ] **Step 4:** Verify no emoji/form/tel:+

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code sozdanie-saytov`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/sozdanie-saytov/` → 200 + child list + dm-page + featured «Корпоративный сайт»
- [ ] **Step 3:** Report; wait for «задеплой»

Do NOT commit.
