# Design: страница услуги «SEO для образования»

Дата: 2026-08-18  
Статус: по аналогии с соседними SEO-элементами раздела

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO для образования.html` без изменения текстов. Элемент — в уже существующем разделе «SEO продвижение».

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-dlya-b2b.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**) уже есть.
- Правила — **аналогичны** предыдущим SEO-элементам раздела.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `seo-prodvizhenie` (159) |
| Элемент | NAME «SEO для образования под ключ», CODE `seo-dlya-obrazovaniya` |
| URL | `/services/seo-prodvizhenie/seo-dlya-obrazovaniya/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |

**Meta:**
- Title: `SEO для образования под ключ — продвижение школ и курсов | ITWEB`
- Description: `SEO-продвижение сайтов школ, курсов, онлайн-школ, образовательных центров под ключ. Локальное SEO, страницы курсов и преподавателей, работа с отзывами. От 40 000 ₽/мес. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-dlya-obrazovaniya.html`
2. Элемент в iblock 21 / секция 159, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code seo-dlya-obrazovaniya`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 50+ образовательных проектов, Рост записей на курсы, Результат за 3-6 мес, Гарантия 12 месяцев)
2. Что мы делаем — Техническое SEO / Курсы и контент / Локальное SEO и репутация
3. Почему выбирают
4. Кейсы (`#dm-cases`) — онлайн-школа / образовательный центр / курсы английского
5. Тарифы — 40 000 / 70 000 (featured) / 120 000 ₽/мес
6. Доп. услуги (URL из исходника без изменения)
7. Этапы (6 шагов; эмодзи-префиксы не переносить)
8. Инструменты (8×, 8-й Text.ru / Advego)
9. Отзывы (Алина / Евгений / Заур)
10. FAQ (8)
11. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `seo-dlya-obrazovaniya` ACTIVE в 159
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/seo-dlya-obrazovaniya/` открывается
