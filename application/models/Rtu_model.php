<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rtu_model extends CI_Model
{
    public function gateway($mac)
    {
        $query = $this->db->select('id, update_rtu, update_rtu_location, rtu_site_name')
            ->where('mac_addr', $mac)->limit(2)->get('meter_rtu');
        if ($query === FALSE) {
            throw new RuntimeException('Gateway lookup failed');
        }
        $rows = $query->result_array();
        // Never act on an ambiguous gateway identity.
        return count($rows) === 1 ? $rows[0] : NULL;
    }

    public function meters($gatewayId)
    {
        $query = $this->db->select('meter_name, meter_config_file, meter_default_name')
            ->where('rtu_sn_number_id', $gatewayId)->order_by('id', 'ASC')->get('meter_details');
        if ($query === FALSE) {
            throw new RuntimeException('Meter lookup failed');
        }
        return $query->result_array();
    }

    public function reset($gatewayId, $field)
    {
        if (!in_array($field, array('update_rtu', 'update_rtu_location'), TRUE)) {
            throw new InvalidArgumentException('Invalid update flag');
        }
        if (!$this->db->where('id', $gatewayId)->update('meter_rtu', array($field => 0))) {
            throw new RuntimeException('Gateway flag reset failed');
        }
    }
}
