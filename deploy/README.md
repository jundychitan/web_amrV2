# Step-by-step deployment: PHP 8.4 and MariaDB 11.8

> **Operating record, 2026-11-09:** The test deployment and the MARIGOLD VPN
> deployment described in [section 12](#12-deployment-and-vpn-operating-record)
> are already running. They currently use HTTP by explicit request and do not
> satisfy all the HTTPS, authenticated-report, or acceptance gates below. The
> following guide remains the target procedure; the operating record describes
> what was actually done and the remaining differences.

## Scope and current verification

Deploy the tested working tree from `E:\GitHub\web_amrV2`, not the original
`E:\GitHub\web_amr`. Preserve the live database schema and approved setup data
(accounts, permissions, sites, meters, RTUs, and configuration), without copying
historical readings or generated reports. This is a separate installation by
default; do not redirect devices, collectors, or existing users automatically.

**Confirmed source:** `10.3.35-MariaDB-1:10.3.35+maria~stretch-log`.
**New-server target:** MariaDB **11.8.x**, with an installed instance reported
as **11.8.6**, plus PHP **8.4** and the existing `mysqli` driver. Use a maintained
11.8.x patch from an approved repository. This guide now targets that installed
series; do not downgrade or reinstall it just to match an earlier recommendation.
The logical migration and actual application queries still need staging
verification before production activation.

## Procedure map: which machine runs each step

Follow this order. SQL files are **exported on the source/export host**, copied
over SSH/SFTP, then **imported on the new server**. The original server stays
operational and is never the target of an import or destination reset.

1. **Local Windows machine:** run the offline suite in `E:\GitHub\web_amrV2`.
   Prepare a reviewed snapshot of the tested working tree, including any
   uncommitted PHP 8.4 changes, for a private `/opt/web-amr-src` workspace.
2. **New Ubuntu/Debian server:** verify the installed MariaDB server is 11.8.x;
   provision PHP 8.4 CLI/FPM and Apache (sections 1–2).
3. **Source/export host:** provision protected source client settings and
   inventory `meter_reading`, including its schema defaults and programmable
   objects (section 3).
4. **Source/export host:** approve which setup-table rows to retain; historical
   `meter_data` rows are excluded (section 4).
5. **Source/export host:** pause setup edits/DDL, extract the schema and selected
   data into SQL files, and checksum the completed bundle (section 5).
6. **Source/export host → new server:** copy the bundle to a private directory
   using SSH/SFTP and verify its checksums (section 5).
7. **New server, staging first:** create an empty `amr_test_mariadb118` database
   using the original defaults, restore reviewed schema/data, and prepare a
   restricted test account (section 6).
8. **New server, private source workspace:** run the real MariaDB 11.8 query
   validator. Provision synthetic staging readings if populated report
   coverage is needed (section 10). Never run it against the old source.
9. **New server:** install the sanitized app release, private session/upload
   directories, production environment, PHP-FPM pool, TLS, and Apache site
   (sections 7–9). Initially point it at staging and restrict access.
10. **New server:** complete browser acceptance, including authorized report
    downloads. Then restore the approved bundle into a separate, fresh
    production `meter_reading` database and switch to its dedicated runtime
    credentials. Do not promote a staging database containing test readings.
11. **New server:** repeat final schema/data and empty-history smoke checks,
    take a destination backup, and approve user traffic/integrations
    (sections 10–11).
12. **Rollback if needed:** disable the new deployment and preserve the unchanged
    original; do not copy new-server files over the old database (section 11).

The schema-only extraction in section 5 produces **definitions, not records**.
For the previously agreed usable fresh installation, also export the approved
setup rows. If you intentionally choose schema only, skip all data-import
commands and separately provision users/sites/meters before expecting login
or reporting to work.

Verified locally on **2026-10-20**, with **PHP 8.4.16**:

- 820 nonarchived PHP files passed lint, including shipped template/vendor PHP.
- 248 offline PHP assertions passed: sessions/framework (13), mysqli fake
  transport (4), spreadsheets (29 per library copy), session locks (61),
  production config (16), MariaDB validator policy/catalog/date inputs (96).
- The real CodeIgniter bootstrap, loader, models, form validation, and query
  builder passed using an isolated synthetic application and in-memory SQLite.
- All 19 Python exporter tests passed.

Reproduce from the project directory on Windows:

```powershell
python tests/run_php84_tests.py --php "C:\Users\Jun\.config\herd\bin\php84\php.exe"
```

Reproduce from a **private source workspace** on the Linux server:

```bash
python3 /opt/web-amr-src/tests/run_php84_tests.py --php /usr/bin/php8.4
```

These checks never use application database credentials. They are **not** proof
of real MySQL query compatibility, browser behavior, Linux permissions, Apache
routing, or PHP-FPM operation. Those checks below are mandatory. CodeIgniter and
PHPExcel are locally patched legacy dependencies, not newly maintained upstream
releases. PHP minors newer than 8.4 have not been validated.

## Stop conditions before production activation

Do not activate the public site until:

1. A DBA verifies source metadata and the exact setup-data selection.
2. The destination schema/data checks and browser workflows pass.
3. Report downloads have an approved authorization solution. The current
   `application/controllers/Report_action.php` generates direct `/reports/`
   links, while `deploy/apache.conf.example` deliberately denies that directory.
   XLSX generation passes offline, but the current template and application
   download links are **not production-ready together**. Implement an
   authenticated, ownership-checked download endpoint (or equivalent authorized
   delivery) and update/test the links before approval. Do not simply expose
   every generated report publicly.
4. Direct PHP endpoints under template directories are assessed. The template
   matches the **basename** `index.php`, not exclusively the release-root path.
   Verify the sanitized release has no other reachable `index.php`, or restrict
   the handler to the exact front controller. Do not assume `<Files "index.php">`
   alone proves a root-only execution policy.
5. External RTU commands, collectors, scheduled jobs, and integrations are
   inventoried and remain isolated until explicitly enabled.

## 1. Record the deployment inputs

### Final prerequisite check: before any export/import window

Record approval/evidence for each item; the examples below do not establish
that these prerequisites already exist:

- **Recoverable backups:** confirm a recent source backup with a tested restore
  path, including historical readings that this setup-only migration excludes.
  Confirm backup ownership, retention, and rollback responsibilities. A schema
  dump/setup export is not a replacement for a full recoverable source backup.
- **Exact destination:** confirm the actual Ubuntu/Debian release, 64-bit
  architecture, supported package origins, administrative access, and installed
  MariaDB server/client versions. Local Windows commands and Linux service
  commands are not interchangeable.
- **Capacity:** reserve storage for the SQL bundle, extracted private workspace,
  application releases, both staging and production databases, indexes,
  temporary files, logs, and backups. Size RAM, PHP-FPM workers, and report
  limits using representative reports, not the small offline fixtures.
- **Network and time:** agree hostname, certificate issuance/renewal, DNS and
  firewall rules, and administrator-only staging access. Do not expose port
  3306 publicly. Verify time synchronization and source/destination/application
  timezone expectations before checking time-based reports/session expiry.
- **Protected configuration:** provision `/secure/amr`, export/restore client
  option files, `/etc/web-amr/web-amr.env`, and `query-test.env` before their first
  use. Keep secret files owner-only and outside Git/web roots. Assign a named
  export, transfer, database-admin, and service operator; a non-root operator
  cannot write root-owned `/secure` or `/opt` paths without explicit provisioning.
- **Separate database accounts:** source exporter, destination schema
  administrator, restricted staging query-test account, staging runtime account,
  and production runtime account. Provision authentication securely before
  applying grants; do not rely on GRANT to create a passwordless account.
  Inspect effective privileges, including roles and pre-existing/global grants.
- **Schema compatibility:** confirm required session-lock columns, engines,
  complete metadata visibility, table-name case, cross-schema dependencies,
  object owners, and the approved setup manifest. Resolve helper guard failures
  through a reviewed procedure, not by altering the source or bypassing checks.
  Verify required storage engines/plugins exist on 11.8 and are not silently
  substituted. Sequences, system-versioned tables, or other special objects
  need object-specific DBA review rather than an assumed generic restore.
- **Operational isolation:** identify all collectors, RTUs, cron jobs, events,
  command flags, and external services. Keep them disconnected from staging
  and the new production database until separately authorized.
- **Staging identity:** prefer a separate HTTPS staging hostname and private
  session directory. If a hosts/DNS override reuses the production hostname,
  use an isolated browser profile and do not reuse production cookies. Shared
  cookie names/hosts or session files can disrupt existing browser sessions.

The report-download authorization gap and real MariaDB/browser acceptance are
still production stop conditions. This documentation review did not execute an
export, restore, or Linux configuration test.

Record privately:

- Source PHP version/extensions; the reported database is MariaDB 10.3.35.
  Verify the full version on the source before export.
- Destination OS version, SSH access, hostname, DNS owner, and TLS certificate
  and private-key paths.
- Database charset/collation, engines, SQL mode, timezone, and object owners.
- Whether all setup rows are wanted or only particular users/sites/meters.
- Which device/network endpoints should eventually use the destination.
- A release identifier, artifact checksum, test results, and rollback owner.

The examples use these paths; adapt them consistently:

- `/opt/web-amr-src`: private copy of the tested V2 working tree, including tests
  and deployment tools. Never serve this directory.
- `/secure/amr`: restricted database artifacts, outside the source workspace
  and web root.
- `/srv/web-amr/releases/RELEASE_ID`: sanitized application release.
- `/srv/web-amr/current`: symlink to the selected release.
- `/srv/web-amr/empty`: denied Apache document root; `/amr/` is an explicit alias.
- `/var/lib/web-amr/sessions`: private PHP session files.
- `/etc/web-amr/web-amr.env`: root-owned secret environment file.

Commands with `sudo` are for the destination administrator; they have **not**
been executed or validated on a real Linux server in this project session.
Replace example hostnames, release names, and `@@TOKEN@@` template values first.
Use short commands or script files rather than large multiline pastes in
Windows PowerShell.
Run commands on the machine named in the heading, one block at a time, and
stop on any nonzero exit status. Never overwrite an existing database,
application release, or archive without a separate approved procedure.

## 2. New server: provision PHP 8.4 and verify MariaDB 11.8

Install Apache 2.4, Python 3.9+, and PHP **8.4** CLI/FPM from an approved package
source for the chosen distribution. Debian 13 provides a suitable PHP 8.4
baseline; do not assume a particular Ubuntu release's default PHP is 8.4.
Do not add an unreviewed package repository just to make package names resolve.

After confirming these packages exist in the approved repository:

```bash
sudo apt-get update
sudo apt-get install apache2 python3 rsync ca-certificates php8.4-cli php8.4-fpm php8.4-common php8.4-mysql php8.4-mbstring php8.4-xml php8.4-zip php8.4-gd php8.4-sqlite3 php8.4-curl php8.4-opcache
php8.4 -v
php8.4 -m
```

The offline suite additionally requires PDO SQLite and SQLite3. Production
reporting needs the XML/XMLWriter/SimpleXML, ZIP, mbstring/iconv, and GD modules
exercised by the suite; MySQL access uses mysqli. Verify extensions in **FPM**
as well as CLI, and align timezone behavior with `Asia/Manila`.

### Database target and upgrade boundary

Stay with **MariaDB 11.8**, rather than switching this migration to Oracle
MySQL. MariaDB 10.3 stopped receiving community maintenance in May 2023; do not
reinstall 10.3.35 as the new production database. MariaDB 11.8 is an LTS series
with community maintenance through June 2028. Use a maintained 11.8.x patch.

The numbers are independent: **PHP 8.4 does not require MySQL 8.4**.
The application's `mysqli` driver can connect to MariaDB; however, actual schema
restore, SQL behavior, and business reports still require staging tests.

This procedure is a **logical export from 10.3 and restore into a fresh 11.8
server**, not an in-place replacement of database binaries. Do not copy the
10.3 data directory, redo logs, or MariaDB `mysql` system database into 11.8.
Do not attempt a direct in-place 10.3-to-11.8 upgrade using these instructions.
Any in-place/physical upgrade needs its own supported upgrade-path review.
The new-instance logical-restore procedure does not require running intermediate
10.4/10.5/10.6 servers merely to import application SQL.

### Verify the existing instance; install only if not already provisioned

For the already installed 11.8.6 instance, skip package installation and perform
the server-identity check below. Plan any patch update separately with backups
and administrator approval; do not uninstall or overwrite an existing instance.

For a new installation only, have the administrator configure an approved
MariaDB **11.8 series** repository for the distribution. Default OS packages
may select another series. Follow signed-repository instructions; do not run an
unreviewed remote installer or mix incompatible distro packages.

Inspect package candidates **after** repository configuration:

```bash
sudo apt-get update
apt-cache policy mariadb-server mariadb-client
```

Proceed only when the candidates and package origins are the approved 11.8
series. Pin the intended series where necessary while allowing maintained
11.8.x security/bug-fix updates. Then install:

```bash
sudo apt-get install mariadb-server mariadb-client
mariadb --version
mariadb-dump --version
```

Also verify the **running server**, not just the client binary, using a protected
destination administrator connection:

```bash
mariadb --defaults-file=/secure/amr/destination.cnf --execute="SELECT @@hostname AS server_host, VERSION() AS server_version, @@version_comment AS product;"
```

This check does not require the application database to exist yet. Confirm
11.8.x and the intended destination identity. Recreate database users/grants
deliberately rather than importing 10.3 system tables. Use an authentication
method supported by PHP's mysqli client and test the dedicated application
account separately from the administrator's socket/password authentication.

Preserve source application definitions and explicit charset/collation,
timestamp/default behavior, engines, and indexes. Record SQL-mode differences
and resolve demonstrated compatibility issues; do not globally disable
validation or add `--force` to get past import errors. Do not combine this
migration with opportunistic charset, collation, or schema redesign.

MariaDB 11.8 has newer charset/collation defaults than the 10.3 source. Explicitly
preserve the original schema/table/column defaults; do not let an unqualified
`CREATE DATABASE` silently select the new defaults. Connection collation must
also be checked: the current mysqli adapter applies charset but does not
explicitly enforce `AMR_DB_COLLATION`. The query validator reports a mismatch
instead of silently modifying it.

Lifecycle references:
[MariaDB 10.3 status](https://mariadb.com/docs/release-notes/community-server/old-releases/10.3/what-is-mariadb-103)
and [MariaDB community maintenance policy](https://mariadb.org/about/#maintenance-policy).

Keep the database on loopback/private networking. The current production DB
configuration sets `encrypt => FALSE`; it does not configure database TLS.
If a remote DB requires TLS, configure and validate certificate verification
before using it. HTTPS for the browser does not encrypt the database connection.
`AMR_TEST_DB_SSL_CA` configures only the integration validator, not the web
application. A successful TLS-enabled validator run does not prove the current
`encrypt => FALSE` runtime configuration will connect securely.

## 3. Source/export host: inventory the existing database

Use the MariaDB-native clients on the source or an approved administration host.
On the existing **10.3** installation they are normally named `mysql` and
`mysqldump`; newer MariaDB installations use `mariadb` and `mariadb-dump`.
Verify the actual client versions and paths. Do not assume the old source has
a binary named `mariadb-dump`, or substitute Oracle MySQL clients merely because
their names match. No application PHP needs to run on the source.

The export script requires **Python 3.9 or newer**. The old source host may not
provide that version. If necessary, run the script on a separate approved
administration host with Python 3.9+ and MariaDB-native clients verified against
the 10.3 source, using a restricted database connection or approved SSH tunnel.
Do not upgrade the source host's OS, PHP, Python, or database just to run this
export. The `/usr/bin/mysql` and `/usr/bin/mysqldump` paths below must refer to
the verified clients on whichever host executes the script.

The source commands below explicitly select `/usr/bin/mysql` and
`/usr/bin/mysqldump` from the verified MariaDB 10.3 installation. Adjust paths
if needed. When using approved newer MariaDB clients, use
`--mysql /usr/bin/mariadb --mysqldump /usr/bin/mariadb-dump` instead.

Have the secret manager or DBA provision an owner-only client option file
outside the repository, e.g. `/secure/amr/source.cnf`, with a `[client]` section
containing the approved connection settings. Restrict it to mode `0600` and its
parent directory to `0700`. Never print it, commit it, place a password in a
command line, or copy it into the web root. Check native client login-path and
environment overrides so the file cannot silently target a different server.

Use a DBA-approved account with complete metadata visibility and the required
read/export privileges; an incomplete metadata view is not an authoritative
schema inventory. The commands perform no source application DML/DDL, but
**SELECT alone may not be enough to export all definitions**. Have the source
DBA verify version-specific SHOW VIEW, TRIGGER, EVENT, and routine-definition
access; MariaDB 10.3 routine dumps may need SELECT on `mysql.proc`.
Grant only the required metadata capabilities, not ALL/global write privileges,
and never copy system-table contents into 11.8 as a workaround.

Compare inventory object counts with the DBA's authoritative view. Missing
privileges can hide objects rather than produce an obvious SQL error. The
helper's `COMPLETE` marker means its commands finished and visible metadata
checks passed; it does not certify full privilege visibility, a successful
restore, or business-query correctness.

The source/destination `[client]` option files must explicitly identify host,
port/socket, user, and securely provisioned authentication. For TCP tunnels,
use `127.0.0.1` and the forwarded port: `localhost` can select a Unix socket
instead of the intended TCP endpoint. Apply the same distinction to
`AMR_DB_HOST` and `AMR_TEST_DB_HOST`. Verify server identity before each import.

With the protected parent directory already prepared:

```bash
python3 /opt/web-amr-src/deploy/database_export.py inventory --defaults-file /secure/amr/source.cnf --database meter_reading --mysql /usr/bin/mysql --mysqldump /usr/bin/mysqldump --output /secure/amr/audit
```

The output directory must **not already exist**. Inventory does not export rows
or create `schema.sql`. It creates private `inventory.json`, `manifest.json`,
`SHA256SUMS`, and a `COMPLETE` marker whose content is `inventory`.

Review server/default settings, base tables versus views, columns, indexes,
relationships, and any triggers/routines/events. Inspect full native
`SHOW CREATE` definitions where necessary; JSON metadata alone is not a complete
cross-version schema-diff implementation. Check whether the alternate `fp_db`
connection or any standalone scripts are actually used before assuming the
primary database is the only dependency.

Record the source schema defaults and relevant server behavior before creating
the destination database. These are read-only source queries:

```sql
SELECT VERSION(), @@version_comment, @@sql_mode, @@time_zone,
       @@system_time_zone, @@lower_case_table_names;
SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = 'meter_reading';
```

Do not copy the entire Debian Stretch-era server configuration into MariaDB
11.8. Review removed/renamed options separately and preserve only verified
application requirements.

## 4. Source/export host: approve the setup-data manifest

Edit the private generated `manifest.json`, not a tracked source file:

- `database`: retain the verified database name, normally `meter_reading`.
- `retain`: every approved **base table** whose complete rows should be copied.
- `empty`: every other base table; retain its schema but no rows.
- `unclassified`: must be empty after DBA review.
- `approved`: set to the JSON boolean `true` only after approval.
- `setup_candidates_for_review`: hints only, not an automatically selected list.

Every live base table must appear exactly once in `retain` or `empty`. Views
belong to the schema, not either data list. Unknown or overlapping selections
are rejected. Candidates include:

- `user_tb`, `client_tb`
- `meter_site`, `meter_site_sub_group`, `meter_details`, `meter_rtu`
- `meter_model_config`
- `user_access_group`, `user_client_meter_access`, `user_meter_group_access`
- `meter_energy_report_name`, `meter_instantaneous_energy_report_name`

Verify actual definitions/dependencies; names such as `meter_site_user` may be
views. Keep `meter_data` empty. Classify report request/value/chart tables
individually: some report-related objects contain setup definitions rather
than readings. Validate both declared foreign keys and application-level
relationships, retaining original IDs.

The exporter copies **whole selected tables**, not selected rows. It rejects
nonempty `row_filters`. A subset of users/sites/meters requires a separate
dependency-reviewed export procedure, not SQL filters silently added to this
manifest. Selected tables must all be InnoDB; nontransactional setup tables
require a separate DBA-reviewed locking/export process.

Review mixed-purpose rows for embedded historical values, last-reading
timestamps, and pending RTU command flags. Define destination-only resets
explicitly rather than copying operational state blindly.

The current application requires `user_tb.active_session_hash` and
`active_session_activity`. Export stops if those columns are absent.
`database/single_active_session.sql` is an incremental, non-idempotent ALTER
script, **not** a full schema installer. Do not run it blindly on either server;
a source/schema mismatch needs a separately approved reconciliation.
Do not alter the old server merely to satisfy this exporter guard. Use native
schema-only extraction to inspect the mismatch; any alternative setup export
or destination-only schema extension needs an explicit reviewed procedure.

## 5. Source/export host: extract SQL files and transfer them

### 5.1 Extract the existing schema without records

Run this on the source/export host using its verified MariaDB-native 10.3
`mysqldump`. First provision `/secure/amr` as an owner-only directory belonging
to the export operator and the protected `source.cnf` from section 3.
Use a new `RELEASE_ID`; do not reuse an existing output directory.

Pause source DDL for the duration. `--no-data` exports **table/view definitions
and programmable objects, not table rows**:

```bash
umask 077
mkdir -m 0700 /secure/amr/schema-only-RELEASE_ID &&
/usr/bin/mysqldump --defaults-file=/secure/amr/source.cnf --no-data --routines --events --triggers --skip-lock-tables --result-file=/secure/amr/schema-only-RELEASE_ID/schema.sql meter_reading
```

Check the exit code before proceeding. This `schema.sql` is extracted from the
existing database, not inferred from PHP models and not the incremental
`database/single_active_session.sql`. It includes the triggers/routines/events
visible to the export account; verify complete metadata visibility first.
Keep it private because stored definitions can contain sensitive values.

Checksum only after successful extraction:

```bash
cd /secure/amr/schema-only-RELEASE_ID &&
test -s schema.sql &&
sha256sum schema.sql > SHA256SUMS
```

This direct command does not require Python. For an approved newer MariaDB
client, substitute its verified `mariadb-dump` path. On Windows, use
`--result-file`, not PowerShell `>` redirection: PowerShell 5.1 redirection can
change the SQL file's encoding.

For **schema only**, this is the artifact to transfer; skip setup-data export
below. Still review section 6's restore ordering. There will be no accounts,
sites, or meters until you provision them separately.

### 5.2 Recommended: export schema plus approved setup rows

Agree a window in which **all setup edits and DDL remain paused**, including
edits made by users, services, or collectors. The flag below acknowledges this
operator action; it does not pause writers itself. A single transaction
snapshots selected InnoDB rows; the schema and data exports are separate
commands and still depend on the coordinated pause.

Capture the approved retained-table counts/IDs while that window is still in
effect. Compare restores against this exported baseline, not a source database
that has since changed. Record when setup edits may resume; if source setup
changes must be included at activation, create a new consistent approved bundle
and repeat restore/validation rather than mixing exports or silently losing
late users/configuration. Historical-reading collection need not be stopped
solely because excluded history is not being copied, unless it also changes
selected setup tables or schema.

```bash
python3 /opt/web-amr-src/deploy/database_export.py export --defaults-file /secure/amr/source.cnf --database meter_reading --mysql /usr/bin/mysql --mysqldump /usr/bin/mysqldump --manifest /secure/amr/audit/manifest.json --output /secure/amr/export-RELEASE_ID --setup-writes-paused
```

Retain the exported `schema.sql`, `setup-data.sql`, `inventory.json`,
approved `manifest.json`, and `SHA256SUMS`. A valid export also has a `COMPLETE`
file whose content is `export`. A partial directory without that marker is not
a usable export; use a new output path when retrying.

The native schema dump includes tables/views, triggers, routines, and events.
It does not create the destination database or provision database users/grants.
The data dump includes only the allowlisted base tables.

The helper already creates a complete `schema.sql`; section 5.1 is an
independent schema-extraction option, not a requirement to duplicate exports.
For schema plus setup rows, use the completed helper bundle as the authoritative
restore set. Do not mix SQL from different export windows.

Use the new server's MariaDB 11.8 `mariadb` client for import. Dumps produced by
newer `mariadb-dump` versions can contain a sandbox-mode directive unsupported
by old clients or Oracle MySQL's client. Do not remove safety directives or use
`--force` to get around an incompatible import client. Retain dump timezone/
charset wrappers unless a specific change has been reviewed.

### 5.3 Transfer the export to the new server

On the new server, have the administrator provision `/secure/amr` as an
owner-only directory writable by the transfer operator. Independently verify
the server's SSH host fingerprint. Run on the **source/export host**, replacing
the SSH user and hostname:

```bash
scp -r /secure/amr/export-RELEASE_ID DEPLOY_USER@NEW_SERVER:/secure/amr/
```

For schema only, transfer `/secure/amr/schema-only-RELEASE_ID` instead. Do not
transfer `source.cnf` or production secrets as part of the bundle.

Transfer the bundle over authenticated SSH/SFTP to an owner-only directory on
the destination. It contains account hashes/personal data and must not enter
Git, a public issue, or the web root. Check the marker and checksums there:

```bash
test "$(cat /secure/amr/export-RELEASE_ID/COMPLETE)" = export &&
cd /secure/amr/export-RELEASE_ID &&
sha256sum --check SHA256SUMS
```

Proceed only if the marker and checksum checks succeed. Checksums detect transfer changes; they
are not a substitute for authenticating the source. Do not modify the original
bundle to resolve destination differences; keep reviewed derivative SQL
separately and record its checksums.

For direct schema-only extraction there is no helper `COMPLETE` marker.
Verify its checksum on the **new server**:

```bash
cd /secure/amr/schema-only-RELEASE_ID &&
sha256sum --check SHA256SUMS
```

Run subsequent Linux commands over SSH/on the named server. Bash `< file`
import syntax is not PowerShell 5.1 syntax.

## 6. New server: restore MariaDB 10.3 SQL into MariaDB 11.8

**DBA gate: there is no automatic restore tool in this repository.**
Prepare a protected destination client option file with different credentials
and verified destination identity. Before every import/reset, confirm host,
database, and that this is a new, empty destination—not the source.

The specific migration sequence is:

1. Verify the MariaDB 11.8 server as in section 2; keep
   application traffic, RTUs, collectors, and scheduled execution isolated.
2. Inventory the 10.3 source and approve complete setup-table selection
   (sections 3–4).
3. Pause setup edits/DDL, export with the verified source-native clients, and
   authenticate/checksum the transferred bundle (section 5).
4. Create an empty `amr_test_mariadb118` database on 11.8 using source defaults;
   review 10.3 DDL against 11.8 before importing.
5. Restore reviewed base objects, approved setup rows, and post-data objects
   in the order below. Recreate destination owners/accounts and leave events
   disabled. Do not import the source `mysql` system schema.
6. Clear destination-only session locks and apply approved device/state
   resets; compare schema definitions, rows, IDs, and relationships.
7. Point the **staging** PHP 8.4 application at 11.8, run real application-query
   and browser tests, and resolve all failures before production sign-off.

`mariadb-upgrade` is not a replacement for this export/restore and does not
transfer the application database from the source. The destination is a fresh
11.8 installation; do not point it at old physical database files.

### 6.1 Create the empty staging database

Provision a protected `/secure/amr/destination.cnf` targeting the **new server**.
Confirm its identity with the section 2 query, then open the destination client:

```bash
mariadb --defaults-file=/secure/amr/destination.cnf
```

Inside the MariaDB client, replace `SOURCE_CHARSET` and `SOURCE_COLLATION` with
the exact values returned by section 3. These are placeholders, not defaults:

```sql
CREATE DATABASE amr_test_mariadb118
CHARACTER SET SOURCE_CHARSET COLLATE SOURCE_COLLATION;
```

For example, use `CHARACTER SET utf8 COLLATE utf8_general_ci` **only if those
are the original defaults**. Do not omit these clauses or use `IF NOT EXISTS`:
an existing database must cause a stop/review, not an import into populated
tables. Leave the client with `exit` after successful creation.
Preserve existing object definitions, defaults, indexes, constraints, table
engines, IDs, and AUTO_INCREMENT behavior. Avoid renaming the schema without
reviewing embedded references.

For staging, the name differs from the source. `--database=amr_test_mariadb118`
does **not** rewrite schema-qualified view/routine references or a `USE`
statement in the SQL file. Review them before import. Keep the original export
unchanged and checksum any approved staging-only derivative.

### 6.2 Select the correct restore path

Inspect the source inventory first. Do **not** blindly import `schema.sql`
followed by `setup-data.sql`:

- Triggers created before the setup import can run and generate side effects.
- Events must not start running on the new server unexpectedly.
- DEFINER accounts/security context may not exist on the destination.
- Native dumps may include product/version-specific session, GTID, or
  privileged server statements. Review MariaDB 10.3 output against 11.8.
- Routines/views may refer to other schemas or external resources.

**Path A — simple schema:** use only when the inventory confirms **no triggers,
routines, or events**, all view dependencies are valid, and the SQL contains
no unwanted schema-qualified references/`USE` statements. After review, import
into the newly created empty staging database:

```bash
mariadb --defaults-file=/secure/amr/destination.cnf --database=amr_test_mariadb118 < /secure/amr/export-RELEASE_ID/schema.sql &&
mariadb --defaults-file=/secure/amr/destination.cnf --database=amr_test_mariadb118 < /secure/amr/export-RELEASE_ID/setup-data.sql
```

For schema only, use `/secure/amr/schema-only-RELEASE_ID/schema.sql` and omit
the second command. If staging-only SQL adjustments were needed, use the
reviewed derivative path instead of the original export.

**Path B — programmable objects/dependencies requiring ordered restore:**
have the DBA prepare reviewed restore files using native product-specific tools,
not broad search-and-replace or a regex SQL splitter:

1. `schema-base.sql`: base tables and any prerequisites, without active triggers
   or events.
2. The unchanged approved `setup-data.sql`.
3. `schema-post.sql`: remaining views/routines/triggers in dependency order and
   event definitions explicitly **disabled**.

These reviewed schema files are **not generated automatically** by
`database_export.py` and are not prebuilt installers in this repository.
Preparing them requires inspecting the real schema. Preserve every required
definition; do not use an empty post-schema file to bypass that review.

Resolve object owners deliberately, preserve a mapping of necessary differences,
and ensure the server event scheduler cannot execute imported events during
the restore. Do not disable a shared server's global scheduler without its
administrator's approval.

Only after these files exist and have been reviewed:

```bash
mariadb --defaults-file=/secure/amr/destination.cnf --database=amr_test_mariadb118 < /secure/amr-reviewed/schema-base.sql &&
mariadb --defaults-file=/secure/amr/destination.cnf --database=amr_test_mariadb118 < /secure/amr/export-RELEASE_ID/setup-data.sql &&
mariadb --defaults-file=/secure/amr/destination.cnf --database=amr_test_mariadb118 < /secure/amr-reviewed/schema-post.sql
```

Stop on the first error. Do not execute later lines after an import failure;
restore procedures must check each exit code. Do not retry into partially
populated tables. Rebuild only the disposable destination through the DBA's
approved procedure; never truncate or restore over the source.

For schema only, omit the middle data-import command while retaining schema
dependency order.

### 6.3 Reset staging session ownership and create a test account

Before either staging or production resets, have the DBA review triggers on
every affected table, including the session-lock UPDATE. If a reset would cause
unintended trigger side effects, perform it at an approved point before
restoring those triggers and record the adjusted order. An administrative
UPDATE is not automatically inert.

Once approved, while the new staging database is isolated, clear its browser
ownership:

```sql
UPDATE amr_test_mariadb118.user_tb
SET active_session_hash = NULL, active_session_activity = NULL;
```

Do not copy browser session files. Apply only the approved RTU/timestamp/state
resets, and keep collectors/device commands disconnected.

Have the DBA securely provision an `amr_query_test` account. For a local test
process, grant reads plus optional temporary-table creation after it exists:

```sql
GRANT SELECT, CREATE TEMPORARY TABLES ON `amr\_test\_mariadb118`.*
TO 'amr_query_test'@'localhost';
```

Adjust the account host to the approved connection path, not unrestricted
access. Run section 10's query tests using `AMR_TEST_DB_*`. Use a separate
staging runtime account for browser tests; do not grant permanent DML to the
restricted query-test account.

The backslashes above escape `_` in a **database-level GRANT pattern**:
MariaDB treats unescaped `_` as a wildcard even in a quoted database pattern.
Use literal names such as `amr_test_mariadb118` without these backslashes in
CREATE DATABASE, USE, connection settings, and application arguments. Have the
DBA verify grants/active roles and actual access using each intended connection
path; adding a narrow grant does not revoke earlier broad privileges.
The `@'localhost'` examples assume the corresponding local account/connection.
For TCP or an SSH forward, provision the account host as seen by MariaDB and
verify the match; do not assume a Unix-socket account also matches `127.0.0.1`
or a remote tunnel endpoint.
See the [MariaDB GRANT reference](https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements/grant).
For example, after securely provisioning a separate local staging runtime
account:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON `amr\_test\_mariadb118`.*
TO 'amr_app_stage'@'localhost';
```

### 6.4 After staging passes, restore a fresh production database

On the **new** server, create an empty production database with the same source
defaults (replace the placeholders as above):

```sql
CREATE DATABASE meter_reading
CHARACTER SET SOURCE_CHARSET COLLATE SOURCE_COLLATION;
```

Apply the approved Path A or B again with `--database=meter_reading` and
**production-reviewed** schema references. Restore the verified original bundle,
not staging after synthetic readings were added. Keep historical tables empty.

Check destination identity again, then clear imported production session locks:

```sql
UPDATE meter_reading.user_tb
SET active_session_hash = NULL, active_session_activity = NULL;
```

Create a separate application account with only required database privileges,
normally SELECT/INSERT/UPDATE/DELETE on this database. Grant EXECUTE only if
verified routines need it. Do not grant the web app global privileges or use
the schema-import account at runtime.

For example, after the DBA securely creates a local `amr_app` account:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON `meter\_reading`.*
TO 'amr_app'@'localhost';
```

Update `AMR_DB_NAME=meter_reading`, the production hostname/base URL, private
production session path, and runtime user/secret; have the
administrator restart the dedicated FPM service, and repeat final browser,
schema, and empty-history checks. Never reuse query-test/import credentials
for production traffic.
At the same controlled switch, render Apache's production `ServerName`,
redirect hostname, and certificate/key paths to match `AMR_BASE_URL`, run
`apache2ctl configtest`, and reload Apache after success. A FPM environment
change alone does not change Apache virtual-host routing or its TLS certificate.

## 7. Prepare a sanitized, immutable release

Use the **tested V2 working tree** or a verified commit containing all required
patches and tests. `git archive HEAD` includes only committed files, and a remote
clone includes only published commits; either can omit local/untracked changes.
Record the chosen commit plus any additional working-tree changes. Transfer
the reviewed artifact into the private `/opt/web-amr-src` workspace, record its
checksum, and run the suite against that exact artifact on Linux.

### 7.1 Transfer a private, sanitized working-tree snapshot

Create a separate reviewed copy such as `E:\GitHub\web_amrV2-sanitized`; do not
remove files from the original project to prepare the archive. Strip real
connection credentials and secrets from this copy, but retain tests/deployment
tools in the **private** workspace for validation. Use a new archive filename
in an existing protected `E:\Deploy` directory and verify it does not already
exist. The sanitized copy must also omit `.git`, `.env*`, client option files,
saved SQL exports, and generated reports/logs/cache/upload payloads before
packaging. The approved database bundle is transferred separately.
Then, on Windows:

```powershell
tar.exe -czf E:\Deploy\web-amrV2-RELEASE_ID.tar.gz -C E:\GitHub\web_amrV2-sanitized .
Get-FileHash E:\Deploy\web-amrV2-RELEASE_ID.tar.gz -Algorithm SHA256
scp E:\Deploy\web-amrV2-RELEASE_ID.tar.gz DEPLOY_USER@NEW_SERVER:/secure/amr/
```

On the new server, verify its SHA-256 against the independently recorded local
value before extraction. Create a **new, empty** `/opt/web-amr-src` workspace
owned by the authorized deployment operator, not a public web root. As that
operator, extract and test:

```bash
sha256sum /secure/amr/web-amrV2-RELEASE_ID.tar.gz
tar --no-same-owner -xzf /secure/amr/web-amrV2-RELEASE_ID.tar.gz -C /opt/web-amr-src
python3 /opt/web-amr-src/tests/run_php84_tests.py --php /usr/bin/php8.4
```

Do not extract over an existing release or accept an untrusted archive. The
expected lint-file count can differ if archives/vendor demonstrations were
removed during sanitization; every remaining check must still pass.

### 7.2 Build the public release with restricted permissions

Review source files for hardcoded credentials before packaging. Do not publish
the source workspace. The release needs `index.php`, `application/`, `system/`,
`assets/`, `templates/`, and any verified `Classes/` dependencies. Keep licensing
notices. Preserve Linux filename case exactly.

Exclude `.git`, `.env*`, connection files, SQL/database exports, `tests/`,
`deploy/`, `application/OLD/`, `application/third_party/BACKUP/`, vendor demo/test
entry points, compressed PHP/config backups, and existing `reports/`, logs,
cache, or upload contents. In particular, do not ship inactive connection
credentials from `application/config/database.php` or standalone chart
`db_config.php` files. Production selects
`application/config/production/database.php` directly; retain that file.
Do not delete shared runtime assets simply because a vendor directory contains
tests; build and review an explicit exclusion manifest.

Create a new release path; do not overwrite an existing release:

```bash
sudo install -d -m 0755 /srv/web-amr /srv/web-amr/releases /srv/web-amr/empty
```

After reviewing the exclusions and creating the private sanitized workspace,
create a new release directory and copy only runtime material. Example for the
new Linux server (the first command deliberately fails if the release exists):

```bash
sudo mkdir -m 0750 /srv/web-amr/releases/RELEASE_ID &&
sudo rsync -a --chown=root:www-data --chmod=D750,F640 --exclude=/.git/ --exclude='/.env*' --exclude=/tests/ --exclude=/deploy/ --exclude=/database/ --exclude=/application/OLD/ --exclude=/application/third_party/BACKUP/ --exclude=/application/config/database.php --exclude=/templates/highcharts/scripts/db_config.php --exclude='/reports/***' --exclude='/meter_upload_list/***' --exclude='/application/logs/***' --exclude='/application/cache/***' --exclude='*.php.gz' --exclude='*.php.bak' /opt/web-amr-src/ /srv/web-amr/releases/RELEASE_ID/
```

This is a baseline exclusion list, not a secret scanner or an exhaustive vendor
demo filter. Apply the additional reviewed exclusions described above. No
`--delete` is used, and production credentials must still come from the
protected environment, not the package.

After placing the sanitized files in `/srv/web-amr/releases/RELEASE_ID`, make
the application code root-owned and readable by `www-data`, not writable by it.
Do not use recursive `chmod 777`. Only the following directories need runtime
write access; create them empty:

```bash
sudo install -d -o www-data -g www-data -m 0700 /var/lib/web-amr/sessions
sudo install -d -o www-data -g www-data -m 0700 /var/lib/web-amr/staging-sessions
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/reports
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/meter_upload_list
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/application/logs
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/application/cache
```

Make sure parent directories allow the service user to traverse them. Apply
log rotation and an approved generated-report retention policy.

Verify the complete path, not just the final writable directory: `www-data`
must traverse `/var/lib/web-amr` and the release parents; Apache/FPM need the
dedicated socket permissions and the intended symlink policy. Check applicable
AppArmor/SELinux restrictions without globally disabling enforcement. Never
give the web user access to private source/export/secret directories to fix a
runtime permission error.

For a brand-new destination where `current` does not already exist:

```bash
sudo ln -s /srv/web-amr/releases/RELEASE_ID /srv/web-amr/current
```

For subsequent releases, record the previous symlink target and use an approved
atomic switch. Do not overwrite an existing real directory or discard a prior
release.

## 8. Configure production PHP-FPM securely

Render `deploy/php-fpm.conf.example` to the dedicated pool config
`/etc/php/8.4/fpm/pool.d/web-amr.conf`. Replace `@@PHP_FPM_SOCKET@@`, e.g.
`/run/php/web-amr.sock`, consistently with Apache. Set the rendered pool's
`chdir = /srv/web-amr/current` and verify relative report/upload writes resolve
inside the selected release.

Provision `/etc/web-amr/web-amr.env` through the secret manager, root-owned mode
`0600`, beneath a protected directory. Set **all** whitelisted values:

- `AMR_BASE_URL`: initially the separate staging HTTPS URL including `/amr/`,
  e.g. `https://amr-staging.example.com/amr/`; change to the approved production
  hostname only at the controlled switch.
- `AMR_SESSION_PATH`: initially `/var/lib/web-amr/staging-sessions`; use
  `/var/lib/web-amr/sessions` for production. Keep the two sets of files separate.
- `AMR_DB_HOST`, `AMR_DB_PORT`: destination endpoint; usually private/loopback
  and port `3306`. Use the verified MariaDB 11.8 staging endpoint first, never
  the old source endpoint for migration tests.
- `AMR_DB_NAME`: initially `amr_test_mariadb118` for restricted browser testing;
  change to `meter_reading` only after the fresh production restore is accepted.
- Initially use the separate staging runtime account/secret, not the restricted
  query-test account. Switch to `amr_app` (or the approved production account)
  at the same time as the production database change.
- `AMR_DB_USER`, `AMR_DB_PASSWORD`: dedicated runtime credentials supplied by
  the secret manager, never in tracked templates or terminal history.
- `AMR_DB_CHARSET`, `AMR_DB_COLLATION`: match the source connection requirements
  and preserved schema; do not silently select utf8mb4 as an upgrade.

The application does **not** automatically load a `.env` file. Systemd's
EnvironmentFile syntax is not a shell script; provision/quote it accordingly
and do not `source` it into an interactive shell.

Render Linux configuration/environment files as UTF-8 without a BOM and with
LF line endings, not PowerShell 5.1's default UTF-16 file output. Preserve SQL
files in the encoding produced by the verified native exporter.

Configure a root-owned PHP-FPM service drop-in at
`/etc/systemd/system/php8.4-fpm.service.d/web-amr.conf`:

```ini
[Service]
EnvironmentFile=/etc/web-amr/web-amr.env
```

For example, after rendering the pool and service drop-in into protected
`/secure/amr-rendered` files:

```bash
sudo install -m 0644 /secure/amr-rendered/php-fpm.conf /etc/php/8.4/fpm/pool.d/web-amr.conf &&
sudo install -d -m 0755 /etc/systemd/system/php8.4-fpm.service.d &&
sudo install -m 0644 /secure/amr-rendered/php-fpm-service.conf /etc/systemd/system/php8.4-fpm.service.d/web-amr.conf
```

The pool keeps `clear_env = yes` and explicitly forwards required variables.
Both the pool and Apache configuration must set `CI_ENV=production`; the entry
point otherwise defaults to development. Production fails closed if the URL,
required database variables, or writable session directory are missing.

Environment files apply to the FPM **master service**, so changing them requires
the coordinated service restart, not merely an Apache reload. Make sure every
referenced pool variable is set, including optional application values explicitly
listed in the pool template. Do not forward query-test/export administrator
credentials into the web pool. Service-wide environment changes can affect
other pools hosted by the same service.

These examples switch a **single deployment pool sequentially** from staging
to production. For simultaneous staging and production, use independently
provisioned FPM services/containers and separate sockets/environments/session
directories. Two pools forwarding the same service-wide `AMR_DB_*` variables
do not provide separate database credentials by themselves.

Validate report/upload capacity in FPM: `memory_limit`, `max_execution_time`,
`request_terminate_timeout`, upload/post limits, Apache request/proxy timeouts,
and database packet/temporary-space limits must fit the accepted workload.
The template's upload sizes and worker count are starting values, not load-test
results. Use scoped, measured limits rather than unlimited defaults.

Run FPM configuration validation with the same protected environment available
to the master service. For a systemd administrator, an example validation is:

```bash
sudo systemctl daemon-reload
sudo systemd-run --wait --pipe --property=EnvironmentFile=/etc/web-amr/web-amr.env /usr/sbin/php-fpm8.4 -t
```

Use `-t`, **not `-tt`**, and never dump process environments, `phpinfo()`, or
expanded pool settings containing secrets. On an initial installation, restart
`php8.4-fpm` after validation so the service receives the new environment;
coordinate the restart if it also hosts other applications.

The repository's production settings disable the nonexistent Composer autoload
path and enable secure/HTTP-only cookies. Do not replace the patched framework
or spreadsheet files with their original upstream versions during deployment.

## 9. Configure Apache and HTTPS

Render `deploy/apache.conf.example` into an Apache site file. Replace:

- `@@HOSTNAME@@`: final hostname.
- `@@CERTIFICATE_PATH@@`, `@@PRIVATE_KEY_PATH@@`: valid certificate/key paths.
- `@@PHP_FPM_SOCKET@@`: the dedicated socket chosen above.

For initial staging, `@@HOSTNAME@@`, `AMR_BASE_URL`, the browser test hostname,
and the TLS certificate must all match the staging hostname. Re-render/revalidate
these settings for the approved production hostname at cutover; do not assume
a staging certificate or Host header setting also covers production.

Provision the certificate through the approved CA/DNS workflow before TLS
validation. Restrict staging access to administrators. The template retains
`/amr/`, redirects HTTP to HTTPS, disables `.htaccess` overrides, denies internal
source/writable directories and backups, and selects a PHP handler for the
filename `index.php`. Its current `<Files>` match includes nested files of that
name, so it is not an exact-path front-controller restriction.
Consequently, the repository's old-host `.htaccess` redirect is not used.
Do not enable mod_php or an unrelated global PHP handler that bypasses this
front-controller policy.

At this review, the working tree contained only the root `index.php`. Repeat
that inventory on the sanitized deployed artifact, including vendor assets.
Remove unneeded nested entry points or tighten the handler scope before public
activation, and verify direct PHP/path-info/case-variant requests cannot expose
source or execute unintended scripts. The template has not been tested on a
live Apache server here. See Apache's
[filesystem container matching rules](https://httpd.apache.org/docs/2.4/sections.html).

Stage restriction must be effective **before** the first service reload: a
missing public DNS record or a `curl --resolve` test does not prevent access
through the IP address or a client-supplied Host header. Use approved firewall,
VPN, or server-side access restrictions. Review other enabled/default virtual
hosts and aliases so they cannot expose the same release or private workspace.
Also verify `/amr` redirects to `/amr/` and front-controller PATH_INFO works
through the actual FPM socket/alias mapping.

After rendering the site to `/secure/amr-rendered/apache.conf`, install and
enable the configuration, then test the **enabled** site:

```bash
sudo install -m 0644 /secure/amr-rendered/apache.conf /etc/apache2/sites-available/web-amr.conf &&
sudo a2enmod alias rewrite ssl proxy proxy_fcgi headers env &&
sudo a2ensite web-amr.conf &&
sudo apache2ctl configtest
```

Resolve all template tokens and configuration errors before reloading services.
For **restricted staging access only**, after validation and database restore
and with the environment targeting staging:

```bash
sudo systemctl restart php8.4-fpm &&
sudo systemctl reload apache2
```

Coordinate restarts if FPM hosts other applications. Keep public access and
integrations disabled until all acceptance gates pass. In particular, an
enabled staging site does not resolve the report-download authorization blocker.

Before public DNS changes, test using a restricted DNS/hosts override that
preserves the intended hostname and validates its certificate. Example:

```bash
curl --resolve amr-staging.example.com:443:192.0.2.20 --head https://amr-staging.example.com/amr/index.php/login
```

Replace both example values. Do not use `--insecure` to bypass TLS failures.
Verify rewritten routes and `index.php/...` routes, correct asset URLs, absence
of redirects to the old server, and `CI_ENV=production` through restricted
operational checks—not a publicly accessible diagnostic page.

## 10. Validate before sign-off

### Opt-in MariaDB 11.8 application-query test

`tests/mariadb118_query_test.php` executes a curated catalog of **28 actual
model methods** through the real CodeIgniter mysqli adapter/query builder.
It does not extract/rewrite SQL, load production DB configuration, run
migrations, or connect automatically from the offline suite.

Prepare a **separate staging schema**, e.g. `amr_test_mariadb118`, on the 11.8
instance using the reviewed logical restore procedure. Include the real
application tables/views and approved setup rows. Have the DBA review
schema-qualified view/routine references so they do not point back to the
source database. Never rename a production database merely to satisfy the
test's naming guard.

Use a dedicated test account with **SELECT only** on that staging schema.
For the optional temporary-write tests, add **CREATE TEMPORARY TABLES** on that
schema. Do not use root, the runtime/import account, broad roles, or permanent
INSERT/UPDATE/DELETE/CREATE/DROP/ALTER/EXECUTE/FILE privileges. The application-side
SQL allowlist is not a substitute for DBA-enforced permissions.

Provision a protected process environment through the secret manager. On Linux,
an example root-owned mode-0600 environment file is
`/etc/web-amr/query-test.env`, separate from the runtime environment:

- `AMR_TEST_DB_HOST`: staging endpoint, preferably loopback or an approved
  localhost SSH tunnel.
- `AMR_TEST_DB_PORT`: explicit port, normally `3306` or the local tunnel port.
- `AMR_TEST_DB_NAME`: `amr_test_mariadb118`; the prefix is required.
- `AMR_TEST_DB_USER`, `AMR_TEST_DB_PASSWORD`: restricted test account. Populate
  the password through secret provisioning, not a command-line literal.
- `AMR_TEST_DB_CHARSET`, `AMR_TEST_DB_COLLATION`: expected application connection
  settings. Defaults are `utf8` and `utf8_general_ci`, matching the current app
  fallback. Set these deliberately to the intended deployment settings.
- `AMR_TEST_DB_SSL_CA`: readable CA certificate path, required for remote hosts
  other than loopback/Unix sockets; TLS certificate verification is enabled.

There is no fallback to `AMR_DB_*`, no automatic `.env` loading, and no password
CLI option. Do not print environment files or diagnostic dumps.

Inspect coverage without connecting:

```bash
php8.4 /opt/web-amr-src/tests/mariadb118_query_test.php --help
php8.4 /opt/web-amr-src/tests/mariadb118_query_test.php --list
```

With that protected environment supplied to the process, run:

```bash
php8.4 /opt/web-amr-src/tests/mariadb118_query_test.php --confirm-staging=amr_test_mariadb118
```

On a systemd server, an administrator can inject the protected environment
without exposing the password in the command line:

```bash
sudo systemd-run --wait --pipe --property=EnvironmentFile=/etc/web-amr/query-test.env /usr/bin/php8.4 /opt/web-amr-src/tests/mariadb118_query_test.php --confirm-staging=amr_test_mariadb118
```

On Windows, after secure provisioning of the same process environment:

```powershell
& "C:\Users\Jun\.config\herd\bin\php84\php.exe" tests/mariadb118_query_test.php --confirm-staging=amr_test_mariadb118
```

Default execution:

- Verifies the selected server is MariaDB 11.8 and the database matches the
  explicit confirmation.
- Reports non-secret version, charset/collation, SQL-mode, and timezone metadata.
  It mirrors the application's `stricton=FALSE` connection behavior, not a
  separate server-default/strict-mode test matrix.
- Runs the model catalog with sentinel/missing inputs and, when setup rows
  exist, an automatically selected staging sample. Reads run inside a
  `READ ONLY` transaction with a five-second per-statement limit.
- Exercises authentication lookup, site/meter/RTU/access queries, latest reading,
  raw/date-range reporting, fifteen-minute/hourly/daily report queries, and
  offline-gateway checks.
- Reports SQL errors/warnings and PHP diagnostics as failures, without emitting
  rows, credentials, raw SQL, or database exception messages.
- Fails a connection-collation mismatch instead of silently changing it. The
  current adapter sets charset but does not explicitly apply `dbcollat`;
  `utf8`/`utf8mb3` aliases are normalized for comparison. Investigate a mismatch;
  do not simply change the expected value to make the test green.

For optional real login/session-lock and query-builder write coverage:

```bash
php8.4 /opt/web-amr-src/tests/mariadb118_query_test.php --confirm-staging=amr_test_mariadb118 --temporary-writes
```

This option snapshots one staging user row privately in memory, creates a
connection-local temporary `user_tb` clone, and inserts a synthetic account
into the clone. The actual login/session-lock methods and query-builder writes
then operate on that temporary table, not permanent user records. Triggers/FKs
are not copied by `CREATE TEMPORARY TABLE ... LIKE`. Temporary DDL is executed
outside the read-only transaction because MariaDB forbids it inside; the
temporary DML tests start a new read-only transaction. Cleanup rolls back first,
drops **only** the temporary table, and closes the connection.

Optional write tests need an existing staging user row and compatible required
columns/constraints; fixture failures are not proof that the application SQL
is incompatible. They test sequential competing claims, not a real
multi-connection race, full business writes, or permanent-table trigger/FK
behavior.

Exit codes:

- `0`: all selected checks passed; read-only mode does not imply writes passed.
- `1`: at least one validation case failed.
- `2`: setup failed or coverage is incomplete/skipped, e.g. no sample users,
  sites/meters, or readings.

A setup-only migration intentionally has no readings, so populated-report
coverage will be skipped until the DBA provisions representative disposable
staging readings. The script does not invent a meter-data schema or seed
permanent records. Even with exit 0, this is curated query execution, **not**
exhaustive controller coverage or source/destination report-result equivalence.
Compare known expected results and browser workflows separately.

Local verification without an instance is available through
`tests/mariadb118_policy_test.php` (also included in
`tests/run_php84_tests.py`). Those offline policy/catalog checks are not a live
11.8 integration run. No MariaDB endpoint/credentials have been provided or used
to run this new validator in this session.

### Deployment acceptance checks

Record results and resolve every failure:

- Run `tests/run_php84_tests.py` in the private Linux source workspace.
- Inventory the destination with `database_export.py inventory` using a new
  private output directory and destination-only credentials:

  ```bash
  python3 /opt/web-amr-src/deploy/database_export.py inventory --defaults-file /secure/amr/destination.cnf --database meter_reading --mysql /usr/bin/mariadb --mysqldump /usr/bin/mariadb-dump --output /secure/amr/destination-audit-RELEASE_ID
  ```

- Confirm the destination reports MariaDB 11.8.x. On **staging**, run actual
  SELECT/INSERT/UPDATE/DELETE and session-lock operations using the staging
  runtime account, not just the offline fake-transport test. Exercise report aggregation/date queries
  with both empty data and representative disposable readings; compare expected
  results against a controlled source baseline. Check SQL modes, explicit
  timestamp defaults, text sorting/collations, filename/table-name case, and
  query plans/index use where performance matters.
- Keep synthetic accounts/readings and destructive CRUD exercises confined to
  disposable staging. After the fresh production restore, perform only the
  approved production smoke workflows; do not seed test history or improvise
  cleanup against production tables/triggers.
- Compare native source/destination schema definitions: types, nullability,
  defaults, keys, FK actions, engines, collations, views, routines, triggers,
  events, and AUTO_INCREMENT behavior. Normalize only approved differences,
  such as mapped owners and disabled events. Do not ignore unexplained drift.
- Compare retained-table row counts and IDs; check relationship integrity and
  authorization mappings. Re-enabling foreign-key checks does not retroactively
  validate rows imported while checks were disabled.
- Confirm every excluded historical base table is empty, especially
  `meter_data`, and retained setup rows contain no unapproved historical payload.
- Test login/logout and concurrent-login rejection, all applicable user roles,
  site/meter/RTU lists, permission boundaries, CSV upload, charts, XLSX creation,
  and the approved **authorized download** flow.
- Test no-readings/empty-report behavior. Use disposable synthetic readings on
  staging when nonempty reporting needs testing; rebuild the final fresh
  database before approval rather than retaining test readings.
- Verify writable paths, secure cookies, TLS, Linux filename case, and runtime
  extensions. Review restricted application/FPM/Apache logs with sensitive
  values redacted before sharing.
- Verify HTTP denial for `.git`, application/system source, SQL/backup files,
  and direct PHP execution in writable directories. Confirm unauthorized users
  cannot obtain another user's generated report.
- Verify the restricted query-test account has no permanent writes or
  cross-database access through broad grants/roles; test authentication from the
  same TCP/socket/host path used by the process. The validator's name guard is
  not a substitute for database-enforced least privilege.

Do not expose legacy backup/vendor demonstration endpoints. Existing MD5
password hashes are retained for compatibility; do not treat the PHP upgrade
as a completed authentication/security modernization.

## 11. Activation, monitoring, and rollback

Keep the original server operational. Save a destination backup and record
release/config checksums, schema comparison, approved data manifest, prior
symlink target, DNS state, and all intentional object/state changes.

After sign-off, enable user traffic and then only the specifically approved
collectors/jobs/device integrations. Check credentials, network allowlists,
RTU flags, scheduled events, and absence of duplicate ingestion. If this becomes
a replacement-server cutover, coordinate all writers and a maintenance window
separately; a setup-only export is not a history-preserving cutover.

Rollback is operational: disable the new site/integrations, restore traffic to
the unchanged original when applicable, or switch to the recorded prior
destination release with its matching approved configuration. Preserve failed
destination artifacts/logs privately for diagnosis. Do not restore a destination
backup over the source. If the new server has accepted writes, reconcile those
writes before discarding or redirecting them.

After acceptance, revoke temporary export/import credentials and retain or
expire private bundles through the approved backup policy. No step in this
guide requires deleting or altering the original server's records.

## Final go/no-go record

Before proceeding beyond private staging, obtain named approval for: a
recoverable source backup; complete schema and approved setup snapshot; tested
11.8 restore and verified application connection settings; representative
queries/browser/report downloads; deployed-file/Apache access controls;
fresh production restore without test readings; and monitoring/rollback
ownership. Export preparation may proceed only after its own prerequisites
are met; passing offline tests or this document review alone is not a
production migration approval.

## 12. Deployment and VPN operating record

This is a snapshot of the deployment as observed on **2026-11-09**, not a
claim that all production acceptance checks above passed. Do not commit
passwords, private keys, database connection files, session cookies, or
ThousandEyes enrollment strings. Addresses and names below are operational
configuration; protect this document appropriately when sharing it.

### Systems and access

- The application is this **CodeIgniter `web_amrV2`** project. It is not the
  separate Laravel `camr_robinsons` project; their schemas are incompatible.
- Test: `172.104.42.220`, HTTP app `http://172.104.42.220/amr/`.
- MARIGOLD production-side host: `172.16.47.155`, Debian 13, hostname
  `mamasitas` (`mamasitas.workgroup` locally). The app is at
  `http://172.16.47.155/amr/`; it is reachable only where routing and firewall
  policy allow access to that private IP. It is not a public internet address.
- The production-side SSH login is `amr`. Administrative work was performed
  with an independently authenticated root shell. Verify SSH host keys through
  the administrator's trusted channel before connecting; do not disable host
  key checking or copy passwords into scripts or this document.
- TeamViewer was discussed as an attended remote-support option, but it was
  not required for this deployment. A remote session may expose the other
  machine's desktop and credentials; use the organization's approved access
  controls and keep the remote operator available for prompts.

### Cisco Secure Client / VPN

On the operator's Mac, Cisco Secure Client / AnyConnect connects to the VPN
server `mama-sitas-twbnkdbzpgc.dynamic-m.com:443`. Obtain the VPN credentials
and access policy from the organization's administrator; do not record them
here. An active VPN gives this Mac a route to the private server, but it does
not by itself prove that a separate gateway device has a route to that server.

The installer also offered a **ThousandEyes Endpoint Agent** component. Its
connection string is an enrollment value from a ThousandEyes administrator;
our VPN hostname is not that value. ThousandEyes is not required merely to
enter the VPN hostname in the AnyConnect VPN component. Leave it unconfigured
or unselected unless the organization requires it.

The Mac initially reported that the VPN service was unavailable. In macOS
**System Settings → General → Login Items & Extensions → Allow in the
Background**, the **Cisco Secure Client – AnyConnect VPN Service** switch was
off. Enabling it required the Mac user's authorization. After the service was
running, quitting and reopening Cisco Secure Client showed **Ready to
connect**. The client subsequently showed **Connected** to the VPN hostname;
the Mac's default route used a VPN interface. This confirms that operator's
connection only, not access from all devices.

### Production runtime and release

- Apache, MariaDB **11.8.6**, and PHP **8.4.27 FPM** were active at verification.
  PHP 8.3.36 was also installed for the separate Laravel investigation; this
  application requires PHP 8.4 and uses the dedicated 8.4 pool. PHP 8.4 became
  the default CLI alternative. Avoid assuming that an unqualified `php` will
  always use a particular version; use `php8.4` for this application.
- PHP 8.3 packages were installed from the approved signed Sury repository;
  PHP 8.4 packages were installed from Debian's repositories. Composer and
  Git are installed, although this CodeIgniter release does not require
  Composer. Existing Debian APT sources used HTTP and an update stalled;
  temporary HTTPS sources were used during installation. An administrator
  should make sure regular package updates work through approved sources.
- The deployed runtime was copied from the test server after excluding
  reports, uploads, logs, cache contents, and credential files. Its archive
  checksum at transfer was
  `410f9fa83e2d2c97313504ac66d5b713c8cc77569e8013dafed5199e038c1f7c`.
  The active path is `/srv/web-amr/current`, pointing to
  `/srv/web-amr/releases/test-reference-20261109`. The gateway time-check fix
  was later applied to that release in place, so it is no longer an immutable
  copy of the original archive. The corresponding local source changes must
  be retained in future releases.
- Root owns application code; `www-data` can read it. Writable directories are
  the release's `reports`, `meter_upload_list`, `application/logs`, and
  `application/cache`, plus `/var/lib/web-amr/sessions`. The environment file
  `/etc/web-amr/web-amr.env` is root-owned mode `0600` and loaded by the
  PHP 8.4 FPM systemd drop-in. The pool is
  `/etc/php/8.4/fpm/pool.d/web-amr.conf` and listens on
  `/run/php/web-amr.sock`. It sets `CI_ENV=production`, clears the environment
  except for the explicit `AMR_*` list, and sets `Asia/Manila` timezone.
- The enabled Apache site is `/etc/apache2/sites-available/001-web-amr.conf`.
  It maps `/amr/` and the gateway routes through `index.php`, denies direct
  access to application/system files and generated reports, and redirects
  `/` and `/amr` to `/amr/`. A local-only staging site was disabled after
  checks. `/check_time.php` was added to production after initial activation;
  the test server did not have this route when compared.
- HTTP by IP was explicitly requested. `AMR_BASE_URL` is the production IP
  URL and `AMR_ALLOW_HTTP_IP=1`; this makes the production configuration accept
  an HTTP IPv4 URL and disables the Secure cookie flag. Passwords, sessions,
  and readings therefore travel without HTTPS encryption. Move to a managed
  hostname and valid TLS certificate when available, and remove the HTTP
  exception. The gateway receiver has no device authentication and can accept
  writes from any client that can reach it; network restrictions and a device
  authentication design should be reviewed before wider access.

### Database and setup data

MariaDB binds to `127.0.0.1:3306`. The production database is `meter_reading`;
`amr_test_mariadb118` is separate staging. The test-server schema-only export
was restored into staging and then production. Database defaults were
`utf8mb4` / `utf8mb4_uca1400_ai_ci`. The restore contained 27 base tables,
three views, and one trigger. A fresh export from production matched the
source schema after the intentional definer mapping, excluding dump timestamps
and host comments. Views were queried successfully.

The source views and trigger used a test-server definer. Production maps the
four object definers to the locked `web_amr_definer@localhost` account with
only the table privileges required for those objects. `web_amr_app@localhost`
has `SELECT`, `INSERT`, `UPDATE`, and `DELETE` on `meter_reading`; the separate
`web_amr_stage@localhost` has the same data privileges only on the staging
schema. Neither runtime account was granted schema DDL privileges. Some legacy
report code contains `ALTER TABLE` calls, so those paths require separate
review and cannot be assumed to work with the restricted account.

The root-only client option files are under `/secure/web-amr/`; the runtime
PHP-FPM environment is separate. Do not print these files or pass their
passwords on command lines. The production `admin` record was copied from the
test server on request, with its active-session fields cleared. Its login and
logout were tested; the test session lock was released. Other setup records
were not bulk imported. Users subsequently configured production-side data.
As of 2026-11-09 22:13 Manila time, there was one user, one site, one meter,
one gateway, one meter-model entry, 29 historical rows and two live rows. These
are time-specific observations, not desired seed counts or a reason to modify
existing records. Production uses an `Elite440` / `elite440.cfg` entry;
verification that the actual gateway has and uses that file remains open.

### Gateway protocol and troubleshooting

The reading endpoint is:

```text
POST http://172.16.47.155/http_post_server.php
```

The `/amr/http_post_server.php` path is also routed. The endpoint accepts
URL-encoded fields in the POST body or the query string; POST body fields take
precedence. Required fields are `save_to_meter_data` (`1` historical, `0`
live), `location`, `meter_id`, and `datetime` in `YYYY-MM-DD HH:MM:SS` form.
The parser handles the gateway's `vpha_*` / `ipha_*` aliases, maps missing
optional numeric fields to zero, and treats short demand-time placeholders
such as `20` as unavailable (`NULL`). Successful requests return
`OK, YYYY-MM-DD HH:MM:SS` using the server's Manila time. A GET returns 405;
invalid input returns 400. A 500 indicates a server-side storage failure. The
schema has no unique historical-reading key, so retries may create duplicates.

`GET http://172.16.47.155/check_time.php` returns the server time in
`YYYY-MM-DD HH:MM:SS` format; other methods receive 405. The time route and
field-name-only validation diagnostics were tested in staging and then
explicitly approved for production. Those local changes were uncommitted when
this record was written.

When a gateway reported “failed to connect,” production access logs showed
that some POSTs reached the endpoint and returned 200, while others returned
400. On 2026-11-09 at 22:05, rejected requests were logged as
`Invalid numeric field: wh_total`; the diagnostics deliberately do not log the
field value. Later, at 22:10, two POSTs returned 200 and the historical count
increased. This does not prove that every later reading is valid. Obtain the
exact rejected field value from the gateway operator before changing validation;
do not silently coerce an invalid energy reading to zero.

The same device also requested `/rtu/index.php/rtu/rtu_check_update/...` and
received 403. Those gateway configuration/update endpoints are not implemented
by this deployment; in particular, do not turn on remote-command or reset
endpoints based on assumptions. Their protocol, authorization, configuration
file delivery, and state changes need separate review. Direct generated report
downloads remain blocked pending an authenticated, ownership-checked delivery
solution.

Useful read-only checks, executed on the production host by an authorized
administrator:

```bash
systemctl is-active apache2 php8.4-fpm mariadb
php8.4 -v
apache2ctl configtest
# Endpoint availability; GET on the POST endpoint is expected to return 405.
curl -i http://127.0.0.1/check_time.php
curl -i http://127.0.0.1/http_post_server.php
# Inspect only necessary recent diagnostics; access logs may contain full
# query strings and should be treated as sensitive.
tail -n 50 /srv/web-amr/current/application/logs/log-$(date +%F).php
```

When using loopback for Apache checks, send the production `Host` header if the
server selects a different virtual host, or request the VPN IP directly. Do
not send synthetic valid readings to production as a health check: they create
real data. Use a disposable staging database and remove only known synthetic
records. The previous production gateway code, route, and Apache configuration
were saved under `/secure/web-amr/rollback-gateway-20261109`; assess concurrent
traffic and preserve new readings before any rollback. A database backup and a
reconciliation plan remain necessary before a cutover or destructive recovery.

## 13. Guarded Git-based updates on the test server

`deploy/git_deploy.py` implements a **selected-file update**, not `git pull` in
the live release. The root-owned installed command is
`/usr/local/sbin/web-amr-deploy`. Git fetches and database-free source tests run
as the non-login `web-amr-deploy` account, using a private checkout at
`/opt/web-amr-deploy/work/repo`; the existing `/opt/web-amr-src` is untouched.
Configuration, baseline metadata, audit results, and backups live under the
root-only `/var/lib/web-amr-deploy`.

Run these commands **on 172.104.42.220 as root**:

```bash
web-amr-deploy status
web-amr-deploy check
web-amr-deploy update
web-amr-deploy rollback BACKUP_ID
```

`check` fetches and pins `origin/main`, previews the changed paths, verifies
managed live-file hashes, and runs offline PHP 8.4 and isolated RTU tests.
It does not install application files or reload services. `update` repeats
those checks, backs up affected paths, preserves file ownership/modes, applies
only permitted runtime changes, reloads FPM, and checks services and read-only
HTTP behavior. If the fetched revision is already the recorded baseline, it
does not create a backup or reload services. Only committed, pushed changes
are available to this server; local working-tree edits are not deployed.

Changes to database/schema, application configuration/routes, Apache/FPM
templates, root entry points, archived content, symlinks, or unsupported paths
stop the automatic update for a separate reviewed deployment. Generated data,
secrets, reports, sessions, uploads, logs, cache, tests, and deployment tooling
are never copied into the web root. Documentation/test/tooling-only commits can
advance the recorded source baseline without copying those files. Installed
deployment tooling does not automatically replace itself from Git.

Initial `init FULL_REVIEWED_COMMIT` is an audit only and refuses to overwrite an
existing baseline. Files matching the source become managed; missing or
different source files remain unmanaged and are listed in `baseline-audit.json`.
These differences are not silently overwritten or certified as deployed.
Later changes to an unmanaged existing path require manual reconciliation.

Backups are under `/var/lib/web-amr-deploy/backups/BACKUP_ID`. Automatic rollback
restores affected files after an ordinary post-update failure. Explicit
rollback verifies that the most recent applied update has not acquired later
file drift. If a process or machine is interrupted mid-update, `status`
prints a recovery backup ID; use `rollback` with that exact ID. Recovery refuses
unexpected edits rather than overwriting them. This is a multi-file in-place
update with per-file atomic replacement, **not** an atomic full-release switch;
use an agreed maintenance window for changes that cannot tolerate mixed versions.
Backups restore files only, never database records.

Node.js is required by the upload UI regression test when
`assets/meter_upload.js` changes. If it is absent, such an update stops with an
explicit prerequisite; unchanged JavaScript is not re-tested without Node.
The PHP/RTU tests use PHP 8.4 and disposable fixtures, never real gateway resets
or readings. HTTP checks are not a substitute for authenticated browser
acceptance. Clone access is currently public HTTPS without credentials; do not
embed a token/password if repository access policy later changes.

One-time installation is performed from a reviewed, checksummed bundle using
`deploy/install_git_deploy.py`; it refuses existing account/installation paths.
It installs the command and tests, clones privately, audits a reviewed baseline,
and runs `status`/`check` without copying live application code. The installer
is intentionally specific to the observed test host and must not be used on
MARIGOLD or another release without a new review. It does not install Node or
other OS packages and must not be rerun blindly after a partial installation.
