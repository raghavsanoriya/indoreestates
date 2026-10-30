"""Check phone header alignment and compare desktop header geometry."""
import argparse
import json
import subprocess
from pathlib import Path

from playwright.sync_api import sync_playwright


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--base", default="http://127.0.0.1:8006")
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[2]
    output = root / "output/playwright/mobile-workspace"
    output.mkdir(parents=True, exist_ok=True)
    checks, errors = [], []
    geometry = """el => {const b=el.getBoundingClientRect();return {x:b.x,y:b.y,width:b.width,height:b.height}}"""
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 1440, "height": 950})
        page.on("pageerror", lambda error: errors.append(str(error)))
        try:
            baseline = {}
            baseline_css = subprocess.check_output(["git", "show", "HEAD:public/css/mobile-app.css"], cwd=root)
            baseline_css += b"\n.mobile-header-profile,.mobile-header-menu-icon{display:none}\n"
            page.route("**/css/mobile-app.css*", lambda route: route.fulfill(body=baseline_css, content_type="text/css"))
            page.goto(args.base, wait_until="networkidle")
            page.evaluate("document.fonts.ready")
            for width in [768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 950})
                page.evaluate("new Promise(requestAnimationFrame)")
                page.locator(".o3-header").evaluate("el => Promise.all(el.getAnimations({subtree:true}).filter(a => a.effect.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {})))")
                baseline[width] = page.locator(".o3-logo").evaluate(geometry)
            page.unroute("**/css/mobile-app.css*")
            page.reload(wait_until="networkidle")
            page.evaluate("document.fonts.ready")
            for width in [320, 390, 430, 767, 768, 1024, 1440]:
                page.set_viewport_size({"width": width, "height": 844})
                page.evaluate("new Promise(requestAnimationFrame)")
                page.locator(".o3-header").evaluate("el => Promise.all(el.getAnimations({subtree:true}).filter(a => a.effect.getTiming().iterations !== Infinity).map(a => a.finished.catch(() => {})))")
                logo = page.locator(".o3-logo").bounding_box()
                if width < 768:
                    menu = page.get_by_role("button", name="Open navigation", exact=True)
                    profile = page.locator(".mobile-header-profile")
                    left, right = menu.bounding_box(), profile.bounding_box()
                    assert abs(logo["x"] + logo["width"] / 2 - width / 2) < 1
                    assert left["x"] + left["width"] <= logo["x"]
                    assert right["x"] >= logo["x"] + logo["width"]
                    assert abs(left["y"] - right["y"]) < 1 and left["height"] == right["height"] == 44
                    assert page.locator(".o3-header").bounding_box()["height"] <= 68
                    icon = page.locator(".mobile-header-menu-icon").bounding_box()
                    assert abs(icon["y"] + icon["height"] / 2 - left["y"] - left["height"] / 2) < 1
                    assert profile.get_attribute("href").endswith("/login")
                    assert page.evaluate("document.documentElement.scrollWidth <= innerWidth + 1")
                    menu.click()
                    link = page.locator(".o3-has-submenu > a")
                    assert link.evaluate("el => getComputedStyle(el).alignItems") == "center"
                    assert link.evaluate("el => getComputedStyle(el).textDecorationLine") == "none"
                    if width == 390:
                        page.screenshot(path=str(output / "mobile-menu-aligned.png"), animations="disabled")
                    page.keyboard.press("Escape")
                    assert menu.get_attribute("aria-expanded") == "false"
                else:
                    actual = page.locator(".o3-logo").evaluate(geometry)
                    assert all(abs(actual[key] - baseline[width][key]) < 1 for key in actual), (width, actual, baseline[width])
                    assert not page.locator(".mobile-header-profile").is_visible()
                checks.append(f"Header alignment at {width}px")
            page.set_viewport_size({"width": 390, "height": 844})
            page.locator(".o3-header").screenshot(path=str(output / "mobile-header-centred.png"))
            page.goto(args.base + "/properties", wait_until="networkidle")
            sort = page.locator("#sort")
            assert sort.evaluate("el => getComputedStyle(el).appearance") == "none"
            assert "50%" in sort.evaluate("el => getComputedStyle(el).backgroundPosition")
            with page.expect_navigation(wait_until="networkidle"):
                sort.select_option("price_asc")
            assert "sort=price_asc" in page.url
            page.locator(".search-item").screenshot(path=str(output / "mobile-sort-aligned.png"))
            checks.append("Sort arrow placement and working sort submission")
            assert not errors, errors
        finally:
            browser.close()
            (output / "header-results.json").write_text(json.dumps({"checks": checks, "errors": errors}, indent=2))
    print(json.dumps({"passed": len(checks), "errors": errors}))


if __name__ == "__main__":
    main()
