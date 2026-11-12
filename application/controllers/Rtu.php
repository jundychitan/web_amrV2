<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rtu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('rtu_model');
    }

    public function rtu_check_update($mac = '', $action = '')
    {
        $statusRequest = in_array($action, array('get_update_csv', 'get_update_location'), TRUE);
        $actions = array('get_update_csv', 'get_content_csv', 'reset_update_csv',
            'get_update_location', 'get_content_location', 'reset_update_location');
        $this->output->set_content_type('text/plain', 'utf-8');

        if ($this->input->method() !== 'get' || !preg_match('/\A(?:[0-9a-f]{2}:){5}[0-9a-f]{2}\z/i', $mac)
            || !in_array($action, $actions, TRUE)) {
            $this->output->set_status_header(400)->set_output('Invalid request');
            return;
        }

        try {
            $gateway = $this->rtu_model->gateway($mac);
            if ($gateway === NULL) {
                if ($statusRequest) {
                    $this->output->set_output('0');
                } else {
                    $this->output->set_status_header(404)->set_output('Gateway not found');
                }
                return;
            }

            switch ($action) {
                case 'get_update_csv':
                    if ((int) $gateway['update_rtu'] === 1) {
                        $this->csvContent($gateway['id']); // Check that content is available first.
                        $this->output->set_output('1');
                    } else {
                        $this->output->set_output('0');
                    }
                    break;
                case 'get_content_csv':
                    $this->content($this->csvContent($gateway['id']));
                    break;
                case 'reset_update_csv':
                    $this->rtu_model->reset($gateway['id'], 'update_rtu');
                    break;
                case 'get_update_location':
                    if ((int) $gateway['update_rtu_location'] === 1) {
                        $this->locationContent($gateway);
                        $this->output->set_output('1');
                    } else {
                        $this->output->set_output('0');
                    }
                    break;
                case 'get_content_location':
                    $this->content($this->locationContent($gateway));
                    break;
                case 'reset_update_location':
                    $this->rtu_model->reset($gateway['id'], 'update_rtu_location');
                    break;
            }
        } catch (Throwable $e) {
            log_message('error', 'RTU update request failed: '.$e->getMessage());
            if ($statusRequest) {
                $this->output->set_output('0');
            } else {
                $this->output->set_status_header(503)->set_output('Update unavailable');
            }
        }
    }

    private function csvContent($gatewayId)
    {
        $content = '';
        foreach ($this->rtu_model->meters($gatewayId) as $meter) {
            $fields = array($meter['meter_name'], $meter['meter_config_file'], $meter['meter_default_name']);
            foreach ($fields as $field) {
                if (preg_match('/[,\r\n]/', (string) $field)) {
                    throw new RuntimeException('Invalid meter configuration field');
                }
            }
            $content .= implode(',', $fields)."\n";
        }
        return $content;
    }

    private function locationContent($gateway)
    {
        $location = (string) $gateway['rtu_site_name'];
        if (preg_match('/["\\\\\r\n\x00$`]/', $location)) {
            throw new RuntimeException('Invalid gateway location');
        }
        return 'location = "'.$location."\"\n";
    }

    private function content($body)
    {
        $this->output->set_header('Content-Length: '.strlen($body))->set_output($body);
    }
}
