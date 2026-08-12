# Design: страница услуги «Сайты для образования»

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `Сайты для образования.html` без изменения CSS.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-sayty-dlya-obschepita.html`, `uslugi-sozdanie-saytov-sayty-dlya-nedvizhimosti.html`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Правила — **аналогичны** предыдущим элементам (тексты дословно, SVG, CTA = Aspro CALLBACK).

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Сайт для образования под ключ», CODE `sayty-dlya-obrazovaniya` |
| URL | `/services/sozdanie-saytov/sayty-dlya-obrazovaniya/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK «Получить расчёт стоимости»; без формы/телефонов |

**Meta:**
- Title: `Сайт для образовательного центра под ключ — от 90 000 ₽ | ITWEB`
- Description: `Разработка сайтов для школ, курсов, образовательных центров, онлайн-школ. Запись на курсы, расписание, личный кабинет студента, интеграция с LMS. 50+ проектов. От 90 000 ₽. Рассчитаем стоимость!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-sayty-dlya-obrazovaniya.html`
- Staging sync + commit по «задеплой»

## Секции (порядок исходника)

1. Hero (50+ сайтов для образования / Интеграция с LMS и Zoom / Запуск от 25 дней / Гарантия 12 месяцев)
2. Почему выбирают Ай Ти Веб для разработки сайтов образования
3. Проекты в сфере образования (`#dm-cases`)
4. Функционал сайта для образования
5. Тарифы (90 000 / **150 000 featured** «Сайт образовательного центра» / 250 000 ₽)
6. 6 этапов создания сайта для образования
7. Интеграции для сайтов образования (`dm-tool` + `<p>` у каждого)
8. Отзывы
9. FAQ
10. CTA

## Критерии готовности

- [ ] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [ ] Элемент `sayty-dlya-obrazovaniya` ACTIVE в 158
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/sozdanie-saytov/sayty-dlya-obrazovaniya/` открывается
