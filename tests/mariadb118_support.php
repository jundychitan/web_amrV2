<?php
// Pure policy/catalog helpers; including this file never opens a connection.
class Amr118Failure extends RuntimeException {}

function amr118_options(array $arguments): array {
    $options = array('confirm' => NULL, 'temporary_writes' => FALSE, 'help' => FALSE, 'list' => FALSE);
    foreach ($arguments as $argument) {
        if ($argument === '--help') {
            $options['help'] = TRUE;
        } elseif ($argument === '--list') {
            $options['list'] = TRUE;
        } elseif ($argument === '--temporary-writes') {
            $options['temporary_writes'] = TRUE;
        } elseif (str_starts_with($argument, '--confirm-staging=')) {
            if ($options['confirm'] !== NULL) {
                throw new Amr118Failure('Duplicate staging confirmation.');
            }
            $options['confirm'] = substr($argument, strlen('--confirm-staging='));
        } else {
            throw new Amr118Failure('Unknown argument. Use --help.');
        }
    }
    return $options;
}

function amr118_config(array $environment, array $options): array {
    $required = array('HOST', 'PORT', 'NAME', 'USER', 'PASSWORD');
    foreach ($required as $suffix) {
        if (!isset($environment[$suffix]) || $environment[$suffix] === '') {
            throw new Amr118Failure('Missing AMR_TEST_DB_'.$suffix.'. No production fallback is allowed.');
        }
    }
    $name = $environment['NAME'];
    if (!preg_match('/\Aamr_test_[A-Za-z0-9_]+\z/D', $name)
        || $options['confirm'] !== $name) {
        throw new Amr118Failure('Require an amr_test_ database and matching --confirm-staging name.');
    }
    if (!ctype_digit((string) $environment['PORT'])
        || (int) $environment['PORT'] < 1 || (int) $environment['PORT'] > 65535) {
        throw new Amr118Failure('AMR_TEST_DB_PORT must be between 1 and 65535.');
    }
    $charset = $environment['CHARSET'] ?? 'utf8';
    $collation = $environment['COLLATION'] ?? 'utf8_general_ci';
    if (!preg_match('/\A[A-Za-z0-9_]+\z/D', $charset)
        || !preg_match('/\A[A-Za-z0-9_]+\z/D', $collation)) {
        throw new Amr118Failure('Invalid test charset or collation identifier.');
    }
    $ca = $environment['SSL_CA'] ?? '';
    $local = in_array($environment['HOST'], array('localhost', '127.0.0.1', '::1'), TRUE)
        || str_starts_with($environment['HOST'], '/');
    if (!$local && $ca === '') {
        throw new Amr118Failure('Remote test connections require AMR_TEST_DB_SSL_CA; alternatively use an approved localhost SSH tunnel.');
    }
    return array(
        'hostname' => $environment['HOST'], 'port' => (int) $environment['PORT'],
        'database' => $name, 'username' => $environment['USER'],
        'password' => $environment['PASSWORD'], 'dbdriver' => 'mysqli',
        'char_set' => $charset, 'dbcollat' => $collation,
        'encrypt' => $ca === '' ? FALSE : array('ssl_ca' => $ca, 'ssl_verify' => TRUE),
        'pconnect' => FALSE, 'db_debug' => FALSE, 'save_queries' => FALSE,
        'stricton' => FALSE, 'dbprefix' => ''
    );
}

function amr118_version(string $version): bool {
    return (bool) preg_match('/\A11\.8\.\d+.*MariaDB/i', $version);
}

function amr118_normalize(string $value): string {
    return preg_replace('/\Autf8(?=_|\z)/', 'utf8mb3', strtolower($value));
}

function amr118_control(object $connection, string $sql): void {
    $allowed = array(
        'SET SESSION max_statement_time = 5', 'START TRANSACTION READ ONLY', 'ROLLBACK',
        'CREATE TEMPORARY TABLE `user_tb` LIKE `user_tb`',
        'DROP TEMPORARY TABLE IF EXISTS `user_tb`'
    );
    if (!in_array($sql, $allowed, TRUE)) {
        throw new Amr118Failure('Unrecognized test control statement.');
    }
    if ($connection->query($sql) !== TRUE) {
        throw new Amr118Failure('Test control statement failed; no unsafe fallback is allowed.');
    }
}

