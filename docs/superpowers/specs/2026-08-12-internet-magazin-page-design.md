# Design: страница услуги «Создание интернет-магазина»

Дата: 2026-08-12  
Статус: согласовано в диалоге (CODE `internet-magazin`), ожидает ревью файла

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Internet-Mag (2).html`, по аналогии с визиткой / корпоративным сайтом (расширенный каталог блоков).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-sayt-vizitka.html`, `uslugi-sozdanie-saytov-korporativnyy-sayt.html`, `BLOCKS.md`, `design-model.css`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- На remote уже есть `sozdanie-internet-magazina-na-bitriks` (#1259) — **не трогаем**; создаём новый элемент с коротким CODE

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание интернет-магазина», CODE `internet-magazin` |
| URL | `/services/sozdanie-saytov/internet-magazin/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи (в т.ч. в portfolio-image и префиксах строк) |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*`; новых CSS-блоков не требуется |
| Окружение | sync только на **remote**; local не трогаем |
| Commit | только по явной команде «задеплой» |

**Meta:**
- Title: `Создание интернет-магазина под ключ — от 120 000 ₽ | ITWEB`
- Description: `Разработка интернет-магазина под ключ. Интеграция с 1С, CRM, платёжными системами. 150+ магазинов на 1С-Битрикс. Рассчитаем стоимость за 1 день!`

## Маппинг секций → dm-*

| # | Секция исходника | Блок |
|---|---|---|
| 1 | Hero (Золотой партнёр / 150+ магазинов / от 30 дней / Интеграция с 1С) | `.dm-hero` |
| 2 | Почему заказывают создание интернет-магазина у нас (7) | `.dm-card` |
| 3 | Рынок e-commerce в цифрах (4) | `.dm-stat` в сетке (без карты) |
| 4 | Результаты наших клиентов (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 5 | Весь необходимый функционал (8) | `.dm-feature` |
| 6 | Создаём интернет-магазины для 30+ отраслей (10) | `.dm-industry` |
| 7 | Тарифы: Старт / Малый бизнес / **Бизнес `is-featured` «Популярный»** / Бизнес+ | `.dm-tariff` |
| 8 | На каких платформах… | `.dm-table` (+ `.dm-table-badge` у Битрикс) |
| 9 | Что такое готовое решение… | lead + `.dm-solution` |
| 10 | Что вы получите после запуска | `.dm-deliverable` |
| 11 | 8 этапов создания… | `.dm-timeline` |
| 12 | Команда… | `.dm-team` |
| 13 | Какие интернет-магазины мы разрабатываем (5 типов) | `.dm-offer` (красный head как у портфолио + 3 параметра) |
| 14 | Подключаем внешние системы… | `.dm-tool` |
| 15 | FAQ | `.dm-faq` |
| 16 | Сертификаты | `.dm-cert` |
| 17 | Посмотрите, какие интернет-магазины мы создали | `.dm-case` (+ `.dm-case-metric` где есть цифра) |
| 18 | Отзывы | `.dm-review` |
| 19 | CTA | `.dm-cta` + CALLBACK |

Чередование `dm-section` / `dm-section--alt` — как у визитки. Якорь `#dm-cases` на блок кейсов.

Правило по эмодзи: иконки и декоративные префиксы не копировать; словесный текст рядом сохранять дословно. В таблице платформ текст бейджа «Золотой партнёр» сохранить без ⭐.

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-internet-magazin.html`
- Manifest: запись `internet-magazin` в `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-12-internet-magazin-page*.md`
- Sync: `scripts/dm-sync-page.sh --env remote --code internet-magazin`

## Вне scope

- Правка текстов исходника
- Обновление `#1259` / `sozdanie-internet-magazina-na-bitriks`
- Новые стили в `design-model.css`
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `internet-magazin`
- [ ] DETAIL_TEXT + meta с remote
- [ ] URL `/services/sozdanie-saytov/internet-magazin/` открывается; featured тариф «Бизнес»
- [ ] Существующий `#1259` не изменён
