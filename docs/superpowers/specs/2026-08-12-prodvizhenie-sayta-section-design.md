# Design: раздел «Продвижение сайта» + страница раздела

Дата: 2026-08-12  
Статус: согласовано в диалоге, ожидает ревью файла

## Цель

1. Создать корневой раздел инфоблока «Услуги» **«Продвижение сайта»** и вложить в него существующий раздел **«SEO продвижение»** (без 301-редиректов).
2. Собрать посадочную страницу нового раздела по `.dm-page` из исходника `Продвижение сайта (главный раздел).html`, по той же модели, что страница раздела SEO.

## Контекст

- Iblock **21** (Услуги), Aspro Max `news/services/section.php`.
- Сейчас: `seo-prodvizhenie` (ID **159**) — корень (`DEPTH_LEVEL=1`), URL `/services/seo-prodvizhenie/…`.
- Дочерние элементы SEO (1262–1265 и др.) привязаны к секции 159; после вложения их URL станут `/services/prodvizhenie-sayta/seo-prodvizhenie/<code>/`.
- Контент раздела рендерится из `DESCRIPTION` в `text_after_items` **после** списка дочерних — оставляем (как у SEO-раздела).
- Ветка/worktree: `feature/seo-korporativnyy-sayt` / `.worktrees/seo-korporativnyy-sayt`.
- CSS: общий `design-model.css`.

## Решения (зафиксировано с заказчиком)

| Тема | Решение |
|---|---|
| Новый раздел | NAME «Продвижение сайта», CODE `prodvizhenie-sayta`, ACTIVE, корень (`IBLOCK_SECTION_ID` = false/null) |
| URL родителя | `/services/prodvizhenie-sayta/` |
| Вложение SEO | Перенести секцию 159 под новый родитель; без 301 со старых URL |
| URL SEO после | `/services/prodvizhenie-sayta/seo-prodvizhenie/` (+ элементы под этим путём) |
| Контент страницы | `DESCRIPTION` нового раздела, `DESCRIPTION_TYPE=html` |
| Список дочерних | Сверху по шаблону Aspro (после вложения — карточка SEO) |
| Тексты | Дословно из `Продвижение сайта (главный раздел).html` |
| Иконки | SVG; без эмодзи / префиксов `⏱`/`✓` |
| CTA | H2 + lead + Aspro CALLBACK; без формы и телефонов |
| Meta | Title/description из `<head>` → SEO-свойства раздела |
| CSS | Новый не требуется |
| Ссылка «Подробнее о SEO» | `/services/prodvizhenie-sayta/seo-prodvizhenie/` (актуальный путь сайта) |
| Ссылка «Подробнее о контексте» | Как в исходнике: `/uslugi/prodvizhenie/kontekst/` |
| Прочие URL доп. услуг | Как в исходнике, без правок |

**Meta из исходника:**
- Title: `Продвижение сайтов — SEO и контекстная реклама | ITWEB`
- Description: `Комплексное продвижение сайтов: SEO-оптимизация и контекстная реклама. Вывод в ТОП, гарантия результата. От 40 000 ₽/мес.`

## Архитектура доставки

1. Bitrix API: создать секцию `prodvizhenie-sayta`; `Update` секции 159 с `IBLOCK_SECTION_ID` = ID родителя; при необходимости `CIBlockSection::ReSort(21)` / очистка кеша.
2. HTML-обёртка `<div class="dm-page">…</div>` в файле-источнике git.
3. Файл: `bitrix/templates/aspro_max/design-model/pages/uslugi-prodvizhenie-sayta.html`.
4. Sync содержимого файла → `DESCRIPTION` нового раздела (+ IPROPERTY meta раздела).
5. Staging + cache clear; commit/push по «задеплой».

## Порядок секций и маппинг

1. **Hero** → `dm-hero` (H1 «Продвижение сайтов под ключ», subtitle, benefits, CALLBACK + `#dm-cases`).
2. **Направления продвижения** → `dm-solution` ×2 (SEO / Контекст) + списки + кнопки-ссылки (см. таблицу решений).
3. **Почему выбирают** → `dm-card`.
4. **Кейсы** → `dm-case`, `id="dm-cases"`.
5. **Тарифы** → `dm-tariff` (+ featured / badge).
6. **Доп. услуги** → `dm-card` + outline-ссылки из исходника.
7. **Этапы** → `dm-timeline` / `dm-step` (без эмодзи в пунктах).
8. **Инструменты** → `dm-tool`.
9. **Отзывы** → `dm-review` + SVG-звёзды.
10. **FAQ** → `dm-faq` / `<details>`.
11. **CTA** → `dm-cta` без формы.

## Вне scope

- 301-редиректы со старых `/services/seo-prodvizhenie/…`.
- Создание раздела/страницы «Контекстная реклама».
- Правка текстов исходника.
- Новый CSS (если не вскроется баг).

## Критерии готовности

- [ ] Раздел `prodvizhenie-sayta` существует, ACTIVE, корень.
- [ ] Секция 159 — дочерняя к `prodvizhenie-sayta`; дерево корректно.
- [ ] `/services/prodvizhenie-sayta/` открывается; meta выставлены.
- [ ] `/services/prodvizhenie-sayta/seo-prodvizhenie/` и дочерние элементы открываются по новым путям.
- [ ] HTML-источник на `dm-*` с полным порядком секций и текстами из исходника; SVG; CALLBACK CTA.
- [ ] `DESCRIPTION` синхронизирован с файлом.
- [ ] Старые `/services/seo-prodvizhenie/…` могут отдавать 404 (ожидаемо при варианте A).
