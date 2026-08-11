# Design: страница услуги «SEO сайта-каталога»

Дата: 2026-08-11  
Статус: согласовано в диалоге

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO сайта-каталога.html` без изменения текстов. Элемент — в уже существующем разделе «SEO продвижение».

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-lending.html` / соседние SEO-страницы, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**) уже есть.
- Правила — **аналогичны** предыдущим SEO-элементам раздела.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `seo-prodvizhenie` (159) |
| Элемент | NAME «SEO сайта-каталога под ключ», CODE `seo-sayta-kataloga` |
| URL | `/services/seo-prodvizhenie/seo-sayta-kataloga/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно (опечатки сохранять) |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |

**Meta:**
- Title: `SEO сайта-каталога под ключ — вывод в ТОП | ITWEB`
- Description: `SEO-продвижение сайтов-каталогов под ключ. Вывод в ТОП Яндекса и Google за 3-6 месяцев. Оптимизация категорий, карточек товаров, фильтров. От 40 000 ₽/мес. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-sayt-katalog.html`
2. Элемент в iblock 21 / секция 159, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync + cache clear; commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 100+ каталогов, Рост заявок, Результат за 3-6 мес, Гарантия 12 месяцев)
2. Что мы делаем — Техническое SEO / Оптимизация каталога / Контент и ссылки
3. Почему выбирают
4. Кейсы (`#dm-cases`) — стройматериалы / оборудование / мебель
5. Тарифы — 40 000 / 70 000 (featured) / 120 000 ₽/мес
6. Доп. услуги
7. Этапы
8. Инструменты (8×, 8-й Text.ru / Advego)
9. Отзывы (Антон / Вячеслав / Диана)
10. FAQ
11. CTA без формы

## Критерии готовности

- [ ] HTML на `dm-*`, тексты из исходника, SVG
- [ ] Элемент `seo-sayta-kataloga` ACTIVE в 159
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/seo-prodvizhenie/seo-sayta-kataloga/` открывается
