"""Database-free regression tests for the Linux deployment workflow."""

import importlib.util
import json
import os
from pathlib import Path
import stat
import tempfile
import unittest
from unittest import mock

SPEC = importlib.util.spec_from_file_location(
    "git_deploy", Path(__file__).resolve().parents[1] / "deploy/git_deploy.py")
deploy = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(deploy)


class PolicyTests(unittest.TestCase):
    def test_runtime_paths(self):
        for name in ("application/controllers/New.php", "application/views/admin/page.php",
                     "assets/upload.js", "system/core/Loader.php", "Classes/PHPExcel.php"):
            with self.subTest(name=name):
                self.assertEqual(deploy.policy(name), "runtime")

    def test_protected_paths(self):
        for name in ("application/config/routes.php", "application/config/database.php",
                     "application/config/production/config.php", "reports/file.php",
                     "meter_upload_list/readings.csv", "application/logs/log.php",
                     "application/cache/cache.php", "database/migration.sql",
                     ".env", ".htaccess", "index.php", "http_post_server.php", "Rtu.php",
                     "deploy/apache.conf.example", "deploy/php-fpm.conf.example",
                     "templates/highcharts/scripts/db_config.php",
                     "application/OLD/Foo.php", "assets/examples/demo.js",
                     "assets/secrets.ini", "assets/archive.php.gz"):
            with self.subTest(name=name):
                self.assertEqual(deploy.policy(name), "protected")

    def test_new_public_php_entrypoints_are_blocked(self):
        for name in ("assets/endpoint.php", "templates/demo/index.php", "Classes/index.php"):
            self.assertEqual(deploy.policy(name), "protected")

    def test_non_runtime_changes(self):
        for name in ("README.md", ".gitignore", "deploy/README.md", "deploy/git_deploy.py",
                     "tests/test_git_deploy.py", "docs/instructions.md"):
            self.assertEqual(deploy.policy(name), "ignore")

    def test_traversal_and_ambiguous_paths(self):
        for name in ("", ".", "../assets/x.js", "/assets/x.js", "assets/../../x.js",
                     "assets\\x.js", "assets//x.js", "assets/./x.js", "assets/\nx.js"):
            with self.subTest(name=name), self.assertRaises(deploy.DeploymentError):
                deploy.policy(name)


class WorkflowTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.release = self.root / "release"
        self.release.mkdir()
        self.storage = self.root / "storage"
        self.storage.mkdir()
        self.config = {
            "storage": str(self.storage), "user": "test-deployer", "repo": str(self.root / "repo"),
            "work": str(self.root / "work"), "web_gid": 0,
            "current": str(self.root / "current"), "releases": str(self.root),
            "remote": "https://example.invalid/repository.git", "url": "http://example.invalid",
        }
        self.workflow = deploy.Workflow(self.config)
        self.workflow.release = mock.Mock(return_value=self.release)
        self.workflow.command = mock.Mock(return_value=b"")
        self.workflow.reload = mock.Mock()
        self.workflow.health = mock.Mock()
        self.chown = mock.patch.object(deploy.os, "chown", create=True).start()
        self.addCleanup(mock.patch.stopall)
        self.old = b"old code\n"
        self.new = b"new code\n"
        self.name = "assets/update.js"
        self.path = self.release / self.name
        self.path.parent.mkdir()
        self.path.write_bytes(self.old)
        self.state = {
            "version": 1, "commit": "a" * 40, "release": str(self.release),
            "managed": {self.name: deploy.sha(self.old)}, "last_backup": None,
        }
        deploy.write_json(self.workflow.state_path, self.state)

    def change(self, name=None, action="M", data=None):
        data = self.new if data is None else data
        return {"path": name or self.name, "action": action,
                "data": None if action == "D" else data,
                "target": None if action == "D" else deploy.sha(data)}

    def test_live_drift_is_rejected(self):
        self.path.write_bytes(b"server-specific change")
        with self.assertRaises(deploy.DeploymentError):
            deploy.validate_delta([self.change()], self.state["managed"], self.release)

    def test_unmanaged_existing_file_cannot_be_adopted(self):
        with self.assertRaises(deploy.DeploymentError):
            deploy.validate_delta([self.change()], {}, self.release)
        with self.assertRaises(deploy.DeploymentError):
            deploy.validate_delta([self.change(action="A")], {}, self.release)

    def test_addition_requires_absence(self):
        change = self.change("assets/new.js", action="A")
        deploy.validate_delta([change], self.state["managed"], self.release)

    def test_symlinked_parent_is_rejected(self):
        outside = self.root / "outside"
        outside.mkdir()
        link = self.release / "assets/link"
        try:
            link.symlink_to(outside, target_is_directory=True)
        except OSError:
            self.skipTest("Symlinks unavailable for this user")
        with self.assertRaises(deploy.DeploymentError):
            deploy.safe_path(self.release, "assets/link/code.js")

    def test_update_and_explicit_rollback(self):
        os.chmod(self.path, 0o640)
        original_mode = stat.S_IMODE(self.path.stat().st_mode)
        self.workflow.apply(self.state, "b" * 40, [self.change()])
        self.assertEqual(self.path.read_bytes(), self.new)
        self.assertEqual(stat.S_IMODE(self.path.stat().st_mode), original_mode)
        state = self.workflow.state()
        self.assertEqual(state["commit"], "b" * 40)
        backup = self.storage / "backups" / state["last_backup"]
        self.assertEqual((backup / "files" / self.name).read_bytes(), self.old)
        self.workflow.rollback(state["last_backup"])
        self.assertEqual(self.path.read_bytes(), self.old)
        self.assertEqual(self.workflow.state(), self.state)

    def test_failure_restores_modified_added_and_deleted_files(self):
        deleted = self.release / "assets/delete.js"
        deleted.write_bytes(b"keep me")
        self.state["managed"]["assets/delete.js"] = deploy.sha(b"keep me")
        deploy.write_json(self.workflow.state_path, self.state)
        changes = [self.change(), self.change("assets/new.js", "A"),
                   self.change("assets/delete.js", "D")]
        self.workflow.health.side_effect = [deploy.DeploymentError("health failed"), None]
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.apply(self.state, "b" * 40, changes)
        self.assertEqual(self.path.read_bytes(), self.old)
        self.assertEqual(deleted.read_bytes(), b"keep me")
        self.assertFalse((self.release / "assets/new.js").exists())
        self.assertEqual(self.workflow.state(), self.state)

    def test_rollback_rejects_later_file_drift(self):
        self.workflow.apply(self.state, "b" * 40, [self.change()])
        state = self.workflow.state()
        self.path.write_bytes(b"later unrelated edit")
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.rollback(state["last_backup"])
        self.assertEqual(self.path.read_bytes(), b"later unrelated edit")

    def test_rollback_rejects_tampered_backup(self):
        self.workflow.apply(self.state, "b" * 40, [self.change()])
        state = self.workflow.state()
        backup = self.storage / "backups" / state["last_backup"]
        (backup / "files" / self.name).write_bytes(b"bad backup")
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.rollback(state["last_backup"])
        self.assertEqual(self.path.read_bytes(), self.new)

    def test_release_switch_during_tests_is_rejected(self):
        self.workflow.release.return_value = self.root / "different"
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.apply(self.state, "b" * 40, [self.change()])
        self.assertFalse((self.storage / "backups").exists())

    def test_baseline_only_manages_matching_files(self):
        self.workflow.state_path.unlink()
        mismatch = self.release / "assets/mismatch.js"
        mismatch.write_bytes(b"server override")
        self.workflow.fetch = mock.Mock()
        self.workflow.git = mock.Mock(return_value=b"")
        self.workflow.tree = mock.Mock(return_value={
            self.name: "matching", "assets/mismatch.js": "different",
            "assets/absent.js": "missing", "application/config/database.php": "secret",
        })
        self.workflow.blob = mock.Mock(side_effect=lambda entry: {
            "matching": self.old, "different": b"source code",
        }[entry])
        self.workflow.initialize("a" * 40)
        self.assertEqual(self.workflow.state()["managed"], {self.name: deploy.sha(self.old)})
        audit = json.loads((self.storage / "baseline-audit.json").read_text())
        self.assertEqual(audit["mismatched"], ["assets/mismatch.js"])
        self.assertEqual(audit["missing"], ["assets/absent.js"])
        self.assertEqual(mismatch.read_bytes(), b"server override")

    def test_baseline_cannot_be_replaced(self):
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.initialize("a" * 40)

    def test_prepare_stops_on_configuration_changes(self):
        self.workflow.git = mock.Mock(side_effect=[
            b"", b"M\0application/config/routes.php\0",
        ])
        self.workflow.tree = mock.Mock(return_value={})
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.prepare(self.state, "b" * 40)

    def test_noop_update_does_not_backup_or_reload(self):
        self.workflow.status = mock.Mock()
        self.workflow.fetch = mock.Mock(return_value=self.state["commit"])
        self.workflow.prepare = mock.Mock(return_value=[])
        self.workflow.tests = mock.Mock()
        self.workflow.update(apply=True)
        self.workflow.reload.assert_not_called()
        self.assertFalse((self.storage / "backups").exists())

    def test_node_is_required_for_upload_javascript_delta(self):
        self.workflow.git = mock.Mock(return_value=b"")
        with mock.patch.object(deploy.shutil, "which", return_value=None):
            with self.assertRaises(deploy.DeploymentError):
                self.workflow.tests("a" * 40, [self.change("assets/meter_upload.js")])

    def test_interrupted_update_can_be_recovered(self):
        changes = [self.change(), self.change("assets/new.js", "A")]
        backup = self.workflow.snapshot(self.state, "b" * 40, changes)
        deploy.write_json(self.workflow.pending_path, {"backup": backup.name})
        # Simulate a hard interruption after one of two file replacements.
        self.path.write_bytes(self.new)
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.status()
        self.workflow.rollback(backup.name)
        self.assertEqual(self.path.read_bytes(), self.old)
        self.assertFalse((self.release / "assets/new.js").exists())
        self.assertFalse(self.workflow.pending_path.exists())
        self.assertEqual(self.workflow.state(), self.state)

    def test_interrupted_recovery_rejects_unrelated_edit(self):
        backup = self.workflow.snapshot(self.state, "b" * 40, [self.change()])
        deploy.write_json(self.workflow.pending_path, {"backup": backup.name})
        self.path.write_bytes(b"unrelated edit")
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.rollback(backup.name)
        self.assertTrue(self.workflow.pending_path.exists())


if __name__ == "__main__":
    unittest.main()
