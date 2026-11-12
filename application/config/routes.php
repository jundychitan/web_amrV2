<?php
$route['default_controller'] = "login";
$route['http_post_server\.php'] = 'meter_ingest/index';
$route['check_time\.php'] = 'meter_ingest/check_time';
$route['rtu/index\.php/rtu/rtu_check_update/([^/]+)/([^/]+)'] = 'rtu/rtu_check_update/$1/$2';
$route['404_override'] = '';
