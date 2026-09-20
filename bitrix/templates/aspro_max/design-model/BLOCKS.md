# Design Model — каталог блоков (`dm-*`)

Источник стилей: `bitrix/templates/aspro_max/css/design-model.css`  
Страницы-эталоны: `bitrix/templates/aspro_max/design-model/pages/`  
Обёртка любой посадочной: `<div class="dm-page">…</div>`

**Правило для новых страниц:** сначала выбрать блок из этого каталога. Новый `dm-*` добавлять только если ни один существующий не подходит по структуре/смыслу. Не тащить сырые классы из HTML-исходников заказчика.

После правок CSS на staging сбрасывать Bitrix CSS-cache (`bitrix/cache/css/…`), иначе отдаётся старый бандл `template_*_v1.css`.

---

## База (layout / typography / CTA)

| Класс | Назначение |
|---|---|
| `.dm-page` | Корень лендинга, CSS-переменные бренда |
| `.dm-container` | Контентная ширина |
| `.dm-section` / `.dm-section--alt` | Секция; `--alt` = фон `#f5f6f8`, задаёт `--dm-surface` |
| `.dm-h2` / `.dm-h3` / `.dm-lead` | Заголовки и лид |
| `.dm-btn` + `primary` / `outline` / `secondary` / `lg` | Кнопки |
| `.dm-grid` + `dm-grid-2/3/4` | Сетки |
| `.dm-ico` | Красный круг 55px + SVG stroke |
| `.dm-center` / `.dm-mt-40` | Утилиты |

---

## Универсальные блоки (раньше)

### Hero — `.dm-hero`
Первый экран: H1, subtitle, `.dm-hero-benefits` + `.ico`, `.dm-hero-buttons`.

### Карточка преимущества — `.dm-card`
Иконка + h3 + p. На `--alt` секции фон белый.

### Плитка возможности — `.dm-feature`
Горизонтально: `.dm-ico` + текст.

### Кейс (полная статистика) — `.dm-case` + `.dm-case-stats`
Красная шапка `.dm-case-head`, тело со сеткой `.dm-case-stat` (число + подпись) и кнопкой.

### Тариф — `.dm-tariff` (+ `.is-featured`, `.dm-tariff-badge`)
Цена, срок, список с галочками, CTA.

### Этапы (крупные) — `.dm-timeline` + `.dm-step`
Зигзаг, круги 104px, пунктир. Не менять геометрию коннекторов без нужды — от неё зависит `.dm-timeline--sm`.

### Решение/шаблон — `.dm-solution`
Карточка продукта: заголовок, цена (можно `.dm-tariff-price`), ul, кнопка.

### Инструмент — `.dm-tool`
Лого/название внешней системы.

### FAQ — `.dm-faq` (+ details/summary по эталону страниц)

### Отзыв — `.dm-review`

### Финальный CTA — `.dm-cta` + Aspro `CALLBACK`

---

## Блоки, добавленные со страницы «Сайт-визитка» (2026-08-12)

Эталон разметки: `pages/uslugi-sozdanie-saytov-sayt-vizitka.html`

### География — `.dm-geo`
**Когда:** «работаем по России / регионам», текст + цифры + карта.

```html
<div class="dm-geo">
  <div class="dm-geo-copy">
    <p>…</p>
    <div class="dm-geo-stats">
      <div class="dm-stat"><span class="dm-stat-value">40+</span><span class="dm-stat-label">городов</span></div>
      <!-- … -->
    </div>
  </div>
  <div class="dm-geo-media">
    <div>
      <img class="dm-geo-map"
           src="/bitrix/templates/aspro_max/design-model/images/russia-map-clients.svg"
           alt="Карта России" width="900" height="586" loading="lazy">
    </div>
  </div>
</div>
```

Карта — SVG (`russia-map-clients.svg`), без тёмной подложки; фон = секция. PNG — запасной артефакт.

### SEO-пункты — `.dm-seo-item`
**Когда:** сетка коротких SEO/метод-карточек по центру.

```html
<div class="dm-grid">
  <div class="dm-seo-item">
    <span class="dm-ico" aria-hidden="true"><svg viewBox="0 0 24 24">…</svg></span>
    <h3>…</h3><p>…</p>
  </div>
</div>
```

### Отрасли / профессии — `.dm-industry`
**Когда:** много коротких ниш (на desktop 5 в ряд).

```html
<div class="dm-grid">
  <div class="dm-industry">
    <span class="dm-ico" aria-hidden="true"><svg …></svg></span>
    <h3>Медицина</h3>
  </div>
</div>
```

### Сравнительная таблица — `.dm-table-wrap` + `.dm-table`
**Когда:** платформы / сравнение параметров. Desktop: table; mobile ≤767: карточки через `data-label`.

