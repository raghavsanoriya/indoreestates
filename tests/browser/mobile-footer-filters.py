"""Check compact mobile footer and native filter sheet without changing data."""
import argparse
import json
from pathlib import Path
from urllib.parse import parse_qs, urlparse

from playwright.sync_api import sync_playwright


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="https://indoreestates.com")
    parser.add_argument("--candidate-assets", action="store_true")
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[2]
    output = root / "output/playwright/mobile-workspace"
    output.mkdir(parents=True, exist_ok=True)
    checks = []
    errors = []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 390, "height": 844})
        page.on("pageerror", lambda error: errors.append(str(error)))
        try:
            page.goto(args.base, wait_until="networkidle")
            before_height = page.locator(".o3-footer").bounding_box()["height"]
            desktop_before = {}
            for width in [768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                page.evaluate("new Promise(requestAnimationFrame)")
                page.evaluate("document.fonts.ready")
                page.locator(".o3-footer").evaluate("el => Promise.all(el.getAnimations({subtree:true}).filter(a => a.effect.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {})))")
                desktop_before[width] = page.locator(".o3-footer").evaluate("el => ({width:el.offsetWidth,height:el.offsetHeight})")
            if args.candidate_assets:
                for asset in ["css/mobile-app.css", "js/mobile-app.js"]:
                    page.route("**/" + asset + "*", lambda route, request, asset=asset: route.fulfill(path=str(root / "public" / asset)))
                page.reload(wait_until="networkidle")
            for width in [320, 390, 430, 767, 768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                page.evaluate("new Promise(requestAnimationFrame)")
                page.evaluate("document.fonts.ready")
                footer = page.locator(".o3-footer")
                footer.evaluate("el => Promise.all(el.getAnimations({subtree:true}).filter(a => a.effect.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {})))")
                if width < 768:
                    assert footer.locator(".row").evaluate("el => getComputedStyle(el).display") == "grid"
                    assert footer.bounding_box()["height"] < 700, width
                    assert page.evaluate("document.documentElement.scrollWidth <= innerWidth + 1"), width
                    if args.candidate_assets and width == 390:
                        assert footer.bounding_box()["height"] < before_height * 0.7
                else:
                    actual = footer.evaluate("el => ({width:el.offsetWidth,height:el.offsetHeight})")
                    assert actual == desktop_before[width], (width, actual, desktop_before[width])
                checks.append(f"Footer at {width}px")
            page.set_viewport_size({"width": 390, "height": 844})
            page.locator(".o3-footer").screenshot(path=str(output / "compact-mobile-footer.png"))
            page.goto(args.base + "/properties", wait_until="networkidle")
            for width in [320, 390, 430, 767]:
                page.set_viewport_size({"width": width, "height": 844})
                opener = page.get_by_role("button", name="Search & filters")
                opener.click()
                sheet = page.get_by_role("dialog", name="Filters", exact=True)
                assert sheet.is_visible()
                box = sheet.bounding_box()
                assert box["height"] <= 844 * 0.88 + 1
                search = sheet.get_by_role("button", name="Search", exact=True).bounding_box()
                assert box["y"] < search["y"] and search["y"] + search["height"] <= 845
                sheet.get_by_label("Keyword", exact=True).fill("Vijay")
                page.keyboard.press("Shift+Tab")
                assert sheet.evaluate("el => el.contains(document.activeElement)")
                page.keyboard.press("Escape")
                assert not sheet.is_visible()
                opener.click()
                assert sheet.get_by_label("Keyword", exact=True).input_value() == "Vijay"
                sheet.get_by_role("button", name="Close filters").click()
                opener.click()
                page.mouse.click(8, 8)
                assert not sheet.is_visible()
                checks.append(f"Filter sheet, focus, close and retained input at {width}px")
            page.set_viewport_size({"width": 390, "height": 844})
            page.get_by_role("button", name="Search & filters").click()
            sheet = page.get_by_role("dialog", name="Filters", exact=True)
            sheet.get_by_label("Keyword", exact=True).fill("")
            sheet.get_by_label("Sale / Rent", exact=True).select_option("sale")
            page.screenshot(path=str(output / "mobile-filter-popup.png"))
            sheet.get_by_role("button", name="Search", exact=True).click()
            page.wait_for_load_state("networkidle")
            assert parse_qs(urlparse(page.url).query)["purpose"] == ["sale"]
            page.get_by_role("button", name="Search & filters").click()
            assert page.get_by_label("Sale / Rent", exact=True).input_value() == "sale"
            page.get_by_role("dialog").get_by_role("link", name="Reset", exact=True).click()
            page.wait_for_load_state("networkidle")
            assert not urlparse(page.url).query
            page.get_by_role("button", name="Search & filters").click()
            page.get_by_label("Keyword", exact=True).fill("Retained on resize")
            page.set_viewport_size({"width": 1440, "height": 950})
            page.wait_for_function("!document.querySelector('.mobile-filter-dialog').open")
            assert page.locator("aside .filter-sidebar").is_visible()
            assert page.get_by_label("Keyword", exact=True).input_value() == "Retained on resize"
            assert not page.get_by_role("button", name="Close filters").is_visible()
            checks.append("Search, Reset and desktop sidebar restoration")
            assert not errors, errors
        finally:
            browser.close()
            (output / "public-results.json").write_text(json.dumps({"checks": checks, "errors": errors}, indent=2))
    print(json.dumps({"passed": len(checks), "errors": errors}))


if __name__ == "__main__":
    main()
