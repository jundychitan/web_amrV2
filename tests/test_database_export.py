"""Offline deployment tests: python -m unittest discover -s tests -p test_*.py."""

from contextlib import redirect_stderr, redirect_stdout
import copy
import importlib.util
import io
import json
from pathlib import Path
import subprocess
import tempfile
import unittest
from unittest.mock import patch


SPEC = importlib.util.spec_from_file_location(
    "database_export", Path(__file__).resolve().parents[1] / "deploy" / "database_export.py")
export = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(export)


def metadata():
    return {
        "database": "meter_reading",
        "objects": [
            {"name": "user_tb", "type": "BASE TABLE", "engine": "InnoDB"},
            {"name": "meter_site", "type": "BASE TABLE", "engine": "InnoDB"},
            {"name": "meter_data", "type": "BASE TABLE", "engine": "InnoDB"},
            {"name": "meter_data_view", "type": "VIEW", "engine": ""},
        ],
    }


def manifest():
    return {"database": "meter_reading", "approved": True,
            "retain": ["user_tb", "meter_site"], "empty": ["meter_data"], "unclassified": []}


class ManifestTests(unittest.TestCase):
    def test_generated_manifest_needs_review(self):
        generated = export.manifest_template(metadata())
        self.assertFalse(generated["approved"])
        self.assertEqual(generated["retain"], [])
        self.assertNotIn("meter_data_view", generated["unclassified"])
        with self.assertRaises(export.ExportError):
            export.validate_manifest(generated, metadata())

    def test_valid_full_table_classification(self):
        self.assertEqual(export.validate_manifest(manifest(), metadata()), ["meter_site", "user_tb"])

    def test_rejects_unsafe_manifests(self):
        mutations = [
            {"approved": False}, {"approved": "true"}, {"database": "other"},
            {"unclassified": ["meter_data"]}, {"retain": []}, {"retain": "user_tb"},
            {"empty": []}, {"empty": ["meter_data", "unknown"]},
            {"empty": ["meter_data", "meter_site"]},
            {"retain": ["user_tb", "meter_site", "meter_data"], "empty": []},
            {"retain": ["user_tb", "meter_site", "meter_data_view"]},
            {"retain": ["user_tb", "--all-databases"]},
            {"row_filters": {"user_tb": "id=1"}},
            {"retain": ["meter_site"], "empty": ["user_tb", "meter_data"]},
        ]
        for mutation in mutations:
            with self.subTest(mutation=mutation), self.assertRaises(export.ExportError):
                export.validate_manifest(dict(manifest(), **mutation), metadata())

    def test_rejects_nontransactional_setup_tables(self):
        current = metadata()
        current["objects"][0]["engine"] = "MyISAM"
        with self.assertRaises(export.ExportError):
            export.validate_manifest(manifest(), current)

    def test_rejects_nonobject_manifest(self):
        with self.assertRaises(export.ExportError):
            export.validate_manifest([], metadata())


class NativeCommandTests(unittest.TestCase):
    def test_defaults_file_is_first_and_credentials_are_not_arguments(self):
        option_file = Path("/private/source.cnf")
        command = export.native_command("mysql", option_file, "meter_reading")
        self.assertEqual(command[1], "--defaults-file=" + str(option_file))
        self.assertFalse(any("--password" in arg for arg in command))

    def test_rejects_sql_and_option_injection(self):
        for name in ("meter_reading'; DROP TABLE user_tb", "--all-databases", "db`", ""):
            with self.subTest(name=name), self.assertRaises(export.ExportError):
                export.identifier(name)

    def test_client_failure_does_not_disclose_stderr(self):
        result = subprocess.CompletedProcess([], 1, "", "sensitive client diagnostic")
        with patch.object(export.subprocess, "run", return_value=result):
            with self.assertRaises(export.ExportError) as raised:
                export.invoke(["mysql"])
        self.assertNotIn("sensitive", str(raised.exception))

    def test_cannot_write_artifacts_in_repository(self):
        with self.assertRaises(export.ExportError):
            export.outside_repository(export.ROOT / "database" / "dump.sql")

    def test_missing_native_binary(self):
        with patch.object(export.subprocess, "run", side_effect=FileNotFoundError):
            with self.assertRaises(export.ExportError):
                export.invoke(["nonexistent-mysql"])

    def test_dump_requires_an_actual_nonempty_artifact(self):
        args = type("Arguments", (), {"mysqldump": "mysqldump", "database": "meter_reading"})()
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "schema.sql"
            with patch.object(export, "invoke", return_value=""):
                with self.assertRaises(export.ExportError):
                    export.dump(args, Path(directory) / "source.cnf", output, ("--no-data",))

    def test_dump_preserves_native_sql_and_does_not_modify_it(self):
        args = type("Arguments", (), {"mysqldump": "mysqldump", "database": "meter_reading"})()
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "schema.sql"
            expected = b"-- native schema\nCREATE TABLE example (id INT);\n"

            def fake_native(command):
                self.assertEqual(command[1], "--defaults-file=" + str(Path(directory) / "source.cnf"))
                self.assertEqual(command[-1], "meter_reading")
                self.assertIn("--no-data", command)
                output.write_bytes(expected)

            with patch.object(export, "invoke", side_effect=fake_native):
                export.dump(args, Path(directory) / "source.cnf", output, ("--no-data",))
            self.assertEqual(output.read_bytes(), expected)


