import subprocess
import sys
import time
import urllib.request
import argparse
import os

# Ensure UTF-8 output on Windows consoles
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8')

E2E_DB_PATH = os.path.abspath(os.path.join(os.path.dirname(__file__), "database", "e2e_testing.sqlite"))

# Mapping shortcuts for single test execution
TEST_SHORTCUTS = {
    "auth": "tests_e2e/test_01_auth_and_navigation.py",
    "login": "tests_e2e/test_01_auth_and_navigation.py",
    "stores": "tests_e2e/test_01_stores_and_branches.py",
    "branches": "tests_e2e/test_01_stores_and_branches.py",
    "branch": "tests_e2e/test_01_stores_and_branches.py",
    "1": "tests_e2e/test_01_stores_and_branches.py",
    "items": "tests_e2e/test_02_items_and_inventory.py",
    "products": "tests_e2e/test_02_items_and_inventory.py",
    "inventory": "tests_e2e/test_02_items_and_inventory.py",
    "2": "tests_e2e/test_02_items_and_inventory.py",
    "customers": "tests_e2e/test_03_customers_and_suppliers.py",
    "suppliers": "tests_e2e/test_03_customers_and_suppliers.py",
    "3": "tests_e2e/test_03_customers_and_suppliers.py",
    "purchases": "tests_e2e/test_04_purchases_and_stock.py",
    "stock": "tests_e2e/test_04_purchases_and_stock.py",
    "4": "tests_e2e/test_04_purchases_and_stock.py",
    "pos": "tests_e2e/test_05_pos_sales_and_invoices.py",
    "sales": "tests_e2e/test_05_pos_sales_and_invoices.py",
    "invoices": "tests_e2e/test_05_pos_sales_and_invoices.py",
    "5": "tests_e2e/test_05_pos_sales_and_invoices.py",
    "transfers": "tests_e2e/test_06_stock_transfers.py",
    "6": "tests_e2e/test_06_stock_transfers.py",
    "journal": "tests_e2e/test_07_shifts_and_journal.py",
    "shifts": "tests_e2e/test_07_shifts_and_journal.py",
    "7": "tests_e2e/test_07_shifts_and_journal.py",
    "expenses": "tests_e2e/test_08_expenses_and_returns.py",
    "returns": "tests_e2e/test_08_expenses_and_returns.py",
    "8": "tests_e2e/test_08_expenses_and_returns.py",
    "reports": "tests_e2e/test_09_reports_and_exports.py",
    "9": "tests_e2e/test_09_reports_and_exports.py",
    "trash": "tests_e2e/test_10_trash_and_permissions.py",
    "10": "tests_e2e/test_10_trash_and_permissions.py",
}

# The active default suites (1, 2, 3, 4)
ACTIVE_TEST_SUITES = [
    "tests_e2e/test_01_auth_and_navigation.py",
    "tests_e2e/test_01_stores_and_branches.py",
    "tests_e2e/test_02_items_and_inventory.py",
    "tests_e2e/test_03_customers_and_suppliers.py",
    "tests_e2e/test_04_purchases_and_stock.py",
]

def print_header(title: str):
    print("\n" + "=" * 60)
    print(f"[*] {title}")
    print("=" * 60)

def reset_e2e_database() -> bool:
    print("\n[+] جاري تصفير وبناء قاعدة بيانات الاختبارات المنعزلة (database/e2e_testing.sqlite)...")
    try:
        os.makedirs(os.path.dirname(E2E_DB_PATH), exist_ok=True)
        with open(E2E_DB_PATH, "w") as f:
            pass
            
        env = os.environ.copy()
        env["DB_DATABASE"] = E2E_DB_PATH
        
        res = subprocess.run(
            ["php", "artisan", "migrate:fresh", "--env=e2e", "--seed", "--force"], 
            env=env,
            shell=True,
            capture_output=True,
            text=True
        )
        if res.returncode == 0:
            print("[+] تم تصفير قاعدة بيانات E2E وزرع المستخدمين والمخزن الرئيسي بنجاح!")
            return True
        else:
            print(f"[!] خطأ أثناء تصفير قاعدة البيانات: {res.stderr or res.stdout}")
            return False
    except Exception as e:
        print(f"[!] استثناء أثناء تجهيز قاعدة البيانات: {e}")
        return False

def run_php_tests() -> bool:
    print_header("1. تشغيل اختبارات Laravel الداخلية (PHPUnit Feature & Unit Tests)")
    try:
        res = subprocess.run(["php", "artisan", "test"], shell=True, capture_output=False)
        return res.returncode == 0
    except Exception as e:
        print(f"[!] خطأ أثناء تشغيل php artisan test: {e}")
        return False

