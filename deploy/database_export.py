"""Read-only database inventory and schema/setup export using native MySQL tools.

Run this on the source server with Python 3.9+ and matching mysql/mysqldump
(or mariadb/mariadb-dump) binaries. No application credentials are read.

Examples (use absolute paths; store artifacts OUTSIDE the application):
  python3 deploy/database_export.py inventory --defaults-file /secure/source.cnf --output /secure/amr-audit
  python3 deploy/database_export.py export --defaults-file /secure/source.cnf --manifest /secure/amr-audit/manifest.json --output /secure/amr-export --setup-writes-paused

The client option file must already exist, have restricted access, and contain
the [client] connection settings. Never commit it or pass a password on the CLI.
This tool does NOT restore SQL, modify the source, or select a target runtime.
schema.sql includes trigger/event definitions: do not blindly restore it before
setup-data.sql. The DBA must review trigger ordering, disabled event execution,
DEFINER ownership, and destination-only session/device-state resets first.
"""

import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import subprocess
import sys


ROOT = Path(__file__).resolve().parents[1]
SETUP_CANDIDATES = {
    "user_tb", "client_tb", "meter_site", "meter_site_sub_group",
    "meter_details", "meter_rtu", "meter_model_config", "user_access_group",
    "user_client_meter_access", "user_meter_group_access",
    "meter_energy_report_name", "meter_instantaneous_energy_report_name",
}


class ExportError(Exception):
    pass


def identifier(value):
    # Deliberately restrict identifiers before placing them in native CLI/SQL.
    if not re.fullmatch(r"[A-Za-z_][A-Za-z0-9_]*", value):
        raise ExportError("Unsupported database/table identifier; review manually.")
    return value


def outside_repository(path):
    path = Path(path).expanduser().resolve()
    if path == ROOT or ROOT in path.parents:
        raise ExportError("Keep connection files and exports outside the application repository.")
    return path


def connection_file(path):
    path = outside_repository(path)
    if not path.is_file():
        raise ExportError("The protected client option file does not exist.")
    if os.name != "nt" and path.stat().st_mode & 0o077:
        raise ExportError("Client option file must not be accessible by group/others (use mode 600).")
    return path


def native_command(binary, defaults, database):
    # MySQL requires defaults-file options before all other options.
    # Use a dedicated --defaults-file rather than overlaying normal option
    # files. The DBA must also check native login-path/environment overrides.
    return [binary, "--defaults-file=" + str(defaults), "--database=" + identifier(database)]


def invoke(command):
    try:
        result = subprocess.run(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                                encoding="utf-8", errors="replace", check=False)
    except OSError:
        raise ExportError("Cannot start native database client; check the selected binary.") from None
    if result.returncode:
        # Client errors can contain sensitive connection details. Do not echo them.
        raise ExportError("Native database command failed; have the DBA check connectivity and privileges.")
    return result.stdout


def query(args, defaults, sql):
    command = native_command(args.mysql, defaults, args.database)
    command += ["--batch", "--raw", "--skip-column-names", "--execute=" + sql]
    return [line.split("\t") for line in invoke(command).splitlines() if line]


