# Technical Support Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Создать и опубликовать дочернюю страницу технической поддержки по адресу `/services/podderzhka/tekhnicheskaya/`.

**Architecture:** Статический HTML-источник на существующих `.dm-*` компонентах регистрируется в manifest как элемент iblock 21 внутри раздела `podderzhka`. Существующий `dm-sync-page.sh` копирует HTML/CSS и выполняет upsert `DETAIL_TEXT` и meta на staging.

**Tech Stack:** HTML5, schema.org FAQ, SVG, JSON manifest, Python `unittest`, Bash, Bitrix iblock 21.

## Global Constraints

- Использовать визуальный язык родительской страницы и существующий `design-model.css`.
- Не переносить inline CSS/JavaScript, emoji, самостоятельную форму и фиктивные контакты исходника.
- Все заявки открывают Aspro `CALLBACK`.
- Родительскую страницу и другие элементы iblock не изменять.
- После реализации всегда синхронизировать страницу на staging.

---

### Task 1: Статический контракт страницы

**Files:**
- Create: `scripts/tests/test_technical_support_page.py`
- Test: `scripts/tests/test_technical_support_page.py`

**Interfaces:**
- Consumes: `scripts/dm-pages.manifest.json` и будущий HTML-источник.
- Produces: повторяемую проверку структуры, контента и запретных конструкций.

- [x] **Step 1: Write the failing test**

Создать `unittest`, который:

```python
import json
import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PAGE = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-tekhnicheskaya.html"
MANIFEST = ROOT / "scripts/dm-pages.manifest.json"


class TechnicalSupportPageTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.html = PAGE.read_text(encoding="utf-8")
        cls.manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))

    def test_manifest_registers_child_element(self):
        entry = next(page for page in self.manifest["pages"] if page["code"] == "tekhnicheskaya")
        self.assertEqual(entry["kind"], "element")
        self.assertEqual(entry["section"], "podderzhka")
        self.assertEqual(entry["html"], PAGE.name)

    def test_uses_design_model_contract(self):
        self.assertTrue(self.html.startswith('<div class="dm-page">'))
        self.assertIn("<h1>Техническая поддержка сайта под ключ</h1>", self.html)
        self.assertNotRegex(self.html, r"<(?:style|script|form)\b")
        self.assertNotRegex(self.html, r"[\U0001F300-\U0001FAFF]")

    def test_has_required_sections_and_callbacks(self):
        for section_id in ("dm-support-scope", "dm-tariffs", "dm-cases", "dm-steps", "dm-form"):
            self.assertIn(f'id="{section_id}"', self.html)
        self.assertGreaterEqual(self.html.count('data-param-form_id="CALLBACK"'), 6)

    def test_has_eight_schema_faq_items(self):
        self.assertIn('itemtype="https://schema.org/FAQPage"', self.html)
        self.assertEqual(len(re.findall(r'class="dm-faq-item"', self.html)), 8)


if __name__ == "__main__":
    unittest.main()
```

- [x] **Step 2: Run test to verify it fails**

Run:

```bash
python3 -m unittest scripts.tests.test_technical_support_page -v
```

Expected: ERROR `FileNotFoundError` for `uslugi-podderzhka-tekhnicheskaya.html`.

- [x] **Step 3: Commit the red test**

```bash
git add scripts/tests/test_technical_support_page.py
git commit -m "Add technical support page contract test"
```

---

### Task 2: HTML source and Bitrix manifest

**Files:**
- Create: `bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-tekhnicheskaya.html`
- Modify: `scripts/dm-pages.manifest.json`
- Test: `scripts/tests/test_technical_support_page.py`

**Interfaces:**
- Consumes: existing `.dm-*` classes and `CALLBACK` data attributes.
- Produces: manifest code `tekhnicheskaya` and its complete `DETAIL_TEXT` source.

- [x] **Step 1: Add the manifest entry**

Перед section-объектом `podderzhka` добавить:

