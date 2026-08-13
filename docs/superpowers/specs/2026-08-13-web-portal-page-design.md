# Design: страница услуги «Разработка веб-порталов»

Дата: 2026-08-13  
Статус: согласовано в диалоге (CODE `web-portal`), ожидает ревью файла

## Цель

Элемент услуги в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Web-portal.html`, по аналогии с промо / лендингом (простой каталог блоков).

## Контекст

- Раздел: iblock **21**, section ID **158**, CODE `sozdanie-saytov`
- Эталоны: `uslugi-sozdanie-saytov-promo-sayt.html`, `uslugi-sozdanie-saytov-lending.html`, `BLOCKS.md`, `design-model.css`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- На remote уже есть `b2b-portal` (#1329) — **не трогаем**; это другая услуга

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Разработка веб-порталов», CODE `web-portal` |
| URL | `/services/sozdanie-saytov/web-portal/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro `CALLBACK`; без своей формы/телефонов |
| Design-model | **Только существующие** `dm-*`; новых CSS-блоков не требуется |
| Окружение | sync только на **remote**; local не трогаем |
| Commit | только по явной команде «задеплой» |

**Meta:**
- Title: `Разработка веб-порталов под ключ — от 300 000 ₽ | ITWEB`
- Description: `Разработка веб-порталов и онлайн-сервисов под ключ. Личные кабинеты, интеграции с 1С и CRM, высокие нагрузки. 50+ порталов. Рассчитаем стоимость под ваши задачи!`

## Маппинг секций → dm-*

| # | Секция исходника | Блок |
|---|---|---|
| 1 | Hero (Золотой партнёр / 50+ порталов / Интеграция с 1С и CRM / Гарантия 24 месяца) | `.dm-hero` |
| 2 | Почему заказывают разработку портала у нас (6) | `.dm-card` |
| 3 | Результаты наших клиентов (3 кейса) | `.dm-case` + `.dm-case-stats` |
| 4 | Весь необходимый функционал веб-портала (8) | `.dm-feature` |
| 5 | Тарифы: Корпоративный / **B2B `is-featured` «Популярный»** / Образовательная платформа | `.dm-tariff` |
| 6 | 8 этапов разработки веб-портала | `.dm-timeline` |
| 7 | Подключаем внешние системы… (8) | `.dm-tool` |
| 8 | Отзывы (3) | `.dm-review` |
| 9 | FAQ (10) | `.dm-faq` |
| 10 | CTA | `.dm-cta` + CALLBACK |

Чередование `dm-section` / `dm-section--alt` — как у промо. Якорь `#dm-cases` на блок кейсов.

Правило по эмодзи: иконки и декоративные префиксы не копировать; словесный текст рядом сохранять дословно.

H1 в HTML: «Разработка веб-порталов» (как NAME элемента; subtitle из исходника).

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-web-portal.html`
- Manifest: запись `web-portal` в `scripts/dm-pages.manifest.json`
- Spec/plan: `docs/superpowers/specs|plans/2026-08-13-web-portal-page*.md`
- Sync: `scripts/dm-sync-page.sh --env remote --code web-portal`

## Вне scope

- Правка текстов исходника
- Обновление `#1329` / `b2b-portal`
- Новые стили в `design-model.css`
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG
- [ ] Элемент ACTIVE в 158, CODE `web-portal`
- [ ] DETAIL_TEXT + meta с remote
- [ ] URL `/services/sozdanie-saytov/web-portal/` открывается; featured тариф «B2B-портал»
- [ ] Существующий `#1329` не изменён
