import json
import unittest
from collections import Counter
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PAGE = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-tekhnicheskaya.html"
MANIFEST = ROOT / "scripts/dm-pages.manifest.json"

EXPECTED_NAME = "Техническая поддержка сайта"
EXPECTED_META_TITLE = "Техническая поддержка сайта и абонентское обслуживание | Ай Ти Веб"
EXPECTED_META_DESCRIPTION = (
    "Техническая поддержка и обслуживание сайтов под ключ: обновления, бэкапы, "
    "мониторинг 24/7, правки контента, исправление ошибок. От 15 000 ₽ в месяц."
)


class TechnicalSupportHTMLParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.class_counts = Counter()
        self.featured_tariffs = 0
        self.callback_elements = []
        self.case_links = []
        self._in_cases = False

    def handle_starttag(self, tag, attrs):
        attributes = dict(attrs)
        classes = set(attributes.get("class", "").split())
        self.class_counts.update(classes)

        if "dm-tariff" in classes and "is-featured" in classes:
            self.featured_tariffs += 1
        callback_attributes = ("data-event", "data-param-form_id", "data-name")
        if tag == "button" or any(name in attributes for name in callback_attributes):
            self.callback_elements.append(attributes)
        if tag == "section" and attributes.get("id") == "dm-cases":
            self._in_cases = True
        elif self._in_cases and tag == "a":
            self.case_links.append(attributes.get("href"))

    def handle_endtag(self, tag):
        if self._in_cases and tag == "section":
            self._in_cases = False


class TechnicalSupportPageTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.html = PAGE.read_text(encoding="utf-8")
        cls.manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
        cls.parser = TechnicalSupportHTMLParser()
        cls.parser.feed(cls.html)

    def test_manifest_registers_child_element(self):
        entry = next(page for page in self.manifest["pages"] if page["code"] == "tekhnicheskaya")
        self.assertEqual(entry["kind"], "element")
        self.assertEqual(entry["section"], "podderzhka")
        self.assertEqual(entry["html"], PAGE.name)
        self.assertEqual(entry["name"], EXPECTED_NAME)
        self.assertEqual(entry["meta_title"], EXPECTED_META_TITLE)
        self.assertEqual(entry["meta_description"], EXPECTED_META_DESCRIPTION)

    def test_uses_design_model_contract(self):
        self.assertTrue(self.html.startswith('<div class="dm-page">'))
        self.assertIn("<h1>Техническая поддержка сайта под ключ</h1>", self.html)
        self.assertNotRegex(self.html, r"<(?:style|script|form)\b")
        self.assertNotRegex(self.html, r"[\U0001F300-\U0001FAFF]")

    def test_has_required_sections(self):
        for section_id in ("dm-support-scope", "dm-tariffs", "dm-cases", "dm-steps", "dm-form"):
            self.assertIn(f'id="{section_id}"', self.html)

    def test_has_exact_component_counts(self):
        expected_counts = {
            "dm-solution": 3,
            "dm-card": 12,
            "dm-tariff": 3,
            "dm-case": 3,
            "dm-step": 6,
            "dm-tool": 8,
        }
        for class_name, expected_count in expected_counts.items():
            with self.subTest(class_name=class_name):
                self.assertEqual(self.parser.class_counts[class_name], expected_count)
        self.assertEqual(self.parser.featured_tariffs, 1)

    def test_case_section_has_exact_project_links(self):
        self.assertEqual(len(self.parser.case_links), 4)
        self.assertEqual(self.parser.case_links, ["/projects/"] * 4)

    def test_has_exact_callback_elements_with_required_attributes(self):
        self.assertEqual(len(self.parser.callback_elements), 6)
        for attributes in self.parser.callback_elements:
            with self.subTest(attributes=attributes):
                self.assertEqual(attributes.get("data-event"), "jqm")
                self.assertEqual(attributes.get("data-param-form_id"), "CALLBACK")
                self.assertEqual(attributes.get("data-name"), "callback")

    def test_has_eight_schema_faq_items(self):
        self.assertIn('itemtype="https://schema.org/FAQPage"', self.html)
        self.assertEqual(self.parser.class_counts["dm-faq-item"], 8)


if __name__ == "__main__":
    unittest.main()
