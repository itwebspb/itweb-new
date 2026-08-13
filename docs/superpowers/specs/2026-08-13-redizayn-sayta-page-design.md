# Design: страница услуги «Редизайн сайта»

Дата: 2026-08-13  
Статус: согласовано в диалоге (CODE `redizayn-sayta`), ожидает ревью файла

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Редизайн сайта.html`, по аналогии с промо / веб-порталом (простой каталог блоков).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-promo-sayt.html`, `uslugi-sozdanie-saytov-web-portal.html`, `BLOCKS.md`, `design-model.css`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- На remote элемента с этим CODE нет

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Редизайн сайта», CODE `redizayn-sayta` |
| URL | `/services/sozdanie-saytov/redizayn-sayta/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*`; новых CSS-блоков не требуется |
| Окружение | sync только на **remote**; local не трогаем |
| Commit | только по явной команде «задеплой» |

**Meta:**
- Title: `Редизайн сайта под ключ — от 50 000 ₽ | ITWEB`
- Description: `Профессиональный редизайн сайта с сохранением SEO-позиций. Обновление дизайна, UX, адаптивности. Аудит, миграция контента, 301 редиректы. 100+ проектов. От 50 000 ₽. Рассчитаем стоимость!`

## Маппинг секций → dm-*

| # | Секция исходника | Блок |
|---|---|---|
| 1 | Hero (100+ редизайнов / Сохранение SEO / Запуск от 14 дней / Гарантия 12 месяцев) | `.dm-hero` |
| 2 | Почему выбирают Ай Ти Веб для редизайна сайта (6) | `.dm-card` |
| 3 | Проекты по редизайну сайта (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 4 | Что включает редизайн сайта (8) | `.dm-feature` |
| 5 | Тарифы: Косметический / **Комплексный `is-featured` «Популярный»** / с миграцией | `.dm-tariff` |
| 6 | 6 этапов редизайна сайта | `.dm-timeline` |
| 7 | Что сохраняем при редизайне сайта (8) | `.dm-tool` |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

Чередование `dm-section` / `dm-section--alt` — как у промо. Якорь `#dm-cases` на блок кейсов.

Правило по эмодзи: иконки и декоративные префиксы не копировать; словесный текст рядом сохранять дословно.

H1 в HTML: «Редизайн сайта» (как NAME элемента; subtitle из исходника).

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-redizayn-sayta.html`
- Manifest: запись `redizayn-sayta` в `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-13-redizayn-sayta-page*.md`
- Sync: `scripts/dm-sync-page.sh --env remote --code redizayn-sayta`

## Вне scope

- Правка текстов исходника
- Новые стили в `design-model.css`
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `redizayn-sayta`
- [ ] DETAIL_TEXT + meta с remote
- [ ] URL `/services/sozdanie-saytov/redizayn-sayta/` открывается; featured тариф «Комплексный редизайн»
