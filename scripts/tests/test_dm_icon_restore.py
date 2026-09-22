import json
import re
import subprocess
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PAGES = ROOT / "bitrix/templates/aspro_max/design-model/pages"
HELPER = ROOT / "bitrix/templates/aspro_max/design-model/dm-html.php"
MANIFEST = ROOT / "scripts/dm-pages.manifest.json"

SUPPORT_PAGES = (
    ("kontentnaya", "uslugi-podderzhka-kontentnaya.html"),
    ("dorabotka", "uslugi-podderzhka-dorabotka.html"),
    ("tekhnicheskaya", "uslugi-podderzhka-tekhnicheskaya.html"),
    ("geo", "uslugi-prodvizhenie-ai-geo.html"),
    ("aeo", "uslugi-prodvizhenie-ai-aeo.html"),
)

ICON_SPAN = re.compile(
    r'<span class="(?:dm-ico|ico)"[^>]*>(.*?)</span>',
    re.DOTALL,
)


def php_restore(detail_text: str, code: str, pages_dir: Path) -> str:
    script = (
        "<?php\n"
        "require {helper};\n"
        "$detail = file_get_contents('php://stdin');\n"
        "echo dm_restore_detail_html($detail, {code}, {pages});\n"
    ).format(
        helper=json.dumps(str(HELPER)),
        code=json.dumps(code),
        pages=json.dumps(str(pages_dir)),
    )
    with tempfile.NamedTemporaryFile("w", suffix=".php", delete=False) as handle:
        handle.write(script)
        path = handle.name
    try:
        result = subprocess.run(
            ["php", path],
            input=detail_text.encode("utf-8"),
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            check=False,
        )
    finally:
        Path(path).unlink(missing_ok=True)
    if result.returncode != 0:
        raise AssertionError(result.stderr.decode("utf-8", "replace") or "php failed")
    return result.stdout.decode("utf-8")


class DmIconRestoreTest(unittest.TestCase):
    def test_helper_file_exists(self):
        self.assertTrue(HELPER.is_file(), "dm-html.php must restore stripped SVG icons")

    def test_source_pages_keep_svg_inside_icon_spans(self):
        for code, filename in SUPPORT_PAGES:
            with self.subTest(code=code):
                html = (PAGES / filename).read_text(encoding="utf-8")
                spans = ICON_SPAN.findall(html)
                self.assertGreaterEqual(len(spans), 8)
                empty = [span for span in spans if "<svg" not in span]
                self.assertEqual(empty, [])
                self.assertGreaterEqual(html.count("<svg"), 20)

    def test_restores_svg_when_bitrix_editor_strips_icons(self):
        source = (PAGES / "uslugi-podderzhka-kontentnaya.html").read_text(encoding="utf-8")
        stripped = re.sub(r"<svg\b[^>]*>.*?</svg>", "", source, flags=re.DOTALL)
        self.assertNotIn("<svg", stripped)
        self.assertIn('class="ico"', stripped)
        restored = php_restore(stripped, "kontentnaya", PAGES)
        self.assertIn("<svg", restored)
        self.assertGreaterEqual(restored.count("<svg"), 20)
        self.assertIn("Контентная поддержка сайта под ключ", restored)

    def test_leaves_intact_detail_text_that_already_has_svg(self):
        html = (PAGES / "uslugi-sozdanie-saytov-lending.html").read_text(encoding="utf-8")
        restored = php_restore(html, "lending", PAGES)
        self.assertEqual(restored, html)

    def test_manifest_registers_icon_restore_elements(self):
        manifest = json.loads(MANIFEST.read_text(encoding="utf-8"))
        by_code = {page["code"]: page for page in manifest["pages"]}
        expected_section = {
            "kontentnaya": "podderzhka",
            "dorabotka": "podderzhka",
            "tekhnicheskaya": "podderzhka",
            "geo": "prodvizhenie-v-ai-poiske",
            "aeo": "prodvizhenie-v-ai-poiske",
        }
        for code, filename in SUPPORT_PAGES:
            with self.subTest(code=code):
                entry = by_code[code]
                self.assertEqual(entry["kind"], "element")
                self.assertEqual(entry["section"], expected_section[code])
                self.assertEqual(entry["html"], filename)


if __name__ == "__main__":
    unittest.main()
