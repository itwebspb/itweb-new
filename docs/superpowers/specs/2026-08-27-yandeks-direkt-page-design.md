# Design: страница услуги «Настройка Яндекс.Директ»

Дата: 2026-08-27  
Статус: элемент в разделе «Контекстная реклама»

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `Яндекс Директ.html` без изменения текстов. Элемент — в уже существующем разделе `kontekst`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-kontekst-rsya.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `kontekst` (ID **164**) уже есть.
- В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `kontekst` (164) |
| Элемент | NAME «Настройка Яндекс.Директ под ключ», CODE `yandeks-direkt` |
| URL | `/services/kontekst/yandeks-direkt/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| Ссылки | как в исходнике: `/uslugi/prodvizhenie/kontekst/…`, `/uslugi/prodvizhenie/seo/` |

**Meta:**
- Title: `Настройка Яндекс.Директ под ключ — цена от 25 000 ₽ | ITWEB`
- Description: `Настройка и ведение Яндекс.Директ под ключ: поиск, РСЯ, ретаргетинг. Чистая семантика, минус-слова, A/B тесты объявлений. Первые заявки за 7 дней, цена лида −30-40%. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst-yandeks-direkt.html`
2. Элемент в iblock 21 / секция 164, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code yandeks-direkt`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 90+ кампаний в Директе, Заявки за 7 дней, Цена лида −30-40%, Сертифицированные специалисты)
2. Что входит — Поисковые кампании / РСЯ / Ретаргетинг
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 25 000 / 35 000 (featured) / 45 000 ₽
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [x] HTML на `dm-*`, тексты из исходника, SVG
- [x] Элемент `yandeks-direkt` ACTIVE в `kontekst`
- [x] DETAIL_TEXT + meta синхронизированы
- [x] URL `/services/kontekst/yandeks-direkt/` открывается
