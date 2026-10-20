<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Set these in the PHP-FPM service environment, never in tracked source files.
$required = array('AMR_DB_HOST', 'AMR_DB_NAME', 'AMR_DB_USER', 'AMR_DB_PASSWORD');
foreach ($required as $name) {
    if (getenv($name) === FALSE || getenv($name) === '') {
        throw new RuntimeException('Required production database configuration is missing.');
    }
}

$active_group = 'default';
$query_builder = TRUE;
$db['default'] = array(
    'dsn' => '',
    'hostname' => getenv('AMR_DB_HOST'),
    'username' => getenv('AMR_DB_USER'),
    'password' => getenv('AMR_DB_PASSWORD'),
    'database' => getenv('AMR_DB_NAME'),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => FALSE,
    'cache_on' => FALSE,
    'cachedir' => '',
    // Match the source defaults; do not silently convert the database schema.
    'char_set' => getenv('AMR_DB_CHARSET') ?: 'utf8',
    'dbcollat' => getenv('AMR_DB_COLLATION') ?: 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => FALSE,
    'port' => getenv('AMR_DB_PORT') ?: 3306
);
