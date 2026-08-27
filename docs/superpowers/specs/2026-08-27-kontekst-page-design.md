# Design: раздел «Контекстная реклама»

Дата: 2026-08-27  
Статус: по аналогии с разделами SEO и «Продвижение сайта»

## Цель

Создать посадочную страницу раздела инфоблока «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `Раздел Контекстная реклама.html` без изменения текстов.

## Контекст

- Ветка `feature/seo-korporativnyy-sayt`, эталон `uslugi-seo-prodvizhenie.html`, CSS `design-model.css`.
- `DESCRIPTION` раздела iblock 21; HTML в `design-model/pages/`.
- Раздел `kontekst` уже существует на staging, URL `/services/kontekst/`.
- В исходнике нет блока отзывов — не добавлять.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | NAME «Контекстная реклама», CODE `kontekst`, корень (`parent: null`) |
| URL | `/services/kontekst/` |
| Meta | из `<head>` через `IPROPERTY_TEMPLATES` |
| Тексты | дословно |
| Иконки | SVG, без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |
| CSS | новый не требуется |
| Ссылки направлений | как в исходнике: `/uslugi/prodvizhenie/kontekst/…` |
| Ссылки доп. услуг | как в исходнике: `/uslugi/prodvizhenie/semanticheskoe-yadro/`, `/uslugi/prodvizhenie/seo/` |

**Meta:**
- Title: `Контекстная реклама под ключ — Яндекс.Директ и Google Реклама | ITWEB`
- Description: `Настройка и ведение контекстной рекламы под ключ: Яндекс.Директ, Google Реклама, РСЯ, ретаргетинг. Первые заявки за 7 дней, снижение стоимости лида на 30-40%. От 25 000 ₽. Бесплатный аудит кампаний.`

## Архитектура доставки

1. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-kontekst.html`
2. Раздел iblock 21, `DESCRIPTION` = HTML, ACTIVE=Y
3. Staging sync (`scripts/dm-sync-page.sh --env remote --code kontekst`); commit/push по «задеплой»

## Порядок секций

1. Hero (benefits: 120+ кампаний, Заявки за 7 дней, Цена лида −30-40%, Сертифицированные специалисты)
2. Направления — Яндекс.Директ / РСЯ / Ретаргетинг
3. Почему выбирают
4. Кейсы (`#dm-cases`)
5. Тарифы — 25 000 / 40 000 (featured) / 60 000 ₽
6. Этапы (6 шагов; эмодзи-префиксы не переносить)
7. Инструменты (8×)
8. Что вы получите (6 карточек)
9. FAQ (8)
10. CTA без формы

## Критерии готовности

- [x] HTML на `dm-*`, тексты из исходника, SVG
- [x] Раздел `kontekst` ACTIVE
- [x] DESCRIPTION + meta синхронизированы
- [x] URL `/services/kontekst/` открывается с контентом