```html
<div class="dm-table-wrap">
  <table class="dm-table">
    <thead><tr>
      <th>Платформа</th><th>Для чего</th><th>Стоимость</th><th>Срок</th><th>Преимущества</th>
    </tr></thead>
    <tbody>
      <tr>
        <td data-label="Платформа"><strong>Tilda</strong>
          <span class="dm-table-badge">Быстрый запуск</span></td>
        <td data-label="Для чего">…</td>
        <td data-label="Стоимость">от 25 000 ₽</td>
        <td data-label="Срок">от 7 дней</td>
        <td data-label="Преимущества">…</td>
      </tr>
    </tbody>
  </table>
</div>
```

Текст в ячейках переносится (`overflow-wrap: break-word`); на мобилке ширины колонок сбрасываются.

### Deliverable — `.dm-deliverable`
**Когда:** «что получите» — иконка слева, заголовок + текст.

```html
<div class="dm-deliverable">
  <span class="dm-ico" aria-hidden="true"><svg …></svg></span>
  <div><h3>…</h3><p>…</p></div>
</div>
```

### Команда — `.dm-team`
**Когда:** роли проекта (иконка, имя роли, описание).

```html
<div class="dm-team">
  <span class="dm-ico" aria-hidden="true"><svg …></svg></span>
  <h3>…</h3><p>…</p>
</div>
```

### Сертификат — `.dm-cert`
**Когда:** статусы/сертификаты (светлые карточки).

```html
<div class="dm-cert">
  <span class="dm-ico" aria-hidden="true"><svg …></svg></span>
  <h3>…</h3><p>…</p>
</div>
```

### Proof / доверие — `.dm-proof`
**Когда:** крупные цифры доверия (рейтинг, отзывы, объём). Фон `#dc1e28`, **без иконок**.

```html
<div class="dm-proof">
  <div class="dm-proof-value">1 место</div>
  <p>в рейтинге … <span class="dm-proof-meta">2024</span></p>
</div>
```

### Компактный таймлайн — `.dm-timeline.dm-timeline--sm`
**Когда:** короткие шаги «как это работает» (55px круги). Базовый `.dm-timeline` (104px) не ломать — коннекторы у `--sm` отдельные.

```html
<div class="dm-timeline dm-timeline--sm">
  <div class="dm-step">
    <div class="dm-step-media">
      <div class="dm-step-circle"><svg viewBox="0 0 24 24">…</svg></div>
      <span class="dm-step-num">1</span>
    </div>
    <div class="dm-step-body"><h3><span class="n">Шаг 1:</span> …</h3></div>
  </div>
  <!-- чётные шаги уезжают вправо сами -->
</div>
```

Подписи шагов имеют фон `--dm-surface`, чтобы пунктир уходил под текст.

### Кейс с одной метрикой — `.dm-case` + `.dm-case-metric`
**Когда:** компактное портфолио: одна ключевая цифра вместо сетки статов.

```html
<div class="dm-case">
  <div class="dm-case-head"><h3>Сайт стоматологии</h3><p>Медицина • Tilda</p></div>
  <div class="dm-case-body">
    <div class="dm-case-metric">
      <b class="dm-case-metric-value">+45</b>
      <span class="dm-case-metric-label">записей/мес</span>
    </div>
    <a href="/projects/" class="dm-btn dm-btn-outline">Смотреть</a>
  </div>
</div>
```

Метрика центрируется между шапкой и кнопкой (grid `minmax(20px,1fr)`). Для полной статистики по-прежнему `.dm-case-stats`.

### Оффер / тип услуги — `.dm-offer`
**Когда:** сетка типов продукта (небольшой / средний / B2B…): красная шапка как у портфолио + 3 параметра (срок / цена / конверсия) + CTA.  
Эталон: блок «Какие интернет-магазины мы разрабатываем» на `internet-magazin`; визуально рядом с `.dm-case` + `.dm-case-metric`.

```html
<div class="dm-grid dm-grid-2">
  <div class="dm-offer">
    <div class="dm-offer-head">
      <h3>Небольшой магазин</h3>
      <p>Старт в e-commerce, нишевые товары до 500 SKU</p>
    </div>
    <div class="dm-offer-body">
      <div class="dm-offer-specs">
        <div class="dm-offer-spec"><b>30-40 дней</b><span>Срок</span></div>
        <div class="dm-offer-spec"><b>от 120 000 ₽</b><span>Цена</span></div>
        <div class="dm-offer-spec"><b>2-4%</b><span>Конверсия</span></div>
      </div>
      <span class="dm-btn dm-btn-outline"
        data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">Подробнее</span>
    </div>
  </div>
</div>
```

Сетка — **2 в ряд** (`dm-grid-2`), чтобы длинные значения (напр. «индивидуально») не вылезали из плиток. Шапка — акцент бренда; «Для чего» в `<p>` шапки. Параметры — компактные плитки как у `.dm-case-stat`.

---

## Чеклист новой страницы услуг

1. Прочитать этот каталог и соседний эталон в `pages/`.
2. Собрать HTML только на `dm-*`.
3. Тексты — дословно из исходника; иконки — SVG; CTA — Aspro CALLBACK.
4. Если нужен новый блок — CSS в `design-model.css` + запись в этот файл.
5. Sync DETAIL_TEXT элемента + сброс CSS-cache Bitrix.
6. «Задеплой» = commit + push ветки.