class ExportWorkflowTests(unittest.TestCase):
    def execute(self, args):
        with redirect_stdout(io.StringIO()), redirect_stderr(io.StringIO()):
            return export.main(args)

    def test_inventory_is_not_a_data_export(self):
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "inventory"
            with patch.object(export, "connection_file", return_value=Path(directory) / "source.cnf"), \
                    patch.object(export, "inventory", return_value=metadata()), \
                    patch.object(export, "dump") as dump:
                status = self.execute(["inventory", "--defaults-file", "unused", "--output", str(output)])
            self.assertEqual(status, 0)
            dump.assert_not_called()
            self.assertEqual((output / "COMPLETE").read_text().strip(), "inventory")
            self.assertFalse(json.loads((output / "manifest.json").read_text())["approved"])

    def test_refuses_existing_output_directory(self):
        with tempfile.TemporaryDirectory() as directory:
            with patch.object(export, "connection_file", return_value=Path(directory) / "source.cnf"), \
                    patch.object(export, "inventory") as inventory:
                status = self.execute(["inventory", "--defaults-file", "unused", "--output", directory])
            self.assertEqual(status, 1)
            inventory.assert_not_called()

    def test_export_requires_pause_confirmation(self):
        with tempfile.TemporaryDirectory() as directory:
            output = Path(directory) / "export"
            with patch.object(export, "connection_file", return_value=Path(directory) / "source.cnf"), \
                    patch.object(export, "inventory", return_value=metadata()), \
                    patch.object(export, "dump") as dump:
                status = self.execute(["export", "--defaults-file", "unused", "--output", str(output)])
            self.assertEqual(status, 1)
            self.assertFalse(output.exists())
            dump.assert_not_called()

    def run_export(self, directory, inventories, columns=None, fail_dump=False):
        output = Path(directory) / "export"
        manifest_path = Path(directory) / "manifest.json"
        manifest_path.write_text(json.dumps(manifest()), encoding="utf-8")
        calls = []

        def fake_dump(args, defaults, path, options, tables=()):
            calls.append((path.name, options, tables))
            if fail_dump:
                raise export.ExportError("Simulated dump failure")
            path.write_text("-- disposable fake SQL\n", encoding="utf-8")

        with patch.object(export, "connection_file", return_value=Path(directory) / "source.cnf"), \
                patch.object(export, "inventory", side_effect=inventories), \
                patch.object(export, "query", return_value=columns if columns is not None else [
                    ["active_session_hash"], ["active_session_activity"]]), \
                patch.object(export, "dump", side_effect=fake_dump):
            status = self.execute([
                "export", "--defaults-file", "unused", "--manifest", str(manifest_path),
                "--output", str(output), "--setup-writes-paused"])
        return status, output, calls

    def test_export_uses_native_schema_and_allowlisted_data(self):
        with tempfile.TemporaryDirectory() as directory:
            status, output, calls = self.run_export(directory, [metadata(), metadata()])
            self.assertEqual(status, 0)
            self.assertEqual((output / "COMPLETE").read_text().strip(), "export")
            self.assertIn("--routines", calls[0][1])
            self.assertIn("--events", calls[0][1])
            self.assertIn("--triggers", calls[0][1])
            self.assertIn("--single-transaction", calls[1][1])
            self.assertIn("--skip-triggers", calls[1][1])
            self.assertEqual(calls[1][2], ["meter_site", "user_tb"])
            self.assertNotIn("meter_data", calls[1][2])
            self.assertTrue((output / "SHA256SUMS").is_file())

    def test_schema_drift_leaves_no_complete_marker(self):
        changed = copy.deepcopy(metadata())
        changed["objects"][0]["engine"] = "MyISAM"
        with tempfile.TemporaryDirectory() as directory:
            status, output, _ = self.run_export(directory, [metadata(), changed])
            self.assertEqual(status, 1)
            self.assertFalse((output / "COMPLETE").exists())

    def test_dump_failure_leaves_no_complete_marker(self):
        with tempfile.TemporaryDirectory() as directory:
            status, output, _ = self.run_export(directory, [metadata()], fail_dump=True)
            self.assertEqual(status, 1)
            self.assertFalse((output / "COMPLETE").exists())

    def test_missing_session_columns_blocks_export(self):
        with tempfile.TemporaryDirectory() as directory:
            status, output, calls = self.run_export(directory, [metadata()], columns=[])
            self.assertEqual(status, 1)
            self.assertFalse(output.exists())
            self.assertEqual(calls, [])


if __name__ == "__main__":
    unittest.main()
