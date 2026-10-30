"""Verify compact authenticated dashboard UI using a local preview account only."""
import argparse
import json
import subprocess
from pathlib import Path
from urllib.parse import urlparse

from playwright.sync_api import sync_playwright


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="http://127.0.0.1:8006")
    args = parser.parse_args()
    assert urlparse(args.base).hostname in ["127.0.0.1", "localhost"], "Local preview only"
    root = Path(__file__).resolve().parents[2]
    output = root / "output/playwright/mobile-workspace"
    output.mkdir(parents=True, exist_ok=True)
    paths = ["/dashboard", "/dashboard/properties", "/dashboard/properties/create"]
    checks, errors = [], []
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 390, "height": 950})
        page.on("pageerror", lambda error: errors.append(str(error)))
        try:
            page.goto(args.base + "/login", wait_until="networkidle")
            page.locator("#email").fill("mobile-preview@example.test")
            page.locator("#password").fill("LocalPreview#4821")
            page.get_by_role("button", name="Sign In", exact=True).click()
            page.wait_for_url("**/dashboard")
            for asset in ["css/mobile-app.css", "js/mobile-app.js"]:
                data = subprocess.check_output(["git", "show", "HEAD:public/" + asset], cwd=root)
                content_type = "text/css" if asset.endswith("css") else "text/javascript"
                page.route("**/" + asset + "*", lambda route, request, data=data, content_type=content_type: route.fulfill(body=data, content_type=content_type))
            baseline = {}
            for width in [390, 768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                for path in paths:
                    page.goto(args.base + path, wait_until="networkidle")
                    baseline[(width, path)] = page.locator(".workspace-main").evaluate("el => ({width:el.offsetWidth,height:el.offsetHeight})")
            page.unroute("**/css/mobile-app.css*")
            page.unroute("**/js/mobile-app.js*")
            for width in [320, 390, 430, 767, 768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                for path in paths:
                    page.goto(args.base + path, wait_until="networkidle")
                    current = page.locator(".workspace-main").evaluate("el => ({width:el.offsetWidth,height:el.offsetHeight})")
                    assert page.evaluate("document.documentElement.scrollWidth <= innerWidth + 1"), (width, path)
                    if width >= 768:
                        assert current == baseline[(width, path)], ("Desktop layout changed", width, path, current, baseline[(width, path)])
                        assert not page.locator(".mobile-section-toggle").first.is_visible() if path.endswith("create") else True
                    elif width == 390 and path.endswith("create"):
                        assert current["height"] < baseline[(width, path)]["height"] * 0.65
                        assert page.locator(".property-form-section.is-open").count() == 1
                    if width < 768 and path.endswith("properties"):
                        rows = page.locator(".workspace-listing-table tbody tr")
                        assert rows.count() > 0
                        assert rows.first.bounding_box()["height"] < 170
                        assert rows.first.get_by_role("link", name="Edit", exact=True).is_visible()
                        assert rows.first.get_by_role("link", name="Preview", exact=True).is_visible()
                    if width == 390:
                        filename = "mobile-add-property.png" if path.endswith("create") else "mobile-listings.png" if path.endswith("properties") else "mobile-dashboard.png"
                        page.screenshot(path=str(output / filename), full_page=path.endswith("create"))
                checks.append(f"Dashboard, listings and Add Property at {width}px")
                print(checks[-1], flush=True)
            page.set_viewport_size({"width": 390, "height": 950})
            page.goto(args.base + paths[-1], wait_until="networkidle")
            page.locator("#title").fill("Unsaved local mobile preview")
            page.get_by_role("button", name="2. Property Details", exact=True).click()
            assert not page.locator("#title").is_visible()
            page.locator("#area").fill("1200")
            page.get_by_role("button", name="1. Property Information", exact=True).click()
            assert page.locator("#title").input_value() == "Unsaved local mobile preview"
            page.locator('.add-tab-list a[href="#add-list-9"]').click()
            assert page.locator("#address").is_visible()
            assert page.locator("#add-list-9 .mobile-section-toggle").get_attribute("aria-expanded") == "true"
            assert not page.locator("#add-property-form").evaluate("form => form.reportValidity()")
            assert page.locator("#price").is_visible()
            assert page.locator("#description").is_visible()
            page.set_viewport_size({"width": 1440, "height": 950})
            page.evaluate("new Promise(requestAnimationFrame)")
            assert page.locator("#title").is_visible() and page.locator("#area").is_visible()
            assert page.locator("#title").input_value() == "Unsaved local mobile preview"
            checks.append("Accordion links, retained inputs, native validation and desktop restoration")
            assert not errors, errors
        finally:
            browser.close()
            (output / "dashboard-results.json").write_text(json.dumps({"checks": checks, "errors": errors}, indent=2))
    print(json.dumps({"passed": len(checks), "errors": errors}))


if __name__ == "__main__":
    main()
