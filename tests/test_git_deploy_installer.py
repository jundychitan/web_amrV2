"""Cross-platform checks for the reviewed Linux deployment installer."""
import importlib.util
from pathlib import Path
import unittest
from unittest import mock

SPEC = importlib.util.spec_from_file_location(
    "install_git_deploy",
    Path(__file__).resolve().parents[1] / "deploy/install_git_deploy.py",
)
installer = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(installer)


class InstallerTests(unittest.TestCase):
    def test_test_profile_retains_existing_defaults(self):
        profile = installer.select_profile(
            "test", "/srv/web-amr/releases/20261105-e854afe", "localhost")
        self.assertEqual(profile["url"], "http://172.104.42.220")
        self.assertEqual(profile["baseline"], "e606559ba53daffbf3ee9c447f4b0c1097983f7b")
        self.assertEqual(profile["initial_check"], "check")
        self.assertNotIn("health_checks", profile)

    def test_marigold_profile_uses_its_own_release_and_baseline(self):
        profile = installer.select_profile(
            "marigold", "/srv/web-amr/releases/test-reference-20261109", "mamasitas")
        self.assertEqual(profile["url"], "http://172.16.47.155")
        self.assertEqual(profile["baseline"], "7b22699c9007d7588819f431c6265febc71fbe25")
        self.assertEqual(profile["initial_check"], "verify")
        self.assertIn(["/check_time.php", 200], profile["health_checks"])

    def test_profile_rejects_wrong_host_or_release(self):
        for release, hostname in (
            ("/srv/web-amr/releases/20261105-e854afe", "mamasitas"),
            ("/srv/web-amr/releases/test-reference-20261109", "localhost"),
        ):
            with self.subTest(release=release, hostname=hostname), self.assertRaises(RuntimeError):
                installer.select_profile("marigold", release, hostname)

    def test_absolute_prerequisites_do_not_depend_on_su_path(self):
        with mock.patch.object(installer.Path, "is_file", return_value=True), \
                mock.patch.object(installer.os, "access", return_value=True), \
                mock.patch.object(installer.shutil, "which", return_value=None):
            installer.check_prerequisites()
        self.assertIn("/usr/sbin/runuser", installer.PREREQUISITES)
        self.assertIn("/usr/sbin/useradd", installer.PREREQUISITES)

    def test_missing_prerequisite_stops_before_installation(self):
        with mock.patch.object(installer.Path, "is_file", return_value=False):
            with self.assertRaises(RuntimeError):
                installer.check_prerequisites()

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
