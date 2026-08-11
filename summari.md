# Handoff: дизайн-модель страниц услуг ITWEB

## Цель для следующего агента

Создавать **новые посадочные страницы услуг** по уже готовой дизайн-модели (namespace `.dm-page`), по образцу страницы «Сайты на 1С-Битрикс». Не копировать чужой HTML layout 1:1 — собирать из блоков `dm-*`.

---

## Репозиторий и ветка

- Repo: `github.com/itwebspb/itweb-new`
- Ветка с работой: `cursor/bitrix-1c-services-page-e816`
- PR: https://github.com/itwebspb/itweb-new/pull/3
- База: `master`
- Локальный hostname в VM: `itweb-new.local` (только внутри VM)
- Staging: `https://itweb-new.acrobat.test-itweb.ru/` (SSH из cloud-агента без ключа не работает; деплой вручную)

Архив для ручного переноса:  
`deploy/itweb-bitrix-1c-services-deploy.zip`  
https://github.com/itwebspb/itweb-new/raw/cursor/bitrix-1c-services-page-e816/deploy/itweb-bitrix-1c-services-deploy.zip

---

## Как устроены страницы услуг

1. **Шаблон Aspro Max** — оболочка сайта.
2. **Контент страницы** лежит в **элементе инфоблока** (`DETAIL_TEXT`, тип HTML), не в отдельном `.php` URL.
3. HTML оборачивается в `<div class="dm-page">…</div>` и использует классы `dm-*`.
4. Стили — общий файл дизайн-модели (один на все такие страницы).

### Эталонная страница

| Поле | Значение |
|---|---|
| Название | Сайты на 1С-Битрикс |
| CODE | `sayty-na-1c-bitriks` |
| URL | `/services/sozdanie-saytov/sayty-na-1c-bitriks/` |
| IBLOCK_ID | **21** (Услуги) |
| SECTION_ID | **158** (`sozdanie-saytov` / Создание сайтов) |
| Element ID (локально) | **1260** (на проде ID может отличаться — искать по CODE) |

Источник HTML (версионируется в git):  
`bitrix/templates/aspro_max/design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html`

---

## Ключевые файлы

| Файл | Роль |
|---|---|
| `bitrix/templates/aspro_max/css/design-model.css` | Дизайн-система `.dm-*` (акцент `#DC1E28`) |
| `bitrix/templates/aspro_max/header.php` | Подключение CSS через `SetAdditionalCSS` |
| `bitrix/templates/aspro_max/design-model/pages/*.html` | HTML-источники страниц для git + копирования в `DETAIL_TEXT` |

**Важно:** `@import` в `custom.css` **не работает** — Bitrix CSS optimizer его срезает. Только:

```php
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/design-model.css");
```

После правок CSS обязательно чистить:

```bash
rm -rf bitrix/cache/css/* bitrix/managed_cache/*
```

и Ctrl+F5 в браузере.

---

## Дизайн-модель: блоки и классы

Акцент: `#DC1E28`. Всё namespaced под `.dm-page`.

Типовой каркас страницы:

1. `dm-hero` — первый экран
2. `dm-section` / `dm-section--alt` — секции
3. Сетки: `dm-grid dm-grid-2|3|4`
4. Карточки: `dm-card`, `dm-feature`, `dm-case`, `dm-tariff`, `dm-solution`, `dm-review`
5. `dm-timeline` + `dm-step` — этапы (зигзаг)
6. `dm-faq` — FAQ (`<details>`)
7. `dm-cta` — финальный CTA

Иконки: **только тонкие SVG**, круг **55×55**, красный фон, белый stroke. Класс `.dm-ico` (в hero — `.dm-hero-benefit .ico`). **Без эмодзи.**

Кнопки / формы Aspro:

```html
<span class="dm-btn dm-btn-primary"
  data-event="jqm"
  data-param-form_id="CALLBACK"
  data-name="callback">Текст</span>
```

