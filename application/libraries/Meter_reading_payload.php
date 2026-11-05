<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Decode the gateway's form fields without silently accepting malformed readings. */
class Meter_reading_payload {
    public function parse(array $post) {
        foreach (array('save_to_meter_data', 'location', 'meter_id', 'datetime') as $key) {
            if (!isset($post[$key]) || !is_scalar($post[$key]) || trim((string) $post[$key]) === '') {
                throw new InvalidArgumentException('Missing required field: '.$key);
            }
        }
        $mode = (string) $post['save_to_meter_data'];
        if ($mode !== '0' && $mode !== '1') {
            throw new InvalidArgumentException('Invalid save_to_meter_data.');
        }
        $location = trim((string) $post['location']);
        $meter = trim((string) $post['meter_id']);
        if (strlen($location) > 30 || strlen($meter) > 30) {
            throw new InvalidArgumentException('Location or meter ID is too long.');
        }
        $datetime = str_replace('%20', ' ', (string) $post['datetime']);
        if (!$this->valid_date($datetime)) {
            throw new InvalidArgumentException('Invalid reading datetime.');
        }
        $data = array('location' => $location, 'meter_id' => $meter, 'datetime' => $datetime);
        $fields = array('vrms_a','vrms_b','vrms_c','irms_a','irms_b','irms_c','freq','pf','watt','va','var',
            'wh_del','wh_rec','wh_net','wh_total','varh_neg','varh_pos','varh_net','varh_total','vah_total',
            'max_rec_kw_dmd','max_del_kw_dmd','max_pos_kvar_dmd','max_neg_kvar_dmd',
            'v_ph_angle_a','v_ph_angle_b','v_ph_angle_c','i_ph_angle_a','i_ph_angle_b','i_ph_angle_c');
        foreach ($fields as $key) {
            $alias = preg_replace('/^v_ph_angle_/', 'vpha_', $key);
            $alias = preg_replace('/^i_ph_angle_/', 'ipha_', $alias);
            $value = isset($post[$key]) ? $post[$key] : (isset($post[$alias]) ? $post[$alias] : 0);
            if (!is_scalar($value) || !is_numeric($value) || !is_finite((float) $value)) {
                throw new InvalidArgumentException('Invalid numeric field: '.$key);
            }
            $data[$key] = (float) $value;
        }
        foreach (array('max_rec_kw_dmd_time','max_del_kw_dmd_time','max_pos_kvar_dmd_time','max_neg_kvar_dmd_time') as $key) {
            $value = isset($post[$key]) && is_scalar($post[$key]) ? str_replace('%20', ' ', (string) $post[$key]) : '';
            // Existing gateways send short placeholders such as "20" when a demand time is unavailable.
            $data[$key] = $this->valid_date($value) ? $value : NULL;
        }
        foreach (array('mac_addr' => array('mac_address', 'gateway_mac'), 'soft_rev' => array('soft_rev')) as $column => $keys) {
            $value = '';
            foreach ($keys as $key) {
                if (isset($post[$key])) {
                    if (!is_scalar($post[$key])) throw new InvalidArgumentException('Invalid field: '.$key);
                    $value = (string) $post[$key];
                    break;
                }
            }
            $data[$column] = $value;
        }
        $relay = isset($post['relay_status']) ? $post['relay_status'] : 0;
        if (!is_scalar($relay) || !preg_match('/^-?\d+$/', (string) $relay)) {
            throw new InvalidArgumentException('Invalid relay_status.');
        }
        $data['relay_status'] = (int) $relay;
        return array('historical' => $mode === '1', 'data' => $data);
    }

    private function valid_date($value) {
        $date = DateTime::createFromFormat('!Y-m-d H:i:s', $value);
        return $date !== FALSE && $date->format('Y-m-d H:i:s') === $value;
    }
}