def check_local_server(url: str = "http://localhost:8000") -> bool:
    try:
        req = urllib.request.Request(
            url, 
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req, timeout=3) as response:
            return response.status in [200, 302]
    except Exception:
        return False

def run_e2e_tests(headed: bool = False, all_suites: bool = False, specific_test: str = None) -> bool:
    print_header("2. تشغيل اختبارات المتصفح التفاعلية E2E عبر Python Playwright")
    
    # 1. Reset dedicated E2E database
    reset_e2e_database()
    
    server_url = "http://localhost:8000"
    server_proc = None
    
    env = os.environ.copy()
    env["DB_DATABASE"] = E2E_DB_PATH
    
    print(f"[*] جاري تشغيل سيرفر Laravel ببيئة الاختبار (.env.e2e) على المنفذ 8000 ...")
    server_proc = subprocess.Popen(
        ["php", "artisan", "serve", "--env=e2e", "--port=8000"], 
        env=env,
        shell=True,
        stdout=subprocess.DEVNULL, 
        stderr=subprocess.DEVNULL
    )
    for _ in range(8):
        time.sleep(1)
        if check_local_server(server_url):
            break
    
    if not check_local_server(server_url):
        print(f"[!] تعذر تشغيل السيرفر المحلي. يرجى التأكد من عدم حجز المنفذ 8000.")
        if server_proc:
            server_proc.terminate()
        return False
    else:
        print("[+] السيرفر المحلي نشط ويعمل على قاعدة بيانات e2e_testing.sqlite!")

    try:
        if specific_test:
            # Resolve shortcut or exact file
            target_file = TEST_SHORTCUTS.get(specific_test.lower(), specific_test)
            targets = [target_file]
            print(f"[*] تشغيل اختبار محدد: {target_file}")
        elif all_suites:
            targets = ["tests_e2e/"]
        else:
            targets = ACTIVE_TEST_SUITES
            
        env["PYTHONIOENCODING"] = "utf-8"
        env["PYTHONUTF8"] = "1"
        cmd = [sys.executable, "-m", "pytest"] + targets + ["-v", "-s"]
        if headed:
            cmd.append("--headed")
            
        res = subprocess.run(cmd, env=env, capture_output=False)
        return res.returncode == 0
    except Exception as e:
        print(f"[!] خطأ أثناء تشغيل pytest: {e}")
        return False
    finally:
        if server_proc:
            print("[*] جاري إيقاف سيرفر الاختبار المحلي...")
            server_proc.terminate()

def main():
    parser = argparse.ArgumentParser(description="مشغّل الاختبارات الشامل لنظام سرور كوفي ERP")
    parser.add_argument("--e2e", action="store_true", help="تشغيل اختبارات المتصفح E2E فقط")
    parser.add_argument("--headed", action="store_true", help="فتح نافذة المتصفح بشكل مرئي أثناء اختبارات E2E")
    parser.add_argument("--unit", action="store_true", help="تشغيل اختبارات PHPUnit فقط")
    parser.add_argument("--all", action="store_true", help="تشغيل كافة السيناريوهات (1 إلى 10)")
    parser.add_argument("--test", type=str, default=None, help="تشغيل اختبار محدد بالاسم أو الرقم (مثال: --test stores أو --test 1 أو --test items)")
    
    args = parser.parse_args()
    
    php_success = True
    e2e_success = True
    
    # If a specific test is requested, default to E2E
    if args.test:
        args.e2e = True
    
    if not args.e2e and not args.test:
        php_success = run_php_tests()
        
    if not args.unit:
        e2e_success = run_e2e_tests(headed=args.headed, all_suites=args.all, specific_test=args.test)
        
    print_header("التقرير النهائي للاختبارات (Test Summary)")
    if not args.e2e and not args.test:
        print(f"• اختبارات PHPUnit الداخلية: {'[+] ناجحة 100%' if php_success else '[-] فشل في بعض الاختبارات'}")
    if not args.unit:
        print(f"• اختبارات المتصفح E2E Playwright: {'[+] ناجحة 100%' if e2e_success else '[-] فشل في بعض الاختبارات'}")
        
    if php_success and (args.unit or e2e_success):
        print("\n[+] اكتمل الاختبار بنجاح!")
        sys.exit(0)
    else:
        print("\n[!] يرجى مراجعة الأخطاء أعلاه وتصحيحها.")
        sys.exit(1)

if __name__ == "__main__":
    main()
