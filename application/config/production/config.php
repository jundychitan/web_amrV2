<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$url = getenv('AMR_BASE_URL');
if ($url === FALSE || !filter_var($url, FILTER_VALIDATE_URL)
    || parse_url($url, PHP_URL_SCHEME) !== 'https'
    || parse_url($url, PHP_URL_USER) !== NULL
    || parse_url($url, PHP_URL_PASS) !== NULL
    || parse_url($url, PHP_URL_QUERY) !== NULL
    || parse_url($url, PHP_URL_FRAGMENT) !== NULL) {
    throw new RuntimeException('AMR_BASE_URL must be a canonical HTTPS application URL.');
}
$session_path = getenv('AMR_SESSION_PATH') ?: '/var/lib/web-amr/sessions';
if (!is_dir($session_path) || !is_writable($session_path)) {
    throw new RuntimeException('The private production session directory is not writable.');
}

$config['base_url'] = rtrim($url, '/').'/';
$config['composer_autoload'] = FALSE;
$config['sess_driver'] = 'files';
$config['sess_save_path'] = $session_path;
$config['cookie_secure'] = TRUE;
$config['cookie_httponly'] = TRUE;
$config['log_threshold'] = 1;
