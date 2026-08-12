# Design: страница услуги «Корпоративный сайт»

Дата: 2026-08-12  
Статус: согласовано (CODE `korporativnyy-sayt`)

## Цель

Элемент в разделе «Создание сайтов» по `.dm-page` из `/Users/viktorgromov/Downloads/Korporativniy.html`, по паттерну визитки (расширенные блоки).

## Решения

| Тема | Решение |
|---|---|
| CODE / NAME | `korporativnyy-sayt` / «Создание корпоративного сайта» |
| URL | `/services/sozdanie-saytov/korporativnyy-sayt/` |
| Section | 158 `sozdanie-saytov` |
| Sync | только remote |
| Design-model | существующие `dm-*` (вкл. geo/seo/industry/table/solution/deliverable/team/cert/proof) |

**Meta:** из исходника (от 80 000 ₽ | ITWEB).

## Маппинг

Hero → dm-hero · Почему (7) → dm-card · Кейсы → dm-case · Россия → dm-geo · SEO → dm-seo-item · Отрасли → dm-industry · Тарифы (4, featured 250k) → dm-tariff · Платформы → dm-table · Готовое решение → dm-timeline--sm + dm-solution · Deliverables → dm-deliverable · Функционал → dm-feature · 8 этапов → dm-timeline · Команда → dm-team · Интеграции → dm-tool · FAQ → dm-faq · Сертификаты → dm-cert · Портфолио → dm-proof · Отзывы → dm-review · CTA → dm-cta + CALLBACK

## Артефакты

- `…/pages/uslugi-sozdanie-saytov-korporativnyy-sayt.html`
- манифест + spec/plan
