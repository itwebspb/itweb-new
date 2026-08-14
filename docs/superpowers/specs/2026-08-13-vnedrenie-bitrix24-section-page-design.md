# Design: страница раздела «Внедрение Битрикс24»

Дата: 2026-08-13  
Статус: согласовано в диалоге

## Цель

Новый корневой раздел `/services/vnedrenie-bitrix24/` по `.dm-page` из https://itweb-ai.acrobat.test-itweb.ru/vnedrenie-bitrix24/, по модели разделов SEO / «Продвижение сайта» / «Создание сайтов».

## Контекст

- Новый раздел iblock **21**: CODE `vnedrenie-bitrix24`, NAME «Внедрение Битрикс24», корень (`parent: null`)
- URL обслуживает Aspro `news/services/section.php`
- `DESCRIPTION` выводится в `text_after_items` **после** списка дочерних (сейчас список пустой — дочерние не создаём)
- Эталоны: `uslugi-seo-prodvizhenie.html`, `uslugi-prodvizhenie-sayta.html`, `BLOCKS.md`
- Worktree/ветка: `.worktrees/seo-korporativnyy-sayt` / `feature/seo-korporativnyy-sayt`
- После INSERT секции upsert пересобирает nested set (`LEFT_MARGIN`/`RIGHT_MARGIN`) — CLI Bitrix `ReSort` на remote падает

## Решения

| Тема | Решение |
|---|---|
| Контент | `DESCRIPTION` нового раздела, type html |
| Дочерние | не создаём; «Подробнее» / видео / кейс / FAQ-ссылки → CALLBACK |
| H1 | «Внедрение Битрикс24 под ключ за 14 дней в 2026 году» |
| NAME | «Внедрение Битрикс24» |
| Год в H2 | восстановить «2026» (дыра CMS «в  году») |
| Акция | оффер + «до конца месяца»; без таймера; «скидка 15% … до конца месяца» |
| Тексты | дословно из исходника |
| Иконки | SVG; без эмодзи |
| CTA | Aspro CALLBACK; без формы и телефонов |
| Два закрывающих блока | один `.dm-cta` |
| CSS | только существующие `dm-*` |
| Меню `/vnedrenie-bitrix24/` | не трогаем |
| Sync | только remote |
| Commit | по «задеплой» |

**Meta** (из `<head>` исходника):
- Title: `Внедрение Битрикс24 под ключ за 14 дней | ITWeb 2026`
- Description: `Внедрение Битрикс24 за 14 дней. Цена от 60 000 ₽. Топ-10 внедрений в 2026 году. Гарантия результата 12 месяцев. Бесплатный аудит процессов. Звоните сейчас!`

## Маппинг секций → dm-*

| # | Секция | Блок |
|---|---|---|
| 1 | Hero (4 бенефита, 2 кнопки) | `.dm-hero` |
| 2 | 5 проблем бизнеса | `.dm-card` + CALLBACK |
| 3 | 4 шага + срок/гарантия/поддержка | `.dm-timeline` + `.dm-proof` |
| 4 | Почему 427 компаний + 2 отзыва | `.dm-card` + `.dm-review` |
| 5 | Акция до конца месяца | `.dm-card` + CALLBACK |
| 6 | FAQ (4) | `.dm-faq`; «Все вопросы» → CALLBACK |
| 7 | Финальный CTA | `.dm-cta` + CALLBACK |

## Артефакты

- HTML: `bitrix/templates/aspro_max/design-model/pages/uslugi-vnedrenie-bitrix24.html`
- Manifest: `kind: section`, `code: vnedrenie-bitrix24`, `parent: null`
- Sync: `scripts/dm-sync-page.sh --env remote --code vnedrenie-bitrix24` + ReSort

## Вне scope

- Дочерние страницы (`etapy`, `integracii`, `doverie`, …)
- Правка меню «Битрикс24» и редирект со `/vnedrenie-bitrix24/`
- Правка `section.php`, таймер акции, новые CSS
- Sync на local / commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG, CALLBACK, «1С» на месте
- [ ] Раздел создан, `DESCRIPTION` + meta, дерево после ReSort
- [ ] URL 200; `.dm-page` на странице; H1 лендинга с 2026; акции без таймера
