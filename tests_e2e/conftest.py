import pytest
from playwright.sync_api import sync_playwright
import os
import sys
import time

# Add tests_e2e directory to python path
sys.path.insert(0, os.path.dirname(__file__))

from config import DEFAULT_TIMEOUT, BASE_URL, ADMIN_PHONE, ADMIN_PASSWORD

def pytest_addoption(parser):
    """Register custom CLI options for pytest."""
    parser.addoption(
        "--headed",
        action="store_true",
        default=False,
        help="Run tests in headed mode (opens visible browser window)"
    )

@pytest.fixture(scope="session")
def playwright_instance():
    with sync_playwright() as p:
        yield p

@pytest.fixture(scope="session")
def browser(playwright_instance, request):
    headed = request.config.getoption("--headed")
    browser = playwright_instance.chromium.launch(
        headless=not headed,
        slow_mo=100 if headed else 0
    )
    yield browser
    browser.close()

@pytest.fixture(scope="session")
def browser_context(browser):
    """Single persistent browser context across all tests."""
    context = browser.new_context(
        viewport={"width": 1280, "height": 800},
        locale="ar-EG"
    )
    yield context
    context.close()

@pytest.fixture(scope="session")
def page(browser_context):
    """Single persistent window/page that stays open continuously for the ENTIRE test run.
    Auto-accepts all browser confirmation dialogs (wire:confirm) globally,
    and ensures admin authentication is active before any test starts.
    """
    page = browser_context.new_page()
    page.set_default_timeout(DEFAULT_TIMEOUT)
    page.on("dialog", lambda dialog: dialog.accept())
    
    # Auto-login as admin if not already logged in
    from helpers import login_as_admin
    login_as_admin(page)
    
    yield page
    page.close()

@pytest.fixture(autouse=True)
def pause_between_tests():
    """Add a 1.5 second pause between each test for stability."""
    yield
    time.sleep(1.5)
