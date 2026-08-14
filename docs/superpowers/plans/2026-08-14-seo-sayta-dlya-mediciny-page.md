# SEO сайта для медицины — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать посадочную страницу «SEO сайта для медицины под ключ» в разделе seo-prodvizhenie (159) по `.dm-page` из `SEO сайта для медицины.html`.

**Architecture:** HTML в `design-model/pages/` по образцу sibling SEO-страниц; DETAIL_TEXT sync на staging. CSS готов.

**Tech Stack:** Bitrix iblock 21, Aspro Max, dm-*, CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-14-seo-sayta-dlya-mediciny-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/SEO сайта для медицины.html` (опечатки сохранять).
- Только SVG; без эмодзи/`⏱`/`✓` в маркерах.
- 11 секций; CTA без формы/телефонов.
- IBLOCK 21; секция 159; CODE `seo-sayta-dlya-mediciny`.
- Worktree: `/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Commit/push/deploy — только по «задеплой».
- Не трогать localhost; только `--env remote`.

## File map

| File | Role |
|---|---|
| `/Users/viktorgromov/Downloads/SEO сайта для медицины.html` | Copy source |
| `…/uslugi-seo-prodvizhenie-sayt-katalog.html` | Pattern sibling |
| `…/uslugi-seo-prodvizhenie-sayt-dlya-mediciny.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element entry |
| Staging iblock 21 | Element + meta |

---

### Task 1: Проверить базу worktree и CSS

- [ ] **Step 1:** Ветка `feature/seo-korporativnyy-sayt`; эталон sibling HTML; CSS `dm-tool` + `.dm-solution ul`
- [ ] **Step 2:** Staging section 159 = `seo-prodvizhenie` ACTIVE
- [ ] **Step 3:** Commit — пропустить

```bash
WT=/Users/viktorgromov/itweb-new/itweb-new/.worktrees/seo-korporativnyy-sayt
cd "$WT" && git status -sb
test -f bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-sayt-katalog.html && echo HAS_REF
```

---

### Task 2: Собрать HTML-источник

**Create:** `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-sayt-dlya-mediciny.html`

- [ ] Скопировать sibling; заменить тексты на медицину
- [ ] Hero: H1 `SEO сайта для медицины под ключ`; benefits `50+ медицинских сайтов` / `Рост записей` / `Результат за 3-6 мес` / `Гарантия 12 месяцев`
- [ ] Solutions: Техническое SEO / Контент и структура / Локальное SEO и репутация
- [ ] Cases: стоматология +320%/150/ТОП-5/70 000; косметология +280%/120/ТОП-5/80 000; медцентр +250%/95/ТОП-10/90 000
- [ ] Tariffs: 40k / 70k featured / 120k; duration без `⏱`
- [ ] Extra URLs как в исходнике
- [ ] Tools: 8 items, 8th `Text.ru / Advego`
- [ ] Reviews: Андрей / Корина / Олег
- [ ] FAQ 8×; CTA CALLBACK «Получить бесплатный аудит и расчёт стоимости»
- [ ] Verify: no emoji/form/tel; 8 tool; 8 faq; prices 40/70/120; keep `1С-Битрикс`

Do NOT commit.

---

### Task 3: Manifest + remote sync

- [ ] Добавить элемент в `scripts/dm-pages.manifest.json`
- [ ] `scripts/dm-sync-page.sh --env remote --code seo-sayta-dlya-mediciny`
- [ ] URL `/services/seo-prodvizhenie/seo-sayta-dlya-mediciny/` → 200 + dm-page
- [ ] No git commit

---

### Task 4: Финальная сверка

- [ ] Spec checklist; spot-check H1/tariffs/FAQ/review vs Downloads
- [ ] Report URL + element ID + HTML path
