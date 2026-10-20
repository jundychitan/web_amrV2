"""Run all database-free compatibility checks using PHP 8.4.

Windows: python tests/run_php84_tests.py --php C:/path/to/php84/php.exe
Linux:   python3 tests/run_php84_tests.py --php /usr/bin/php8.4
Uses disposable test databases/files, never application connection settings.
"""

import argparse
import json
from pathlib import Path
import re
import subprocess
import sys


ROOT = Path(__file__).resolve().parents[1]
DIAGNOSTIC = re.compile(r"(?:PHP )?(?:Deprecated|Warning|Fatal error|Parse error):")


def run(command):
    result = subprocess.run(command, cwd=ROOT, capture_output=True, text=True,
                            encoding="utf-8", errors="replace")
    text = result.stdout + result.stderr
    if text:
        print(text.rstrip(), flush=True)
    if result.returncode or DIAGNOSTIC.search(text):
        raise RuntimeError("Compatibility check failed: " + str(command[-1]))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--php", default="php")
    args = parser.parse_args()
    try:
        result = subprocess.run(
            [args.php, "-r", "print json_encode(array(PHP_VERSION_ID, PHP_BINARY, get_loaded_extensions()));"],
            capture_output=True, text=True, check=True)
        version, binary, extensions = json.loads(result.stdout)
        if not 80400 <= version < 80500:
            raise RuntimeError("Select a PHP 8.4 interpreter with --php.")
        required = {"mysqli", "pdo_sqlite", "sqlite3", "mbstring", "iconv", "xml",
                    "xmlwriter", "simplexml", "zip", "gd"}
        missing = required.difference(extension.lower() for extension in extensions)
        if missing:
            raise RuntimeError("Missing test extensions: " + ", ".join(sorted(missing)))
        print("Running PHP 8.4 checks with " + binary, flush=True)
        run([sys.executable, str(ROOT / "tests/php84_lint.py"), "--php", binary])
        for script in ("php84_bootstrap_test.php", "php84_framework_test.php",
                       "php84_mysqli_test.php", "php84_spreadsheet_test.php",
                       "session_lock_test.php", "deployment_config_test.php",
                       "mariadb118_policy_test.php"):
            run([binary, "-d", "error_reporting=32767", str(ROOT / "tests" / script)])
        run([binary, "-d", "error_reporting=32767",
             str(ROOT / "tests/php84_spreadsheet_test.php"), "Classes"])
        run([sys.executable, "-m", "unittest", "discover", "-s",
             str(ROOT / "tests"), "-p", "test_*.py", "-v"])
        print("PASS: all offline PHP 8.4 compatibility checks.")
        return 0
    except (RuntimeError, OSError, ValueError, subprocess.CalledProcessError) as error:
        print(str(error), file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
