# Design: страница услуги «SEO интернет-магазина»

Дата: 2026-08-11  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO интернет-магазина.html` без изменения текстов. Элемент — в уже существующем разделе «SEO продвижение».

## Контекст

- Handoff: ветка `feature/seo-korporativnyy-sayt` (design-model уже подключён), эталон соседней услуги `uslugi-seo-prodvizhenie-korporativnyy-sayt.html`, CSS `design-model.css`.
- Контент страниц услуг живёт в `DETAIL_TEXT` элемента iblock 21; в git версионируется HTML-источник в `design-model/pages/`.
- Раздел `seo-prodvizhenie` (ID **159**) уже создан и активен.
- Референс визуала/блоков: существующие `dm-*` (не layout исходного standalone 1:1).

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | Существующий: NAME «SEO продвижение», CODE `seo-prodvizhenie` (159) |
| Элемент | NAME как H1 исходника: «SEO интернет-магазина под ключ», CODE `seo-internet-magazina` |
| URL | `/services/seo-prodvizhenie/seo-internet-magazina/` |
| Meta | Title/description из `<head>` исходника через `IPROPERTY_TEMPLATES` |
| Тексты | Без правок относительно исходника (заголовки, лиды, списки, FAQ, кейсы, тарифы) |
| Порядок секций | Как в исходнике, целиком |
| Иконки | Только тонкие SVG (`.dm-ico` / hero `.ico`), без эмодзи |
| Финальный CTA | Заголовок + лид из исходника + `dm-cta` + Aspro `CALLBACK`; без HTML-формы и без телефонов/мессенджеров |
| CSS | Новый CSS не требуется: переиспользовать `dm-solution`, `dm-tool`, выравнивание кнопок карточек |
| Подход реализации | HTML-источник + элемент в разделе 159 + sync `DETAIL_TEXT` |

## Архитектура доставки

1. HTML-обёртка `<div class="dm-page">…</div>` в файле-источнике.
2. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-internet-magazin.html`.
3. Bitrix iblock **21**:
   - раздел `seo-prodvizhenie` уже есть (не создавать заново);
   - создать элемент с CODE выше, привязка к секции 159, `DETAIL_TEXT` = содержимое файла, `DETAIL_TEXT_TYPE=html`, ACTIVE=Y.
4. Стили только в `design-model.css` (уже готовы); не inline и не через `@import` в `custom.css`.
5. После правок/деплоя — очистка `bitrix/cache/` и `bitrix/managed_cache/` при необходимости.

Standalone-файл в Downloads / копии остаётся референсом текстов; рабочий артефакт для сайта — файл в `design-model/pages/` + БД.

## Порядок секций и маппинг

1. **Hero** → `dm-hero` (H1, subtitle, 4 benefit с SVG, кнопки CALLBACK + якорь `#dm-cases`).
2. **Что мы делаем** → `dm-section` + `dm-grid dm-grid-3` + `dm-solution` с иконкой, H3, абзацем и списком фич (Техническое SEO / Оптимизация каталога / Контент и ссылки).
3. **Почему выбирают** → `dm-card` в сетке + SVG (e-commerce фокус).
4. **Кейсы** → `dm-case` / `dm-case-head` / stats; `id="dm-cases"`; тексты и цифры из исходника; ссылки как в исходнике.
5. **Тарифы** → `dm-tariff`; средний — `is-featured` + `dm-tariff-badge` «Популярный»; кнопки CALLBACK; длительность без префикса `⏱`.
6. **Доп. услуги** → `dm-card` (SVG + H3 + p + `dm-btn-outline` со ссылкой); URL из исходника без изменения.
7. **Этапы** → `dm-timeline` + `dm-step`; формулировки пунктов как в исходнике; декоративные эмодзи в строках этапов не переносить (текст пунктов сохранить, разделение через `<br>`).
8. **Инструменты** → `dm-tool` (SVG + H3 + короткий p), сетка.
9. **Отзывы** → `dm-review` + `dm-review-stars` (SVG), тексты из исходника.
10. **FAQ** → `dm-faq` + `<details class="dm-faq-item">` / `summary` / `.dm-faq-answer`.
11. **CTA** → `dm-cta`: H2 и lead из form-section исходника (без form/контактов) + кнопка CALLBACK.

Правило по эмодзи в тексте: иконки-эмодзи и префиксы вроде `⏱` / `✓` в маркерах списков не копировать (маркеры даёт CSS `dm-*`); словесный текст рядом с ними сохраняется дословно.

Чередование `dm-section` / `dm-section--alt` — как у эталона Bitrix (читаемый ритм фона).

## Кнопки CALLBACK

```html
<span class="dm-btn dm-btn-primary"
  data-event="jqm"
  data-param-form_id="CALLBACK"
  data-name="callback">…</span>
```

## Ветка и окружение

- База работы: worktree `.worktrees/seo-korporativnyy-sayt`, ветка `feature/seo-korporativnyy-sayt`.
- Staging: `https://itweb-new.acrobat.test-itweb.ru/` (и `http://…`).
- Деплой на staging + commit/push — по запросу «задеплой».

## Вне scope

- Правка текстов/смысла копирайта.
- Кастомная HTML-форма и контакты внизу страницы.
- Новый CSS (если не вскроется баг при вёрстке).
- Коммит ядра Bitrix / cache / upload.
- Автодеплой без явного запроса.

## Критерии готовности

- [ ] HTML-источник на `dm-*` с полным порядком секций и текстами из исходника.
- [ ] SVG вместо эмодзи в иконках; отзывы со звёздами SVG.
- [ ] Элемент `seo-internet-magazina` в разделе 159 (iblock 21), ACTIVE.
- [ ] `DETAIL_TEXT` синхронизирован с файлом-источником; meta title/description выставлены.
- [ ] Страница открывается по `/services/seo-prodvizhenie/seo-internet-magazina/` и визуально согласована с эталоном `.dm-page`.
