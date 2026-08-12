# Сайты на WordPress — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans.

**Goal:** Элемент «Создание сайтов на WordPress под ключ» в `sozdanie-saytov` (158) на `.dm-page`.

**Spec:** `docs/superpowers/specs/2026-08-12-sayty-na-wordpress-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Сайты на WordPress.html`
- SVG only; `#form` → CALLBACK; без формы/телефонов в CTA
- CODE `sayty-na-wordpress`; section 158; iblock 21
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Commit только по «задеплой»
- Все `dm-tool` должны иметь `<p>` (не только h3)

---

### Task 1: HTML

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-na-wordpress.html`  
**Pattern:** `uslugi-sozdanie-saytov-sayty-na-tilda.html`

- [ ] 10 sections per source order
- [ ] Featured tariff = Корпоративный сайт 80 000
- [ ] Durations without ⏱
- [ ] Integrations: WooCommerce … ЮKassa / Тинькофф (8 tools with p)
- [ ] Reviews: Владимир / Диана / Иван
- [ ] FAQ all from source; CTA «Получить расчёт стоимости»
- [ ] Verify no emoji/form/tel; prices 40/80/120

Do NOT commit.

---

### Task 2: Bitrix sync

- [ ] scp; create element; DETAIL_TEXT; meta from spec
- [ ] URL `/services/sozdanie-saytov/sayty-na-wordpress/` → 200

---

### Task 3: Verify checklist + report ID/URL
