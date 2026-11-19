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
    def test_route_exception_never_allows_other_configuration(self):
        with self.assertRaises(deploy.DeploymentError):
            deploy.safe_path("/tmp/release", "application/config/database.php", reviewed_routes=True)
        with self.assertRaises(deploy.DeploymentError):
            deploy.safe_path("/tmp/release", deploy.ROUTES_PATH)

    def test_default_health_checks_are_unchanged(self):
        self.assertEqual(tuple(deploy.health_checks({})), deploy.DEFAULT_HEALTH_CHECKS)
        self.assertIn(("/amr/index.php/api/gateways/import/token", 401),
                      deploy.health_checks({}))

    def test_marigold_health_checks_do_not_require_upload_features(self):
        checks = [["/amr/", 200], ["/amr/index.php/login", 200],
                  ["/http_post_server.php", 405], ["/check_time.php", 200]]
        self.assertEqual(deploy.health_checks({"health_checks": checks}), checks)
        self.assertFalse(any("import" in name for name, _ in checks))

    def test_health_checks_cannot_invoke_gateway_actions(self):
        for checks in ([], [["/rtu/reset_update_csv", 200]],
                       [["/check_time.php?reset=1", 200]], [["/amr/", True]]):
            with self.subTest(checks=checks), self.assertRaises(deploy.DeploymentError):
                deploy.health_checks({"health_checks": checks})

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
        self.workflow.verify_static = mock.Mock()
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

    def reviewed_fixture(self):
        current = (b"<?php\n$route['default_controller'] = \"login\";\n"
                   b"$route['check_time\\.php'] = 'meter_ingest/check_time';\n"
                   b"$route['404_override'] = '';\n\n"
                   b"$route['rtu/custom'] = 'rtu/preserved';\n")
        source = (b"<?php\n" + b"\n".join(
            ("$route['{}'] = '{}';".format(key, value)).encode()
            for key, value in deploy.IMPORT_ROUTES) + b"\n")
        path = self.release / deploy.ROUTES_PATH
        path.parent.mkdir(exist_ok=True, parents=True)
        path.write_bytes(current)
        approval = {
            "baseline": self.state["commit"], "target": "b" * 40,
            "route_before_sha256": deploy.sha(current),
            "route_source_sha256": deploy.sha(source),
            "absent_deleted": deploy.LEGACY_ARTIFACT,
        }
        return path, current, source, approval

    def test_route_merge_preserves_every_existing_byte(self):
        path, current, source, approval = self.reviewed_fixture()
        merged = deploy.merge_import_routes(current, source)
        additions = b"\n".join(
            ("$route['{}'] = '{}';".format(key, value)).encode()
            for key, value in deploy.IMPORT_ROUTES) + b"\n"
        self.assertEqual(merged.replace(additions, b"", 1), current)
        self.assertEqual(path.read_bytes(), current)

    def test_route_merge_rejects_existing_or_conflicting_routes(self):
        path, current, source, approval = self.reviewed_fixture()
        with self.assertRaises(deploy.DeploymentError):
            deploy.merge_import_routes(current + b"// api/meters/import\n", source)
        with self.assertRaises(deploy.DeploymentError):
            deploy.merge_import_routes(current, source.replace(b"meter_import/index", b"unsafe/index"))

    def test_approval_rejects_stale_or_broader_scope(self):
        path, current, source, approval = self.reviewed_fixture()
        deploy.validate_approval(self.state, "b" * 40, approval)
        for key, value in (("baseline", "c" * 40), ("target", "c" * 40),
                           ("absent_deleted", "application/config/database.php"),
                           ("route_before_sha256", "not a hash"), ("extra", "not allowed")):
            with self.subTest(key=key), self.assertRaises(deploy.DeploymentError):
                deploy.validate_approval(self.state, "b" * 40, dict(approval, **{key: value}))

    def test_reviewed_prepare_accepts_exact_merge_and_absent_artifact(self):
        path, current, source, approval = self.reviewed_fixture()
        self.workflow.git = mock.Mock(side_effect=[
            b"", b"M\0" + deploy.ROUTES_PATH.encode() + b"\0D\0"
            + deploy.LEGACY_ARTIFACT.encode() + b"\0",
        ])
        self.workflow.tree = mock.Mock(return_value={deploy.ROUTES_PATH: "source"})
        self.workflow.blob = mock.Mock(return_value=source)
        changes = self.workflow.prepare(self.state, "b" * 40, approval=approval)
        self.assertEqual(len(changes), 1)
        self.assertEqual(changes[0]["data"], deploy.merge_import_routes(current, source))
        self.assertEqual(path.read_bytes(), current)

    def test_reviewed_prepare_never_deletes_a_present_root_artifact(self):
        path, current, source, approval = self.reviewed_fixture()
        artifact = self.release / deploy.LEGACY_ARTIFACT
        artifact.write_bytes(b"existing operator data")
        self.workflow.git = mock.Mock(side_effect=[
            b"", b"D\0" + deploy.LEGACY_ARTIFACT.encode() + b"\0",
        ])
        self.workflow.tree = mock.Mock(return_value={})
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.prepare(self.state, "b" * 40, approval=approval)
        self.assertEqual(artifact.read_bytes(), b"existing operator data")

    def test_reviewed_approval_does_not_allow_database_configuration(self):
        path, current, source, approval = self.reviewed_fixture()
        self.workflow.git = mock.Mock(side_effect=[
            b"", b"M\0application/config/database.php\0",
        ])
        self.workflow.tree = mock.Mock(return_value={})
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.prepare(self.state, "b" * 40, approval=approval)

    def test_interrupted_reviewed_update_recovers_routes_and_baseline(self):
        path, current, source, approval = self.reviewed_fixture()
        merged = deploy.merge_import_routes(current, source)
        changes = [self.change(), self.change(deploy.ROUTES_PATH, data=merged)]
        backup = self.workflow.snapshot(
            self.state, "b" * 40, changes,
            reviewed_routes={deploy.ROUTES_PATH: deploy.sha(current)})
        deploy.write_json(self.workflow.pending_path, {"backup": backup.name})
        path.write_bytes(merged)
        self.workflow.rollback(backup.name)
        self.assertEqual(path.read_bytes(), current)
        self.assertEqual(self.workflow.state(), self.state)

    def test_reviewed_route_drift_or_wrong_source_stops_prepare(self):
        path, current, source, approval = self.reviewed_fixture()
        for wrong_live in (True, False):
            path.write_bytes(b"drift" if wrong_live else current)
            self.workflow.git = mock.Mock(side_effect=[
                b"", b"M\0" + deploy.ROUTES_PATH.encode() + b"\0",
            ])
            self.workflow.tree = mock.Mock(return_value={deploy.ROUTES_PATH: "source"})
            self.workflow.blob = mock.Mock(return_value=source + b"unexpected")
            with self.subTest(wrong_live=wrong_live), self.assertRaises(deploy.DeploymentError):
                self.workflow.prepare(self.state, "b" * 40, approval=approval)

    def test_reviewed_route_update_and_rollback_are_one_transaction(self):
        path, current, source, approval = self.reviewed_fixture()
        merged = deploy.merge_import_routes(current, source)
        changes = [self.change(), self.change(deploy.ROUTES_PATH, data=merged),
                   self.change("assets/new.js", "A")]
        self.workflow.apply(self.state, "b" * 40, changes,
                            reviewed_routes={deploy.ROUTES_PATH: deploy.sha(current)})
        state = self.workflow.state()
        self.assertEqual(path.read_bytes(), merged)
        self.assertIn(deploy.ROUTES_PATH, state["reviewed_paths"])
        self.workflow.status()
        self.workflow.rollback(state["last_backup"])
        self.assertEqual(path.read_bytes(), current)
        self.assertEqual(self.path.read_bytes(), self.old)
        self.assertFalse((self.release / "assets/new.js").exists())
        self.assertEqual(self.workflow.state(), self.state)

    def test_reviewed_route_rolls_back_after_postdeployment_failure(self):
        path, current, source, approval = self.reviewed_fixture()
        changes = [self.change(), self.change(
            deploy.ROUTES_PATH, data=deploy.merge_import_routes(current, source))]
        self.workflow.verify_static.side_effect = deploy.DeploymentError("served asset mismatch")
        with self.assertRaises(deploy.DeploymentError):
            self.workflow.apply(self.state, "b" * 40, changes,
                                reviewed_routes={deploy.ROUTES_PATH: deploy.sha(current)})
        self.assertEqual(path.read_bytes(), current)
        self.assertEqual(self.path.read_bytes(), self.old)
        self.assertEqual(self.workflow.state(), self.state)

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

    def test_verify_tests_baseline_without_fetching_or_deploying(self):
        self.workflow.status = mock.Mock()
        self.workflow.tests = mock.Mock()
        self.workflow.fetch = mock.Mock()
        self.workflow.apply = mock.Mock()
        self.workflow.verify()
        self.workflow.tests.assert_called_once_with(self.state["commit"], [])
        self.workflow.health.assert_called_once()
        self.workflow.fetch.assert_not_called()
        self.workflow.apply.assert_not_called()
        self.workflow.reload.assert_not_called()

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
