# Design: страница услуги «SEO сайта для строительства»

Дата: 2026-08-14  
Статус: по аналогии с соседними SEO-элементами раздела

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO сайта для строительства.html` без изменения текстов. Элемент — в уже существующем разделе «SEO продвижение».

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-sayt-dlya-mediciny.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**) уже есть.
- Правила — **аналогичны** предыдущим SEO-элементам раздела.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `seo-prodvizhenie` (159) |
| Элемент | NAME «SEO сайта для строительства под ключ», CODE `seo-sayta-dlya-stroitelstva` |
| URL | `/services/seo-prodvizhenie/seo-sayta-dlya-stroitelstva/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно (опечатки сохранять) |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| «1С» | оставлять как в исходнике (`1С-Битрикс`) |

**Meta:**
- Title: `SEO сайта для строительства под ключ — вывод в ТОП | ITWEB`
- Description: `SEO-продвижение сайтов строительных компаний, застройщиков, подрядчиков под ключ. Локальное SEO, портфолио объектов, калькулятор смет. От 40 000 ₽/мес. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-sayt-dlya-stroitelstva.html`
2. Элемент в iblock 21 / секция 159, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code seo-sayta-dlya-stroitelstva`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 50+ строительных сайтов, Рост заявок, Результат за 3-6 мес, Гарантия 12 месяцев)
2. Что мы делаем — Техническое SEO / Контент и структура / Локальное SEO и репутация
3. Почему выбирают
4. Кейсы (`#dm-cases`) — строительная компания / ремонт под ключ / застройщик
5. Тарифы — 40 000 / 70 000 (featured) / 120 000 ₽/мес
6. Доп. услуги (URL из исходника без изменения)
7. Этапы (6 шагов; эмодзи-префиксы не переносить)
8. Инструменты (8×, 8-й Text.ru / Advego)
9. Отзывы (Георгий / Владимир / Анастасия)
10. FAQ (8)
11. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `seo-sayta-dlya-stroitelstva` ACTIVE в 159
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/seo-sayta-dlya-stroitelstva/` открывается
