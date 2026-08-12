# Design: страница услуги «Сайты для общепита»

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `Сайты для общепита.html` без изменения CSS.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-sayty-dlya-nedvizhimosti.html`, `uslugi-sozdanie-saytov-sayty-dlya-mediciny.html`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Правила — **аналогичны** предыдущим элементам (тексты дословно, SVG, CTA = Aspro CALLBACK).

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Сайт для ресторана и кафе под ключ», CODE `sayty-dlya-obschepita` |
| URL | `/services/sozdanie-saytov/sayty-dlya-obschepita/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK «Получить расчёт стоимости»; без формы/телефонов |

**Meta:**
- Title: `Сайт для ресторана и кафе под ключ — от 90 000 ₽ | ITWEB`
- Description: `Разработка сайтов для ресторанов, кафе, баров, служб доставки еды. Онлайн-меню, бронирование столиков, интеграция с iiko и R-Keeper. 50+ проектов в общепите. От 90 000 ₽. Рассчитаем точную стоимость!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-dlya-obschepita.html`
- Staging sync + commit по «задеплой»

## Секции (порядок исходника)

1. Hero (50+ сайтов для общепита / Интеграция с iiko и R-Keeper / Запуск от 25 дней / Гарантия 12 месяцев)
2. Почему выбирают Ай Ти Веб для разработки сайтов общепита
3. Проекты в сфере общепита (`#dm-cases`)
4. Функционал сайта для общепита
5. Тарифы (90 000 / **160 000 featured** «Ресторан с доставкой» / 280 000 ₽)
6. 6 этапов создания сайта для общепита
7. Интеграции для сайтов общепита (`dm-tool` + `<p>` у каждого)
8. Отзывы
9. FAQ
10. CTA

## Критерии готовности

- [ ] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [ ] Элемент `sayty-dlya-obschepita` ACTIVE в 158
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/sozdanie-saytov/sayty-dlya-obschepita/` открывается
