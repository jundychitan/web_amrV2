<?php
$route['default_controller'] = "login";
$route['api/meters/import'] = 'meter_import/index';
$route['api/meters/import/token'] = 'meter_import/token';
$route['api/gateways/import'] = 'gateway_import/index';
$route['api/gateways/import/token'] = 'gateway_import/token';
$route['http_post_server\.php'] = 'meter_ingest/index';
$route['check_time\.php'] = 'meter_ingest/check_time';
$route['rtu/index\.php/rtu/rtu_check_update/([^/]+)/([^/]+)'] = 'rtu/rtu_check_update/$1/$2';
$route['404_override'] = '';
