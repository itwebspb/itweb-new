import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SEF = ROOT / "services/index.php"
SECTION_PAGE = (
    ROOT / "bitrix/templates/aspro_max/components/bitrix/news/services/section.php"
)


class ServicesSectionListTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.sef = SEF.read_text(encoding="utf-8")
        cls.section_page = SECTION_PAGE.read_text(encoding="utf-8")

    def test_section_list_does_not_print_descriptions(self):
        self.assertIn('"SHOW_SECTION_PREVIEW_DESCRIPTION" => "N"', self.sef)
        self.assertNotIn('"SHOW_SECTION_PREVIEW_DESCRIPTION" => "Y"', self.sef)

    def test_section_pages_still_render_description_for_dm_page(self):
        self.assertIn("text_after_items", self.section_page)
        self.assertIn("$arSection['DESCRIPTION']", self.section_page)
