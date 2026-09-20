import json
import re
import unittest
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PAGE = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka-kontentnaya.html"
PARENT = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-podderzhka.html"
MANIFEST = ROOT / "scripts/dm-pages.manifest.json"
CSS = ROOT / "bitrix/templates/aspro_max/css/design-model.css"
SEF = ROOT / "services/index.php"


class IdCollector(HTMLParser):
    def __init__(self):
        super().__init__()
        self.ids = []
        self.classes = []
        self.tags = []

    def handle_starttag(self, tag, attrs):
        self.tags.append(tag)
        attr = dict(attrs)
        if "id" in attr:
            self.ids.append(attr["id"])
        if "class" in attr:
            self.classes.extend(attr["class"].split())


class ContentSupportPageTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.html = PAGE.read_text(encoding="utf-8")
        cls.parent = PARENT.read_text(encoding="utf-8")
        cls.manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
        cls.css = CSS.read_text(encoding="utf-8")
        cls.sef = SEF.read_text(encoding="utf-8")
        parser = IdCollector()
        parser.feed(cls.html)
        cls.ids = parser.ids
        cls.classes = parser.classes
        cls.tags = parser.tags

    def test_manifest_registers_child_element(self):
        entry = next(page for page in self.manifest["pages"] if page["code"] == "kontentnaya")
        self.assertEqual(entry["kind"], "element")
        self.assertEqual(entry["section"], "podderzhka")
        self.assertEqual(entry["html"], PAGE.name)
        self.assertIn("контент", entry["meta_description"].lower())
        codes = [page["code"] for page in self.manifest["pages"]]
        self.assertEqual(len(codes), len(set(codes)))

    def test_uses_design_model_contract(self):
        self.assertTrue(self.html.startswith('<div class="dm-page">'))
        self.assertIn("<h1>Контентная поддержка сайта под ключ</h1>", self.html)
        self.assertEqual(self.html.count("<h1>"), 1)
        self.assertNotRegex(self.html, r"<(?:style|script|form)\b")
        self.assertNotRegex(self.html, r'style="')
        self.assertNotRegex(self.html, r"[\U0001F300-\U0001FAFF]")
        self.assertEqual(len(re.findall(r"<section\b", self.html)), 10)
        non_dm = [
            cls
            for cls in self.classes
            if cls
            not in {"ico", "n", "is-featured"}
            and not cls.startswith("dm-")
        ]
        self.assertEqual(non_dm, [])

    def test_has_required_sections_and_callbacks(self):
        for section_id in ("dm-scope", "dm-tariffs", "dm-cases", "dm-steps", "dm-form"):
            self.assertIn(f'id="{section_id}"', self.html)
        self.assertEqual(len(self.ids), len(set(self.ids)))
        callbacks = re.findall(
            r'<button type="button"[^>]*data-param-form_id="CALLBACK"',
            self.html,
        )
        self.assertGreaterEqual(len(callbacks), 11)
        self.assertNotIn("<span class=\"dm-btn", self.html)
        self.assertIn('data-event="jqm"', self.html)
        self.assertIn('data-name="callback"', self.html)

    def test_has_eight_schema_faq_items(self):
        self.assertIn('itemtype="https://schema.org/FAQPage"', self.html)
        self.assertEqual(len(re.findall(r'class="dm-faq-item"', self.html)), 8)
        self.assertEqual(self.html.count('itemtype="https://schema.org/Question"'), 8)
        self.assertEqual(self.html.count('itemtype="https://schema.org/Answer"'), 8)
        self.assertEqual(self.html.count('itemprop="name"'), 8)
        self.assertEqual(self.html.count('itemprop="text"'), 8)

    def test_url_contract_and_existing_links(self):
        self.assertIn('"detail" => "#SECTION_CODE_PATH#/#ELEMENT_CODE#/"', self.sef)
        self.assertIn('href="/services/podderzhka/kontentnaya/"', self.parent)
        self.assertNotIn("/services/podderzhka/kontentnaya//", self.parent)
        self.assertNotIn("/services/podderzhka/tekhnicheskaya/", self.html)
        self.assertIn('href="/services/prodvizhenie-sayta/seo-prodvizhenie/"', self.html)
        self.assertIn('href="/services/podderzhka/"', self.html)
        self.assertNotIn("/uslugi/", self.html)
        hrefs = re.findall(r'href="([^"]+)"', self.html)
        fragments = [href[1:] for href in hrefs if href.startswith("#")]
        for fragment in fragments:
            self.assertIn(fragment, self.ids)

    def test_mobile_grid_override_exists(self):
        self.assertIn(".dm-page .dm-grid-3 { grid-template-columns: minmax(0, 1fr); }", self.css)


if __name__ == "__main__":
    unittest.main()
