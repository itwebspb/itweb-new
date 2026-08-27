# Design: страница услуги «Продвижение нового сайта»

Дата: 2026-08-27  
Статус: элемент в подразделе «Дополнительно» раздела SEO-продвижение

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `Продвижение нового сайта.html` без изменения текстов. Элемент — в уже существующем подразделе `dopolnitelno`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-serm.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**), подраздел `dopolnitelno` (ID **1225**) уже есть.
- В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `dopolnitelno` (1225), parent `seo-prodvizhenie` |
| Элемент | NAME «Продвижение нового сайта под ключ», CODE `novyy-sayt` |
| URL | `/services/seo-prodvizhenie/dopolnitelno/novyy-sayt/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |
| Ссылки | как в исходнике: `/uslugi/prodvizhenie/kontekst/` |

**Meta:**
- Title: `Продвижение нового сайта — SEO для молодого сайта с нуля | ITWEB`
- Description: `Продвижение новых и молодых сайтов под ключ. Стратегия выхода из «песочницы», рост с низкочастотных запросов, постепенное наращивание ссылочного профиля. От 40 000 ₽/мес. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-novyy-sayt.html`
2. Элемент в iblock 21 / секция 1225, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code novyy-sayt`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 80+ молодых сайтов, Стратегия выхода из «песочницы», Первые результаты за 2-3 мес, Гарантия 12 месяцев)
2. Что входит — Фундамент / Стратегия «снизу вверх» / Аккуратное наращивание траста
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 40 000 / 60 000 (featured) / 90 000 ₽/мес
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [x] HTML на `dm-*`, тексты из исходника, SVG
- [x] Элемент `novyy-sayt` ACTIVE в `dopolnitelno`
- [x] DETAIL_TEXT + meta синхронизированы
- [x] URL `/services/seo-prodvizhenie/dopolnitelno/novyy-sayt/` открывается
