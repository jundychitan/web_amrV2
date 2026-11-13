<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once __DIR__.'/Meter_import.php';

class Gateway_import extends Meter_import {
    protected $csrf_key = 'gateway_import_csrf';
    protected $workbook_library = 'gateway_enrollment_workbook';
    protected $enrollment_model = 'gateway_enrollment_model';
    protected $entity = 'gateways';
}