function amr118_allowed(string $sql, bool $temporaryUser): bool {
    if (preg_match('/\A\s*SELECT\b/i', $sql)) {
        return !preg_match('/\bINTO\s+(?:OUTFILE|DUMPFILE)\b|\bFOR\s+UPDATE\b|\bLOCK\s+IN\s+SHARE\s+MODE\b/i', $sql);
    }
    if (!$temporaryUser) {
        return FALSE;
    }
    // Only known model/query-builder writes are admitted. Database privileges
    // and the server READ ONLY transaction remain the independent safety layer.
    return (bool) preg_match(
        '/\A\s*(?:UPDATE\s+`?user_tb`?\s+SET\b|INSERT\s+INTO\s+`?user_tb`?\s*\(|DELETE\s+FROM\s+`?user_tb`?\s+WHERE\b)/i',
        $sql
    );
}

function amr118_catalog(array $c): array {
    return array(
        array('login.lookup', 'Login_model', 'log_in_correctly', array()),
        array('admin.user', 'Admin_model', 'get_selected_user', array($c['user_id'])),
        array('admin.site', 'Admin_model', 'get_selected_site', array($c['site_id'])),
        array('admin.sites', 'Admin_model', 'site_list', array()),
        array('admin.mall-access', 'Admin_model', 'site_list_mall', array($c['user_id'])),
        array('admin.cpg-access', 'Admin_model', 'site_list_cpg', array($c['user_id'])),
        array('admin.models', 'Admin_model', 'meter_model_list', array()),
        array('admin.rtus', 'Admin_model', 'rtu_list', array($c['site_id'])),
        array('admin.site-access', 'Admin_model', 'user_access_group_list', array($c['user_id'])),
        array('admin.meter-access', 'Admin_model', 'get_client_meter_access_list', array($c['user_id'])),
        array('admin.meter', 'Admin_model', 'meter_info', array($c['meter'])),
        array('admin.latest-reading', 'Admin_model', 'get_meter_last_update', array($c['meter'])),
        array('admin.user-meter-groups', 'Admin_model', 'get_meter_detail_from_meter_data', array()),
        array('admin.offline', 'Admin_model', 'offline_meters', array($c['site_id'], $c['time'])),
        array('client.access', 'Client_model', 'get_client_meter_list', array()),
        array('report.meter', 'Report_model', 'get_selected_meter', array($c['meter_id'])),
        array('report.meter-info', 'Report_model', 'get_meter_info', array($c['meter'], $c['site_code'])),
        array('report.site', 'Report_model', 'site_details', array($c['site_code'])),
        array('report.meter-list', 'Report_model', 'get_location_meter_list', array($c['site_code'])),
        array('report.locations', 'Report_model', 'get_physical_location_meter_list', array($c['site_code'])),
        array('report.buildings', 'Report_model', 'get_building_list', array($c['site_code'])),
        array('report.raw-readings', 'Report_model', 'meter_all_logs_report', array($c['site_code'])),
        array('report.fifteen-minute', 'Report_model', 'fifteen_minutes_query', array($c['meter'], $c['time'], $c['site_code'])),
        array('report.hourly', 'Report_model', 'hourly_query', array($c['meter'], substr($c['time'], 0, 13), $c['site_code'])),
        array('report.daily', 'Report_model', 'meter_daily_report_query', array($c['meter'], substr($c['time'], 0, 10), $c['site_code'])),
        array('report.gateways', 'Report_model', 'location_gateway_list', array($c['site_code'])),
        array('report.gateway-meters', 'Report_model', 'gateway_meter_list', array($c['rtu_id'])),
        array('report.offline-gateways', 'Report_model', 'offline_gateway', array($c['time']))
    );
}
