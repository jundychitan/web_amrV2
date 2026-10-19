<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Officer_datatables extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
			 	$this->load->model('Officer_model');
				$this->load->model('Meter_site_admin_rtu_model');
				$this->load->model('site_site_admin_datatable_model');
				$this->load->model('Officer_meter_model');
				//$this->load->model('sub_group_datatable_model');
	            $this->load->helper('url_helper');
				$this->load->library('session');
				$this->load->helper('html');
				$this->load->helper('date');
        }
		 
		//DATATABLE SCRIPT
		public function site_list()
		{
		//$user_id = $_SESSION['user_id_sap'];
		//if($this->session->userdata('currently_logged_in_as')=='Site'){
			$user_type = 'officer';	
		//}
		//else{
		//	$user_type = 'admin';
		//}
			$list = $this->site_site_admin_datatable_model->get_datatables();
			$data = array();
			$no = $_POST['start'];
			$n = 1;
			foreach ($list as $cols) {
				$row = array();
				$row[] = $n;
				$row[] = $cols->site_code;
				$row[] = $cols->site_name;
				$row[] = $cols->site_cut_off;
				$row[] = $cols->created_by;
				$row[] = $cols->date_created;
				$row[] = $cols->modified_by;
				$row[] = $cols->date_modified;
				$row[] = $cols->last_log_update; 
				$row[] = "<center><a href=" . site_url("$user_type/rtu_management/" . $cols->id ) . " class='list-btn'>Gateway</a></center>";
				$row[] = "<center><a href=" . site_url("$user_type/meter_management/" . $cols->id ) . " class='list-btn'>Meter</a></center>";
				$data[] = $row;
				$n++;
			}
	 
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->site_site_admin_datatable_model->count_all(),
							"recordsFiltered" => $this->site_site_admin_datatable_model->count_filtered(),
							"data" => $data,
			);
			/*output to json format*/
			echo json_encode($output);
		}	
		
		public function group_meter_list()
		{
			$siteid = $this->uri->segment(3);
			$list = $this->Officer_meter_model->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) {
				$row = array();
				$meter_default_name = $cols->meter_default_name;
	
		 		if($cols->meter_name_addressable==1){ $meter_name_addressable = '<div align="center">YES</div>'; }else{ $meter_name_addressable = "<div align='center'>NO <div style='color:green; font-weight:bold;display: inline-block;'>[ $meter_default_name ]</div></div>"; }
				
				
				@$customer_name = "$cols->customer_name"; 
				$row[] = '';
				$row[] = $m;
				$row[] = $cols->measuring_point;
				$row[] = $cols->meter_name;
				$row[] = $customer_name;
				$row[] = $cols->rtu_sn_number;
				$row[] = $meter_name_addressable;
				$row[] = $cols->meter_model;
				$row[] = $cols->meter_type;
				$row[] = $cols->meter_multiplier;
				$row[] = $cols->meter_role;
				$row[] = $cols->meter_status;
				$row[] = $cols->last_log_update;
				$data[] = $row;
				$m++;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Officer_meter_model->count_all(),
							"recordsFiltered" => $this->Officer_meter_model->count_filtered(),
							"data" => $data,
					);
			/*output to json format*/
			echo json_encode($output);
		}

		public function rtu_list()
		{
			$siteid = $this->uri->segment(3);
			$list = $this->Meter_site_admin_rtu_model->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) {

				$row = array(); 
				$row[] = $m;
				$row[] = $cols->rtu_physical_location;
				$row[] = $cols->rtu_sn_number;
				$row[] = $cols->connection_type;
				$row[] = $cols->phone_no_or_ip_address;
				$row[] = $cols->mac_addr;
				$row[] = $cols->created_by;
				$row[] = $cols->date_created;
				$row[] = $cols->modified_by;
				$row[] = $cols->date_modified;
				$row[] = $cols->last_log_update;
				$row[] = $cols->soft_rev;
				$row[] = "<center><a href=" . site_url("officer/meter_management/$siteid/" . $cols->id ) . " class='list-btn'>Meter</a></center>";
				$data[] = $row;
				$m++;
			}
	 
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Meter_site_admin_rtu_model->count_all(),
							"recordsFiltered" => $this->Meter_site_admin_rtu_model->count_filtered(),
							"data" => $data,
					);
			/*output to json format*/
			echo json_encode($output);
		}		
}
