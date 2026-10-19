<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ba_datatable_model extends CI_Model {

	var $table = 'meter_site_user'; 
	//set column field database for datatable orderable
	var $column_order = array(null,'company_no','business_entity','site_code','site_name','building_type','site_cut_off','settlement_valid_from','settlement_valid_to','last_log_update',null,null); 
	//set column field database for datatable searchable
	var $column_search = array('site_code','company_no','site_name','site_cut_off','business_entity');  
	// default order
	var $order = array('last_log_update' => 'ASC');   

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
	}

	private function _get_datatables_query() 
	{
		$user_id = $_SESSION['user_id_sap'];
		$user_type = $_SESSION['currently_logged_in_as'];
		$user_access = $_SESSION['user_access'];
		
			$this->db->from($this->table);
			$this->db->where('user_id', $user_id);
		
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

}
