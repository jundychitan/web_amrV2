<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sub_group_datatable_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	private function get_datatables($siteid)
	{
		
		$query = $this->db->get_where('meter_site_sub_group', array('site_id' => $siteid));
		return $query->result_array();

	}

}
