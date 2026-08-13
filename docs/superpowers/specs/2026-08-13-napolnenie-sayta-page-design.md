# Design: страница услуги «Наполнение сайта товарами и контентом»

Дата: 2026-08-13  
Статус: согласовано в диалоге (CODE `napolnenie-sayta`)

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Наполнение сайта.html`, по аналогии с редизайном / интеграцией (простой каталог блоков).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-integraciya-s-1s.html`, `uslugi-sozdanie-saytov-redizayn-sayta.html`, `BLOCKS.md`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- На remote элемента с этим CODE нет

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Наполнение сайта товарами и контентом», CODE `napolnenie-sayta` |
| URL | `/services/sozdanie-saytov/napolnenie-sayta/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*`; новых CSS-блоков не требуется |
| Окружение | sync только на **remote**; local не трогаем |
| Commit | только по явной команде «задеплой» |

**Meta:**
- Title: `Наполнение сайта товарами и контентом — от 15 000 ₽ | ITWEB`
- Description: `Профессиональное наполнение сайта товарами, описаниями, фото. Парсинг товаров, перенос контента со старых сайтов, SEO-оптимизация карточек. От 15 000 ₽. Рассчитаем стоимость!`

## Маппинг секций → dm-*

| # | Секция исходника | Блок |
|---|---|---|
| 1 | Hero (200+ проектов / Быстрое наполнение / Базовая SEO-оптимизация / Гарантия качества) | `.dm-hero` |
| 2 | Почему выбирают Ай Ти Веб для наполнения сайта (6) | `.dm-card` |
| 3 | Проекты по наполнению сайтов (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 4 | Что включает наполнение сайта (8) | `.dm-feature` |
| 5 | Тарифы: Базовый / **Стандартный пакет `is-featured` «Популярный»** / Премиум | `.dm-tariff` |
| 6 | 6 этапов наполнения сайта | `.dm-timeline` |
| 7 | Источники контента и инструменты (8) | `.dm-tool` |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

Чередование `dm-section` / `dm-section--alt` — как у редизайна. Якорь `#dm-cases` на блок кейсов.

Правило по эмодзи: иконки и декоративные префиксы не копировать; словесный текст рядом сохранять дословно. Не вырезать «1С».

H1 в HTML: «Наполнение сайта товарами и контентом» (как NAME элемента; subtitle из исходника).

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-napolnenie-sayta.html`
- Manifest: запись `napolnenie-sayta` в `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-13-napolnenie-sayta-page*.md`
- Sync: `scripts/dm-sync-page.sh --env remote --code napolnenie-sayta`

## Вне scope

- Правка текстов исходника
- Новые стили в `design-model.css`
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `napolnenie-sayta`
- [ ] DETAIL_TEXT + meta с remote
- [ ] URL `/services/sozdanie-saytov/napolnenie-sayta/` открывается; featured тариф «Стандартный пакет»
