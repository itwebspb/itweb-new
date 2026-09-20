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
        self.assertNotRegex(self.html, r"<(?:style|script|form)\\b")
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
