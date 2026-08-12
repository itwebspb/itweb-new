# Design: страница услуги «Сайт-каталог»

Дата: 2026-08-12  
Статус: согласовано (CODE `sayt-katalog`)

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Sait-katalog.html`.

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-promo-sayt.html`, `BLOCKS.md`
- Worktree: `.worktrees/seo-korporativnyy-sayt`
- Sync: **только remote** (`itweb-new.acrobat.test-itweb.ru`); local — по отдельной команде

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание сайта-каталога», CODE `sayt-katalog` |
| URL | `/services/sozdanie-saytov/sayt-katalog/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK` |
| Design-model | только существующие `dm-*` |

**Meta:**
- Title: `Создание сайта-каталога под ключ — от 70 000 ₽ | ITWEB`
- Description: `Разработка сайта-каталога товаров под ключ. Удобная структура, фильтры, карточки товаров, интеграция с 1С. 100+ каталогов. Рассчитаем стоимость за 1 день!`

## Маппинг секций → dm-*

| # | Секция | Блок |
|---|---|---|
| 1 | Hero (партнёр / 100+ / от 20 дней / 1С) | `.dm-hero` |
| 2 | Почему… (6) | `.dm-card` |
| 3 | Кейсы (3) | `.dm-case` |
| 4 | Функционал (8) | `.dm-feature` |
| 5 | Тарифы: 70k / **120k `is-featured`** / 180k | `.dm-tariff` |
| 6 | 6 этапов | `.dm-timeline` |
| 7 | Интеграции (8) | `.dm-tool` |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayt-katalog.html`
- Манифест: `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-12-sayt-katalog-page*.md`

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `sayt-katalog` на **remote**
- [ ] DETAIL_TEXT + meta
- [ ] URL `/services/sozdanie-saytov/sayt-katalog/` → 200, featured 120k
