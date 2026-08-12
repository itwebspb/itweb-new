# Сайты на Tilda — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans.

**Goal:** Элемент «Создание сайтов на Tilda под ключ» в `sozdanie-saytov` (158) на `.dm-page`.

**Spec:** `docs/superpowers/specs/2026-08-12-sayty-na-tilda-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Сайты на Tilda.html`
- SVG only; `#form` → CALLBACK; без HTML-формы и телефонов в CTA
- CODE `sayty-na-tilda`; section 158; iblock 21
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Commit только по «задеплой»
- Текст «WhatsApp-интеграция» в блоке интеграций сохранить; не добавлять мессенджер-ссылки в CTA

---

### Task 1: HTML

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-na-tilda.html`  
**Pattern:** `uslugi-sozdanie-saytov-sozdanie-s-seo.html`

- [ ] Hero + 4 benefits
- [ ] Почему Tilda: 6 cards
- [ ] Проекты `#dm-cases`: медцентр / фотограф / мероприятие
- [ ] Возможности: 8 items → `dm-tool` grid
- [ ] Тарифы: Базовый 20k / Стандартный 30k featured / Промо 50k; duration без `⏱`
- [ ] 6 этапов
- [ ] Интеграции: 8 tools (Метрика … Онлайн-оплата)
- [ ] Reviews: Вероника / Ксения / Георгий
- [ ] FAQ (все из исходника); CTA «Получить расчёт стоимости»
- [ ] Verify no emoji/form/tel; prices 20/30/50

Do NOT commit.

---

### Task 2: Bitrix sync

- [ ] scp; create element; DETAIL_TEXT; meta from spec
- [ ] Cache clear; URL `/services/sozdanie-saytov/sayty-na-tilda/` → 200

---

### Task 3: Verify

- [ ] Checklist + spot-check; report ID/URL
