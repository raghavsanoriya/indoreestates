"""Read-only Playwright checks for homepage mobile gutters and navigation."""

import argparse
import json
from pathlib import Path

from playwright.sync_api import sync_playwright


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="https://indoreestates.com")
    parser.add_argument("--candidate-css", type=Path)
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[2]
    output = root / "output/playwright/padding"
    output.mkdir(parents=True, exist_ok=True)
    report = {"checks": [], "page_errors": []}
    heading_selector = ".homez-home .widgettitle"
    geometry = """elements => elements.map(el => {
        const r = el.getBoundingClientRect();
        return {text: el.textContent.trim(), left: r.left, right: r.right, width: r.width};
    })"""

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 390, "height": 844})
        page.on("pageerror", lambda error: report["page_errors"].append(str(error)))
        try:
            page.goto(args.base, wait_until="networkidle")
            page.evaluate("document.fonts.ready")
            desktop_before = {}
            for width in [768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                page.evaluate("new Promise(requestAnimationFrame)")
                desktop_before[width] = page.locator(heading_selector).evaluate_all(geometry)
                print(f"Captured desktop baseline: {width}px", flush=True)

            if args.candidate_css:
                page.route("**/css/mobile-app.css*", lambda route: route.fulfill(
                    path=str(args.candidate_css.resolve()), content_type="text/css"))
                page.reload(wait_until="networkidle")

            for width in [320, 390, 430, 767, 768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                page.evaluate("new Promise(requestAnimationFrame)")
                headings = page.locator(heading_selector).evaluate_all(geometry)
                assert headings, "Homepage headings were not rendered"
                if width <= 767:
                    for heading in headings:
                        assert abs(heading["left"] - 20) < 1, (width, heading)
                        assert heading["right"] <= width - 19, (width, heading)
                    for selector in [".elementor-element-91d0496", ".elementor-element-5d2e746"]:
                        section = page.locator(selector + " > .elementor-container")
                        padding = section.evaluate("el => [getComputedStyle(el).paddingLeft, getComputedStyle(el).paddingRight]")
                        assert padding == ["20px", "20px"], (width, padding)
                    counters = page.locator(".elementor-counter").evaluate_all(geometry)
                    assert len(counters) == 3
                    assert counters[0]["left"] >= 19, (width, counters)
                    assert counters[-1]["right"] <= width - 19, (width, counters)
                    assert page.evaluate("document.documentElement.scrollWidth <= innerWidth + 1"), width
                    assert page.get_by_role("navigation", name="Mobile navigation", exact=True).is_visible()
                    page.get_by_role("button", name="Open navigation", exact=True).click()
                    assert page.locator("#mobile_btn").get_attribute("aria-expanded") == "true"
                    page.keyboard.press("Escape")
                    assert page.locator("#mobile_btn").get_attribute("aria-expanded") == "false"
                else:
                    assert headings == desktop_before[width], ("Desktop heading geometry changed", width)
                    assert not page.get_by_role("navigation", name="Mobile navigation", exact=True).is_visible()
                report["checks"].append({"width": width, "status": "passed", "headings": headings})
                print(f"Passed layout and navigation: {width}px", flush=True)

            page.set_viewport_size({"width": 390, "height": 844})
            page.evaluate("new Promise(requestAnimationFrame)")
            title = page.get_by_role("heading", name="Discover Indore Properties", exact=True)
            page.evaluate("el => window.scrollTo({top: el.getBoundingClientRect().top + scrollY - 20, behavior: 'instant'})", title.element_handle())
            page.wait_for_function("el => Math.abs(el.getBoundingClientRect().top - 20) < 1", arg=title.element_handle())
            page.screenshot(path=str(output / "mobile-listings-padding.png"), animations="disabled")
            page.get_by_role("button", name="Next property", exact=True).first.click()
            page.wait_for_function("document.querySelector('#home-sale .mobile-property-slide:nth-child(2)').classList.contains('is-centred')")
            report["checks"].append({"interaction": "Property carousel next control", "status": "passed"})
            stats = page.locator(".elementor-element-9d1814b")
            page.evaluate("el => window.scrollTo({top: el.getBoundingClientRect().top + scrollY - 20, behavior: 'instant'})", stats.element_handle())
            page.wait_for_function("el => Math.abs(el.getBoundingClientRect().top - 20) < 1", arg=stats.element_handle())
            page.screenshot(path=str(output / "mobile-types-padding.png"), animations="disabled")
            assert not report["page_errors"], report["page_errors"]
        finally:
            browser.close()
            (output / "results.json").write_text(json.dumps(report, indent=2))
    print(json.dumps({"status": "passed", "checks": len(report["checks"]), "page_errors": report["page_errors"]}))


if __name__ == "__main__":
    main()
