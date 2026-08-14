# Внедрение Битрикс24 (раздел) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Новый корневой раздел «Внедрение Битрикс24» (`vnedrenie-bitrix24`) с посадочной `.dm-page` на remote.

**Architecture:** HTML из исходника itweb-ai на существующих `dm-*` → Bitrix section `DESCRIPTION` + meta через `dm-sync-page.sh` → nested-set rebuild в `dm-sync-upsert.php`.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-vnedrenie-bitrix24-section-page-design.md`

## Global Constraints

- Тексты дословно из https://itweb-ai.acrobat.test-itweb.ru/vnedrenie-bitrix24/
- H1 и H2 с «2026»; акция без таймера, «до конца месяца»
- SVG only; без формы/телефонов; CTA = CALLBACK
- Дочерние не создавать
- Worktree `.worktrees/seo-korporativnyy-sayt`
- Sync только remote; commit по «задеплой»
- Не вырезать «1С»

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-vnedrenie-bitrix24.html` | Create |
| `scripts/dm-pages.manifest.json` | Add section `vnedrenie-bitrix24` |
| `scripts/dm-sync-upsert.php` | Nested-set rebuild after section insert |
| Remote iblock 21 section | CREATE + DESCRIPTION + meta |

---

### Task 1: HTML + manifest + remote ReSort

- [ ] **Step 1:** Build `.dm-page` with 7 sections from spec
- [ ] **Step 2:** Manifest `kind: section`, parent null, meta from spec
- [ ] **Step 3:** Nested-set rebuild in `dm-sync-upsert.php` (remote Bitrix CLI ReSort is broken)
- [ ] **Step 4:** Verify no emoji/`<form>`/`tel:+`

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code vnedrenie-bitrix24`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/vnedrenie-bitrix24/` → 200 + `.dm-page` + H1 2026 + CALLBACK + «1С»
- [ ] **Step 3:** Report; wait for «задеплой»

Do NOT commit.
