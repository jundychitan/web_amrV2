<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meter_import extends CI_Controller {
    protected $csrf_key = 'meter_import_csrf';
    protected $workbook_library = 'meter_enrollment_workbook';
    protected $enrollment_model = 'meter_enrollment_model';
    protected $entity = 'meters';
    private function respond($status, array $body) {
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_header('Cache-Control: no-store')
            ->set_status_header($status)->set_output(json_encode($body, JSON_INVALID_UTF8_SUBSTITUTE));
    }

    private function authorized() {
        if (!$this->session->userdata('currently_logged_in_as')) {
            $this->respond(401, array('error' => 'Admin login required.'));
            return FALSE;
        }
        if ($this->session->userdata('currently_logged_in_as') !== 'Admin') {
            $this->respond(403, array('error' => 'Admin access required.'));
            return FALSE;
        }
        return TRUE;
    }

    public function token() {
        if ($this->input->method(TRUE) !== 'GET') {
            $this->output->set_header('Allow: GET');
            $this->respond(405, array('error' => 'GET required.'));
            return;
        }
        if (!$this->authorized()) {
            return;
        }
        $token = $this->session->userdata($this->csrf_key);
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $this->session->set_userdata($this->csrf_key, $token);
        }
        $this->respond(200, array('csrf_token' => $token));
    }

    public function index() {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_header('Allow: POST');
            $this->respond(405, array('error' => 'POST required.'));
            return;
        }
        if (!$this->authorized()) {
            return;
        }
        // Endpoint-specific synchronizer token: global CI CSRF is disabled in this app.
        $expected = $this->session->userdata($this->csrf_key);
        $provided = $this->input->get_request_header('X-CSRF-Token', FALSE);
        if (!is_string($expected) || !is_string($provided) || !hash_equals($expected, $provided)) {
            $this->respond(403, array('error' => 'Obtain an import CSRF token and send X-CSRF-Token.'));
            return;
        }
        $site_id = $this->input->post('site_id', FALSE);
        $dry_run = $this->input->post('dry_run', FALSE);
        if (!is_string($site_id) || !preg_match('/^[1-9][0-9]{0,9}$/D', $site_id)
            || (float) $site_id > 2147483647
            || !in_array($dry_run, array(NULL, '0', '1'), TRUE)) {
            $this->respond(400, array('error' => 'Provide a positive site_id and optional dry_run=0 or 1.'));
            return;
        }
        $file = $_FILES['file'] ?? NULL;
        if (!is_array($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK
            || !isset($file['name'], $file['tmp_name']) || !is_string($file['name'])
            || !is_string($file['tmp_name'])
            || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'xlsx'
            || !is_uploaded_file($file['tmp_name'])) {
            $this->respond(400, array('error' => 'Upload one XLSX workbook in the file field (maximum 5 MiB).'));
            return;
        }
        $this->load->library($this->workbook_library);
        try {
            $records = $this->{$this->workbook_library}->read($file['tmp_name']);
        } catch (Throwable $error) {
            $this->respond(400, array('error' => $error instanceof InvalidArgumentException
                ? $error->getMessage() : 'Unable to read the XLSX workbook.'));
            return;
        }
        $this->load->model($this->enrollment_model);
        try {
            $report = $this->{$this->enrollment_model}->import($records, (int) $site_id, array(
                'user_id' => $this->session->userdata('user_id'),
                'user_name' => $this->session->userdata('user_name')
            ), $dry_run === '1');
            $this->respond($report['invalid'] ? 422 : 200, $report);
        } catch (InvalidArgumentException $error) {
            $this->respond(400, array('error' => $error->getMessage()));
        } catch (Throwable $error) {
            log_message('error', 'Enrollment import failed for '.$this->entity.'; transaction rolled back.');
            $this->respond(500, array('error' => 'Unable to store '.$this->entity.'; no import was committed.'));
        }
    }
}