Варианты: `dm-btn-primary` | `dm-btn-secondary` | `dm-btn-outline` (+ `dm-btn-lg`).

---

## Референсы и принятые решения

- Визуальный референс: pixelplus.ru (в т.ч. AI SEO stages).
- Этапы: геометрия пунктира как `.work-stages` на pixelplus:
  - desktop: нечётные └ → в бок следующего круга; чётные ┘-хвост под текстом;
  - mobile (`≤767px`): только вертикаль **от низа круга до верха следующего**.
- Кейсы (`.dm-case-head`): равная высота через **CSS subgrid**, заливка `#dc1e28`.
- CTA: телефона внизу нет; отступ кнопки сверху/снизу **по 30px** через `.dm-cta .dm-center { margin-top: 30px }`  
  (Aspro обнуляет `margin` у `p`, поэтому на `.dm-lead` margin не опираться).

Комментарий в шапке `design-model.css` про `@import` — устарел; факт = `SetAdditionalCSS`.

---

## Как создать новую страницу услуги (чеклист)

1. Взять эталон: `design-model/pages/uslugi-sozdanie-saytov-1c-bitriks.html`.
2. Создать новый файл  
   `bitrix/templates/aspro_max/design-model/pages/<раздел>-<услуга>.html`  
   с обёрткой `<div class="dm-page">`.
3. Собрать секции только из классов `dm-*`. Новые стили — в `design-model.css` (переиспользуемо), не inline.
4. В Bitrix (IBLOCK 21):
   - нужный раздел (или создать);
   - элемент: NAME, CODE (латиница/дефисы), ACTIVE=Y;
   - `DETAIL_TEXT` = содержимое HTML-файла, тип **html**.
5. URL обычно: `/services/<section_code>/<element_code>/`.
6. Локально проверить:  
   `http://itweb-new.local/...` (Desktop/VM) или Ports → forward **80** → `http://127.0.0.1:<port>/...`  
   (`itweb-new.local` с Mac/встроенного браузера Cursor **не резолвится**).
7. Закоммитить CSS/HTML/header в git. **DETAIL_TEXT в git не попадает** — только файл-источник + синхрон в БД.
8. На staging: залить файлы шаблона + вставить HTML в элемент + очистить кэш.

Синхрон `DETAIL_TEXT` из файла (локальный паттерн):

```php
$html = file_get_contents($_SERVER["DOCUMENT_ROOT"]
  ."/bitrix/templates/aspro_max/design-model/pages/ВАШ-ФАЙЛ.html");
// найти элемент по IBLOCK_ID + CODE, затем Update DETAIL_TEXT / DETAIL_TEXT_TYPE=html
```

---

## Локальное окружение (cloud VM)

- DocumentRoot: `/workspace`
- Старт: `bash .cursor/start.sh`
- LAMP: Apache `:80`, MariaDB, PHP 8.3
- DB: `sitemanager` / user `bitrix` / pass `bitrix`
- Ядро Bitrix в git **нет** (restore локально)

---

## Чего не делать

- Не класть layout в `@import` / не дублировать огромные стили в `custom.css` для dm-страниц.
- Не использовать эмодзи вместо SVG-иконок.
- Не ждать, что правка только HTML-файла обновит сайт — нужен Update `DETAIL_TEXT`.
- Не коммитить ядро/cache/upload.
- Не считать `ERR_NAME_NOT_RESOLVED` для `itweb-new.local` поломкой сайта — это hostname только в VM.

---

## Статус на момент передачи

- Дизайн-модель v1 готова и подключена.
- Эталонная страница «Сайты на 1С-Битрикс» сверстана и отполирована (кейсы, этапы desktop/mobile, CTA).
- Выкладка на `itweb-new.acrobat.test-itweb.ru` — **вручную** (архив в `deploy/` + инструкция в README архива).
- Следующая работа: **новые страницы услуг** по этой же модели (новый HTML-источник + элемент в iblock 21 + при необходимости расширение `design-model.css`).
