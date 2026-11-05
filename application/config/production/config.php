<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$url = getenv('AMR_BASE_URL');
$scheme = $url === FALSE ? NULL : parse_url($url, PHP_URL_SCHEME);
$host = $url === FALSE ? NULL : parse_url($url, PHP_URL_HOST);
$http_ip_enabled = getenv('AMR_ALLOW_HTTP_IP') === '1'
    && $scheme === 'http' && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
if ($url === FALSE || !filter_var($url, FILTER_VALIDATE_URL)
    || ($scheme !== 'https' && !$http_ip_enabled)
    || parse_url($url, PHP_URL_USER) !== NULL
    || parse_url($url, PHP_URL_PASS) !== NULL
    || parse_url($url, PHP_URL_QUERY) !== NULL
    || parse_url($url, PHP_URL_FRAGMENT) !== NULL) {
    throw new RuntimeException('AMR_BASE_URL must be HTTPS, or an explicitly enabled HTTP IPv4 URL.');
}
$session_path = getenv('AMR_SESSION_PATH') ?: '/var/lib/web-amr/sessions';
if (!is_dir($session_path) || !is_writable($session_path)) {
    throw new RuntimeException('The private production session directory is not writable.');
}

$config['base_url'] = rtrim($url, '/').'/';
$config['composer_autoload'] = FALSE;
$config['sess_driver'] = 'files';
$config['sess_save_path'] = $session_path;
$config['cookie_secure'] = !$http_ip_enabled;
$config['cookie_httponly'] = TRUE;
$config['log_threshold'] = 1;
