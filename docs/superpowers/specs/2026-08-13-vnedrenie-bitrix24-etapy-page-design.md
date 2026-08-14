# Design: страница «Этапы внедрения Битрикс24»

Дата: 2026-08-13  
Статус: согласовано в диалоге

## Цель

Элемент в разделе `vnedrenie-bitrix24` по `.dm-page` из https://itweb-ai.acrobat.test-itweb.ru/vnedrenie-bitrix24/etapy/.

## Контекст

- Раздел iblock **21**: CODE `vnedrenie-bitrix24`, ID **163**
- Элемент: CODE `etapy`, NAME «Этапы внедрения Битрикс24»
- URL: `/services/vnedrenie-bitrix24/etapy/`
- Контент: `DETAIL_TEXT`, type html
- Worktree: `.worktrees/seo-korporativnyy-sayt`

## Решения

| Тема | Решение |
|---|---|
| H1 | «4 этапа внедрения Битрикс24 за 14 дней» |
| Год | «в 2026 году» вместо дыры CMS |
| Акция | как на разделе: без таймера, «до конца месяца», сетка 2×2 |
| 4 шага (обзор) | `.dm-timeline` без кнопок |
| Детализация | `.dm-solution` + списки + CALLBACK |
| Сертификаты | `/documents/` |
| Прочие «Подробнее»/кейсы/тест | CALLBACK |
| CTA | без формы и телефонов |
| CSS | только существующие `dm-*` |
| Sync | remote; commit по «задеплой» |

**Meta:**
- Title: `Основные этапы внедрения Битрикс24 | ITWeb 2026`
- Description: `4 этапа внедрения Битрикс24: аудит, КП, настройка, обучение. Срок от 14 дней. Гарантия результата 12 месяцев. Бесплатный аудит процессов. Звоните сейчас!`

## Маппинг

| # | Секция | Блок |
|---|---|---|
| 1 | Hero | `.dm-hero` |
| 2 | Почему методика | `.dm-card` |
| 3 | 4 шага + proof | `.dm-timeline` + `.dm-proof` |
| 4 | Детализация | `.dm-solution` |
| 5 | Почему лучше | `.dm-card` |
| 6 | FAQ | `.dm-faq` |
| 7 | Акция | `.dm-card` 2×2 |
| 8 | CTA | `.dm-cta` |

## Артефакты

- `bitrix/templates/aspro_max/design-model/pages/uslugi-vnedrenie-bitrix24-etapy.html`
- Manifest: `kind: element`, `section: vnedrenie-bitrix24`
- Sync: `scripts/dm-sync-page.sh --env remote --code etapy`

## Вне scope

- Дочерние URL исходника (`vyavlenie-potrebnostej` и т.д.)
- Таймер акции, новые CSS, local sync, commit без «задеплой»

## Критерии готовности

- [ ] HTML на `dm-*`, тексты дословно, SVG, CALLBACK, «1С»
- [ ] Элемент создан, DETAIL_TEXT + meta
- [ ] URL 200, H1 из исходника, акция без таймера
