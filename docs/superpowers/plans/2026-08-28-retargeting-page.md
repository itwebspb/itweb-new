# Ретаргетинг — Implementation Plan

**Goal:** Создать посадочную страницу «Настройка ретаргетинга под ключ» в разделе «Контекстная реклама» (iblock 21, секция `kontekst`) по дизайн-модели `.dm-page`, с текстами из `Ретаргетинг.html`.

**Architecture:** HTML-источник в `design-model/pages/` → `DETAIL_TEXT` элемента.

**Tech Stack:** 1C-Bitrix (iblock 21), Aspro Max, `design-model.css` / `dm-*`, Aspro jqm CALLBACK.

**Spec:** `docs/superpowers/specs/2026-08-28-retargeting-page-design.md`

## Global Constraints

- Тексты **дословно** из `/Users/viktorgromov/Downloads/Ретаргетинг.html`
- SVG-иконки; без эмодзи и префиксов `⏱`/`✓`
- CTA = Aspro CALLBACK; без формы и телефонов
- Sync только на remote: `scripts/dm-sync-page.sh --env remote --code retargeting`

## File Map

| Файл | Действие |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst-retargeting.html` | создать |
| `scripts/dm-pages.manifest.json` | добавить element `retargeting` / section `kontekst` |
| `docs/superpowers/specs/2026-08-28-retargeting-page-design.md` | создать |
| `docs/superpowers/plans/2026-08-28-retargeting-page.md` | создать |

## Task 1: HTML + manifest

- [x] Создать `uslugi-kontekst-retargeting.html`
- [x] 10 секций
- [x] Добавить запись в manifest

## Task 2: Remote sync

- [ ] `scripts/dm-sync-page.sh --env remote --code retargeting`
- [ ] Проверить `/services/kontekst/retargeting/`
