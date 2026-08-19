# Design: страница услуги «Сбор семантического ядра»

Дата: 2026-08-19  
Статус: элемент в подразделе «Дополнительно» раздела SEO-продвижение

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `Сбор семантического ядра.html` без изменения текстов. Элемент — в уже существующем подразделе `dopolnitelno`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-audit-sayta.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**), подраздел `dopolnitelno` (ID **1225**) уже есть.
- Правила — **аналогичны** предыдущим SEO-элементам. В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `dopolnitelno` (1225), parent `seo-prodvizhenie` |
| Элемент | NAME «Сбор семантического ядра под ключ», CODE `semanticheskoe-yadro` |
| URL | `/services/seo-prodvizhenie/dopolnitelno/semanticheskoe-yadro/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |

**Meta:**
- Title: `Сбор семантического ядра — подбор запросов для SEO и рекламы | ITWEB`
- Description: `Профессиональный сбор и кластеризация семантического ядра для SEO и контекстной рекламы. От 100 до 1000+ запросов, приоритизация, разметка по страницам. От 10 000 ₽. Бесплатная консультация.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-semanticheskoe-yadro.html`
2. Элемент в iblock 21 / секция 1225, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code semanticheskoe-yadro`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 150+ семантических ядер, Кластеризация и разметка, От 3 дней, Консультация бесплатно)
2. Что входит — Сбор запросов / Кластеризация / Передача и консультация
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 10 000 / 20 000 (featured) / 40 000 ₽
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `semanticheskoe-yadro` ACTIVE в `dopolnitelno`
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/dopolnitelno/semanticheskoe-yadro/` открывается