def inventory(args, defaults):
    name = identifier(args.database)
    schema = "'" + name + "'"
    details = query(args, defaults,
                    "SELECT VERSION(), @@version_comment, @@sql_mode, "
                    "@@session.time_zone, @@system_time_zone")
    database = query(args, defaults,
                     "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME "
                     "FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=" + schema)
    tables = query(args, defaults,
                   "SELECT TABLE_NAME, TABLE_TYPE, COALESCE(ENGINE,''), "
                   "COALESCE(TABLE_COLLATION,'') FROM information_schema.TABLES "
                   "WHERE TABLE_SCHEMA=" + schema + " ORDER BY TABLE_NAME")
    if len(details) != 1 or len(database) != 1 or not tables:
        raise ExportError("Source metadata is incomplete or the selected database is empty.")
    definitions = {}
    # HEX preserves multiline definitions/defaults in raw TSV output. These
    # artifacts are private; they must not be displayed or committed.
    metadata_queries = {
        "columns": "SELECT TABLE_NAME, ORDINAL_POSITION, COLUMN_NAME, COLUMN_TYPE, "
                   "IS_NULLABLE, IF(COLUMN_DEFAULT IS NULL,'NULL',CONCAT('HEX:',HEX(COLUMN_DEFAULT))), "
                   "EXTRA, COALESCE(CHARACTER_SET_NAME,''), COALESCE(COLLATION_NAME,'') "
                   "FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=" + schema
                   + " ORDER BY TABLE_NAME, ORDINAL_POSITION",
        "indexes": "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, "
                   "COALESCE(COLUMN_NAME,''), COALESCE(SUB_PART,''), INDEX_TYPE "
                   "FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=" + schema
                   + " ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX",
        "constraints": "SELECT TABLE_NAME, CONSTRAINT_NAME, CONSTRAINT_TYPE "
                       "FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=" + schema
                       + " ORDER BY TABLE_NAME, CONSTRAINT_NAME",
        "relationships": "SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, ORDINAL_POSITION, "
                         "COALESCE(REFERENCED_TABLE_SCHEMA,''), COALESCE(REFERENCED_TABLE_NAME,''), "
                         "COALESCE(REFERENCED_COLUMN_NAME,'') "
                         "FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=" + schema
                         + " ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION",
        "views": "SELECT TABLE_NAME, HEX(VIEW_DEFINITION), CHECK_OPTION, SECURITY_TYPE, DEFINER "
                 "FROM information_schema.VIEWS WHERE TABLE_SCHEMA=" + schema + " ORDER BY TABLE_NAME",
        "triggers": "SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING, "
                    "HEX(ACTION_STATEMENT), DEFINER FROM information_schema.TRIGGERS "
                    "WHERE TRIGGER_SCHEMA=" + schema + " ORDER BY TRIGGER_NAME",
        "routines": "SELECT ROUTINE_NAME, ROUTINE_TYPE, HEX(ROUTINE_DEFINITION), "
                    "SECURITY_TYPE, DEFINER FROM information_schema.ROUTINES "
                    "WHERE ROUTINE_SCHEMA=" + schema + " ORDER BY ROUTINE_TYPE, ROUTINE_NAME",
        "events": "SELECT EVENT_NAME, HEX(EVENT_DEFINITION), STATUS, DEFINER "
                  "FROM information_schema.EVENTS WHERE EVENT_SCHEMA=" + schema + " ORDER BY EVENT_NAME",
    }
    for label, sql in metadata_queries.items():
        definitions[label] = query(args, defaults, sql)
    return {
        "database": name,
        "server": dict(zip(("version", "product", "sql_mode", "session_timezone",
                            "system_timezone"), details[0])),
        "defaults": dict(zip(("charset", "collation"), database[0])),
        "objects": [dict(zip(("name", "type", "engine", "collation"), row)) for row in tables],
        "definitions": definitions,
    }


def manifest_template(metadata):
    return {
        "database": metadata["database"],
        "approved": False,
        "retain": [],
        "empty": [],
        "unclassified": [obj["name"] for obj in metadata["objects"] if obj["type"] == "BASE TABLE"],
        "setup_candidates_for_review": sorted(
            SETUP_CANDIDATES.intersection(obj["name"] for obj in metadata["objects"]
                                         if obj["type"] == "BASE TABLE")),
    }


def validate_manifest(manifest, metadata):
    if not isinstance(manifest, dict):
        raise ExportError("Manifest must be a JSON object.")
    if manifest.get("row_filters"):
        raise ExportError("Filtered row exports require a separate dependency-reviewed export procedure.")
    if manifest.get("database") != metadata["database"] or manifest.get("approved") is not True:
        raise ExportError("Manifest must name the source database and have explicit DBA approval.")
    if manifest.get("unclassified"):
        raise ExportError("Classify every base table before exporting setup data.")
    retain, empty = manifest.get("retain"), manifest.get("empty")
    if not isinstance(retain, list) or not isinstance(empty, list) or not retain:
        raise ExportError("Manifest requires a nonempty retain list and an empty-data table list.")
    if not all(isinstance(name, str) for name in retain + empty):
        raise ExportError("Manifest table names must be strings.")
    for name in retain + empty:
        identifier(name)
    if len(set(retain + empty)) != len(retain + empty):
        raise ExportError("Manifest has duplicate or conflicting table classifications.")
    bases = {obj["name"]: obj for obj in metadata["objects"] if obj["type"] == "BASE TABLE"}
    if set(retain + empty) != set(bases):
        raise ExportError("Manifest must classify exactly all live base tables; views are schema-only.")
    if "meter_data" in retain:
        raise ExportError("Historical meter_data cannot be included in this setup-only migration.")
    if "user_tb" not in retain:
        raise ExportError("The selected setup migration requires user_tb.")
    if any(bases[name]["engine"].lower() != "innodb" for name in retain):
        raise ExportError("Selected tables are not all InnoDB; arrange a DBA-reviewed locked export.")
    return sorted(retain)


