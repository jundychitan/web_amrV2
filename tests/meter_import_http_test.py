"""Real HTTP uploads through production controller/parser/model, synthetic sessions and SQLite only."""
import argparse
from contextlib import closing
import http.cookiejar
import json
from pathlib import Path
import re
import socket
import sqlite3
import subprocess
import tempfile
import time
import urllib.error
import urllib.request
import zipfile

ROOT = Path(__file__).resolve().parents[1]
ROUTER = r"""<?php
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) {
    throw new ErrorException($message, 0, $level, $file, $line);
});
define('BASEPATH', __DIR__.'/');
define('APPPATH', ROOT_PATH.'/application/');
ini_set('session.save_path', __DIR__);
session_start();
function log_message($level, $message) {}
#[AllowDynamicProperties]
class CI_Controller {}
#[AllowDynamicProperties]
class CI_Model {}
class TestSession {
    public function userdata($key) { return $_SESSION[$key] ?? NULL; }
    public function set_userdata($key, $value) { $_SESSION[$key] = $value; }
}
class TestInput {
    public function method($upper) { return $_SERVER['REQUEST_METHOD']; }
    public function post($key, $filter) { return $_POST[$key] ?? NULL; }
    public function get_request_header($key, $filter) { return $_SERVER['HTTP_X_CSRF_TOKEN'] ?? NULL; }
}
class TestOutput {
    public function set_content_type($type, $charset) { header('Content-Type: '.$type); return $this; }
    public function set_header($value) { header($value); return $this; }
    public function set_status_header($status) { http_response_code($status); return $this; }
    public function set_output($body) { print $body; return $this; }
}
class TestResult {
    private $statement;
    public function __construct($statement) { $this->statement = $statement; }
    public function result_array() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
}
class TestDb {
    public $db_debug = TRUE;
    private $pdo;
    public function __construct() { $this->pdo = new PDO('sqlite:'.__DIR__.'/test.sqlite'); }
    public function trans_begin() { return $this->pdo->beginTransaction(); }
    public function trans_commit() { return $this->pdo->commit(); }
    public function trans_rollback() { return $this->pdo->rollBack(); }
    public function trans_status() { return TRUE; }
    public function query($sql, $args = array()) {
        $sql = str_replace(' FOR UPDATE', '', $sql);
        $statement = $this->pdo->prepare($sql);
        $statement->execute($args);
        return new TestResult($statement);
    }
    public function get_where($table, $criteria) {
        $where = implode(' AND ', array_map(function ($key) { return $key.' = ?'; }, array_keys($criteria)));
        return $this->query('SELECT * FROM '.$table.' WHERE '.$where, array_values($criteria));
    }
    public function insert($table, $data) {
        $statement = $this->pdo->prepare('INSERT INTO '.$table.' ('.implode(',', array_keys($data)).
            ') VALUES ('.implode(',', array_fill(0, count($data), '?')).')');
        return $statement->execute(array_values($data));
    }
}
class TestLoader {
    private $controller;
    public function __construct($controller) { $this->controller = $controller; }
    public function library($name) {
        require_once APPPATH.'libraries/'.ucfirst($name).'.php';
        $class = ucfirst($name);
        $this->controller->$name = new $class();
    }
    public function model($name) {
        require_once APPPATH.'models/'.ucfirst($name).'.php';
        $class = ucfirst($name);
        $this->controller->$name = new $class();
        $this->controller->$name->db = new TestDb();
    }
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/test-login') {
    $_SESSION = array('currently_logged_in_as' => 'Admin', 'user_id' => 5, 'user_name' => 'fixture-admin');
    print 'fixture';
    return;
}
$class = strpos($path, '/api/gateways/') === 0 ? 'Gateway_import' : 'Meter_import';
require APPPATH.'controllers/'.$class.'.php';
$controller = new $class();
$controller->session = new TestSession();
$controller->input = new TestInput();
$controller->output = new TestOutput();
$controller->load = new TestLoader($controller);
if (substr($path, -6) === '/token') { $controller->token(); }
else { $controller->index(); }
"""


def workbook(serial="000123", multiplier="", extra=""):
    sheet = f"""<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Gateway serial no</t></is></c>
<c r="B1" t="inlineStr"><is><t>GW-01</t></is></c></row><row r="2">"""
    headers = ["Meter Location", "Meter Serial No", "Meter Designation/Tenant", "Meter Type",
               "Config File", "Modbus Address", "Meter Multiplier"]
    for col, value in zip("ABCDEFG", headers):
        sheet += f'<c r="{col}2" t="inlineStr"><is><t>{value}</t></is></c>'
    sheet += '</row><row r="3">'
    for col, value in zip("ABCDEFG", ["Floor", serial, "Tenant", "3P", "elite.cfg", "", multiplier]):
        sheet += f'<c r="{col}3" t="inlineStr"><is><t>{value}</t></is></c>'
    return sheet + f"</row></sheetData>{extra}</worksheet>"


