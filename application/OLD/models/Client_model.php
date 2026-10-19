<?php
	class Client_model extends CI_Model {
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}

	public function get_meter_info($meter_id){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_id));
	return $query->row_array();
	}

	public function get_client_meter_list(){
	$user_id = $_SESSION['user_id'];
	$query = $this->db->get_where('user_client_meter_access', array('user_id' => $user_id));
	return $query->result_array();
	}
}