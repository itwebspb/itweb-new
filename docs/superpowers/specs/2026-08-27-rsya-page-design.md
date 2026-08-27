# Design: страница услуги «Настройка РСЯ»

Дата: 2026-08-27  
Статус: элемент в разделе «Контекстная реклама»

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `РСЯ.html` без изменения текстов. Элемент — в уже существующем разделе `kontekst`.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie-semanticheskoe-yadro.html`, CSS `design-model.css`.
- `DETAIL_TEXT` элемента iblock 21; HTML в `design-model/pages/`.
- Раздел `kontekst` (ID **164**) уже есть.
- В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | `kontekst` (164) |
| Элемент | NAME «Настройка РСЯ под ключ», CODE `rsya` |
| URL | `/services/kontekst/rsya/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| Ссылки | как в исходнике: `/uslugi/prodvizhenie/kontekst/…`, `/uslugi/prodvizhenie/seo/` |

**Meta:**
- Title: `Настройка РСЯ под ключ — Рекламная сеть Яндекса | ITWEB`
- Description: `Настройка и ведение рекламы в РСЯ (Рекламная сеть Яндекса) под ключ: 50 000+ площадок, таргетинги по интересам, look-alike, ретаргетинг. Клики дешевле поиска на 40-60%. От 15 000 ₽. Бесплатный аудит.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst-rsya.html`
2. Элемент в iblock 21 / секция 164, `DETAIL_TEXT` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code rsya`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 70+ кампаний в РСЯ, Клик дешевле на 40-60%, Точные таргетинги, Сертифицированные специалисты)
2. Что входит — Таргетинги / Креативы и тексты / Ретаргетинг и look-alike
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 15 000 / 25 000 (featured) / 40 000 ₽
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [x] HTML на `dm-*`, тексты из исходника, SVG
- [x] Элемент `rsya` ACTIVE в `kontekst`
- [x] DETAIL_TEXT + meta синхронизированы
- [x] URL `/services/kontekst/rsya/` открывается
