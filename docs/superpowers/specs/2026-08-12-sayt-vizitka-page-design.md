# Design: страница услуги «Сайт-визитка»

Дата: 2026-08-12  
Статус: готово (staging + design-model каталог)

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `Visitka.html`. Страница шире типичных landings (~20 секций) — при необходимости **расширить design-model** (новые `dm-*` + CSS), а не ломать layout чужими классами из исходника.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-b2b-portal.html` + `design-model.css`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Базовые правила как у соседних элементов (тексты дословно, SVG, CTA = Aspro CALLBACK).

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание сайта-визитки», CODE `sayt-vizitka` |
| URL | `/services/sozdanie-saytov/sayt-vizitka/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно, все секции исходника |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK «Получить расчёт стоимости»; без формы/телефонов |
| Design-model | Переиспользовать существующие `dm-*`. Если блок **явно не похож** на уже сверстанные (geo-stats, SEO-список, профессии, платформы, готовое решение, deliverables, showcase, team, сертификаты и т.п.) — **добавить новый блок** в `design-model.css` (+ при необходимости JS только если уже принято в модели) и использовать его в HTML. Не копировать сырые классы исходника (`benefit-card`, `geo-stat`, …) в DETAIL_TEXT |

**Meta:**
- Title: `Создание сайта-визитки под ключ — от 25 000 ₽ | ITWEB`
- Description: `Разработка сайта-визитки под ключ. Быстрый запуск за 7 дней. Адаптивный дизайн. Идеально для малого бизнеса. Оставьте заявку!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayt-vizitka.html`
- CSS: `bitrix/templates/aspro_max/css/design-model.css`
- Каталог блоков (для следующих страниц): `bitrix/templates/aspro_max/design-model/BLOCKS.md`
- Карта: `bitrix/templates/aspro_max/design-model/images/russia-map-clients.svg`
- Cursor rule: `.cursor/rules/dm-page-design-model.mdc`
- Staging sync + commit по «задеплой»

## Новые блоки design-model (из этой страницы)

Зафиксированы в `BLOCKS.md`: `dm-geo`, `dm-seo-item`, `dm-industry`, `dm-table` (+ mobile cards), `dm-deliverable`, `dm-team`, `dm-cert`, `dm-proof`, `dm-timeline--sm`, `dm-case-metric`.

## Критерии готовности

- [x] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [x] Новые блоки добавлены в design-model и каталог `BLOCKS.md`
- [x] Элемент `sayt-vizitka` ACTIVE в 158
- [x] DETAIL_TEXT + meta синхронизированы
- [x] URL `/services/sozdanie-saytov/sayt-vizitka/` открывается

## Секции (порядок исходника)

1. Hero (Запуск за 7 дней / От 25 000 ₽ / Адаптивный дизайн / Идеально для старта / Гарантия 12 месяцев)
2. Почему заказывают сайт-визитку у нас
3. Для кого подходит сайт-визитка
4. Результаты наших клиентов
5. Работаем по всей России (`dm-geo`)
6. Каждый сайт-визитку создаём с фокусом на SEO (`dm-seo-item`)
7. Создаём сайты-визитки для 50+ профессий (`dm-industry`)
8. Тарифы (25 000 / **40 000 featured** «Стандарт» / 60 000 ₽)
9. На каких платформах разрабатываем сайты-визитки (`dm-table`)
10. Что такое готовое решение для сайта-визитки? + `dm-timeline--sm` + `dm-solution`
11. Что вы получите после запуска сайта-визитки (`dm-deliverable`)
12. Весь необходимый функционал сайта-визитки
13. 6 этапов создания сайта-визитки под ключ (`dm-timeline`)
14. Команда (`dm-team`)
15. Подключаем внешние системы и сервисы (`dm-tool`)
16. FAQ
17. Сертификаты (`dm-cert`) + proof (`dm-proof`)
18. Портфолио (`dm-case` + `dm-case-metric`)
19. Отзывы
20. CTA
