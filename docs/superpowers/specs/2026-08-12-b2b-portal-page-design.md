# Design: страница услуги «B2B-портал»

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать элемент услуги в разделе «Создание сайтов» по `.dm-page` из `B2B-портал.html` без изменения CSS.

## Контекст

- Раздел: iblock 21, ID **158**, CODE `sozdanie-saytov`.
- Эталоны: `uslugi-sozdanie-saytov-sayty-dlya-stroitelstva.html`, `uslugi-sozdanie-saytov-sayty-dlya-obrazovaniya.html`.
- Ветка/worktree: `feature/seo-korporativnyy-sayt`.
- Правила — **аналогичны** предыдущим элементам (тексты дословно, SVG, CTA = Aspro CALLBACK).

## Решения

| Тема | Решение |
|---|---|
| Элемент | NAME «Создание B2B-портала под ключ», CODE `b2b-portal` |
| URL | `/services/sozdanie-saytov/b2b-portal/` |
| Контент | `DETAIL_TEXT` html |
| Meta | из `<head>` исходника |
| Тексты | дословно |
| Иконки | SVG; без эмодзи |
| CTA | H2+lead + CALLBACK «Получить расчёт стоимости»; без формы/телефонов |

**Meta:**
- Title: `Создание B2B-портала под ключ — от 150 000 ₽ | ITWEB`
- Description: `Разработка B2B-порталов и сайтов для оптовых продаж. Личные кабинеты дилеров, интеграция с 1С, ЭДО, оптовые цены. 50+ проектов. От 150 000 ₽. Рассчитаем стоимость!`

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-b2b-portal.html`
- Staging sync + commit по «задеплой»

## Секции (порядок исходника)

1. Hero (50+ B2B-порталов / Интеграция с 1С и ЭДО / Запуск от 35 дней / Гарантия 12 месяцев)
2. Почему выбирают Ай Ти Веб для разработки B2B-порталов
3. B2B-проекты (`#dm-cases`)
4. Функционал B2B-портала
5. Тарифы (150 000 / **250 000 featured** «Продвинутый B2B-портал» / 400 000 ₽)
6. 6 этапов создания B2B-портала
7. Интеграции для B2B-порталов (`dm-tool` + `<p>` у каждого)
8. Отзывы
9. FAQ
10. CTA

## Критерии готовности

- [ ] HTML на `dm-*`, тексты/порядок из исходника, SVG
- [ ] Элемент `b2b-portal` ACTIVE в 158
- [ ] DETAIL_TEXT + meta синхронизированы
- [ ] URL `/services/sozdanie-saytov/b2b-portal/` открывается
