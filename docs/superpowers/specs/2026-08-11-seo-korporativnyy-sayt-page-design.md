# Design: страница услуги «SEO корпоративного сайта»

Дата: 2026-08-11  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

Создать посадочную страницу услуги в инфоблоке «Услуги» по дизайн-модели `.dm-page`, перенеся контент из standalone-файла `SEO корпоративного сайта.html` без изменения текстов. Новый раздел — корневой «SEO продвижение».

## Контекст

- Handoff: ветка `cursor/bitrix-1c-services-page-e816`, эталон `uslugi-sozdanie-saytov-1c-bitriks.html`, CSS `design-model.css`, подключение через `SetAdditionalCSS`.
- Контент страниц услуг живёт в `DETAIL_TEXT` элемента iblock 21; в git версионируется HTML-источник в `design-model/pages/`.
- Референс визуала/блоков: существующие `dm-*` (не layout исходного standalone 1:1).

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Раздел | Корневой: NAME «SEO продвижение», CODE `seo-prodvizhenie` → URL `/services/seo-prodvizhenie/…` |
| Элемент | NAME как H1 исходника: «SEO корпоративного сайта под ключ», CODE `seo-korporativnogo-sayta` |
| URL | `/services/seo-prodvizhenie/seo-korporativnogo-sayta/` |
| Тексты | Без правок относительно исходника (заголовки, лиды, списки, FAQ, кейсы, тарифы) |
| Порядок секций | Как в исходнике, целиком |
| Иконки | Только тонкие SVG (`.dm-ico` / hero `.ico`), без эмодзи |
| Финальный CTA | Заголовок + лид из исходника + `dm-cta` + Aspro `CALLBACK`; без HTML-формы и без телефонов/мессенджеров |
| Подход реализации | От ветки с дизайн-моделью: HTML-источник + точечный CSS + раздел/элемент + sync `DETAIL_TEXT` |

## Архитектура доставки

1. HTML-обёртка `<div class="dm-page">…</div>` в файле-источнике.
2. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-seo-prodvizhenie-korporativnyy-sayt.html`.
3. Bitrix iblock **21**:
   - создать раздел `seo-prodvizhenie` (ACTIVE=Y, родитель — корень услуг / как у `sozdanie-saytov`);
   - создать элемент с CODE выше, `DETAIL_TEXT` = содержимое файла, `DETAIL_TEXT_TYPE=html`.
4. Стили только в `design-model.css` (переиспользуемые), не inline и не через `@import` в `custom.css`.
5. После правок CSS — очистка `bitrix/cache/css/*` и `bitrix/managed_cache/*`.

Standalone-файл в корне репозитория остаётся референсом текстов; рабочий артефакт для сайта — файл в `design-model/pages/` + БД.

## Порядок секций и маппинг

1. **Hero** → `dm-hero` (H1, subtitle, 4 benefit с SVG, кнопки CALLBACK + якорь `#dm-cases`).
2. **Что мы делаем** → `dm-section` + `dm-grid dm-grid-3` + `dm-solution` с иконкой, H3, абзацем и **списком фич** (расширение стилей списка под `.dm-solution ul`).
3. **Почему выбирают** → `dm-card` в сетке + SVG.
4. **Кейсы** → `dm-case` / `dm-case-head` / stats; `id="dm-cases"`; тексты и цифры из исходника; ссылки как в исходнике.
5. **Тарифы** → `dm-tariff`; средний — `is-featured` + `dm-tariff-badge` «Популярный»; кнопки CALLBACK.
6. **Доп. услуги** → `dm-card` (SVG + H3 + p + `dm-btn-outline` со ссылкой); URL из исходника без изменения.
7. **Этапы** → `dm-timeline` + `dm-step`; формулировки пунктов как в исходнике; декоративные эмодзи в строках этапов не переносить (текст пунктов сохранить, разделение через `<br>`).
8. **Инструменты** → новый блок **`dm-tool`** (компактная карточка: SVG + H3 + короткий p), сетка; стили в `design-model.css` в духе `dm-feature`/`dm-card`.
9. **Отзывы** → `dm-review` + `dm-review-stars` (SVG), тексты из исходника.
10. **FAQ** → `dm-faq` + `<details class="dm-faq-item">` / `summary` / `.dm-faq-answer`.
11. **CTA** → `dm-cta`: H2 и lead из form-section исходника (без form/контактов) + кнопка CALLBACK.

Правило по эмодзи в тексте: иконки-эмодзи и префиксы вроде `⏱` / `✓` в маркерах списков не копировать (маркеры даёт CSS `dm-*`); словесный текст рядом с ними сохраняется дословно.

Чередование `dm-section` / `dm-section--alt` — как у эталона Bitrix (читаемый ритм фона).

## Новые / расширяемые стили

| Класс | Действие |
|---|---|
| `.dm-solution ul` (и li) | Стили списка фич внутри solution (сейчас solution — только icon/h3/p) |
| `.dm-tool` | Новый компактный item для блока инструментов |
| Прочее | Переиспользовать существующие `dm-*`; не дублировать огромные стили в `custom.css` |

Кнопки: `dm-btn dm-btn-primary|secondary|outline` (+ `dm-btn-lg` в hero), CALLBACK:

```html
<span class="dm-btn dm-btn-primary"
  data-event="jqm"
  data-param-form_id="CALLBACK"
  data-name="callback">…</span>
```

## Ветка и окружение

- База работы: ветка с дизайн-моделью `cursor/bitrix-1c-services-page-e816` (или эквивалент с уже подключённым `design-model.css`).
- Локальная проверка: URL элемента; hostname `itweb-new.local` только внутри VM; с Mac — port-forward :80.
- Staging-выкладка вручную (вне scope обязательной реализации, если не запрошено отдельно).

## Вне scope

- Правка текстов/смысла копирайта.
- Кастомная HTML-форма и контакты внизу страницы.
- Коммит ядра Bitrix / cache / upload.
- Автодеплой на staging.

## Критерии готовности

- [ ] HTML-источник на `dm-*` с полным порядком секций и текстами из исходника.
- [ ] SVG вместо эмодзи в иконках; отзывы со звёздами SVG.
- [ ] Раздел `seo-prodvizhenie` и элемент `seo-korporativnogo-sayta` в iblock 21, ACTIVE.
- [ ] `DETAIL_TEXT` синхронизирован с файлом-источником.
- [ ] При добавлении `.dm-tool` / list в solution — стили в `design-model.css`.
- [ ] Страница открывается по `/services/seo-prodvizhenie/seo-korporativnogo-sayta/` и визуально согласована с эталоном `.dm-page`.
