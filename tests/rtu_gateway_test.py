#!/usr/bin/env python3
"""Exercise the real CodeIgniter controller against a disposable SQLite database."""
import pathlib
import shutil
import sqlite3
import subprocess
import tempfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
MAC = 'aa:bb:cc:dd:ee:ff'

with tempfile.TemporaryDirectory(prefix='amr-rtu-test-') as temporary:
    app = pathlib.Path(temporary)
    for directory in ('config', 'controllers', 'models', 'logs', 'cache'):
        (app / directory).mkdir()
    shutil.copy(ROOT / 'application/controllers/Rtu.php', app / 'controllers/Rtu.php')
    shutil.copy(ROOT / 'application/models/Rtu_model.php', app / 'models/Rtu_model.php')
    config = (ROOT / 'application/config/config.php').read_text()
    config += "\n$config['enable_hooks'] = FALSE; $config['log_threshold'] = 0;\n"
    (app / 'config/config.php').write_text(config)
    shutil.copy(ROOT / 'application/config/routes.php', app / 'config/routes.php')
    (app / 'config/autoload.php').write_text(
        "<?php $autoload = array('packages'=>array(), 'libraries'=>array('database'), "
        "'drivers'=>array(), 'helper'=>array(), 'config'=>array(), 'language'=>array(), 'model'=>array());\n"
    )
    database = app / 'test.sqlite'
    (app / 'config/database.php').write_text(
        "<?php $active_group='default'; $query_builder=TRUE; "
        "$db['default']=array('dbdriver'=>'sqlite3','database'=>" + repr(str(database)) +
        ",'db_debug'=>FALSE, 'pconnect'=>FALSE, 'save_queries'=>TRUE);\n"
    )
    runner = app / 'runner.php'
    runner.write_text("""<?php
define('ENVIRONMENT', 'testing');
define('BASEPATH', %s);
define('APPPATH', %s);
define('VIEWPATH', %s);
define('FCPATH', %s);
define('SELF', 'index.php');
define('SYSDIR', 'system');
$_SERVER['HTTP_HOST'] = 'example.invalid';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/index.php/rtu/rtu_check_update/' . $argv[1] . '/' . $argv[2];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['argv'] = array('runner.php', 'rtu', 'index.php', 'rtu', 'rtu_check_update', $argv[1], $argv[2]);
require BASEPATH . 'core/CodeIgniter.php';
""" % (repr(str(ROOT / 'system') + '/'), repr(str(app) + '/'),
       repr(str(ROOT / 'application/views') + '/'), repr(str(app) + '/')))
    with sqlite3.connect(database) as db:
        db.executescript('''
            CREATE TABLE meter_rtu (id INTEGER PRIMARY KEY, mac_addr TEXT,
                update_rtu INTEGER, update_rtu_location INTEGER, rtu_site_name TEXT);
            CREATE TABLE meter_details (id INTEGER PRIMARY KEY, rtu_sn_number_id INTEGER,
                meter_name TEXT, meter_config_file TEXT, meter_default_name TEXT);
            INSERT INTO meter_rtu VALUES (1, 'aa:bb:cc:dd:ee:ff', 1, 1, 'MARIGOLD');
            INSERT INTO meter_details VALUES (1, 1, 'meter1', 'config.ini', 'address1');
            INSERT INTO meter_details VALUES (2, 2, 'other', 'other.ini', 'other');
        ''')

    def request(action, mac=MAC):
        result = subprocess.run(['php', str(runner), mac, action], capture_output=True, text=True)
        if result.returncode or result.stderr:
            raise AssertionError((action, result.returncode, result.stdout, result.stderr))
        return result.stdout

    assert request('get_update_csv') == '1'
    assert request('get_content_csv') == 'meter1,config.ini,address1\n'
    assert request('get_update_location') == '1'
    assert request('get_content_location') == 'location = "MARIGOLD"\n'
    assert request('reset_update_csv') == ''
    assert request('get_update_csv') == '0'
    assert request('get_update_location') == '1'
    assert request('reset_update_location') == ''
    assert request('get_update_location') == '0'
    assert request('get_update_csv', '00:00:00:00:00:00') == '0'
    with sqlite3.connect(database) as db:
        db.execute('UPDATE meter_rtu SET update_rtu=1')
        db.execute("UPDATE meter_details SET meter_name='bad,name' WHERE id=1")
    assert request('get_update_csv') == '0'
    assert request('get_content_csv') == 'Update unavailable'
    with sqlite3.connect(database) as db:
        db.execute("UPDATE meter_rtu SET update_rtu_location=1, rtu_site_name='bad`command`'")
    assert request('get_update_location') == '0'
    assert request('get_content_location') == 'Update unavailable'
    with sqlite3.connect(database) as db:
        db.execute("INSERT INTO meter_rtu VALUES (2, ?, 1, 1, 'other')", (MAC,))
    assert request('get_update_location') == '0'
    assert request('reset_update_location') == 'Gateway not found'
    print('PASS: gateway status, content, resets, validation and duplicate MAC handling')
