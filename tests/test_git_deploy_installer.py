"""Cross-platform checks for the reviewed Linux deployment installer."""
import importlib.util
from pathlib import Path
import unittest

SPEC = importlib.util.spec_from_file_location(
    "install_git_deploy",
    Path(__file__).resolve().parents[1] / "deploy/install_git_deploy.py",
)
installer = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(installer)


class InstallerTests(unittest.TestCase):
    def test_windows_source_becomes_a_valid_linux_shebang(self):
        source = b"#!/usr/bin/python3\r\nprint('hello')\r\n"
        self.assertEqual(
            installer.linux_source(source),
            b"#!/usr/bin/python3\nprint('hello')\n",
        )

    def test_lf_source_is_unchanged(self):
        source = b"#!/usr/bin/python3\nprint('hello')\n"
        self.assertEqual(installer.linux_source(source), source)


if __name__ == "__main__":
    unittest.main()
