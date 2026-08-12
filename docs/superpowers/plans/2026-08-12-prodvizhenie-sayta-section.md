# Продвижение сайта (раздел) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать корневой раздел «Продвижение сайта» (`prodvizhenie-sayta`), вложить в него «SEO продвижение» (159), и собрать посадочную страницу нового раздела на `.dm-page` из `Продвижение сайта (главный раздел).html`.

**Architecture:** Bitrix section tree update + HTML в `design-model/pages/` → sync в `DESCRIPTION` нового раздела. Список дочерних Aspro сверху без смены шаблона.

**Tech Stack:** Bitrix iblock 21, Aspro Max, `design-model.css`, CALLBACK jqm.

**Spec:** `docs/superpowers/specs/2026-08-12-prodvizhenie-sayta-section-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Продвижение сайта (главный раздел).html`.
- SVG only; CTA без формы/телефонов.
- Без 301 со старых `/services/seo-prodvizhenie/…`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push только по «задеплой».
- Ссылка «Подробнее о SEO» (и аналогичная на SEO в тарифах, если ведёт на SEO-раздел) → `/services/prodvizhenie-sayta/seo-prodvizhenie/`.
- Контекст-ссылки — как в исходнике `/uslugi/prodvizhenie/kontekst/`.
- Кнопки `#form` → CALLBACK jqm.

---

### Task 1: Создать раздел + вложить SEO

**Staging Bitrix iblock 21**

- [ ] **Step 1:** Создать секцию (если нет): NAME «Продвижение сайта», CODE `prodvizhenie-sayta`, ACTIVE=Y, `IBLOCK_SECTION_ID` = false/null (корень).
- [ ] **Step 2:** Update секции 159: `IBLOCK_SECTION_ID` = ID родителя; сохранить CODE `seo-prodvizhenie`.
- [ ] **Step 3:** `CIBlockSection::ReSort(21)` при необходимости; очистить кеш.
- [ ] **Step 4:** Verify SQL/API: 159.DEPTH_LEVEL=2, parent CODE=`prodvizhenie-sayta`.
- [ ] **Step 5:** curl:
  - NEW parent `/services/prodvizhenie-sayta/` → 200 (может быть пусто до Task 3)
  - NEW SEO `/services/prodvizhenie-sayta/seo-prodvizhenie/` → 200
  - OLD `/services/seo-prodvizhenie/` → может быть 404 (ожидаемо)

Использовать temp web PHP с секретным ключом на staging; удалить после.

Do NOT commit.

---

### Task 2: HTML-источник страницы раздела

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-prodvizhenie-sayta.html`  
**Pattern:** `uslugi-seo-prodvizhenie.html`  
**Source:** Downloads файл

- [ ] 11 секций `dm-*`.
- [ ] Hero: H1 «Продвижение сайтов под ключ»; benefits: 100+ проектов / Рост позиций / Быстрый результат / Гарантия 12 месяцев.
- [ ] Направления: 2× `dm-solution` (SEO / Контекст) + ul; SEO btn → `/services/prodvizhenie-sayta/seo-prodvizhenie/`; контекст → `/uslugi/prodvizhenie/kontekst/`.
- [ ] Почему: 6 cards.
- [ ] Кейсы: стройматериалы / юр.фирма контекст / медклиника.
- [ ] Тарифы: SEO 40k; Контекст 25k **featured**; Комплекс 60k. Duration без `⏱`. `#form` → CALLBACK; SEO tariff link → новый SEO path; контекст — как в исходнике.
- [ ] Доп. услуги / этапы / 8 tools (4-й Яндекс.Директ) / отзывы / FAQ / CTA CALLBACK.
- [ ] Verify: no emoji/form/tel; key strings present.

Do NOT commit.

---

### Task 3: Sync DESCRIPTION + meta на staging

- [ ] scp HTML на staging.
- [ ] Update нового раздела: `DESCRIPTION` = HTML, `DESCRIPTION_TYPE=html`.
- [ ] Section meta:
  - TITLE: `Продвижение сайтов — SEO и контекстная реклама | ITWEB`
  - DESCRIPTION: `Комплексное продвижение сайтов: SEO-оптимизация и контекстная реклама. Вывод в ТОП, гарантия результата. От 40 000 ₽/мес.`
- [ ] Cache clear.
- [ ] curl `/services/prodvizhenie-sayta/` → 200, `dm-page`, meta, child SEO listed.

Do NOT commit.

---

### Task 4: Финальная сверка

- [ ] Дерево: parent + SEO nested.
- [ ] Новые URL работают; старые SEO URL — 404 OK.
- [ ] Spot-check H1, тарифы 40/25/60, FAQ vs Downloads.
- [ ] Report IDs + URLs.
