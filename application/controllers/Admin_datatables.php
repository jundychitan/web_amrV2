<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set('Asia/Manila');
class Admin_datatables extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
			 	$this->load->model('Admin_model');
				$this->load->model('user_datatable_model');
				$this->load->model('site_datatable_model');
				$this->load->model('client_datatable_model');
				$this->load->model('Site_meter_model');
				$this->load->model('Site_meter_model_unassigned');
				$this->load->model('Meter_rtu_model');
				$this->load->model('Meter_all_rtu_model');
				$this->load->model('Meter_model_list_model');
	            $this->load->helper('url_helper');
				$this->load->library('session');
				$this->load->helper('html');
				$this->load->helper('date');
        }
		
		/*DATATABLE SCRIPT*/
		//$list = $this->user_datatable_model->get_datatables();

		public function user_list()
		{
			$list = $this->user_datatable_model->get_datatables();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $cols) {
				$no++;
				$row = array();
				
					if($cols->user_list_src == 'SAP'){
						$add_user_access = "";
						$view_user_access = "<center style=' color:#03A9F4 !important; cursor:pointer;'><div onClick='user_group_access_view($cols->id)' class='glyphicon glyphicon-eye-open'></div></center>";	
						$edit_access = '';
					} 
					else {
						$add_user_access = "<center style=' color:#03A9F4 !important; cursor:pointer;'><div onClick='add_user_group_access($cols->id)' class='glyphicon glyphicon-plus'></div></center>";				
						$view_user_access = "<center style=' color:#03A9F4 !important; cursor:pointer;'><div onClick='user_group_access_view($cols->id)' class='glyphicon glyphicon-eye-open'></div></center>";	
						$edit_access = "<input type='radio' onClick='selected_id($cols->id)' name='select_user'>";
			
							}
							
						if($cols->user_type=='OM'){
							$user_type = 'Operation Analyst/Manager';
						}else if($cols->user_type=='BA'){
							$user_type = 'BAO/BAM';
						}else if($cols->user_type=='RE'){
							$user_type = 'Resident Engineer';
						}else{
							$user_type = 'Admin';
						}
 
				$row[] = $edit_access;
				$row[] = $no;
				$row[] = $cols->user_real_name;
				$row[] = $cols->user_name;
				$row[] = $user_type;
				$row[] = $cols->created_by;
				$row[] = $cols->date_created;
				$row[] = $cols->modified_by;
				$row[] = $cols->date_modified;
				$row[] = $add_user_access;
				$row[] = $view_user_access;
				$data[] = $row;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->user_datatable_model->count_all(),
							"recordsFiltered" => $this->user_datatable_model->count_filtered(),
							"data" => $data,
					);
			//output to json format
			echo json_encode($output);
		}

		public function site_list()
		{
		if($this->session->userdata('currently_logged_in_as')=='Client'){
			$user_type = 'client';	
		}
		else{
			$user_type = 'admin';
		}
		
			/* USE SESSION */
			/* RESET SEARCH */
			$_reset_search = $this->uri->segment(3);
			if($_reset_search == 1){
				unset($_SESSION['site_search_value']);
			}else{

			}
		
			$list = $this->site_datatable_model->get_datatables();
			$data = array();
			$no = $_POST['start'];
			$n = 1;
			foreach ($list as $cols) {

				if(($cols->last_log_update) != '' && ($cols->last_log_update) != '0000-00-00 00:00:00'){
				$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				}else{
				$last_log_update 	=  "0000/00/00 00:00";
				}

				if(($cols->settlement_valid_from) != '' && ($cols->settlement_valid_from) != '0000-00-00 00:00:00'){
				$settlement_valid_from 	=  (date('Y/m/d', strtotime($cols->settlement_valid_from)));
				}else{
				$settlement_valid_from 	=  "0000/00/00";
				}
				
				if(($cols->settlement_valid_to) != '' && ($cols->settlement_valid_to) != '0000-00-00 00:00:00'){
				$settlement_valid_to 	=  (date('Y/m/d', strtotime($cols->settlement_valid_to)));
				}else{
				$settlement_valid_to 	=  "0000/00/00"; 
				}

						/* ONLINE / OFFLINE STATUS*/
						
						/*FROM LOGS*/
						$_date_format = strtotime($cols->last_log_update);
						$date_format = date('Y-m-d H:i:s',$_date_format);
						
						/*SERVER DATETIME*/
						$_server_time=date('Y-m-d H:i:s');
						$server_time_current = strtotime($_server_time);
						
						$date1=date_create("$_server_time");
						$date2=date_create("$date_format");
								
						$diff					= date_diff($date1,$date2);
						$days_last_active 		= $diff->format("%a");
						
						if($last_log_update == "0000/00/00 00:00"){
							$status = "<div style='color:black; font-weight:bold; text-align:center;' title='No Meter Connected on the Gateway/Spare'>No Data</div>";

						}
						else if($diff->format("%a")<=0){
							$status = "<div style='color:green; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>ONLINE</div>";

						}else{
							$status = "<div style='color:red; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>OFFLINE</div>";
						}			
		
				$row = array();
				if($no=1){$row[] = "<input type='radio' title='Click To Edit'  onClick='selected_site_id($cols->id)' name='select_site'>";}else{$row[] = "<input type='radio' onClick='selected_site_id($cols->id)' name='select_site'>";}
				$row[] = $n;
				$row[] = '<center>'. $cols->company_no .'</center>';
				$row[] = $cols->business_entity;
				$row[] = $cols->site_code;
				$row[] = $cols->site_name;
				$row[] = '<center>'. $cols->building_type .'</center>';
				$row[] = '<center>'. $cols->site_cut_off .'</center>';
				$row[] = '<center>'. $settlement_valid_from .'</center>';
				$row[] = '<center>'. $settlement_valid_to .'</center>';
				$row[] = "$status";
				$row[] = "<center><a href=" . site_url("$user_type/rtu_management/" . $cols->id ) . " class='list-btn'>Gateway</a></center>";
				$row[] = "<center><a href=" . site_url("$user_type/meter_management/" . $cols->id ) . " class='list-btn'>Meters</a></center>";
				$data[] = $row;
				$n++;

			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->site_datatable_model->count_all(),
							"recordsFiltered" => $this->site_datatable_model->count_filtered(),
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
				
				#if($no=1){$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}else{$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}
		 		#if($cols->meter_name_addressable==1){ $meter_name_addressable = '<div align="center">YES</div>'; }else{ $meter_name_addressable = "<div align='center'>NO <div style='color:green; font-size:bold;display: inline-block;'>[ $meter_default_name ]</div></div>"; }
				
				if(($cols->creation_date) != '' && ($cols->creation_date) != '00000000'){
				$creation_date 	=  (date('Y/m/d', strtotime($cols->creation_date)));
				}else{
				$creation_date 	=  "";
				}

				$CREATION_BY 	=  $cols->creation_by;

				if(($cols->last_change_on) != '' && ($cols->last_change_on) != '00000000'){
				$last_change_on 	=  (date('Y/m/d', strtotime($cols->last_change_on)));
				}else{
				$last_change_on 	=  "";
				}
				
				$last_change_by 	=  $cols->last_change_by;
				
				#0000-00-00 00:00:00
				if(($cols->last_log_update) != '' && ($cols->last_log_update) != '0000-00-00 00:00:00'){
				$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				}else{
				$last_log_update 	=  "0000/00/00 00:00";
				}
				
				
				#$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				
				@$customer_name = "$cols->customer_name"; 
				$row[] = $m;
				$row[] = $cols->measuring_point;				
				$row[] = $cols->meter_name;
				$row[] = $cols->customer_name;
				$row[] = $cols->rtu_sn_number;
				//$row[] = $meter_name_addressable;
				//$row[] = $cols->meter_model;
				//$row[] = $cols->meter_multiplier; 
				$row[] = $cols->meter_role;
				$row[] = $cols->meter_status;
				$row[] = "$last_log_update";
				//$creation_date 	=  (date('Y/m/d', strtotime($cols->creation_date)));
				$row[] = $creation_date;
				$row[] = $CREATION_BY;
				$row[] = $last_change_on;
				$row[] = $last_change_by;
				$row[] = "<center style=' color:#03A9F4 !important; cursor:pointer;'><div onClick='view_meter_info($cols->id)' name='view_meter' class='glyphicon glyphicon-eye-open'></div></center>";
				$row[] = "<center style=' color:#009688 !important; cursor:pointer;'><div onClick='selected_meter($cols->id)' name='select_meter' class='glyphicon glyphicon-edit'></div></center>";
				$row[] = "<center style=' color:#F44336 !important; cursor:pointer;'><div onClick='doconfirm($cols->id)' name='delete_meter' class='glyphicon glyphicon-trash'></div></center>";
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
		
		public function group_meter_list_unassigned()
		{
			
			$siteid = $this->uri->segment(3);			
			$_reset_search = $this->uri->segment(5);
			@$rtuid = $this->uri->segment(4);
			
			if($_reset_search == 1){

			unset($_SESSION['meter_search_value_un']);
			unset($_SESSION['meter_siteid_ses_un']);
			unset($_SESSION['meter_rtuid_ses_un']);

			}else{

			}

			if(@$_SESSION['meter_siteid_ses_un']!=0 && (@$_SESSION['meter_siteid_ses_un']!=$siteid) || @$_SESSION['meter_rtuid_ses_un']!=0 && (@$_SESSION['meter_rtuid_ses_un']!=$rtuid)){

			unset($_SESSION['meter_search_value_un']);

			}else{

			}
			
			//$siteid = $this->uri->segment(3);
			$list = $this->Site_meter_model_unassigned->get_datatables($siteid);
			$data = array();
			$no = $_POST['start'];
			$m = 1;
			foreach ($list as $cols) {
				$row = array();
				$meter_default_name = $cols->meter_default_name;
				
				#if($no=1){$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}else{$row[] = "<input type='radio' onClick='selected_meter($cols->id)' name='select_meter'>";}
		 		#if($cols->meter_name_addressable==1){ $meter_name_addressable = '<div align="center">YES</div>'; }else{ $meter_name_addressable = "<div align='center'>NO <div style='color:green; font-size:bold;display: inline-block;'>[ $meter_default_name ]</div></div>"; }
				
				if(($cols->creation_date) != '' && ($cols->creation_date) != '00000000'){
				$creation_date 	=  (date('Y/m/d', strtotime($cols->creation_date)));
				}else{
				$creation_date 	=  "";
				}

				$CREATION_BY 	=  $cols->creation_by;

				if(($cols->last_change_on) != '' && ($cols->last_change_on) != '00000000'){
				$last_change_on 	=  (date('Y/m/d', strtotime($cols->last_change_on)));
				}else{
				$last_change_on 	=  "";
				}
				
				$last_change_by 	=  $cols->last_change_by;
				
				#0000-00-00 00:00:00
				if(($cols->last_log_update) != '' && ($cols->last_log_update) != '0000-00-00 00:00:00'){
				$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				}else{
				$last_log_update 	=  "0000/00/00 00:00";
				}
				
				
				#$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				
				@$customer_name = "$cols->customer_name"; 
				$row[] = $m;
				$row[] = $cols->measuring_point;				
				$row[] = $cols->meter_name;
				$row[] = $cols->customer_name;
				$row[] = $cols->rtu_sn_number;
				//$row[] = $meter_name_addressable;
				//$row[] = $cols->meter_model;
				//$row[] = $cols->meter_multiplier; 
				$row[] = $cols->meter_role;
				$row[] = $cols->meter_status;
				$row[] = "$last_log_update";
				//$creation_date 	=  (date('Y/m/d', strtotime($cols->creation_date)));
				$row[] = $creation_date;
				$row[] = $CREATION_BY;
				$row[] = $last_change_on;
				$row[] = $last_change_by;
				$row[] = "<center style=' color:#03A9F4 !important; cursor:pointer;'><div onClick='view_meter_info($cols->id)' name='view_meter' class='glyphicon glyphicon-eye-open'></div></center>";
				$row[] = "<center style=' color:#009688 !important; cursor:pointer;'><div onClick='selected_meter($cols->id)' name='select_meter' class='glyphicon glyphicon-edit'></div></center>";
				$row[] = "<center style=' color:#F44336 !important; cursor:pointer;'><div onClick='doconfirm($cols->id)' name='delete_meter' class='glyphicon glyphicon-trash'></div></center>";
				$data[] = $row;
				$m++;
			}
	
			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Site_meter_model_unassigned->count_all(),
							"recordsFiltered" => $this->Site_meter_model_unassigned->count_filtered(),
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
				if($cols->update_rtu_ssh == '1' ){ $update_rtu_ssh = "<div onClick='rtu_remote_ssh_disable($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_ssh = "<div onClick='rtu_remote_ssh_enable($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }
				if($cols->update_rtu_force_lp == '1' ){ $update_rtu_force_lp = "<div onClick='rtu_force_load_profile_disable($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_force_lp = "<div onClick='rtu_force_load_profile_enable($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }

				#0000-00-00 00:00:00
				if(($cols->last_log_update) != '' && ($cols->last_log_update) != '0000-00-00 00:00:00'){
				$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				}else{
				$last_log_update 	=  "0000/00/00 00:00";
				}
				
						/* ONLINE / OFFLINE STATUS*/
						
						/*FROM LOGS*/
						$_date_format = strtotime($cols->last_log_update);
						$date_format = date('Y-m-d H:i:s',$_date_format);
						
						/*SERVER DATETIME*/
						$_server_time=date('Y-m-d H:i:s');
						$server_time_current = strtotime($_server_time);
						
						$date1=date_create("$_server_time");
						$date2=date_create("$date_format");
							$rrr =  "$_server_time $date_format";	
						$diff					= date_diff($date1,$date2);
						$days_last_active 		= $diff->format("%a");
						
						if($last_log_update == "0000/00/00 00:00"){
							$status = "<div style='color:black; font-weight:bold; text-align:center;' title='No Meter Connected on the Gateway/Spare'>No Data</div>";

						}
						else if($diff->format("%a")<=0){
							$status = "<div style='color:green; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>ONLINE</div>";

						}else{
							$status = "<div style='color:red; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>OFFLINE</div>";
						}			

				$row[] = "<input type='radio' onClick='selected_rtu($cols->id)' name='select_rtu'>";
				$row[] = $m;
				
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->rtu_sn_number . "</div>";
				$row[] = "<div title='Go to " . $cols->phone_no_or_ip_address . "' style='font-weight:bold; text-align:left;'>
				<a href='http://" . $cols->phone_no_or_ip_address . "' target='_blank'>" . $cols->phone_no_or_ip_address . "</a>
				</div>"; 
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->mac_addr . "</div>"; 
				$row[] = $cols->rtu_physical_location; 
				#$row[] = $cols->idf_number; 
				#$row[] = $cols->switch_name; 
				#$row[] = $cols->idf_port; 
				$row[] = $cols->gateway_description;
				$row[] = $cols->soft_rev;
				$row[] = $status;
				$row[] = $update_rtu; 
				$row[] = $update_rtu_location;
				$row[] = $update_rtu_ssh;
				$row[] = $update_rtu_force_lp;
				$row[] = "<center><a href=" . site_url("admin/meter_management/$siteid/" . $cols->id ) . " class='list-btn'>Meters</a></center>";
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
				if($cols->update_rtu_ssh == '1' ){ $update_rtu_ssh = "<div onClick='rtu_remote_ssh_disable($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_ssh = "<div onClick='rtu_remote_ssh_enable($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }
				if($cols->update_rtu_force_lp == '1' ){ $update_rtu_force_lp = "<div onClick='rtu_force_load_profile_disable($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_force_lp = "<div onClick='rtu_force_load_profile_enable($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }
				$row[] = "<input title='$info_hover' type='radio' onClick='selected_rtu($cols->id)' name='select_rtu'>";
				$row[] = $m;
				
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->rtu_sn_number . "</div>";
				$row[] = "<div title='Go to " . $cols->phone_no_or_ip_address . "' style='font-weight:bold; text-align:left;'>
				<a href='http://" . $cols->phone_no_or_ip_address . "' target='_blank'>" . $cols->phone_no_or_ip_address . "</a>
				</div>"; 
				$row[] = "<div title='$info_hover' onClick='selected_rtu($cols->id)' style='font-weight:bold;'>" . $cols->mac_addr . "</div>"; 
				
				#0000-00-00 00:00:00
				if(($cols->last_log_update) != '' && ($cols->last_log_update) != '0000-00-00 00:00:00'){
				$last_log_update 	=  (date('Y/m/d H:i', strtotime($cols->last_log_update)));
				}else{
				$last_log_update 	=  "0000/00/00 00:00";
				}
				
						/* ONLINE / OFFLINE STATUS*/
						
						/*FROM LOGS*/
						$_date_format = strtotime($cols->last_log_update);
						$date_format = date('Y-m-d H:i:s',$_date_format);
						
						/*SERVER DATETIME*/
						$_server_time=date('Y-m-d H:i:s');
						$server_time_current = strtotime($_server_time);
						
						$date1=date_create("$_server_time");
						$date2=date_create("$date_format");
								
						$diff					= date_diff($date1,$date2);
						$days_last_active 		= $diff->format("%a");
						
						if($last_log_update == "0000/00/00 00:00"){
							$status = "<div style='color:black; font-weight:bold; text-align:center;' title='No Meter Connected on the Gateway/Spare'>No Data</div>";

						}
						else if($diff->format("%a")<=0){
							$status = "<div style='color:green; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>ONLINE</div>";

						}else{
							$status = "<div style='color:red; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>OFFLINE</div>";
						}			
				
				
				$row[] = $cols->rtu_site_name;
				$row[] = $cols->rtu_physical_location;
				#$row[] = $cols->idf_number; 
				#$row[] = $cols->switch_name; 
				#$row[] = $cols->idf_port; 
				$row[] = $cols->gateway_description;
				$row[] = $cols->soft_rev;
				$row[] = $status;
				$row[] = $update_rtu; 
				$row[] = $update_rtu_location;
				$row[] = $update_rtu_ssh;
				$row[] = $update_rtu_force_lp;
				$row[] = "<center><a href=" . site_url("admin/meter_management/$cols->rtu_site_id/$cols->id" ) . " class='list-btn'>Meters</a></center>";
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