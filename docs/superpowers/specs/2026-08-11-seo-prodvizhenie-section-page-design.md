# Design: страница раздела «SEO продвижение»

Дата: 2026-08-11  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Собрать посадочную страницу раздела `/services/seo-prodvizhenie/` по дизайн-модели `.dm-page` из исходника `SEO-продвижение.html`, с теми же правилами контента, что у страницы «SEO корпоративного сайта под ключ».

## Контекст

- Раздел iblock 21: ID **159**, CODE `seo-prodvizhenie`, NAME «SEO продвижение».
- URL раздела обслуживает Aspro `news/services/section.php`.
- Дочерний элемент уже есть: `seo-korporativnogo-sayta`.
- `DESCRIPTION` раздела выводится в `text_after_items` **после** списка дочерних услуг — так и оставляем (решение A).
- Стили: общий `design-model.css` (включая выравнивание кнопок в `dm-card:has(> .dm-btn)`).

## Решения

| Тема | Решение |
|---|---|
| Подход | Файл в `design-model/pages/` + sync в поле, которое реально рендерится |
| Поле контента | `DESCRIPTION` раздела 159, `DESCRIPTION_TYPE=html` |
| Список дочерних | Оставить сверху по шаблону Aspro |
| Тексты | Дословно из `SEO-продвижение.html` |
| Порядок секций | Как в исходнике, целиком |
| Иконки | SVG; без эмодзи / префиксов `⏱`/`✓` в маркерах |
| CTA | H2 + lead из form-section + Aspro CALLBACK; без формы и телефонов |
| Meta | Title/description из `<head>` исходника → SEO-свойства раздела |
| CSS | Переиспользовать `dm-*`; новые блоки только при необходимости |

## Артефакты

| Артефакт | Путь / значение |
|---|---|
| HTML-источник (git) | `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie.html` |
| Исходник текстов | `SEO-продвижение.html` (копия из Downloads в репо по желанию) |
| Раздел | IBLOCK 21 / ID 159 / CODE `seo-prodvizhenie` |
| URL | `/services/seo-prodvizhenie/` |
| Staging | `https://itweb-new.acrobat.test-itweb.ru/services/seo-prodvizhenie/` |

## Порядок секций и маппинг

1. Hero → `dm-hero` (CALLBACK + якорь кейсов)
2. Направления SEO → `dm-solution` + списки (+ кнопки CALLBACK «Заказать услугу» вместо `#`/формы)
3. Почему выбирают → `dm-card`
4. Кейсы → `dm-case` (`id="dm-cases"`)
5. Тарифы → `dm-tariff` (+ `is-featured` / badge)
6. Доп. услуги → `dm-card` + outline-ссылки (кнопки низ/центр/равная ширина — уже в CSS)
7. Этапы → `dm-timeline` / `dm-step`
8. Инструменты → `dm-tool`
9. Отзывы → `dm-review`
10. FAQ → `dm-faq` / `<details>`
11. CTA → `dm-cta` + CALLBACK

## Доставка

1. Сверстать HTML-источник.
2. Обновить раздел 159: `DESCRIPTION` = содержимое файла; type `html`.
3. Выставить meta title/description раздела из исходника.
4. Очистить кэш Bitrix.
5. Проверить URL (список дочерних сверху + `dm-page` ниже).

## Вне scope

- Правки `section.php` Aspro.
- Скрытие списка дочерних услуг.
- Изменение текстов.
- Кастомная HTML-форма / контакты в CTA.

## Критерии готовности

- [ ] Файл `uslugi-seo-prodvizhenie.html` на `dm-*`, полный порядок секций, тексты из исходника.
- [ ] `DESCRIPTION` раздела 159 синхронизирован, type html.
- [ ] Meta title/description из исходника на странице раздела.
- [ ] URL `/services/seo-prodvizhenie/` отдаёт лендинг после списка услуг.
- [ ] Нет эмодзи/формы/телефонов в CTA; кнопки CALLBACK где нужно.
