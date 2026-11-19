#!/usr/bin/python3
"""One-time installer for explicitly reviewed hosts; never replaces live app code."""

import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import sys
import zipfile


REMOTE = "https://github.com/jundychitan/web_amrV2.git"
BASE = Path("/opt/web-amr-deploy")
STORAGE = Path("/var/lib/web-amr-deploy")
COMMAND = Path("/usr/local/sbin/web-amr-deploy")
USER = "web-amr-deploy"
MEMBERS = {"deploy/git_deploy.py", "tests/test_git_deploy.py"}
PROFILES = {
    "test": {
        "baseline": "e606559ba53daffbf3ee9c447f4b0c1097983f7b",
        "release": "/srv/web-amr/releases/20261105-e854afe",
        "url": "http://172.104.42.220",
        "initial_check": "check",
    },
    "marigold": {
        "baseline": "7b22699c9007d7588819f431c6265febc71fbe25",
        "release": "/srv/web-amr/releases/test-reference-20261109",
        "hostname": "mamasitas",
        "url": "http://172.16.47.155",
        "initial_check": "verify",
        "health_checks": [
            ["/amr/", 200], ["/amr/index.php/login", 200],
            ["/http_post_server.php", 405], ["/check_time.php", 200],
        ],
    },
}
PREREQUISITES = (
    "/usr/bin/git", "/usr/bin/python3", "/usr/bin/php8.4", "/usr/sbin/runuser",
    "/usr/sbin/useradd", "/usr/bin/systemctl", "/usr/sbin/nologin",
)


def select_profile(name, release, hostname):
    profile = PROFILES[name]
    if str(release) != profile["release"]:
        raise RuntimeError("Active release does not match selected installation profile")
    if profile.get("hostname") and hostname.split(".")[0] != profile["hostname"]:
        raise RuntimeError("Hostname does not match selected installation profile")
    return profile


def check_prerequisites():
    # su shells may omit /usr/sbin from PATH even though these tools are installed.
    for executable in PREREQUISITES:
        if not Path(executable).is_file() or not os.access(executable, os.X_OK):
            raise RuntimeError("Missing prerequisite: " + executable)


def linux_source(data):
    """Preserve source bytes except CRLF, including the executable shebang."""
    return data.replace(b"\r\n", b"\n")


def run(*args, **kwargs):
    subprocess.run(list(map(str, args)), check=True, **kwargs)


def directory(path, mode, uid=0, gid=0):
    path.mkdir(mode=mode)
    os.chmod(path, mode)
    os.chown(path, uid, gid)


def main():
    import grp
    import pwd

    if os.geteuid() != 0:
        raise RuntimeError("Use the root deployment operator")
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("bundle", type=Path)
    parser.add_argument("sha256")
    parser.add_argument("--profile", choices=tuple(PROFILES), default="test")
    args = parser.parse_args()
    bundle = args.bundle
    if hashlib.sha256(bundle.read_bytes()).hexdigest() != args.sha256:
        raise RuntimeError("Bundle checksum mismatch")
    for path in (BASE, STORAGE, COMMAND):
        if path.exists() or path.is_symlink():
            raise RuntimeError("Installation path already exists; review rather than overwrite: " + str(path))
    try:
        pwd.getpwnam(USER)
    except KeyError:
        pass
    else:
        raise RuntimeError("Deployment account already exists; review rather than reuse")
    check_prerequisites()
    release = Path("/srv/web-amr/current").resolve(strict=True)
    profile = select_profile(args.profile, release, socket.gethostname())
    with zipfile.ZipFile(bundle) as archive:
        if set(archive.namelist()) != MEMBERS:
            raise RuntimeError("Unexpected bundle members")
        # A Windows checkout may use CRLF. Linux executable shebangs must use LF.
        contents = {name: linux_source(archive.read(name)) for name in MEMBERS}
    os.umask(0o077)
    print("INSTALL isolated deployment account and private directories", flush=True)
    run("/usr/sbin/useradd", "--system", "--user-group", "--no-create-home",
        "--home-dir", str(BASE / "work"), "--shell", "/usr/sbin/nologin", USER)
    account = pwd.getpwnam(USER)
    directory(BASE, 0o711)
    directory(BASE / "work", 0o700, account.pw_uid, account.pw_gid)
    directory(BASE / "work/bin", 0o700, account.pw_uid, account.pw_gid)
    (BASE / "work/bin/php").symlink_to("/usr/bin/php8.4")
    directory(STORAGE, 0o700)
    directory(STORAGE / "backups", 0o700)
    tooling = BASE / "tooling"
    directory(tooling, 0o755)
    directory(tooling / "deploy", 0o755)
    directory(tooling / "tests", 0o755)
    for name, data in contents.items():
        destination = tooling / name
        destination.write_bytes(data)
        os.chmod(destination, 0o644)
    shutil.copyfile(tooling / "deploy/git_deploy.py", COMMAND)
    os.chmod(COMMAND, 0o755)
    config = {
        "storage": str(STORAGE), "user": USER,
        "repo": str(BASE / "work/repo"), "work": str(BASE / "work"),
        "current": "/srv/web-amr/current", "releases": "/srv/web-amr/releases",
        "remote": REMOTE, "url": profile["url"],
        "web_gid": grp.getgrnam("www-data").gr_gid,
    }
    if "health_checks" in profile:
        config["health_checks"] = profile["health_checks"]
    (STORAGE / "config.json").write_text(json.dumps(config, indent=2) + "\n")
    os.chmod(STORAGE / "config.json", 0o600)
    environment = {
        "PATH": "/usr/local/bin:/usr/bin:/bin", "HOME": str(BASE / "work"),
        "LANG": "C.UTF-8", "GIT_TERMINAL_PROMPT": "0",
        "GIT_CONFIG_NOSYSTEM": "1", "GIT_CONFIG_GLOBAL": "/dev/null",
    }
    print("CLONE public source into private checkout", flush=True)
    run("/usr/sbin/runuser", "-u", USER, "--", "/usr/bin/git", "clone",
        "--quiet", "--no-checkout", REMOTE, BASE / "work/repo", env=environment)
    print("TEST installed tooling as non-login deployment account", flush=True)
    run("/usr/sbin/runuser", "-u", USER, "--", "/usr/bin/python3",
        "-m", "unittest", "discover", "-s", tooling / "tests", "-p", "test_git_deploy.py", "-v",
        env=environment, cwd=BASE / "work")
    print("AUDIT initial baseline (no app files will be changed)", flush=True)
    run(COMMAND, "init", profile["baseline"])
    run(COMMAND, "status")
    print("CHECK Linux regression suite using", profile["initial_check"], flush=True)
    run(COMMAND, profile["initial_check"])
    print("INSTALLATION_OK", COMMAND, flush=True)
    print("No application files, configuration, sessions, or database records were changed.")
    if args.profile == "marigold":
        print("Newer main has not been deployed. Use check to preview pending protected changes.")


if __name__ == "__main__":
    main()
