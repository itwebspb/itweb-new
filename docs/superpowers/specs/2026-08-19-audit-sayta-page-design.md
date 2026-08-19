# Design: страница услуги «Аудит сайта»

Дата: 2026-08-19  
Статус: элемент в подразделе «Дополнительно» раздела SEO-продвижение

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `Аудит сайта.html` без изменения текстов. Элемент — в уже существующем подразделе `dopolnitelno`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон соседних SEO-элементов, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**), подраздел `dopolnitelno` (ID **1225**) уже есть, элементов в нём нет.
- Правила — **аналогичны** предыдущим SEO-элементам.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `dopolnitelno` (1225), parent `seo-prodvizhenie` |
| Элемент | NAME «Аудит сайта под ключ», CODE `audit-sayta` |
| URL | `/services/seo-prodvizhenie/dopolnitelno/audit-sayta/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |

**Meta:**
- Title: `Аудит сайта — технический и SEO-аудит с отчётом | ITWEB`
- Description: `Комплексный аудит сайта: технический, SEO, юзабилити, контент, ссылочный профиль. Подробный отчёт. От 15 000 ₽. Бесплатная консультация по результатам.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-audit-sayta.html`
2. Элемент в iblock 21 / секция 1225, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code audit-sayta`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 200+ аудитов, Отчёт с описанием ошибок, От 3 дней, Консультация бесплатно)
2. Что входит — Технический аудит / SEO-аудит и контент / Юзабилити и коммерческие факторы
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 15 000 / 30 000 (featured) / 60 000 ₽
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×, в т.ч. Screaming Frog)
8. Отзывы (Виталий / Наталья / Тимур)
9. Что вы получите (6 карточек)
10. FAQ (8)
11. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `audit-sayta` ACTIVE в `dopolnitelno`
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/dopolnitelno/audit-sayta/` открывается
