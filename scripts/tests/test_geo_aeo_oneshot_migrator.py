import re
import subprocess
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
MIGRATOR = ROOT / "scripts/migrate-geo-aeo-oneshot.php"
GEO = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-prodvizhenie-ai-geo.html"
AEO = ROOT / "bitrix/templates/aspro_max/design-model/pages/uslugi-prodvizhenie-ai-aeo.html"


class GeoAeoOneshotMigratorTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.src = MIGRATOR.read_text(encoding="utf-8")

    def test_file_is_self_contained_php(self):
        self.assertTrue(MIGRATOR.is_file())
        self.assertTrue(self.src.startswith("<?php"))
        self.assertIn("prolog_before.php", self.src)
        self.assertNotRegex(self.src, r"#!/usr/bin/env php")

    def test_browser_only_refuses_cli(self):
        self.assertIn("PHP_SAPI", self.src)
        self.assertIn("'cli'", self.src)

    def test_auth_is_admin_or_token(self):
        self.assertIn("IsAdmin", self.src)
        self.assertIn("IsAuthorized", self.src)
        self.assertIn("hash_equals", self.src)
        self.assertIn("MIGRATE_GEO_AEO_TOKEN", self.src)
        self.assertIn("NOT_CHECK_PERMISSIONS", self.src)

    def test_only_geo_and_aeo_elements(self):
        self.assertIn("'geo'", self.src)
        self.assertIn("'aeo'", self.src)
        self.assertIn("uslugi-prodvizhenie-ai-geo.html", self.src)
        self.assertIn("uslugi-prodvizhenie-ai-aeo.html", self.src)
        self.assertIn("prodvizhenie-v-ai-poiske", self.src)
        self.assertIn("21", self.src)
        self.assertNotIn("prodvizhenie-v-ii-poiske", self.src)
        self.assertNotRegex(self.src, r"CIBlockSection::Add")
        self.assertIsNone(re.search(r"INSERT\s+INTO\s+b_iblock_section", self.src))
        self.assertNotIn("design-model.css", self.src)
        self.assertNotIn("services/index.php", self.src)
        self.assertNotIn("uslugi-prodvizhenie-v-ai-poiske.html", self.src)

    def test_upsert_detail_text_active_html(self):
        self.assertIn("DETAIL_TEXT", self.src)
        self.assertIn("DETAIL_TEXT_TYPE", self.src)
        self.assertRegex(self.src, r"['\"]html['\"]")
        self.assertRegex(self.src, r"ACTIVE['\"]?\s*=>\s*['\"]Y['\"]")
        self.assertIn("CIBlockElement", self.src)
        self.assertIn("unlink(__FILE__)", self.src)

    def test_parent_section_missing_is_error(self):
        self.assertIn("CIBlockSection", self.src)
        self.assertIsNotNone(re.search(r"раздел|не найден", self.src, flags=re.I))

    def test_source_html_files_exist(self):
        self.assertTrue(GEO.is_file())
        self.assertTrue(AEO.is_file())
        self.assertIn('class="dm-page"', GEO.read_text(encoding="utf-8")[:200])
        self.assertIn('class="dm-page"', AEO.read_text(encoding="utf-8")[:200])

    def test_php_syntax(self):
        result = subprocess.run(["php", "-l", str(MIGRATOR)], capture_output=True, text=True)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertIn("No syntax errors", result.stdout)


if __name__ == "__main__":
    unittest.main()
