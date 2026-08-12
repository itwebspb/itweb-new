# Design: страница услуги «Сайты на WordPress»

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `Сайты на WordPress.html` без изменения текстов.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-sayty-na-tilda.html`, `uslugi-sozdanie-saytov-1c-bitriks.html`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Правила — **аналогичны** предыдущим элементам.

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание сайтов на WordPress под ключ», CODE `sayty-na-wordpress` |
| URL | `/services/sozdanie-saytov/sayty-na-wordpress/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK; без формы/телефонов |

**Meta:**
- Title: `Разработка сайтов на WordPress под ключ — от 40 000 ₽ | ITWEB`
- Description: `Профессиональная разработка сайтов на WordPress. Кастомные темы, WooCommerce, SEO-оптимизация, высокая скорость. От 40 000 ₽. Рассчитаем стоимость за 1 день!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-na-wordpress.html`
- Staging sync + commit по «задеплой»

## Секции (порядок исходника)

1. Hero (Запуск от 14 дней / Идеально для SEO / Кастомные темы / Гарантия 12 месяцев)
2. Почему выбирают создание сайтов на WordPress
3. Проекты (`#dm-cases`)
4. Возможности платформы WordPress
5. Тарифы (ориентир 40 000 / 80 000 featured / 120 000 ₽)
6. 6 этапов разработки
7. С чем интегрируем WordPress (`dm-tool`)
8. Отзывы
9. FAQ
10. CTA

## Критерии готовности

- [ ] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [ ] Элемент `sayty-na-wordpress` ACTIVE в 158
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/sozdanie-saytov/sayty-na-wordpress/` открывается
