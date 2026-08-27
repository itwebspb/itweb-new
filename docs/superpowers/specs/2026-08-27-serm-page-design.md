# Design: страница услуги «SERM»

Дата: 2026-08-27  
Статус: элемент в подразделе «Дополнительно» раздела SEO-продвижение

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SERM.html` без изменения текстов. Элемент — в уже существующем подразделе `dopolnitelno`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-semanticheskoe-yadro.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**), подраздел `dopolnitelno` (ID **1225**) уже есть.
- В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `dopolnitelno` (1225), parent `seo-prodvizhenie` |
| Элемент | NAME «SERM — управление репутацией под ключ», CODE `serm` |
| URL | `/services/seo-prodvizhenie/dopolnitelno/serm/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| Ссылки | как в исходнике: `/uslugi/prodvizhenie/seo/regionalnoe/` |

**Meta:**
- Title: `SERM — управление репутацией в интернете | ITWEB`
- Description: `SERM под ключ: мониторинг упоминаний, работа с отзывами, вытеснение негатива из выдачи Яндекса. Яндекс.Карты, 2ГИС, отзовики. От 30 000 ₽/мес. Бесплатный аудит репутации.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-serm.html`
2. Элемент в iblock 21 / секция 1225, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code serm`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 70+ проектов SERM, Вытеснение негатива, Первые результаты за 1-2 мес, Рост рейтинга и доверия)
2. Что входит — Мониторинг / Работа с отзывами / Вытеснение негатива
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 30 000 / 60 000 (featured) / 100 000 ₽/мес
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [x] HTML на `dm-*`, тексты из исходника, SVG
- [x] Элемент `serm` ACTIVE в `dopolnitelno`
- [x] DETAIL_TEXT + meta синхронизированы
- [x] URL `/services/seo-prodvizhenie/dopolnitelno/serm/` открывается
