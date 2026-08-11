# SEO продвижение (раздел) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Посадочная страница раздела `/services/seo-prodvizhenie/` на `.dm-page` из `SEO-продвижение.html`.

**Architecture:** HTML-источник в `design-model/pages/`; контент в `DESCRIPTION` раздела 159 (type html); список дочерних услуг Aspro сверху без изменений шаблона.

**Tech Stack:** Bitrix iblock 21, Aspro Max, `design-model.css`, CALLBACK jqm.

**Spec:** `docs/superpowers/specs/2026-08-11-seo-prodvizhenie-section-page-design.md`

## Global Constraints

- Тексты дословно из `SEO-продвижение.html` / worktree copy.
- SVG вместо эмодзи; без формы/телефонов в CTA.
- Поле: `DESCRIPTION` раздела ID **159**, CODE `seo-prodvizhenie`.
- Файл: `uslugi-seo-prodvizhenie.html`.
- Commit/push только по слову «задеплой» или явной просьбе.

---

### Task 1: HTML-источник `uslugi-seo-prodvizhenie.html`

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie.html`
- Read: `SEO-продвижение.html`, эталон `uslugi-seo-prodvizhenie-korporativnyy-sayt.html`

- [ ] Собрать 11 секций на `dm-*` (порядок исходника).
- [ ] Directions: `dm-solution` + ul + CALLBACK «Заказать услугу».
- [ ] Доп. услуги: `dm-card` + outline links / кнопки как в исходнике.
- [ ] CTA: H2+lead + CALLBACK only.
- [ ] Проверить: нет emoji/form/tel; 8 tools; FAQ count as in source.

### Task 2: CSS при необходимости

- [ ] Если у `dm-solution` с кнопками кнопки не выровнены — добавить `:has(> .dm-btn)` по аналогии с `dm-card`.

### Task 3: Sync DESCRIPTION раздела 159 + meta + cache

- [ ] Залить HTML на staging DOCUMENT_ROOT.
- [ ] Web/Bitrix update: DESCRIPTION + DESCRIPTION_TYPE=html; SECTION_META_TITLE/DESCRIPTION из head исходника.
- [ ] Очистить кэш; проверить URL 200 + `dm-page`.

### Task 4: Verify

- [ ] Список дочерних сверху + лендинг ниже.
- [ ] Spot-check H1, тарифы, FAQ vs исходник.
