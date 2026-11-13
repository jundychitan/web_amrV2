<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meter_enrollment_model extends CI_Model {
    private function rows($query) {
        if ($query === FALSE) {
            throw new RuntimeException('Enrollment storage unavailable.');
        }
        return $query->result_array();
    }

    public function import(array $records, $site_id, array $actor, $dry_run) {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $started = FALSE;
        try {
            if (!$this->db->trans_begin()) {
                throw new RuntimeException('Enrollment transaction unavailable.');
            }
            $started = TRUE;
            // A site row is the shared lock for concurrent imports to this site.
            $sites = $this->rows($this->db->query(
                'SELECT * FROM meter_site WHERE id = ? FOR UPDATE', array($site_id)
            ));
            if (!$sites) {
                throw new InvalidArgumentException('The selected site does not exist.');
            }
            $site = $sites[0];
            $report = array('dry_run' => (bool) $dry_run, 'total' => count($records),
                'inserted' => 0, 'would_insert' => 0, 'skipped' => 0, 'invalid' => 0,
                'unmatched_gateways' => 0, 'rows' => array());
            $pending = array();
            $seen = array();
            foreach ($records as $record) {
                $item = array('sheet' => $record['sheet'], 'row' => $record['row'],
                    'meter_name' => $record['meter_name'], 'status' => 'ready',
                    'warnings' => array(), 'errors' => array());
                if ($record['meter_name'] === '' || mb_strlen($record['meter_name'], 'UTF-8') > 70) {
                    $item['errors'][] = 'Meter Serial No is required and must not exceed 70 characters.';
                }
                foreach (array('meter_name', 'gateway_serial', 'physical_location', 'customer_name',
                    'meter_type', 'meter_config_file', 'meter_default_name') as $field) {
                    // Legacy meter screens render these values without HTML escaping.
                    if (preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F]/', $record[$field])) {
                        $item['errors'][] = 'Unsupported markup or control characters in '.$field.'.';
                    }
                    if (strlen($record[$field]) > 16000) {
                        $item['errors'][] = 'Value too long in '.$field.'.';
                    }
                }
                if (!is_numeric($record['meter_multiplier'])
                    || !is_finite((float) $record['meter_multiplier'])
                    || (float) $record['meter_multiplier'] <= 0) {
                    $item['errors'][] = 'Meter Multiplier must be a finite positive number.';
                }
                $gateway_id = 0;
                $gateway_serial = '';
                $gateways = $record['gateway_serial'] === '' ? array() : $this->rows(
                    $this->db->get_where('meter_rtu', array('rtu_sn_number' => $record['gateway_serial']))
                );
                if (count($gateways) > 1) {
                    $item['errors'][] = 'Gateway serial matches multiple gateways.';
                } elseif ($gateways && (int) $gateways[0]['rtu_site_id'] !== (int) $site_id) {
                    $item['errors'][] = 'Gateway belongs to a different site.';
                } elseif ($gateways) {
                    $gateway_id = (int) $gateways[0]['id'];
                    $gateway_serial = $gateways[0]['rtu_sn_number'];
                } else {
                    $item['warnings'][] = 'Gateway not found; rtu_sn_number_id will be 0.';
                    $report['unmatched_gateways']++;
                }
                $item['gateway_serial'] = $record['gateway_serial'];
                $item['rtu_sn_number_id'] = $gateway_id;
                $model_id = 0;
                $model_name = '';
                $models = $record['meter_config_file'] === '' ? array() : $this->rows(
                    $this->db->get_where('meter_model_config', array('config_file' => $record['meter_config_file']))
                );
                if (count($models) > 1) {
                    $item['errors'][] = 'Config File matches multiple meter models.';
                } elseif ($models) {
                    $model_id = (int) $models[0]['id'];
                    $model_name = $models[0]['meter_model'];
                } else {
                    $item['warnings'][] = $record['meter_config_file'] === ''
                        ? 'Config File is blank; meter model will remain unassigned.'
                        : 'Config File has no matching meter model; model ID will be 0.';
                }
                if ($item['errors']) {
                    $item['status'] = 'invalid';
                    $report['invalid']++;
                } else {
                    $key = mb_strtolower($record['meter_name'], 'UTF-8');
                    $existing = $this->rows($this->db->get_where('meter_details',
                        array('meter_site_id' => $site_id, 'meter_name' => $record['meter_name'])));
                    if (isset($seen[$key]) || $existing) {
                        $item['status'] = 'skipped';
                        $item['warnings'][] = 'Meter serial already exists in this site or workbook.';
                        $report['skipped']++;
                    } else {
                        $seen[$key] = TRUE;
                        $pending[] = array(count($report['rows']), $this->enrollment(
                            $record, $site, $actor, $gateway_id, $gateway_serial, $model_id, $model_name
                        ));
                    }
                }
                $report['rows'][] = $item;
            }
            $report['would_insert'] = count($pending);
            if ($report['invalid'] || $dry_run) {
                $this->db->trans_rollback();
                $started = FALSE;
                return $report;
            }
            foreach ($pending as $entry) {
                if (!$this->db->insert('meter_details', $entry[1])) {
                    throw new RuntimeException('Unable to insert enrolled meters.');
                }
                $report['rows'][$entry[0]]['status'] = 'inserted';
                $report['inserted']++;
            }
            if ($this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
                throw new RuntimeException('Unable to commit enrolled meters.');
            }
            $started = FALSE;
            return $report;
        } finally {
            if ($started) {
                $this->db->trans_rollback();
            }
            $this->db->db_debug = $debug;
        }
    }

    private function enrollment($record, $site, $actor, $gateway_id, $gateway_serial, $model_id, $model_name) {
        // Explicit values for every NOT NULL/no-default field in the verified schema.
        $data = array_fill_keys(array(
            'soft_rev', 'meter_list_src', 'building', 'building_description', 'land',
            'land_description', 'rental_object', 'rental_object_name', 'usage_type',
            'ro_valid_from', 'ro_valid_to', 'contract_number', 'measuring_point',
            'measuring_point_description', 'measurement_sequence', 'measurement_separator',
            'meter_status', 'participation_group', 'creation_date', 'creation_by',
            'last_change_on', 'last_change_by'
        ), '');
        foreach (array('meter_name', 'physical_location', 'customer_name', 'meter_type',
            'meter_config_file', 'meter_default_name', 'meter_name_addressable') as $field) {
            $data[$field] = $record[$field];
        }
        $now = date('Y-m-d H:i:s');
        return array_merge($data, array(
            'meter_multiplier' => (float) $record['meter_multiplier'],
            'meter_site_id' => (int) $site['id'], 'meter_site_name' => $site['site_code'],
            'company_no' => (string) $site['company_no'], 'business_entity' => $site['business_entity'],
            'rtu_sn_number_id' => $gateway_id, 'rtu_sn_number' => $gateway_serial,
            'meter_model_id' => $model_id, 'meter_model' => $model_name,
            'created_by' => $actor['user_name'], 'modified_by' => $actor['user_name'],
            'added_by' => (int) $actor['user_id'], 'date_created' => $now, 'date_modified' => $now,
            // No reading exists yet. Use a valid sentinel rather than an invalid zero-date.
            'last_log_update' => '1970-01-01 00:00:00', 'last_wh_total' => 0
        ));
    }
}
