import json
import re
import unittest
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PAGES = ROOT / "bitrix/templates/aspro_max/design-model/pages"
SECTION = PAGES / "uslugi-prodvizhenie-ai.html"
GEO = PAGES / "uslugi-prodvizhenie-ai-geo.html"
AEO = PAGES / "uslugi-prodvizhenie-ai-aeo.html"
MANIFEST = ROOT / "scripts/dm-pages.manifest.json"
CSS = ROOT / "bitrix/templates/aspro_max/css/design-model.css"
SEF = ROOT / "services/index.php"
HELPER = ROOT / "bitrix/templates/aspro_max/design-model/dm-html.php"


class IdCollector(HTMLParser):
    def __init__(self):
        super().__init__()
        self.ids = []
        self.classes = []

    def handle_starttag(self, tag, attrs):
        attr = dict(attrs)
        if "id" in attr:
            self.ids.append(attr["id"])
        if "class" in attr:
            self.classes.extend(attr["class"].split())


def parse(html: str) -> IdCollector:
    parser = IdCollector()
    parser.feed(html)
    return parser


class AiSearchPagesTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.section = SECTION.read_text(encoding="utf-8")
        cls.geo = GEO.read_text(encoding="utf-8")
        cls.aeo = AEO.read_text(encoding="utf-8")
        cls.manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
        cls.css = CSS.read_text(encoding="utf-8")
        cls.sef = SEF.read_text(encoding="utf-8")
        cls.section_meta = parse(cls.section)
        cls.geo_meta = parse(cls.geo)
        cls.aeo_meta = parse(cls.aeo)

    def _entry(self, code: str) -> dict:
        return next(page for page in self.manifest["pages"] if page["code"] == code)

    def test_manifest_registers_nested_hierarchy(self):
        parent = self._entry("prodvizhenie")
        section = self._entry("ai")
        geo = self._entry("geo")
        aeo = self._entry("aeo")
        self.assertEqual(parent["kind"], "section")
        self.assertIsNone(parent["parent"])
        self.assertEqual(parent["html"], SECTION.name)
        self.assertEqual(section["kind"], "section")
        self.assertEqual(section["parent"], "prodvizhenie")
        self.assertEqual(section["html"], SECTION.name)
        self.assertEqual(section["name"], "Продвижение в ИИ-поиске")
        self.assertIn("35 000", section["meta_description"])
        self.assertEqual(geo["kind"], "element")
        self.assertEqual(geo["section"], "ai")
        self.assertEqual(geo["html"], GEO.name)
        self.assertIn("geo", geo["meta_description"].lower())
        self.assertIn("35 000", geo["meta_description"])
        self.assertEqual(aeo["kind"], "element")
        self.assertEqual(aeo["section"], "ai")
        self.assertEqual(aeo["html"], AEO.name)
        self.assertIn("aeo", aeo["meta_description"].lower())
        self.assertIn("30 000", aeo["meta_description"])
        codes = [page["code"] for page in self.manifest["pages"]]
        self.assertEqual(len(codes), len(set(codes)))

    def test_uses_design_model_contract(self):
        for html, h1, classes, sections in (
            (self.section, "<h1>Продвижение в ИИ-поиске под ключ</h1>", self.section_meta.classes, 9),
            (self.geo, "<h1>GEO-продвижение в ИИ-поиске под ключ</h1>", self.geo_meta.classes, 12),
            (self.aeo, "<h1>AEO-продвижение сайтов под ключ</h1>", self.aeo_meta.classes, 13),
        ):
            with self.subTest(h1=h1):
                self.assertTrue(html.startswith('<div class="dm-page">'))
                self.assertIn(h1, html)
                self.assertEqual(html.count("<h1>"), 1)
                self.assertNotRegex(html, r"<(?:style|script|form)\b")
                self.assertNotRegex(html, r'style="')
                self.assertNotRegex(html, r"[\U0001F300-\U0001FAFF]")
                self.assertEqual(len(re.findall(r"<section\b", html)), sections)
                non_dm = [
                    cls
                    for cls in classes
                    if cls not in {"ico", "n", "is-featured"} and not cls.startswith("dm-")
                ]
                self.assertEqual(non_dm, [])

    def test_has_required_sections_and_callbacks(self):
        self.assertIn('id="dm-directions"', self.section)
        self.assertIn('id="dm-tariffs"', self.section)
        self.assertIn('id="dm-steps"', self.section)
        self.assertIn('id="dm-form"', self.section)
        self.assertIn("Готовы стать заметными в эпоху ИИ?", self.section)
        self.assertNotIn('id="dm-cases"', self.section)
        self.assertNotIn("class=\"dm-tool\"", self.section)
        for html, ids in (
            (self.geo, self.geo_meta.ids),
            (self.aeo, self.aeo_meta.ids),
        ):
            with self.subTest(page=html[:40]):
                for section_id in ("dm-scope", "dm-tariffs", "dm-cases", "dm-steps", "dm-form"):
                    self.assertIn(f'id="{section_id}"', html)
                self.assertEqual(len(ids), len(set(ids)))
                callbacks = re.findall(
                    r'<button type="button"[^>]*data-param-form_id="CALLBACK"',
                    html,
                )
                self.assertGreaterEqual(len(callbacks), 11)
                self.assertNotIn('<span class="dm-btn', html)
                self.assertIn('data-event="jqm"', html)
                self.assertIn('data-name="callback"', html)
                self.assertNotIn('href="#"', html)

        self.assertEqual(len(self.section_meta.ids), len(set(self.section_meta.ids)))
        self.assertGreaterEqual(
            len(re.findall(r'<button type="button"[^>]*data-param-form_id="CALLBACK"', self.section)),
            7,
        )
        self.assertNotIn('<span class="dm-btn', self.section)

    def test_source_copy_and_tariffs(self):
        self.assertIn("AEO Старт", self.section)
        self.assertIn("GEO + AEO Комплекс", self.section)
        self.assertIn("GEO Enterprise", self.section)
        self.assertIn("от 35 000 ₽/мес", self.section)
        self.assertIn("от 70 000 ₽/мес", self.section)
        self.assertIn("от 120 000 ₽/мес", self.section)
        self.assertIn("Рекомендуемый", self.section)
        self.assertIn("GEO Старт", self.geo)
        self.assertIn("GEO Комплекс", self.geo)
        self.assertIn("GEO Индивидуальный", self.geo)
        self.assertIn("0% → 38%", self.geo)
        self.assertIn("Generative Engine Optimization", self.geo)
        self.assertIn("AEO Старт", self.aeo)
        self.assertIn("AEO + Контент", self.aeo)
        self.assertIn("AEO Индивидуальный", self.aeo)
        self.assertIn("от 30 000 ₽/мес", self.aeo)
        self.assertIn("от 40 000 ₽/мес", self.aeo)
        self.assertIn("Answer Engine Optimization", self.aeo)
        self.assertNotIn("</a>.", self.aeo)
        self.assertIn("class=\"dm-table dm-table--4\"", self.section)
        self.assertIn("class=\"dm-table dm-table--4\"", self.geo)
        self.assertIn("class=\"dm-table dm-table--4\"", self.aeo)

    def test_has_eight_schema_faq_items(self):
        for html in (self.section, self.geo, self.aeo):
            with self.subTest(page=html[html.find("<h1>") : html.find("</h1>") + 5]):
                self.assertIn('itemtype="https://schema.org/FAQPage"', html)
                self.assertEqual(len(re.findall(r'class="dm-faq-item"', html)), 8)
                self.assertEqual(html.count('itemtype="https://schema.org/Question"'), 8)
                self.assertEqual(html.count('itemtype="https://schema.org/Answer"'), 8)
                self.assertEqual(html.count('itemprop="name"'), 8)
                self.assertEqual(html.count('itemprop="text"'), 8)

    def test_url_contract_and_existing_links(self):
        self.assertIn('"section" => "#SECTION_CODE_PATH#/"', self.sef)
        self.assertIn('"detail" => "#SECTION_CODE_PATH#/#ELEMENT_CODE#/"', self.sef)
        self.assertIn('href="/services/prodvizhenie/ai/geo/"', self.section)
        self.assertIn('href="/services/prodvizhenie/ai/aeo/"', self.section)
        self.assertNotIn("/services/prodvizhenie/ai/geo//", self.section)
        self.assertIn('href="/services/prodvizhenie/ai/aeo/"', self.geo)
        self.assertIn('href="/services/prodvizhenie-sayta/seo-prodvizhenie/"', self.geo)
        self.assertIn('href="/services/dopolnitelno/serm/"', self.geo)
        self.assertIn('href="/services/prodvizhenie/ai/geo/"', self.aeo)
        self.assertIn('href="/services/prodvizhenie-sayta/seo-prodvizhenie/"', self.aeo)
        self.assertIn('href="/services/dopolnitelno/audit-sayta/"', self.aeo)
        for html in (self.section, self.geo, self.aeo):
            self.assertNotIn("/uslugi/", html)
            self.assertNotIn("tel:+78001234567", html)
        for html, ids in (
            (self.section, self.section_meta.ids),
            (self.geo, self.geo_meta.ids),
            (self.aeo, self.aeo_meta.ids),
        ):
            hrefs = re.findall(r'href="([^"]+)"', html)
            fragments = [href[1:] for href in hrefs if href.startswith("#")]
            for fragment in fragments:
                self.assertIn(fragment, ids)

    def test_svg_icons_present_for_bitrix_restore(self):
        self.assertTrue(HELPER.is_file())
        for html in (self.section, self.geo, self.aeo):
            with self.subTest(svgs=html.count("<svg")):
                self.assertGreaterEqual(html.count("<svg"), 20)
                self.assertIn('class="dm-ico"', html)
                self.assertIn('class="ico"', html)

    def test_mobile_grid_override_exists(self):
        self.assertIn(".dm-page .dm-grid-3 { grid-template-columns: minmax(0, 1fr); }", self.css)
        self.assertIn(".dm-table.dm-table--4", self.css)


if __name__ == "__main__":
    unittest.main()
