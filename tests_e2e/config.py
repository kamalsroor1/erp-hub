import os

# Base URL for the local Laravel server
BASE_URL = os.getenv("E2E_BASE_URL", "http://localhost:8000")

# Super Admin 1 Credentials
ADMIN_PHONE = os.getenv("E2E_ADMIN_PHONE", "01012316954")
ADMIN_PASSWORD = os.getenv("E2E_ADMIN_PASSWORD", "password")

# Super Admin 2 Credentials
ADMIN2_PHONE = os.getenv("E2E_ADMIN2_PHONE", "01558088841")
ADMIN2_PASSWORD = os.getenv("E2E_ADMIN2_PASSWORD", "123456789")

# Timeouts in milliseconds
DEFAULT_TIMEOUT = 30000
NAVIGATION_TIMEOUT = 30000

# Screenshots Directory
SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "screenshots")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)
