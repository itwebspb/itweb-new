# Design: страница услуги «Интеграция сайта с 1С»

Дата: 2026-08-13  
Статус: согласовано в диалоге (CODE `integraciya-s-1s`)

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Интеграция с 1С.html`, по аналогии с редизайном / промо (простой каталог блоков).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-redizayn-sayta.html`, `uslugi-sozdanie-saytov-promo-sayt.html`, `BLOCKS.md`, `design-model.css`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- На remote элемента с этим CODE нет
- Не трогать: `#1261` `sayty-na-1s-bitriks`

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Интеграция сайта с 1С», CODE `integraciya-s-1s` |
| URL | `/services/sozdanie-saytov/integraciya-s-1s/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*`; новых CSS-блоков не требуется |
| Окружение | sync только на **remote**; local не трогаем |
| Commit | только по явной команде «задеплой» |

**Meta:**
- Title: `Интеграция сайта с 1С под ключ — от 30 000 ₽ | ITWEB`
- Description: `Профессиональная интеграция сайта с 1С:Предприятие, 1С:УТ, 1С:Розница. Синхронизация товаров, цен, остатков, заказов. Настройка обмена данными. От 30 000 ₽. Рассчитаем стоимость!`

## Маппинг секций → dm-*

| # | Секция исходника | Блок |
|---|---|---|
| 1 | Hero (150+ интеграций / Настройка от 5 дней / Двусторонний обмен / Гарантия 12 месяцев) | `.dm-hero` |
| 2 | Почему выбирают Ай Ти Веб для интеграции сайта с 1С (6) | `.dm-card` |
| 3 | Проекты по интеграции сайта с 1С (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 4 | Что включает интеграция сайта с 1С (8) | `.dm-feature` |
| 5 | Тарифы: Базовая / **Стандартная `is-featured` «Популярный»** / Сложная | `.dm-tariff` |
| 6 | 6 этапов интеграции сайта с 1С | `.dm-timeline` |
| 7 | С какими конфигурациями 1С работаем (8) | `.dm-tool` |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

Чередование `dm-section` / `dm-section--alt` — как у редизайна. Якорь `#dm-cases` на блок кейсов.

Правило по эмодзи: иконки и декоративные префиксы не копировать; словесный текст рядом сохранять дословно. Не вырезать «1С».

H1 в HTML: «Интеграция сайта с 1С» (как NAME элемента; subtitle из исходника).

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-integraciya-s-1s.html`
- Manifest: запись `integraciya-s-1s` в `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-13-integraciya-s-1s-page*.md`
- Sync: `scripts/dm-sync-page.sh --env remote --code integraciya-s-1s`

## Вне scope

- Правка текстов исходника
- Новые стили в `design-model.css`
- Sync на local / commit без «задеплой»
- Правка элемента `#1261` `sayty-na-1s-bitriks`

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `integraciya-s-1s`
- [ ] DETAIL_TEXT + meta с remote
- [ ] URL `/services/sozdanie-saytov/integraciya-s-1s/` открывается; featured тариф «Стандартная интеграция»
