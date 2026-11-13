#!/usr/bin/python3
"""Guarded, file-delta deployment for the existing Linux test installation."""

import argparse
import datetime
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import re
import shutil
import stat
import subprocess
import sys
import tempfile
import urllib.error
import urllib.request
import uuid


DEFAULT_CONFIG = "/var/lib/web-amr-deploy/config.json"
COMMIT = re.compile(r"[0-9a-f]{40}")
BACKUP_ID = re.compile(r"\d{8}T\d{6}Z-[0-9a-f]{8}")
RUNTIME_ROOTS = {
    "assets", "system", "Classes", "templates",
}
APP_ROOTS = {
    "controllers", "models", "libraries", "views", "helpers", "core", "hooks",
    "language", "third_party",
}
EXTENSIONS = {
    ".php", ".js", ".css", ".json", ".map", ".png", ".jpg", ".jpeg", ".gif",
    ".svg", ".ico", ".woff", ".woff2", ".ttf", ".eot", ".otf", ".html", ".txt",
}


class DeploymentError(RuntimeError):
    pass


def sha(data):
    return hashlib.sha256(data).hexdigest()


def policy(name):
    """Return runtime, ignore, or protected. Unknown paths fail closed."""
    path = PurePosixPath(name)
    if (not name or not path.parts or name != path.as_posix()
            or path.is_absolute() or ".." in path.parts or "\\" in name
            or any(ord(char) < 32 for char in name)):
        raise DeploymentError("Unsafe relative path")
    parts = path.parts
    if parts[0] in {"tests", "docs"} or name in {".gitignore", "README.md"}:
        return "ignore"
    if parts[0] == "deploy":
        return "protected" if name.endswith(".conf.example") else "ignore"
    if (parts[0] in {"database", "reports", "meter_upload_list"}
            or name.startswith(("application/config/", "application/logs/", "application/cache/"))
            or name == "templates/highcharts/scripts/db_config.php"
            or path.name.lower() == "index.php"
            or parts[0] == "assets" and path.suffix.lower() == ".php"
            or any(part.startswith(".") for part in parts)
            or any(part.lower() in {"old", "backup", "backups", "examples", "demo", "demos", "tests"}
                   for part in parts)):
        return "protected"
    runtime = (parts[0] in RUNTIME_ROOTS or
               len(parts) > 2 and parts[0] == "application" and parts[1] in APP_ROOTS)
    if runtime and path.suffix.lower() in EXTENSIONS:
        return "runtime"
    return "protected"


def safe_path(root, name):
    """Refuse symlinked ancestors, including a symlink at the leaf."""
    if policy(name) != "runtime":
        raise DeploymentError("Not an automatically deployable runtime path: " + name)
    root = Path(root)
    destination = root
    for part in PurePosixPath(name).parts:
        destination /= part
        if destination.is_symlink():
            raise DeploymentError("Symlink in deployment path: " + name)
    return destination


def file_hash(path):
    path = Path(path)
    if path.is_symlink():
        raise DeploymentError("Refusing symlink: " + str(path))
    if not path.exists():
        return None
    if not path.is_file():
        raise DeploymentError("Not a regular file: " + str(path))
    return sha(path.read_bytes())


def atomic_write(path, data, uid=None, gid=None, mode=0o600):
    path = Path(path)
    descriptor, temporary = tempfile.mkstemp(prefix=".web-amr-", dir=path.parent)
    try:
        with os.fdopen(descriptor, "wb") as stream:
            stream.write(data)
            stream.flush()
            os.fsync(stream.fileno())
        if uid is not None:
            os.chown(temporary, uid, gid)
        os.chmod(temporary, mode)
        os.replace(temporary, path)
        if hasattr(os, "O_DIRECTORY"):
            directory = os.open(path.parent, os.O_RDONLY | os.O_DIRECTORY)
            try:
                os.fsync(directory)
            finally:
                os.close(directory)
    finally:
        if os.path.exists(temporary):
            os.unlink(temporary)


def write_json(path, value):
    atomic_write(path, (json.dumps(value, indent=2, sort_keys=True) + "\n").encode())


