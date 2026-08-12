# Создание сайта с SEO — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans.

**Goal:** Элемент «Создание сайта с SEO-продвижением под ключ» в разделе `sozdanie-saytov` (158) на `.dm-page` из `Создание + SEO.html`.

**Architecture:** HTML в `design-model/pages/` → `DETAIL_TEXT` + meta на staging.

**Spec:** `docs/superpowers/specs/2026-08-12-sozdanie-sayta-s-seo-page-design.md`

## Global Constraints

- Тексты дословно из `/Users/viktorgromov/Downloads/Создание + SEO.html` (вкл. «SEO-агенство»).
- SVG only; `#form` → CALLBACK; без формы/телефонов.
- IBLOCK 21; section **158**; CODE `sozdanie-sayta-s-seo`.
- Worktree: `.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit только по «задеплой».

---

### Task 1: HTML-источник

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sozdanie-s-seo.html`  
**Pattern:** `uslugi-sozdanie-saytov-1c-bitriks.html` / SEO siblings

- [ ] Hero: H1 + subtitle; benefits SEO с первого дня / Гарантия позиций / Быстрый результат / Гарантия 12 месяцев; CALLBACK + `#dm-cases`
- [ ] Почему выбирают: 6× `dm-card`
- [ ] Проекты `id="dm-cases"`: стройматериалы / юр.фирма / медклиника — цифры из исходника
- [ ] Что включает: 8 пунктов (ядро, структура, контент, техника, перелинковка, аналитика, ТОП, конверсия) → `dm-tool` или `dm-card` grid
- [ ] Тарифы: Базовый 150k / Комплексный 250k **featured** / Премиум 500k; duration без `⏱` («30 дней + 3 мес SEO» и т.д.); CALLBACK «Заказать»
- [ ] 8 этапов timeline
- [ ] 8 tools (ARSENKIN как в исходнике; GoGetLinks last)
- [ ] Reviews: Владимир / Дарья / Михаил
- [ ] 8 FAQ; CTA lead из form-section; button «Получить расчёт стоимости» CALLBACK
- [ ] Verify: no emoji/form/tel; SEO-агенство present; prices 150/250/500

Do NOT commit.

---

### Task 2: Bitrix element + DETAIL_TEXT + meta

- [ ] scp HTML; create element section 158, CODE `sozdanie-sayta-s-seo`, NAME as H1
- [ ] Meta title/description from spec
- [ ] Cache clear; URL `/services/sozdanie-saytov/sozdanie-sayta-s-seo/` → 200 + dm-page
- [ ] Temp PHP delete; no commit

---

### Task 3: Verify

- [ ] Spec checklist; spot-check H1, tariffs, FAQ, typo SEO-агенство
- [ ] Report ID + URL
