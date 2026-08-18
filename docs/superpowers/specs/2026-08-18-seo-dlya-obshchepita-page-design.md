# Design: страница услуги «SEO для общепита»

Дата: 2026-08-18  
Статус: по аналогии с соседними SEO-элементами раздела

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO для общепита.html` без изменения текстов. Элемент — в уже существующем разделе «SEO продвижение».

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-dlya-obrazovaniya.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**) уже есть.
- Правила — **аналогичны** предыдущим SEO-элементам раздела.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `seo-prodvizhenie` (159) |
| Элемент | NAME «SEO для общепита под ключ», CODE `seo-dlya-obshchepita` |
| URL | `/services/seo-prodvizhenie/seo-dlya-obshchepita/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |

**Meta:**
- Title: `SEO для общепита под ключ — продвижение ресторанов и кафе | ITWEB`
- Description: `SEO-продвижение сайтов ресторанов, кафе, баров, служб доставки под ключ. Локальное SEO, Яндекс.Карты, работа с отзывами, меню. От 40 000 ₽/мес. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-dlya-obshchepita.html`
2. Элемент в iblock 21 / секция 159, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code seo-dlya-obshchepita`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 50+ проектов общепита, Рост бронирований и заказов, Результат за 3-6 мес, Гарантия 12 месяцев)
2. Что мы делаем — Техническое SEO / Меню и контент / Локальное SEO и репутация
3. Почему выбирают
4. Кейсы (`#dm-cases`) — ресторан / кафе / служба доставки еды
5. Тарифы — 40 000 / 70 000 (featured) / 120 000 ₽/мес
6. Доп. услуги (URL из исходника без изменения)
7. Этапы (6 шагов; эмодзи-префиксы не переносить)
8. Инструменты (8×, 8-й Text.ru / Advego)
9. Отзывы (Антон / Ксения / Михаил)
10. FAQ (8; формулировки исходника без правки)
11. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `seo-dlya-obshchepita` ACTIVE в 159
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/seo-dlya-obshchepita/` открывается
