# Этапы внедрения Битрикс24 — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Элемент `etapy` в разделе `vnedrenie-bitrix24` с посадочной `.dm-page` на remote.

**Architecture:** HTML из исходника itweb-ai/etapy на существующих `dm-*` → `DETAIL_TEXT` + meta через `dm-sync-page.sh`.

**Tech Stack:** Bitrix Aspro Max, design-model (`dm-page`), remote SSH + mysql sync.

**Spec:** `docs/superpowers/specs/2026-08-13-vnedrenie-bitrix24-etapy-page-design.md`

## Global Constraints

- Тексты дословно из https://itweb-ai.acrobat.test-itweb.ru/vnedrenie-bitrix24/etapy/
- H1 «4 этапа внедрения Битрикс24 за 14 дней»; «в 2026 году»
- Акция без таймера, как на разделе
- Timeline обзора без кнопок; SVG; CALLBACK; без формы/телефонов
- Не вырезать «1С»
- Worktree `.worktrees/seo-korporativnyy-sayt`; sync remote; commit по «задеплой»

## File map

| File | Role |
|---|---|
| `bitrix/templates/aspro_max/design-model/pages/uslugi-vnedrenie-bitrix24-etapy.html` | Create |
| `scripts/dm-pages.manifest.json` | Add element `etapy` |

---

### Task 1: HTML + manifest

- [ ] **Step 1:** Build `.dm-page` with 8 sections from spec
- [ ] **Step 2:** Manifest element, section `vnedrenie-bitrix24`
- [ ] **Step 3:** Verify no emoji/`<form>`/`tel:+`

Do NOT commit.

---

### Task 2: Remote sync + verify

- [ ] **Step 1:** `scripts/dm-sync-page.sh --env remote --code etapy`
- [ ] **Step 2:** Verify `https://itweb-new.acrobat.test-itweb.ru/services/vnedrenie-bitrix24/etapy/` → 200 + `.dm-page` + H1 + «1С»
- [ ] **Step 3:** Report; wait for «задеплой»

Do NOT commit.