def gateway_workbook(ip="192.0.2.11"):
    sheet = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
    fields = [("Gateway serial no", "GW-02"), ("Gateway mac address", "AA-BB-CC-DD-EE-02"),
              ("Gateway Location", "Gateway Floor"), ("Gateway IP address", ip),
              ("IDF Number", "IDF-1"), ("Switch", "SW-1"), ("Port number", "01")]
    for row, (label, value) in enumerate(fields, 1):
        sheet += (f'<row r="{row}"><c r="B{row}" t="inlineStr"><is><t>{label}</t></is></c>'
                  f'<c r="C{row}" t="inlineStr"><is><t>{value}</t></is></c></row>')
    return sheet + "</sheetData></worksheet>"


def write_book(path, sheet):
    with zipfile.ZipFile(path, "w", zipfile.ZIP_DEFLATED) as archive:
        archive.writestr("[Content_Types].xml", """<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>""")
        archive.writestr("_rels/.rels", """<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>""")
        archive.writestr("xl/workbook.xml", """<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<bookViews><workbookView activeTab="0"/></bookViews>
<sheets><sheet name="Asbuilt" sheetId="1" r:id="rId1"/></sheets></workbook>""")
        archive.writestr("xl/_rels/workbook.xml.rels", """<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>""")
        archive.writestr("xl/sharedStrings.xml", '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="0" uniqueCount="0"/>')
        archive.writestr("xl/styles.xml", """<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
<fills count="1"><fill><patternFill patternType="none"/></fill></fills>
<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>
</styleSheet>""")
        archive.writestr("xl/worksheets/sheet1.xml", sheet)


