# Step-by-step deployment: PHP 8.4 and MariaDB 11.8

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
   Prepare a reviewed snapshot of the actual working tree, including its
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
4. Direct PHP endpoints under template directories are assessed: the template
   permits only the front controller, `index.php`, to execute.
5. External RTU commands, collectors, scheduled jobs, and integrations are
   inventoried and remain isolated until explicitly enabled.

## 1. Record the deployment inputs

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
schema inventory. No source DML/DDL privileges are needed for this procedure.

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

While the new staging database is isolated, clear its browser ownership:

```sql
UPDATE amr_test_mariadb118.user_tb
SET active_session_hash = NULL, active_session_activity = NULL;
```

Do not copy browser session files. Apply only the approved RTU/timestamp/state
resets, and keep collectors/device commands disconnected.

Have the DBA securely provision an `amr_query_test` account. For a local test
process, grant reads plus optional temporary-table creation after it exists:

```sql
GRANT SELECT, CREATE TEMPORARY TABLES ON amr_test_mariadb118.*
TO 'amr_query_test'@'localhost';
```

Adjust the account host to the approved connection path, not unrestricted
access. Run section 10's query tests using `AMR_TEST_DB_*`. Use a separate
staging runtime account for browser tests; do not grant permanent DML to the
restricted query-test account.
For example, after securely provisioning a separate local staging runtime
account:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON amr_test_mariadb118.*
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
GRANT SELECT, INSERT, UPDATE, DELETE ON meter_reading.*
TO 'amr_app'@'localhost';
```

Update `AMR_DB_NAME=meter_reading` and the runtime user/secret, have the
administrator restart the dedicated FPM service, and repeat final browser,
schema, and empty-history checks. Never reuse query-test/import credentials
for production traffic.

## 7. Prepare a sanitized, immutable release

Use the **tested V2 working tree**. These changes were not committed during
verification, so `git archive HEAD` or cloning the remote may omit the PHP 8.4
patches and tests. Transfer a reviewed working-tree snapshot into the private
`/opt/web-amr-src` workspace, record its checksum, and run the suite on Linux.

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
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/reports
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/meter_upload_list
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/application/logs
sudo install -d -o www-data -g www-data -m 0750 /srv/web-amr/releases/RELEASE_ID/application/cache
```

Make sure parent directories allow the service user to traverse them. Apply
log rotation and an approved generated-report retention policy.

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

- `AMR_BASE_URL`: the final HTTPS URL including `/amr/`, e.g.
  `https://amr.example.com/amr/`.
- `AMR_SESSION_PATH`: `/var/lib/web-amr/sessions`.
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

Provision the certificate through the approved CA/DNS workflow before TLS
validation. Restrict staging access to administrators. The template retains
`/amr/`, redirects HTTP to HTTPS, disables `.htaccess` overrides, denies internal
source/writable directories and backups, and executes only `index.php`.
Consequently, the repository's old-host `.htaccess` redirect is not used.
Do not enable mod_php or an unrelated global PHP handler that bypasses this
front-controller policy.

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
curl --resolve amr.example.com:443:192.0.2.20 --head https://amr.example.com/amr/index.php/login
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

- Confirm the destination reports MariaDB 11.8.x. Run actual SELECT/INSERT/
  UPDATE/DELETE and session-lock operations using the runtime account, not just
  the offline fake-transport test. Exercise report aggregation/date queries
  with both empty data and representative disposable readings; compare expected
  results against a controlled source baseline. Check SQL modes, explicit
  timestamp defaults, text sorting/collations, filename/table-name case, and
  query plans/index use where performance matters.
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
