# Design: страница услуги «Сайты на Tilda»

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `Сайты на Tilda.html` без изменения текстов.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-1c-bitriks.html`, `uslugi-sozdanie-saytov-sozdanie-s-seo.html`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Правила — **аналогичны** предыдущим элементам (тексты дословно, SVG, CALLBACK CTA).

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание сайтов на Tilda под ключ», CODE `sayty-na-tilda` |
| URL | `/services/sozdanie-saytov/sayty-na-tilda/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK «Получить расчёт стоимости»; без формы/телефонов |

**Meta:**
- Title: `Создание сайтов на Tilda под ключ — запуск от 5 дней | ITWEB`
- Description: `Разработка сайтов на Tilda под ключ. Лендинги, визитки, промо-сайты. Быстрый запуск от 5 дней. Удобный редактор, современный дизайн. От 20 000 ₽. Рассчитаем стоимость за 1 день!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-na-tilda.html`
- Staging sync + commit по «задеплой»

## Секции (порядок исходника)

1. Hero (benefits: Запуск от 5 дней / Уникальный дизайн / Адаптивный дизайн / Высокая конверсия)
2. Почему выбирают Tilda
3. Проекты (`#dm-cases`)
4. Возможности платформы Tilda
5. Тарифы (ориентир 20 000 / 30 000 featured / 50 000 ₽)
6. 6 этапов
7. Подключаем внешние системы (integrations → `dm-tool`)
8. Отзывы
9. FAQ
10. CTA

## Критерии готовности

- [ ] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [ ] Элемент `sayty-na-tilda` ACTIVE в 158
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/sozdanie-saytov/sayty-na-tilda/` открывается