def seed(path):
    sql = (ROOT / "database/meter_reading_db_schema.sql").read_text()
    with closing(sqlite3.connect(path)) as db:
        gateway_seed = {}
        for table in ("meter_details", "meter_rtu"):
            block = re.search(r"CREATE TABLE IF NOT EXISTS `" + table + r"` \((.*?)\n\)", sql, re.S)[1]
            columns = []
            for name, definition in re.findall(r"^\s*`([^`]+)` (.+)$", block, re.M):
                if name == "id":
                    columns.append("id INTEGER PRIMARY KEY")
                    continue
                kind = "INTEGER" if definition.startswith("int(") else "REAL" if definition.startswith("double") else "TEXT"
                nullable = " NOT NULL" if "NOT NULL" in definition else ""
                default = re.search(r"DEFAULT ('[^']*'|NULL|[0-9]+)", definition)
                columns.append(f"`{name}` {kind}{nullable}" + (" DEFAULT " + default[1] if default else ""))
                if table == "meter_rtu":
                    gateway_seed[name] = 0 if kind == "INTEGER" else ""
            db.execute("CREATE TABLE " + table + " (" + ",".join(columns) + ")")
        gateway_seed.update(id=42, rtu_sn_number="GW-01", rtu_site_id=1, mac_addr="aa:bb:cc:dd:ee:01")
        db.execute("INSERT INTO meter_rtu (" + ",".join(gateway_seed) + ") VALUES (" +
                   ",".join("?" for _ in gateway_seed) + ")", list(gateway_seed.values()))
        db.commit()
        db.executescript("""
CREATE TABLE meter_site (id INTEGER, site_code TEXT, company_no INTEGER, business_entity TEXT);
INSERT INTO meter_site VALUES (1,'SITE',7,'ENTITY');
CREATE TABLE meter_model_config (id INTEGER, config_file TEXT, meter_model TEXT);
INSERT INTO meter_model_config VALUES (8,'elite.cfg','Elite');
""")


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--php", default="php")
    args = parser.parse_args()
    with tempfile.TemporaryDirectory(prefix="amr_import_http_") as directory:
        temp = Path(directory)
        seed(temp / "test.sqlite")
        router = temp / "router.php"
        router.write_text(ROUTER.replace("ROOT_PATH", json.dumps(ROOT.as_posix())), encoding="utf-8")
        with socket.socket() as sock:
            sock.bind(("127.0.0.1", 0))
            port = sock.getsockname()[1]
        with (temp / "server.log").open("wb") as log:
            process = subprocess.Popen([args.php, "-d", "display_errors=1", "-S",
                                        f"127.0.0.1:{port}", str(router)],
                                       cwd=temp, stdout=log, stderr=log)
            try:
                base = f"http://127.0.0.1:{port}"
                client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

                def request(route, body=None, headers=None):
                    req = urllib.request.Request(base + route, data=body, headers=headers or {})
                    try:
                        response = client.open(req, timeout=10)
                    except urllib.error.HTTPError as error:
                        response = error
                    with response:
                        return response.status, response.read()

                for _ in range(100):
                    try:
                        status, _ = request("/api/meters/import/token")
                        break
                    except urllib.error.URLError:
                        if process.poll() is not None:
                            raise RuntimeError("HTTP harness failed to start.")
                        time.sleep(0.05)
                else:
                    raise RuntimeError("HTTP harness startup timed out.")
                assert status == 401
                request("/test-login")
                status, body = request("/api/meters/import/token")
                token = json.loads(body)["csrf_token"]
                assert status == 200 and len(token) == 64
                fixture = temp / "meters.xlsx"
                write_book(fixture, workbook())

                def upload(dry="1", csrf=token, route="/api/meters/import"):
                    boundary = "amr-test-boundary"
                    data = (f"--{boundary}\r\nContent-Disposition: form-data; name=\"site_id\"\r\n\r\n1\r\n"
                            f"--{boundary}\r\nContent-Disposition: form-data; name=\"dry_run\"\r\n\r\n{dry}\r\n"
                            f"--{boundary}\r\nContent-Disposition: form-data; name=\"file\"; filename=\"meters.xlsx\"\r\n"
                            "Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet\r\n\r\n").encode()
                    data += fixture.read_bytes() + f"\r\n--{boundary}--\r\n".encode()
                    status, body = request(route, data, {
                        "Content-Type": f"multipart/form-data; boundary={boundary}", "X-CSRF-Token": csrf})
                    return status, json.loads(body)

                assert upload(csrf="wrong")[0] == 403
                status, report = upload()
                assert status == 200 and report["would_insert"] == 1 and report["inserted"] == 0, (status, report)
                with closing(sqlite3.connect(temp / "test.sqlite")) as db:
                    assert db.execute("SELECT COUNT(*) FROM meter_details").fetchone()[0] == 0
                status, report = upload("0")
                assert status == 200 and report["inserted"] == 1
                with closing(sqlite3.connect(temp / "test.sqlite")) as db:
                    assert db.execute("""SELECT meter_name,rtu_sn_number_id,meter_default_name,
meter_name_addressable,meter_multiplier,meter_model_id FROM meter_details""").fetchone() == (
                        "000123", 42, "000123", 1, 1.0, 8)
                status, report = upload("0")
                assert status == 200 and report["skipped"] == 1 and report["inserted"] == 0
                write_book(fixture, workbook("invalid", "not-numeric"))
                status, report = upload("0")
                assert status == 422 and report["invalid"] == 1 and report["inserted"] == 0
                write_book(fixture, workbook(extra='<mergeCells><mergeCell ref="A1:XFD1048576"/></mergeCells>'))
                assert upload()[0] == 400
                write_book(fixture, workbook().replace('r="A3"', 'r="XFD3"'))
                assert upload()[0] == 400
                fixture.write_bytes(b"not a workbook")
                assert upload()[0] == 400
                with closing(sqlite3.connect(temp / "test.sqlite")) as db:
                    assert db.execute("SELECT COUNT(*) FROM meter_details").fetchone()[0] == 1
                status, body = request("/api/gateways/import/token")
                gateway_token = json.loads(body)["csrf_token"]
                assert status == 200 and gateway_token != token
                write_book(fixture, gateway_workbook())
                assert upload(csrf=token, route="/api/gateways/import")[0] == 403
                status, report = upload(csrf=gateway_token, route="/api/gateways/import")
                assert status == 200 and report["would_insert"] == 1 and report["inserted"] == 0, (status, report)
                status, report = upload("0", csrf=gateway_token, route="/api/gateways/import")
                assert status == 200 and report["inserted"] == 1, (status, report)
                with closing(sqlite3.connect(temp / "test.sqlite")) as db:
                    assert db.execute("""SELECT rtu_sn_number,mac_addr,connection_type,ip_netmask,ip_gateway,
rtu_server_ip,gateway_description,rtu_site_id,rtu_site_name,switch_name,idf_port,
update_rtu,update_rtu_location,update_rtu_ssh,update_rtu_force_lp
FROM meter_rtu WHERE rtu_sn_number='GW-02'""").fetchone() == (
                        "GW-02", "aa:bb:cc:dd:ee:02", "LAN", "n/a", "n/a", "n/a", "Gateway Floor",
                        1, "SITE", "SW-1", "01", 0, 0, 0, 0)
                status, report = upload("0", csrf=gateway_token, route="/api/gateways/import")
                assert status == 200 and report["skipped"] == 1
                write_book(fixture, gateway_workbook("bad-ip"))
                status, report = upload("0", csrf=gateway_token, route="/api/gateways/import")
                assert status == 422 and report["invalid"] == 1
                with closing(sqlite3.connect(temp / "test.sqlite")) as db:
                    assert db.execute("SELECT COUNT(*) FROM meter_rtu").fetchone()[0] == 2
                print("PASS: isolated Meter/Gateway HTTP imports, CSRF, defaults, dry run, insert, retry, validation and archive limits.")
            finally:
                process.terminate()
                process.wait(timeout=10)


if __name__ == "__main__":
    main()
