<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meter_ingest extends CI_Controller {
    public function index() {
        $this->output->set_content_type('text/plain', 'utf-8');
        if ($this->input->method(TRUE) !== 'POST') {
            $this->output->set_status_header(405)->set_output('POST required');
            return;
        }
        $this->load->library('meter_reading_payload');
        try {
            $query = $this->input->get(NULL, FALSE);
            $post = $this->input->post(NULL, FALSE);
            // Some gateways send POST parameters in the URL. Body fields take precedence.
            $reading = $this->meter_reading_payload->parse(array_merge(
                is_array($query) ? $query : array(), is_array($post) ? $post : array()
            ));
        } catch (InvalidArgumentException $error) {
            $this->output->set_status_header(400)->set_output('Invalid meter reading');
            return;
        }
        $this->load->model('meter_ingest_model');
        if (!$this->meter_ingest_model->store($reading)) {
            log_message('error', 'Meter reading could not be stored.');
            $this->output->set_status_header(500)->set_output('Unable to store meter reading');
            return;
        }
        $this->output->set_output('OK, '.date('Y-m-d H:i:s'));
    }
}
