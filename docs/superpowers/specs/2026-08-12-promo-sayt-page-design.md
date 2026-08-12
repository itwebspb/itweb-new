# Design: страница услуги «Промо-сайт»

Дата: 2026-08-12  
Статус: согласовано (CODE `promo-sayt`)

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Promo-sait.html`, по аналогии с соседними элементами (визитка, B2B и др.).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-b2b-portal.html`, `BLOCKS.md`, `design-model.css`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- Каталог блоков: `bitrix/templates/aspro_max/design-model/BLOCKS.md`

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание промо-сайта», CODE `promo-sayt` |
| URL | `/services/sozdanie-saytov/promo-sayt/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*` из каталога; новых CSS-блоков не требуется |

**Meta:**
- Title: `Создание промо-сайта под ключ — от 60 000 ₽ | ITWEB`
- Description: `Разработка промо-сайта и имиджевого сайта под ключ. Яркий дизайн, анимации, видео-контент. 100+ промо-сайтов. Рассчитаем стоимость за 1 день!`

## Маппинг секций → dm-*

| # | Секция | Блок |
|---|---|---|
| 1 | Hero (Яркий дизайн / Анимации / Запуск от 14 дней / 100+ промо-сайтов) | `.dm-hero` |
| 2 | Почему заказывают промо-сайт у нас (6) | `.dm-card` |
| 3 | Результаты наших клиентов (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 4 | Весь необходимый функционал (8) | `.dm-feature` |
| 5 | Тарифы: 60k / **100k `is-featured` «Популярный»** / 150k | `.dm-tariff` |
| 6 | 6 этапов | `.dm-timeline` |
| 7 | Внешние системы | `.dm-tool` (+ `<p>` у каждого) |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-promo-sayt.html`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-12-promo-sayt-page*.md`
- Commit только по «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `promo-sayt`
- [ ] DETAIL_TEXT + meta
- [ ] URL `/services/sozdanie-saytov/promo-sayt/` открывается, featured тариф 100k
