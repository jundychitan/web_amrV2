"""Lint active PHP sources with PHP 8.4, including compile-time deprecations.

Run: python tests/php84_lint.py --php /path/to/php
Archived OLD/BACKUP trees and compressed files are excluded explicitly.
Template/vendor PHP files are checked for syntax too. No application PHP is executed.
"""

import argparse
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
import subprocess
import sys


ROOT = Path(__file__).resolve().parents[1]
EXCLUDED = ("application/OLD/", "application/third_party/BACKUP/")


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--php", default="php")
    args = parser.parse_args()
    version = subprocess.run([args.php, "-r", "print PHP_VERSION_ID;"],
                             capture_output=True, text=True, check=True)
    if not 80400 <= int(version.stdout) < 80500:
        parser.error("Run this check with PHP 8.4.")
    files = sorted(path for path in ROOT.rglob("*.php")
                   if not path.relative_to(ROOT).as_posix().startswith(EXCLUDED))

    def lint(path):
        result = subprocess.run([args.php, "-d", "error_reporting=32767", "-l", str(path)],
                                capture_output=True, text=True)
        output = result.stdout + result.stderr
        if result.returncode or any(word in output for word in ("Deprecated:", "Warning:", "Fatal error:")):
            return str(path.relative_to(ROOT)), output
        return None

    with ThreadPoolExecutor(max_workers=4) as workers:
        failures = [result for result in workers.map(lint, files) if result]
    for path, output in failures:
        print(path)
        messages = set()
        for line in output.splitlines():
            if any(word in line for word in ("Deprecated:", "Warning:", "error:")):
                message = line.removeprefix("PHP ").split(" in " + str(ROOT))[0]
                if message not in messages:
                    print("  " + message)
                    messages.add(message)
    print("PHP 8.4 lint: {} files, {} failures.".format(len(files), len(failures)))
    print("Excluded archives: " + ", ".join(EXCLUDED))
    return int(bool(failures))


if __name__ == "__main__":
    sys.exit(main())
