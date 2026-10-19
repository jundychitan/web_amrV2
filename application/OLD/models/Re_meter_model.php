<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Re_meter_model extends CI_Model {
 
	var $table = 'meter_details';
	var $column_order = array(null,'measuring_point', 'meter_name','customer_name','rtu_sn_number','meter_role','meter_status','last_log_update','creation_date','creation_by','last_change_on','last_change_by'); //set column field database for datatable orderable
	var $column_search = array('measuring_point','meter_name', 'customer_name', 'meter_status','date_created','rtu_sn_number','meter_model','meter_type','meter_multiplier','meter_role','creation_date','creation_by','last_change_on','last_change_by'); //set column field database for datatable searchable 
	var $order = array('last_log_update' => 'desc'); // default order 

	public function __construct()
	{
		parent::__construct(); 
		$this->load->database();
		$this->load->library('session');
	}
	
 
	private function _get_datatables_query()
	{

		$siteid = $this->uri->segment(3);
		@$rtuid = $this->uri->segment(4);
		
		$user_id = $_SESSION['user_id'];
		$user_type = $_SESSION['currently_logged_in_as'];
		
		/*SET IN SESSION*/
		/*REMEMBER LAST SEARCH*/
		$reset_search = $this->uri->segment(5);
		
		if(@$_POST['search']['value'] != ''){

				$data = array(  
                'meter_search_value' => @$_POST['search']['value']
                );   

                $this->session->set_userdata($data); 				
		
		}

		/* USE SESSION */
		$data2 = array(  
			 'meter_siteid_ses' => @$siteid,
			 'meter_rtuid_ses' => @$rtuid
		);   

		$this->session->set_userdata($data2); 						
		
		$query_site_code = $this->db->get_where('meter_site', array('id' => $siteid));
		$r = $query_site_code->row_array();		
		$_site_id = $r['site_code'];
		
		if($user_type=='Admin'){
		 
		$this->db->from($this->table);
		$this->db->where('meter_site_name', $_site_id);
		if(@$rtuid!=0){
		$this->db->where('rtu_sn_number_id', @$rtuid );
		}
		
		}
		else{ 
		 
		$this->db->from($this->table);
		$this->db->where('meter_site_name', $_site_id);
		if(@$rtuid!=0){
		$this->db->where('rtu_sn_number_id', @$rtuid );
		}

		}
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(@$_POST['search']['value'] || @$_SESSION['meter_search_value']!='' || @$_SESSION['meter_search_value']!=NULL) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
					$this->db->like($item, @$_SESSION['meter_search_value']);
				}
				else
				{
					$this->db->or_like($item, @$_SESSION['meter_search_value']);
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
		if(@$_POST['length'] != -1)
		$this->db->limit(@$_POST['length'], @$_POST['start']);
		
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