def write_json(path, value):
    with path.open("x", encoding="utf-8", newline="\n") as stream:
        json.dump(value, stream, indent=2)
        stream.write("\n")


def dump(args, defaults, output, options, tables=()):
    # mysqldump does not accept mysql's --database= syntax.
    command = [args.mysqldump, "--defaults-file=" + str(defaults)]
    command += list(options) + ["--result-file=" + str(output), identifier(args.database)]
    command += [identifier(name) for name in tables]
    invoke(command)
    if not output.is_file() or output.stat().st_size == 0:
        raise ExportError("Native dump did not produce a nonempty SQL artifact.")


def main(argv=None):
    parser = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    parser.add_argument("action", choices=("inventory", "export"))
    parser.add_argument("--defaults-file", required=True, help="Existing protected native client option file")
    parser.add_argument("--database", default="meter_reading")
    parser.add_argument("--mysql", default="mysql")
    parser.add_argument("--mysqldump", default="mysqldump")
    parser.add_argument("--output", required=True, help="NEW private directory outside this repository")
    parser.add_argument("--manifest")
    parser.add_argument("--setup-writes-paused", action="store_true",
                        help="Confirm setup edits AND DDL remain paused throughout both exports")
    args = parser.parse_args(argv)
    output = None
    try:
        defaults = connection_file(args.defaults_file)
        destination = outside_repository(args.output)
        if destination.exists():
            raise ExportError("Output directory already exists; refusing to overwrite any artifacts.")
        metadata = inventory(args, defaults)
        retain = None
        manifest = None
        if args.action == "export":
            if not args.manifest or not args.setup_writes_paused:
                raise ExportError("Export requires --manifest and --setup-writes-paused.")
            with outside_repository(args.manifest).open(encoding="utf-8") as stream:
                manifest = json.load(stream)
            retain = validate_manifest(manifest, metadata)
            columns = query(args, defaults,
                            "SELECT COLUMN_NAME FROM information_schema.COLUMNS "
                            "WHERE TABLE_SCHEMA='" + identifier(args.database) + "' "
                            "AND TABLE_NAME='user_tb' AND COLUMN_NAME IN "
                            "('active_session_hash','active_session_activity')")
            if {row[0] for row in columns} != {"active_session_hash", "active_session_activity"}:
                raise ExportError("Source schema lacks the session-lock columns required by this application.")
        destination.mkdir(mode=0o700)
        output = destination
        write_json(output / "inventory.json", metadata)
        write_json(output / "manifest.json", manifest or manifest_template(metadata))
        if args.action == "export":
            dump(args, defaults, output / "schema.sql",
                 ("--no-data", "--routines", "--events", "--triggers", "--skip-lock-tables"))
            dump(args, defaults, output / "setup-data.sql",
                 ("--no-create-info", "--skip-triggers", "--skip-add-locks",
                  "--skip-disable-keys", "--complete-insert", "--hex-blob",
                  "--single-transaction", "--skip-lock-tables"), retain)
            if inventory(args, defaults) != metadata:
                raise ExportError("Source metadata changed during export; this bundle is not valid.")
        checksums = []
        for path in sorted(output.iterdir()):
            digest = hashlib.sha256()
            with path.open("rb") as stream:
                for chunk in iter(lambda: stream.read(1024 * 1024), b""):
                    digest.update(chunk)
            checksums.append(digest.hexdigest() + "  " + path.name)
        with (output / "SHA256SUMS").open("x", encoding="ascii", newline="\n") as stream:
            stream.write("\n".join(checksums) + "\n")
        # Consumers must require this marker; partial directories are not valid bundles.
        with (output / "COMPLETE").open("x", encoding="ascii") as stream:
            stream.write(args.action + "\n")
        print("Private artifacts created. Review metadata and manifest before any destination restore.")
        if os.name == "nt":
            print("Windows: verify restrictive NTFS ACLs on the option file and artifact directory.")
        return 0
    except (ExportError, OSError, ValueError) as error:
        if isinstance(error, ExportError):
            print("Export stopped: " + str(error), file=sys.stderr)
        else:
            print("Export stopped: cannot read/write the requested artifacts or manifest.", file=sys.stderr)
        if output is not None:
            print("Partial artifacts remain private but are NOT a valid export (no COMPLETE marker).",
                  file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
