# SEO сайта-каталога — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «SEO сайта-каталога под ключ» в разделе seo-prodvizhenie (159) по `.dm-page` из `SEO сайта-каталога.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-11-seo-sayt-katalog-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/SEO сайта-каталога.html` (опечатки сохранять).
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций; CTA без формы/телефонов.
- IBLOCK 21; секция 159; CODE `seo-sayta-kataloga`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO сайта-каталога.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-lending.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-sayt-katalog.html` | Create |
| Staging iblock 21 | Element + meta |

---

### Task 1: Проверить базу worktree и CSS

- [ ] **Step 1:** Ветка `feature/seo-korporativnyy-sayt`; эталон sibling HTML; CSS `dm-tool` + `.dm-solution ul`
- [ ] **Step 2:** Staging section 159 = `seo-prodvizhenie` ACTIVE
- [ ] **Step 3:** Commit — пропустить

```bash
WT=/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt
cd "$WT" && git status -sb
test -f bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-lending.html && echo HAS_REF
```

---

### Task 2: Собрать HTML-источник

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-sayt-katalog.html`

- [ ] Скопировать sibling; заменить тексты на каталог
- [ ] Hero: H1 `SEO сайта-каталога под ключ`; benefits `100+ каталогов` / `Рост заявок` / `Результат за 3-6 мес` / `Гарантия 12 месяцев`
- [ ] Solutions: Техническое SEO / Оптимизация каталога / Контент и ссылки
- [ ] Cases: стройматериалы +380%/150/ТОП-5/90 000; оборудование +290%/95/ТОП-10/110 000; мебель +340%/120/ТОП-5/80 000
- [ ] Tariffs: 40k / 70k featured / 120k; duration без `⏱`
- [ ] Tools: 8 items, 8th `Text.ru / Advego`
- [ ] Reviews: Антон / Вячеслав / Диана
- [ ] FAQ 8×; CTA CALLBACK «Получить бесплатный аудит и расчёт стоимости»
- [ ] Verify: no emoji/form/tel; 8 tool; 8 faq; prices 40/70/120

Do NOT commit.

---

### Task 3: Bitrix element + DETAIL_TEXT + meta

- [ ] scp HTML to staging
- [ ] Create element via temp web PHP then delete:

```
CODE seo-sayta-kataloga
NAME SEO сайта-каталога под ключ
SECTION 159 IBLOCK 21
META TITLE: SEO сайта-каталога под ключ — вывод в ТОП | ITWEB
META DESC: SEO-продвижение сайтов-каталогов под ключ. Вывод в ТОП Яндекса и Google за 3-6 месяцев. Оптимизация категорий, карточек товаров, фильтров. От 40 000 ₽/мес. Бесплатный аудит.
```

- [ ] Clear cache; curl URL `/services/seo-prodvizhenie/seo-sayta-kataloga/` → 200 + dm-page
- [ ] No git commit

---

### Task 4: Финальная сверка

- [ ] Spec checklist; spot-check H1/tariffs/FAQ/review vs Downloads
- [ ] Report URL + element ID + HTML path
