<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Re_rtu_model extends CI_Model {
	var $table = 'meter_rtu';
	var $column_order = array(null,'rtu_sn_number', 'phone_no_or_ip_address', 'mac_addr', 'rtu_physical_location','idf_number','switch_name','idf_port', 'gateway_description','soft_rev','last_log_update'); //set column field database for datatable orderable
	var $column_search = array('rtu_sn_number', 'mac_addr', 'phone_no_or_ip_address', 'rtu_physical_location', 'gateway_description','soft_rev','last_log_update'); //set column field database for datatable searchable 
	var $order = array('last_log_update' => 'asc'); // default order 

	public function __construct()
	{ 
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
	}
	 
	private function _get_datatables_query()
	{

		$siteid = $this->uri->segment(3);
		$user_id = $_SESSION['user_id'];
		$user_type = $_SESSION['currently_logged_in_as'];
		
		/*SET IN SESSION*/
		/*REMEMBER LAST SEARCH*/
		$reset_search = $this->uri->segment(4);

		if(@$_POST['search']['value'] != ''){

				$data = array(  
                'rtu_search_value' => @$_POST['search']['value'] 
                );   

                $this->session->set_userdata($data); 				
		}		
		
		$this->db->from($this->table);
		$this->db->where('rtu_site_id', $siteid);
		
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(@$_POST['search']['value'] || @$_SESSION['rtu_search_value']!='' || @$_SESSION['rtu_search_value']!=NULL) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, @$_SESSION['rtu_search_value']);
				}
				else
				{

					$this->db->or_like($item, @$_SESSION['rtu_search_value']);
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
