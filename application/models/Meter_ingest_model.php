<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meter_ingest_model extends CI_Model {
    public function store(array $reading) {
        $data = $reading['data'];
        $this->db->trans_begin();
        if ($reading['historical']) {
            $ok = $this->db->insert('meter_data', $data);
            if ($ok && $data['mac_addr'] !== '') {
                $ok = $this->db->where('rtu_site_name', $data['location'])
                    ->where('mac_addr', $data['mac_addr'])
                    ->update('meter_rtu', array('last_log_update' => $data['datetime'], 'soft_rev' => $data['soft_rev']));
            }
            if ($ok) {
                $ok = $this->db->where('meter_site_name', $data['location'])->where('meter_name', $data['meter_id'])
                    ->update('meter_details', array('last_log_update' => $data['datetime']));
            }
        } else {
            $query = $this->db->where('meter_id', $data['meter_id'])->get('live_meter_data');
            $ok = $query !== FALSE;
            if ($ok && $query->num_rows() > 0) {
                $ok = $this->db->where('meter_id', $data['meter_id'])->update('live_meter_data', $data);
            } elseif ($ok) {
                $ok = $this->db->insert('live_meter_data', $data);
            }
        }
        if ($ok) {
            $ok = $this->db->where('site_code', $data['location'])
                ->update('meter_site', array('last_log_update' => $data['datetime']));
        }
        if (!$ok || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }
        return $this->db->trans_commit();
    }
}