def require_hash(path, expected, name):
    if file_hash(path) != expected:
        raise DeploymentError("Live-file drift; manual review required: " + name)


def validate_delta(changes, managed, release):
    for change in changes:
        name, action = change["path"], change["action"]
        path = safe_path(release, name)
        if action == "A":
            if name in managed or path.exists():
                raise DeploymentError("New source path already exists on server: " + name)
        else:
            if name not in managed:
                raise DeploymentError("Source path was not verified at baseline: " + name)
            require_hash(path, managed[name], name)


class Workflow:
    def __init__(self, config):
        self.config = config
        self.storage = Path(config["storage"])
        self.state_path = self.storage / "state.json"
        self.pending_path = self.storage / "pending.json"
        self.user = config["user"]
        self.repo = Path(config["repo"])

    def command(self, args, user=False, capture=False, cwd=None):
        environment = {
            "PATH": self.config["work"] + "/bin:/usr/local/bin:/usr/bin:/bin", "LANG": "C.UTF-8",
            "HOME": self.config["work"], "GIT_TERMINAL_PROMPT": "0",
            "GIT_CONFIG_NOSYSTEM": "1", "GIT_CONFIG_GLOBAL": "/dev/null",
        }
        if user:
            args = ["/usr/sbin/runuser", "-u", self.user, "--", *map(str, args)]
        result = subprocess.run(
            list(map(str, args)), env=environment, cwd=cwd,
            stdout=subprocess.PIPE if capture else None,
            stderr=subprocess.PIPE if capture else None, check=False, timeout=600,
        )
        if result.returncode:
            # Do not echo arbitrary subprocess diagnostics that might contain secrets.
            raise DeploymentError("Command failed (exit {}): {}".format(result.returncode, args[-1]))
        return result.stdout if capture else b""

    def git(self, *args):
        return self.command(["/usr/bin/git", "-C", self.repo, *args], user=True, capture=True)

    def release(self):
        current = Path(self.config["current"])
        if not current.is_symlink():
            raise DeploymentError("Expected the existing current symlink")
        release = current.resolve(strict=True)
        if not release.is_relative_to(Path(self.config["releases"])):
            raise DeploymentError("Unexpected active release location")
        return release

    def state(self):
        if not self.state_path.exists():
            raise DeploymentError("Run init with a reviewed baseline commit first")
        state = json.loads(self.state_path.read_text())
        if str(self.release()) != state["release"]:
            raise DeploymentError("Active release changed outside this workflow")
        return state

    def fetch(self):
        if self.git("remote", "get-url", "origin").decode().strip() != self.config["remote"]:
            raise DeploymentError("Unexpected Git remote")
        self.git("fetch", "--no-tags", "origin", "+refs/heads/main:refs/remotes/origin/main")
        commit = self.git("rev-parse", "refs/remotes/origin/main").decode().strip()
        if not COMMIT.fullmatch(commit):
            raise DeploymentError("Expected a full source commit")
        return commit

    def tree(self, commit):
        entries = {}
        for record in self.git("ls-tree", "-r", "-z", commit).split(b"\0"):
            if not record:
                continue
            metadata, encoded_name = record.split(b"\t", 1)
            mode, kind, oid = metadata.decode().split()
            entries[encoded_name.decode()] = (mode, kind, oid)
        return entries

    def blob(self, entry):
        mode, kind, oid = entry
        if kind != "blob" or mode != "100644":
            raise DeploymentError("Only non-executable regular runtime files are supported")
        return self.git("cat-file", "blob", oid)

    def initialize(self, commit):
        if self.state_path.exists():
            raise DeploymentError("Baseline already exists; refusing to replace it")
        if not COMMIT.fullmatch(commit):
            raise DeploymentError("Use a full reviewed commit hash")
        self.fetch()
        self.git("merge-base", "--is-ancestor", commit, "refs/remotes/origin/main")
        release = self.release()
        managed, mismatched, missing = {}, [], []
        for name, entry in self.tree(commit).items():
            if policy(name) != "runtime":
                continue
            path = safe_path(release, name)
            actual = file_hash(path)
            if actual is None:
                missing.append(name)
            elif actual == sha(self.blob(entry)):
                managed[name] = actual
            else:
                mismatched.append(name)
        if not managed:
            raise DeploymentError("No matching managed runtime files")
        audit = {"mismatched": mismatched, "missing": missing}
        write_json(self.storage / "baseline-audit.json", audit)
        write_json(self.state_path, {
            "version": 1, "commit": commit, "release": str(release),
            "managed": managed, "last_backup": None,
        })
        print("BASELINE", commit, "managed", len(managed),
              "unmanaged_mismatches", len(mismatched), "unmanaged_missing", len(missing))
        print("AUDIT", self.storage / "baseline-audit.json")

    def status(self):
        state = self.state()
        if self.pending_path.exists():
            pending = json.loads(self.pending_path.read_text())
            print("RECOVERY_REQUIRED", pending["backup"])
            raise DeploymentError("Interrupted deployment; use rollback with the printed backup ID")
        drift = [name for name, expected in state["managed"].items()
                 if file_hash(safe_path(state["release"], name)) != expected]
        print("SOURCE_BASELINE", state["commit"])
        print("ACTIVE_RELEASE", state["release"])
        print("MANAGED_FILES", len(state["managed"]))
        print("LAST_BACKUP", state["last_backup"])
        for name in drift:
            print("DRIFT", name)
        if drift:
            raise DeploymentError("Managed-file drift detected")

    def prepare(self, state, target):
        self.git("merge-base", "--is-ancestor", state["commit"], target)
        tokens = self.git("diff", "--no-renames", "--name-status", "-z",
                          state["commit"], target).split(b"\0")
        entries = self.tree(target)
        changes, ignored, blocked = [], [], []
        for index in range(0, len(tokens) - 1, 2):
            action, name = tokens[index].decode(), tokens[index + 1].decode()
            category = policy(name)
            if category == "ignore":
                ignored.append(name)
            elif category != "runtime" or action not in {"A", "M", "D"}:
                blocked.append(name)
            else:
                data = None if action == "D" else self.blob(entries[name])
                changes.append({"path": name, "action": action, "data": data,
                                "target": None if data is None else sha(data)})
        for name in ignored:
            print("NON_RUNTIME", name)
        for name in blocked:
            print("MANUAL_REVIEW", name)
        if blocked:
            raise DeploymentError("Protected or unsupported changes; deploy separately")
        validate_delta(changes, state["managed"], state["release"])
        for change in changes:
            print(change["action"], change["path"])
        return changes

    def tests(self, target, changes):
        workspace = Path(self.config["work"]) / ("verify-" + uuid.uuid4().hex)
        self.git("worktree", "add", "--detach", str(workspace), target)
        try:
            self.command(["/usr/bin/python3", workspace / "tests/run_php84_tests.py",
                          "--php", "/usr/bin/php8.4"], user=True, cwd=workspace)
            self.command(["/usr/bin/python3", workspace / "tests/rtu_gateway_test.py"],
                         user=True, cwd=workspace)
            node = shutil.which("node")
            if node:
                self.command([node, workspace / "tests/meter_upload_ui_test.js"],
                             user=True, cwd=workspace)
            elif any(item["path"] == "assets/meter_upload.js" for item in changes):
                raise DeploymentError("Node.js is required to validate upload JavaScript changes")
            else:
                print("NOTE Node unavailable; unchanged upload JavaScript was not re-tested")
        finally:
            self.git("worktree", "remove", "--force", str(workspace))

    def health(self):
        self.command(["/usr/bin/systemctl", "is-active", "apache2", "php8.4-fpm", "mariadb"])
        for path, expected in (
            ("/amr/", 200), ("/amr/index.php/login", 200),
            ("/amr/index.php/api/meters/import/token", 401),
            ("/amr/index.php/api/gateways/import/token", 401),
            ("/http_post_server.php", 405),
        ):
            request = urllib.request.Request(self.config["url"] + path, method="GET")
            try:
                with urllib.request.urlopen(request, timeout=15) as response:
                    code = response.status
            except urllib.error.HTTPError as error:
                code = error.code
            if code != expected:
                raise DeploymentError("HTTP check failed: " + path + " status " + str(code))
            print("HTTP", path, code)

    def reload(self):
        self.command(["/usr/bin/systemctl", "reload", "php8.4-fpm"])

    def snapshot(self, state, target, changes):
        identifier = datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        identifier += "-" + uuid.uuid4().hex[:8]
        backup = self.storage / "backups" / identifier
        backup.mkdir(mode=0o700, parents=True)
        records = {}
        release = Path(state["release"])
        for change in changes:
            name = change["path"]
            path = safe_path(release, name)
            metadata = path.stat() if path.exists() else None
            data = path.read_bytes() if metadata else None
            before = sha(data) if data is not None else None
            expected = None if change["action"] == "A" else state["managed"][name]
            if before != expected:
                raise DeploymentError("File changed while preparing backup: " + name)
            if metadata:
                saved = backup / "files" / name
                saved.parent.mkdir(mode=0o700, parents=True, exist_ok=True)
                saved.write_bytes(data)
                os.chmod(saved, 0o600)
            records[name] = {
                "before": before, "after": change["target"],
                "uid": metadata.st_uid if metadata else 0,
                "gid": metadata.st_gid if metadata else self.config["web_gid"],
                "mode": stat.S_IMODE(metadata.st_mode) if metadata else 0o640,
            }
        write_json(backup / "manifest.json", {
            "previous_state": state, "target": target, "files": records,
            "status": "prepared",
        })
        print("BACKUP", backup, flush=True)
        return backup

    def restore(self, backup, manifest):
        release = Path(manifest["previous_state"]["release"])
        for name, record in manifest["files"].items():
            path = safe_path(release, name)
            if record["before"] is None:
                if path.exists():
                    path.unlink()
            else:
                data = (backup / "files" / name).read_bytes()
                if sha(data) != record["before"]:
                    raise DeploymentError("Backup checksum mismatch: " + name)
                atomic_write(path, data, record["uid"], record["gid"], record["mode"])
        write_json(self.state_path, manifest["previous_state"])
        self.reload()
        self.health()

    def apply(self, state, target, changes):
        self.status()
        release = self.release()
        if str(release) != state["release"]:
            raise DeploymentError("Active release changed during validation")
        validate_delta(changes, state["managed"], release)
        for change in changes:
            if change["data"] is not None and change["path"].endswith(".php"):
                with tempfile.TemporaryDirectory(prefix="web-amr-lint-", dir=self.storage) as directory:
                    staged = Path(directory) / "source.php"
                    staged.write_bytes(change["data"])
                    # -n -l parses source without executing it or loading application configuration.
                    self.command(["/usr/bin/php8.4", "-n", "-l", staged])
        backup = self.snapshot(state, target, changes)
        manifest = json.loads((backup / "manifest.json").read_text())
        write_json(self.pending_path, {"backup": backup.name})
        changed = []
        try:
            for change in changes:
                name = change["path"]
                path = safe_path(release, name)
                record = manifest["files"][name]
                require_hash(path, record["before"], name)
                # Record intent before the filesystem operation: an interrupt immediately
                # after os.replace/unlink must still restore this path.
                changed.append(name)
                if change["data"] is None:
                    path.unlink()
                else:
                    missing = []
                    parent = path.parent
                    while parent != release and not parent.exists():
                        missing.append(parent)
                        parent = parent.parent
                    for directory in reversed(missing):
                        directory.mkdir(mode=0o750)
                        os.chmod(directory, 0o750)
                        os.chown(directory, 0, self.config["web_gid"])
                    atomic_write(path, change["data"], record["uid"], record["gid"], record["mode"])
            for change in changes:
                path = safe_path(release, change["path"])
                require_hash(path, change["target"], change["path"])
                if path.exists():
                    self.command(["/usr/sbin/runuser", "-u", "www-data", "--",
                                  "/usr/bin/test", "-r", path])
            self.reload()
            self.health()
            managed = dict(state["managed"])
            for change in changes:
                if change["target"] is None:
                    managed.pop(change["path"], None)
                else:
                    managed[change["path"]] = change["target"]
            new_state = dict(state, commit=target, managed=managed, last_backup=backup.name)
            write_json(self.state_path, new_state)
            manifest["status"] = "applied"
            write_json(backup / "manifest.json", manifest)
            self.pending_path.unlink()
            print("DEPLOYED", target, "runtime_files", len(changes))
        except BaseException:
            # Restore only paths actually written; do not overwrite unrelated concurrent edits.
            affected = dict(manifest, files={name: manifest["files"][name] for name in changed})
            self.restore(backup, affected)
            manifest["status"] = "auto-rolled-back"
            write_json(backup / "manifest.json", manifest)
            self.pending_path.unlink()
            print("AUTO_ROLLBACK", backup.name)
            raise

    def update(self, apply=False):
        state = self.state()
        self.status()
        target = self.fetch()
        print("TARGET", target)
        changes = self.prepare(state, target)
        self.tests(target, changes)
        self.health()
        if not apply:
            print("CHECK_OK", target, "runtime_files", len(changes))
            return
        if target == state["commit"]:
            print("ALREADY_CURRENT", target)
            return
        self.apply(state, target, changes)

    def rollback(self, identifier):
        if not BACKUP_ID.fullmatch(identifier):
            raise DeploymentError("Use a backup ID printed by this command")
        state = self.state()
        pending = (json.loads(self.pending_path.read_text())["backup"]
                   if self.pending_path.exists() else None)
        recovering = pending == identifier
        if pending and not recovering:
            raise DeploymentError("Recover the interrupted deployment first: " + pending)
        if not recovering and state["last_backup"] != identifier:
            raise DeploymentError("Only the most recent applied update can be rolled back")
        backup = self.storage / "backups" / identifier
        manifest = json.loads((backup / "manifest.json").read_text())
        if manifest["previous_state"]["release"] != state["release"]:
            raise DeploymentError("Backup belongs to a different release")
        if not recovering and (manifest["status"] != "applied" or state["commit"] != manifest["target"]):
            raise DeploymentError("Backup is not the active applied update")
        for name, record in manifest["files"].items():
            path = safe_path(state["release"], name)
            if recovering:
                if file_hash(path) not in {record["before"], record["after"]}:
                    raise DeploymentError("Unexpected file drift during recovery: " + name)
            else:
                require_hash(path, record["after"], name)
            if record["before"] is not None:
                if file_hash(backup / "files" / name) != record["before"]:
                    raise DeploymentError("Backup checksum mismatch: " + name)
        write_json(self.pending_path, {"backup": identifier})
        self.restore(backup, manifest)
        manifest["status"] = "rolled-back"
        write_json(backup / "manifest.json", manifest)
        self.pending_path.unlink()
        print("ROLLED_BACK", identifier)


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--config", default=DEFAULT_CONFIG)
    commands = parser.add_subparsers(dest="action", required=True)
    initialize = commands.add_parser("init", help="Audit and record a reviewed initial baseline")
    initialize.add_argument("commit")
    for action in ("status", "check", "update"):
        commands.add_parser(action)
    rollback = commands.add_parser("rollback")
    rollback.add_argument("backup_id")
    args = parser.parse_args()
    if not hasattr(os, "geteuid") or os.geteuid() != 0:
        parser.error("Run as the root deployment operator on the Linux server")
    import fcntl

    os.umask(0o077)
    config_path = Path(args.config)
    for protected in (config_path, Path(__file__).resolve()):
        metadata = protected.stat()
        if metadata.st_uid != 0 or metadata.st_mode & 0o022:
            parser.error("Deployment command/config must be root-owned and not group/world writable")
    config = json.loads(config_path.read_text())
    workflow = Workflow(config)
    with (workflow.storage / "deployment.lock").open("a") as lock:
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            parser.error("Another deployment operation is running")
        if args.action == "init":
            workflow.initialize(args.commit)
        elif args.action == "status":
            workflow.status()
        elif args.action == "rollback":
            workflow.rollback(args.backup_id)
        else:
            workflow.update(apply=args.action == "update")


if __name__ == "__main__":
    try:
        main()
    except (DeploymentError, OSError, ValueError) as error:
        print("STOP:", error, file=sys.stderr)
        sys.exit(1)
