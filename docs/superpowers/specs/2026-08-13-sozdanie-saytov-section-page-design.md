# Design: страница раздела «Создание сайтов»

Дата: 2026-08-13  
Статус: согласовано в диалоге (раздел `sozdanie-saytov` / 158)

## Цель

Посадочная страница раздела `/services/sozdanie-saytov/` по `.dm-page` из `/Users/viktorgromov/Downloads/index2 (1) (1).html`, по модели разделов SEO / «Продвижение сайта».

## Контекст

- Раздел iblock **21**: ID **158**, CODE `sozdanie-saytov`, NAME «Создание сайтов» (уже существует, не создаём заново)
- URL обслуживает Aspro `news/services/section.php`
- `DESCRIPTION` выводится в `text_after_items` **после** списка дочерних услуг
- Эталоны: `uslugi-seo-prodvizhenie.html`, `uslugi-sozdanie-saytov-sayt-vizitka.html`, `BLOCKS.md`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`

## Решения

| Тема | Решение |
|---|---|
| Контент | `DESCRIPTION` раздела 158, type html |
| Список дочерних | Сверху по шаблону Aspro |
| H1 | «Создание сайтов под ключ» (из исходника) |
| Тексты | дословно из исходника |
| Иконки | SVG; без эмодзи / ⏱ |
| CTA | Aspro CALLBACK; без формы и телефонов |
| Типы сайтов «Подробнее» | `/lending/`, `/sayt-vizitka/`, `/korporativnyy-sayt/`, `/internet-magazin/`, `/web-portal/` |
| Готовые решения | `.dm-solution`, каталог `/catalog/` |
| Сертификаты | `/documents/` |
| Кейсы | `/projects/` |
| CSS | только существующие `dm-*` |
| Sync | только remote |
| Commit | по «задеплой» |

**Meta:**
- Title: `Создание сайтов под ключ — от 20 000 ₽ | Ай Ти Веб`
- Description: `Профессиональное создание сайтов под ключ. Работаем на 1С-Битрикс, WordPress, Tilda и др. Интернет-магазины, корпоративные сайты, лендинги. Рассчитаем стоимость за 1 день!`

## Маппинг секций → dm-*

| # | Секция | Блок |
|---|---|---|
| 1 | Hero | `.dm-hero` |
| 2 | Почему заказывают (7) | `.dm-card` |
| 3 | Результаты клиентов (3) | `.dm-case` + `#dm-cases` |
| 4 | География | `.dm-geo` |
| 5 | Фокус на SEO (5) | `.dm-seo-item` |
| 6 | 15+ отраслей (10) | `.dm-industry` |
| 7 | Тарифы (6), featured «Корпоративный сайт» | `.dm-tariff` |
| 8 | Платформы | `.dm-table` |
| 9 | Готовое решение | intro + `.dm-timeline--sm` + `.dm-card` + `.dm-solution` |
| 10 | Что получите (6) | `.dm-deliverable` |
| 11 | 8 этапов | `.dm-timeline` |
| 12 | Команда (6) | `.dm-team` |
| 13 | Какие сайты (5) | `.dm-offer` |
| 14 | Интеграции (10) | `.dm-tool` |
| 15 | FAQ (10) | `.dm-faq` |
| 16 | Сертификаты + proof | `.dm-cert` + `.dm-proof` |
| 17 | Портфолио (6) | `.dm-case` + `.dm-case-metric` |
| 18 | Отзывы (3) | `.dm-review` |
| 19 | CTA | `.dm-cta` + CALLBACK |

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov.html`
- Manifest: `kind: section`, `code: sozdanie-saytov`, `parent: null`
- Sync: `scripts/dm-sync-page.sh --env remote --code sozdanie-saytov`

## Вне scope

- Правка `section.php`, скрытие списка дочерних
- Новые CSS-блоки
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] `DESCRIPTION` раздела 158 + meta
- [ ] URL 200; список услуг сверху, лендинг ниже; featured «Корпоративный сайт»