```json
{
  "kind": "element",
  "code": "tekhnicheskaya",
  "section": "podderzhka",
  "name": "Техническая поддержка сайта",
  "html": "uslugi-podderzhka-tekhnicheskaya.html",
  "meta_title": "Техническая поддержка сайта и абонентское обслуживание | Ай Ти Веб",
  "meta_description": "Техническая поддержка и обслуживание сайтов под ключ: обновления, бэкапы, мониторинг 24/7, правки контента, исправление ошибок. От 15 000 ₽ в месяц."
}
```

- [x] **Step 2: Implement the page with existing components**

Создать один `<div class="dm-page">` со следующей точной структурой:

- `.dm-hero`: H1, исходный лид, четыре `.dm-hero-benefit`, CALLBACK и `#dm-tariffs`;
- `#dm-support-scope`: три `.dm-solution` со списками для стабильности, развития и SEO-гигиены;
- преимущества: шесть `.dm-card`;
- `#dm-tariffs`: три `.dm-tariff`, средний `is-featured`;
- `#dm-cases`: три `.dm-case`, ссылки только `/projects/`;
- `#dm-steps`: шесть `.dm-step`;
- инструменты: восемь `.dm-tool`;
- результаты: шесть `.dm-card`;
- FAQ: `FAQPage`, восемь `<details class="dm-faq-item">`;
- `#dm-form`: `.dm-cta` и CALLBACK.

Для каждой визуальной иконки использовать inline:

```html
<span class="dm-ico" aria-hidden="true">
	<svg viewBox="0 0 24 24"><path d="..."/></svg>
</span>
```

Для каждой кнопки заявки использовать:

```html
<button type="button" class="dm-btn dm-btn-primary"
	data-event="jqm" data-param-form_id="CALLBACK" data-name="callback">...</button>
```

- [x] **Step 3: Run contract test**

Run:

```bash
python3 -m unittest scripts.tests.test_technical_support_page -v
```

Expected: `Ran 4 tests` and `OK`.

- [x] **Step 4: Validate manifest and forbidden constructs**

Run:

```bash
python3 -m json.tool scripts/dm-pages.manifest.json >/dev/null
rg -n '<(style|script|form)\\b|[\x{1F300}-\x{1FAFF}]' \
  bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-tekhnicheskaya.html
```

Expected: JSON command exits 0; `rg` exits 1 with no matches.

- [x] **Step 5: Commit implementation**

```bash
git add scripts/dm-pages.manifest.json \
  bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-tekhnicheskaya.html
git commit -m "Add technical support service page"
git push -u origin cursor/technical-support-page-e540
```

---

### Task 3: Staging sync and public verification

**Files:**
- No repository changes expected.
- Artifact: `/opt/cursor/artifacts/technical-support-page-verification.log`

**Interfaces:**
- Consumes: runtime secret `ITWEB_NEW_SSH_KEY`, `scripts/setup-ssh.sh`, `scripts/dm-sync-page.sh`.
- Produces: live Bitrix element and a verification log.

- [x] **Step 1: Configure SSH without exposing the secret**

Run `scripts/setup-ssh.sh`; if absent from the feature branch, restore the trusted version temporarily from commit `08a7792`, execute it, and remove the temporary file.

- [x] **Step 2: Sync only the new element and shared CSS**

Run:

```bash
DM_REMOTE_SSH=itweb-new-test \
  scripts/dm-sync-page.sh --env remote --code tekhnicheskaya --css
```

Expected: `ELEMENT_CREATED` or `ELEMENT_UPDATED`, then `OK` and `REMOTE done`.

- [x] **Step 3: Verify the public URL**

Fetch `https://itweb-new.acrobat.test-itweb.ru/services/podderzhka/tekhnicheskaya/` and assert:

- final HTTP status is 200;
- `.dm-page` and exact H1 exist;
- design-model CSS is registered and responds 200;
- IDs `dm-support-scope`, `dm-tariffs`, `dm-cases`, `dm-steps`, `dm-form` exist;
- schema.org FAQ contains exactly eight `.dm-faq-item`;
- remote HTML SHA-256 equals the committed source.

Write all PASS/FAIL lines to `/opt/cursor/artifacts/technical-support-page-verification.log`.

- [x] **Step 4: Final repository check**

Run:

```bash
git status --short --branch
git log -3 --oneline
```

Expected: clean branch `cursor/technical-support-page-e540`.
