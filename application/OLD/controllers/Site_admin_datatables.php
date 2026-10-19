<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Site_admin_datatables extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
			 	$this->load->model('Site_admin_model');
				$this->load->model('Site_site_admin_datatable_model');
				$this->load->model('Site_meter_model');
				$this->load->model('Meter_rtu_model');
				$this->load->model('Meter_all_rtu_model');
				$this->load->model('Meter_model_list_model');
	            $this->load->helper('url_helper');
				$this->load->library('session');
				$this->load->helper('html');
				$this->load->helper('date');
        }
		
		/*DATATABLE SCRIPT*/

		public function site_list()
		{
		
			$user_type = 'site_admin';
		
			/* USE SESSION */
			/* RESET SEARCH */
			$_reset_search = $this->uri->segment(3);
			if($_reset_search == 1){
				unset($_SESSION['site_search_value']);
			}else{

			}
		
			$list = $this->Site_site_admin_datatable_model->get_datatables();
			$data = array();
			$no = $_POST['start'];
			$n = 1;
			foreach ($list as $cols) { 

				$row = array();
				$row[] = $n;
				$row[] = "<div title='Click To Edit' onClick='selected_site_id($cols->id)' style='font-weight:bold;'>" . $cols->site_code . "</div>";
				$row[] = "<div title='Click To Edit' onClick='selected_site_id($cols->id)' style='font-weight:bold;'>" . $cols->site_name . "</div>";
				$row[] = "<div title='Click To Edit' onClick='selected_site_id($cols->id)' style='font-weight:bold;'>" . $cols->site_cut_off . "</div>";
				$row[] = $cols->created_by;
				$row[] = $cols->date_created;
				$row[] = $cols->modified_by;
				$row[] = $cols->date_modified;
				$row[] = $cols->last_log_update;
				$row[] = "<center><a href=" . site_url("$user_type/rtu_management/" . $cols->id ) . " class='list-btn'>Gateway</a></center>";
				$row[] = "<center><a href=" . site_url("$user_type/meter_management/" . $cols->id ) . " class='list-btn'>Meters</a></center>";
				$data[] = $row;
				$n++;

			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Site_site_admin_datatable_model->count_all(),
							"recordsFiltered" => $this->Site_site_admin_datatable_model->count_filtered(),
							"data" => $data,
			);

			//output to json format
			echo json_encode($output);

		}	
				 
		public function group_meter_list()
		{
			
			$siteid = $this->uri->segment(3);			
			$_reset_search = $this->uri->segment(5);
			@$rtuid = $this->uri->segment(4);
			
			if($_reset_search == 1){

			unset($_SESSION['meter_search_value']);
			unset($_SESSION['meter_siteid_ses']);
			unset($_SESSION['meter_rtuid_ses']);

			}else{

			}

			if(@$_SESSION['meter_siteid_ses']!=0 && (@$_SESSION['meter_siteid_ses']!=$siteid) || @$_SESSION['meter_rtuid_ses']!=0 && (@$_SESSION['meter_rtuid_ses']!=$rtuid)){

			unset($_SESSION['meter_search_value']);

			}else{

			}
			
			//$siteid = $this->uri->segment(3);
			$list = $this->Site_meter_model->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) {
				$row = array();
				$meter_default_name = $cols->meter_default_name;
				
				if($no=1){$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}else{$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}
		 		if($cols->meter_name_addressable==1){ $meter_name_addressable = '<div align="center">YES</div>'; }else{ $meter_name_addressable = "<div align='center'>NO <div style='color:green; font-size:bold;display: inline-block;'>[ $meter_default_name ]</div></div>"; }
				
					
				@$customer_name = "$cols->customer_name"; 
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
							"recordsTotal" => $this->Site_meter_model->count_all(),
							"recordsFiltered" => $this->Site_meter_model->count_filtered(),
							"data" => $data,
					);
			/*output to json format*/
			echo json_encode($output);
		}
		
		public function rtu_list_per_site()
		{
			
			$siteid = $this->uri->segment(3);
			$_reset_search = $this->uri->segment(4);

			/* USE SESSION */
			/* RESET SEARCH */
			
			if($_reset_search == 1){

			unset($_SESSION['rtu_search_value']);

			}else{

			}

			if(@$_SESSION['rtu_siteid_ses']!=0 && (@$_SESSION['rtu_siteid_ses']!=$siteid)){
			
			unset($_SESSION['rtu_search_value']);
							
			}else{
			
			}			
			
			$list = $this->Meter_rtu_model->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) { 
				
				$row = array();
				$info_hover = "Click to Edit\nDate Created : $cols->created_by\nCreated By : $cols->date_created\nModified By : $cols->modified_by\nDate Modified : $cols->date_modified\n";	
				if($cols->update_rtu == '1' ){ $update_rtu = "<div onClick='selected_rtu_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu = "<div onClick='selected_rtu_enable_update($cols->id)' style='color:red; text-align:center; cursor: pointer;'><b>OFF</b></div>"; }
				if($cols->update_rtu_location == '1' ){ $update_rtu_location = "<div onClick='selected_rtu_location_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_location = "<div onClick='selected_rtu_location_enable_update($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }


				$row[] = $m;
				$row[] = $cols->rtu_physical_location; 
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->rtu_sn_number . "</div>";
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->mac_addr . "</div>"; 
				$row[] = $cols->phone_no_or_ip_address; 
				$row[] = $cols->connection_type;
				$row[] = $cols->last_log_update;
				$row[] = $cols->soft_rev;
				$row[] = $update_rtu; 
				$row[] = $update_rtu_location;
				$row[] = "<center><a href=" . site_url("site_admin/meter_management/$siteid/" . $cols->id ) . " class='list-btn'>Meters</a></center>";
				$data[] = $row;
				$m++;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Meter_rtu_model->count_all(),
							"recordsFiltered" => $this->Meter_rtu_model->count_filtered(),
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}		

		public function rtu_list()
		{
	
			$_reset_search = $this->uri->segment(3);

			/* USE SESSION */
			/* RESET SEARCH */
			
			if($_reset_search == 1){

			unset($_SESSION['all_rtu_search_value']);

			}else{

			}
			
			$list = $this->Meter_all_rtu_model->get_datatables();
			$data = array();
			$no = $_POST['start'];
			$m = 1;

			foreach ($list as $cols) { 
				
				$row = array();

				$info_hover = "Click to Edit\nDate Created : $cols->created_by\nCreated By : $cols->date_created\nModified By : $cols->modified_by\nDate Modified : $cols->date_modified\n";	

				if($cols->update_rtu == '1' ){ $update_rtu = "<div onClick='selected_rtu_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu = "<div onClick='selected_rtu_enable_update($cols->id)' style='color:red; text-align:center; cursor: pointer;'><b>OFF</b></div>"; }
				if($cols->update_rtu_location == '1' ){ $update_rtu_location = "<div onClick='selected_rtu_location_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_location = "<div onClick='selected_rtu_location_enable_update($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }
				$row[] = $m;
				$row[] = $cols->rtu_physical_location;
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->rtu_sn_number . "</div>";
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->mac_addr . "</div>"; 
				$row[] = $cols->rtu_site_name;
				$row[] = $cols->connection_type;
				$row[] = $cols->phone_no_or_ip_address; 
				$row[] = $cols->soft_rev;
				$row[] = $cols->last_log_update;
				$row[] = $update_rtu; 
				$row[] = $update_rtu_location;
				$row[] = "<center><a href=" . site_url("site_admin/meter_management/$cols->rtu_site_id/$cols->id" ) . " class='list-btn'>Meters</a></center>";
				$data[] = $row;
				$m++;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Meter_all_rtu_model->count_all(),
							"recordsFiltered" => $this->Meter_all_rtu_model->count_filtered(),
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}	

		public function meter_model_list()
		{
			$siteid = $this->uri->segment(3);
			$list = $this->Meter_model_list_model->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) {
				
				$row = array();
								
				$row[] = "<input type='radio' onClick='selected_meter_model($cols->id)' name='select_rtu'>";
				$row[] = $m;
				$row[] = $cols->meter_model;
				$row[] = $cols->config_file;
				$row[] = $cols->created_by;
				$row[] = $cols->date_created;
				$row[] = $cols->modified_by;
				$row[] = $cols->date_modified;
				$data[] = $row;
				$m++;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Meter_model_list_model->count_all(),
							"recordsFiltered" => $this->Meter_model_list_model->count_filtered(),
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}		

}