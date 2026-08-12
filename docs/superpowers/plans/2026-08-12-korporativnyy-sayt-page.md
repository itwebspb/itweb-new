# Корпоративный сайт — Implementation Plan

**Goal:** Элемент `korporativnyy-sayt` в section 158 на `.dm-page`; sync только remote.

**Spec:** `docs/superpowers/specs/2026-08-12-korporativnyy-sayt-page-design.md`

## Constraints

- Тексты дословно из `Korporativniy.html`; SVG; CALLBACK; remote only; commit по «задеплой»
- Паттерн: `uslugi-sozdanie-saytov-sayt-vizitka.html`
- Featured тариф: «Бизнес» 250 000 ₽

## Tasks

### Task 1: HTML + manifest
- [ ] Build full page with all sections
- [ ] Add `korporativnyy-sayt` to `scripts/dm-pages.manifest.json`

### Task 2: Remote sync
- [ ] `scripts/dm-sync-page.sh --env remote --code korporativnyy-sayt`
- [ ] Verify URL 200 + dm-page + meta + featured
