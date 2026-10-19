<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
date_default_timezone_set('Asia/Manila');
class Ba_datatables extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
			 	$this->load->model('Ba_model');
				$this->load->model('Ba_datatable_model');
				$this->load->model('Site_meter_model');/*For Meter List*/
				$this->load->model('Site_meter_model_unassigned');/*For Unassigned Meter List*/
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
		
			$user_type = 'ba';
		
			/* USE SESSION */
			/* RESET SEARCH */
			$_reset_search = $this->uri->segment(3);
			if($_reset_search == 1){
				unset($_SESSION['site_search_value']);
			}else{

			}
		
			$list = $this->Ba_datatable_model->get_datatables();
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

						} else{
							$status = "<div style='color:red; font-weight:bold; text-align:center;' title='Last log Update : $last_log_update'>OFFLINE</div>";
						}			

				$row = array();
				$row[] = $n;
				$row[] = '<center>'.$cols->company_no.'</center>';
				$row[] = $cols->business_entity;
				$row[] = $cols->site_code;
				$row[] = $cols->site_name;
				$row[] = '<center>'.$cols->building_type.'</center>';
				$row[] = '<center>'.$cols->site_cut_off.'</center>';
				$row[] = $settlement_valid_from;
				$row[] = $settlement_valid_to;
				$row[] = $status;
				$row[] = "<center><a href=" . site_url("$user_type/rtu_management/" . $cols->meter_site_id ) . " class='list-btn'>Gateway</a></center>";
				$row[] = "<center><a href=" . site_url("$user_type/meter_management/" . $cols->meter_site_id ) . " class='list-btn'>Meters</a></center>";
				$data[] = $row;
				$n++;

			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->Ba_datatable_model->count_all(),
							"recordsFiltered" => $this->Ba_datatable_model->count_filtered(),
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
				$last_log_update 	=  "0000/00/00 00:00:00";
				}
				

				@$customer_name = "$cols->customer_name"; 
				$row[] = $m;
				$row[] = $cols->measuring_point;				
				$row[] = $cols->meter_name;
				$row[] = $customer_name;
				$row[] = $cols->rtu_sn_number;
				$row[] = $cols->meter_role;
				$row[] = $cols->meter_status;
				$row[] = $last_log_update;
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
				$last_log_update 	=  "0000/00/00 00:00:00";
				}
				

				@$customer_name = "$cols->customer_name"; 
				$row[] = $m;
				$row[] = $cols->measuring_point;				
				$row[] = $cols->meter_name;
				$row[] = $customer_name;
				$row[] = $cols->rtu_sn_number;
				$row[] = $cols->meter_role;
				$row[] = $cols->meter_status;
				$row[] = $last_log_update;
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
				
				$row = array();
				$info_hover = "Click to Edit\nDate Created : $cols->created_by\nCreated By : $cols->date_created\nModified By : $cols->modified_by\nDate Modified : $cols->date_modified\n";	
				if($cols->update_rtu == '1' ){ $update_rtu = "<div onClick='selected_rtu_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu = "<div onClick='selected_rtu_enable_update($cols->id)' style='color:red; text-align:center; cursor: pointer;'><b>OFF</b></div>"; }
				if($cols->update_rtu_location == '1' ){ $update_rtu_location = "<div onClick='selected_rtu_location_disable_update($cols->id)' style='color:green; text-align:center; cursor: pointer;'><b>ON</b></div>"; }else{ $update_rtu_location = "<div onClick='selected_rtu_location_enable_update($cols->id)' style='color:red; text-align:center;  cursor: pointer;'><b>OFF</b></div>"; }
				$row[] = "<input type='radio' onClick='selected_rtu($cols->id)' name='select_rtu'>";

				$row[] = $m;
				$row[] = $cols->rtu_sn_number;
				$row[] = $cols->phone_no_or_ip_address; 
				$row[] = $cols->mac_addr;
				$row[] = $cols->rtu_physical_location; 
				$row[] = $cols->idf_number; 
				$row[] = $cols->switch_name; 
				$row[] = $cols->idf_port; 
				$row[] = $cols->gateway_description;
				$row[] = $cols->soft_rev;
				$row[] = $status;
				$row[] = $update_rtu; 
				//$row[] = $update_rtu_location;
				$row[] = "<center><a href=" . site_url("ba/meter_management/$siteid/" . $cols->id ) . " class='list-btn'>Meters</a></center>";
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


}