<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gateway_enrollment_model extends CI_Model {
    private function rows($query) {
        if ($query === FALSE) { throw new RuntimeException('Gateway enrollment storage unavailable.'); }
        return $query->result_array();
    }

    public function import(array $records, $site_id, array $actor, $dry_run) {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $started = FALSE;
        try {
            if (!$this->db->trans_begin()) { throw new RuntimeException('Transaction unavailable.'); }
            $started = TRUE;
            // Lock sites in a stable order: gateway serial/MAC identity is global across sites.
            $sites = $this->rows($this->db->query('SELECT * FROM meter_site ORDER BY id FOR UPDATE'));
            $site = NULL;
            foreach ($sites as $candidate) {
                if ((int) $candidate['id'] === (int) $site_id) { $site = $candidate; break; }
            }
            if ($site === NULL) { throw new InvalidArgumentException('The selected site does not exist.'); }
            $report = array('dry_run' => (bool) $dry_run, 'total' => count($records),
                'inserted' => 0, 'would_insert' => 0, 'skipped' => 0, 'invalid' => 0, 'rows' => array());
            $pending = $serials = $macs = array();
            foreach ($records as $record) {
                $record['mac_addr'] = strtolower(str_replace('-', ':', $record['mac_addr']));
                $item = array_merge($record, array('status' => 'ready', 'warnings' => array(), 'errors' => array()));
                foreach (array('rtu_sn_number', 'rtu_physical_location', 'phone_no_or_ip_address') as $field) {
                    if ($record[$field] === '') { $item['errors'][] = $field.' is required.'; }
                }
                foreach (array('rtu_sn_number', 'mac_addr', 'rtu_physical_location', 'phone_no_or_ip_address',
                    'idf_number', 'switch_name', 'idf_port') as $field) {
                    if (strlen($record[$field]) > 16000
                        || preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F]/', $record[$field])) {
                        $item['errors'][] = 'Unsupported markup, control characters or length in '.$field.'.';
                    }
                }
                if (!preg_match('/^(?:[0-9a-f]{2}:){5}[0-9a-f]{2}$/D', $record['mac_addr'])) {
                    $item['errors'][] = 'Gateway mac address must contain six hexadecimal octets.';
                }
                if (!filter_var($record['phone_no_or_ip_address'], FILTER_VALIDATE_IP)) {
                    $item['errors'][] = 'Gateway IP address must be a valid IP address.';
                }
                foreach (array('idf_number', 'switch_name', 'idf_port') as $optional) {
                    if ($record[$optional] === '') { $item['warnings'][] = $optional.' is blank.'; }
                }
                $key = mb_strtolower($record['rtu_sn_number'], 'UTF-8');
                $existing = $this->rows($this->db->get_where('meter_rtu',
                    array('rtu_sn_number' => $record['rtu_sn_number'])));
                $same_mac = $this->rows($this->db->query(
                    "SELECT * FROM meter_rtu WHERE LOWER(REPLACE(mac_addr, '-', ':')) = ?",
                    array($record['mac_addr'])
                ));
                $skip = FALSE;
                if (count($existing) > 1) {
                    $item['errors'][] = 'Gateway serial matches multiple existing records.';
                } elseif ($existing) {
                    $stored_mac = strtolower(str_replace('-', ':', $existing[0]['mac_addr']));
                    if ((int) $existing[0]['rtu_site_id'] !== (int) $site_id || $stored_mac !== $record['mac_addr']) {
                        $item['errors'][] = 'Gateway serial already belongs to a different site or MAC address.';
                    } else {
                        $skip = TRUE;
                        $item['warnings'][] = 'Gateway already exists; existing settings will not be overwritten.';
                    }
                }
                foreach ($same_mac as $match) {
                    if (!$existing || (int) $match['id'] !== (int) $existing[0]['id']) {
                        $item['errors'][] = 'MAC address is already assigned to another gateway.';
                    }
                }
                if (isset($serials[$key])) {
                    $copy = $record;
                    unset($copy['sheet'], $copy['row']);
                    if ($serials[$key] !== $copy) {
                        $item['errors'][] = 'Workbook repeats a gateway serial with conflicting details.';
                    } else {
                        $skip = TRUE;
                        $item['warnings'][] = 'Duplicate gateway in workbook; skipped.';
                    }
                }
                if (isset($macs[$record['mac_addr']]) && $macs[$record['mac_addr']] !== $key) {
                    $item['errors'][] = 'Workbook assigns the same MAC address to multiple gateway serials.';
                }
                if ($item['errors']) {
                    $item['status'] = 'invalid';
                    $report['invalid']++;
                } elseif ($skip) {
                    $item['status'] = 'skipped';
                    $report['skipped']++;
                } else {
                    $copy = $record;
                    unset($copy['sheet'], $copy['row']);
                    $serials[$key] = $copy;
                    $macs[$record['mac_addr']] = $key;
                    $data = array_merge($copy, array(
                        'connection_type' => 'LAN', 'ip_netmask' => 'n/a', 'ip_gateway' => 'n/a',
                        'rtu_server_ip' => 'n/a', 'gateway_description' => $record['rtu_physical_location'],
                        'rtu_site_id' => (int) $site['id'], 'rtu_site_name' => $site['site_code'],
                        'update_rtu' => 0, 'update_rtu_location' => 0, 'update_rtu_ssh' => 0,
                        'update_rtu_force_lp' => 0, 'soft_rev' => '',
                        'created_by' => $actor['user_name'], 'modified_by' => $actor['user_name'],
                        'added_by' => (int) $actor['user_id'],
                        'date_created' => date('Y-m-d H:i:s'), 'date_modified' => date('Y-m-d H:i:s'),
                        'last_log_update' => '1970-01-01 00:00:00'
                    ));
                    $pending[] = array(count($report['rows']), $data);
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
                if (!$this->db->insert('meter_rtu', $entry[1])) { throw new RuntimeException('Gateway insert failed.'); }
                $report['rows'][$entry[0]]['status'] = 'inserted';
                $report['inserted']++;
            }
            if ($this->db->trans_status() === FALSE || !$this->db->trans_commit()) {
                throw new RuntimeException('Gateway commit failed.');
            }
            $started = FALSE;
            return $report;
        } finally {
            if ($started) { $this->db->trans_rollback(); }
            $this->db->db_debug = $debug;
        }
    }
}
