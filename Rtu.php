<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Rtu extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('rtu_model');
	$this->load->helper('url_helper');
	$this->load->helper('html');
	$this->load->helper('date'); 
	}
	
	/*PAGE LOAD HERE*/
	
	public function rtu_check_update(){
	
	$mac_addr = $this->uri->segment(3);	
	$request = $this->uri->segment(4);	
	$datetime_request = date("Y-m-d H:i:s");
	/*RECORD RTU ACCESS*/

			//@$log_row = "$datetime_request $mac_addr $request \n";
			//$write_log_file = "rtu_access_log.txt";			

			//file_put_contents($write_log_file, $log_row, FILE_APPEND | LOCK_EX);

	/*meter_batch.csv Update*/
	if($request=="get_update_csv"){
	/*CHECK IF UPDATE AVAILABLE*/
	/*QUERY RTU INFO*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$update_rtu = $rtu_list['update_rtu'];
	echo $update_rtu;
	} 
	elseif($request=="get_content_csv"){
	/*GET CONTENT(METER LIST)*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$rtu_sn_number = $rtu_list['id'];
	
	$meter_list = $this->rtu_model->meter_list($rtu_sn_number);	

	 foreach ($meter_list as $meter_list_col): 
	 
	 if($meter_list_col['meter_name']==$meter_list_col['meter_default_name']){
		 $meter_name = $meter_list_col['meter_name'];
		 $addressable_meter = $meter_list_col['meter_default_name'];
		 }
	 // elseif($meter_list_col['meter_default_name']=='1'){
	// 	 $meter_name = '1';
	// 	 $addressable_meter = $meter_list_col['meter_default_name'];
	// 	 }
	 else{
		 $meter_name = $meter_list_col['meter_name'];
		 $addressable_meter = $meter_list_col['meter_default_name'];
		 }
	 
	 echo "$meter_name,$meter_list_col[meter_config_file],$addressable_meter\n";
	
	 endforeach;

	} 
	elseif($request=="reset_update_csv"){
	/*RESET TO ZERO*/
	$this->rtu_model->rtu_list_reset_update($mac_addr);
	}

	/*********************************************************************/
	
	/*Location Update*/
	elseif($request=="get_update_location"){
	/*CHECK IF UPDATE AVAILABLE*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$update_rtu_location = $rtu_list['update_rtu_location'];
	echo $update_rtu_location;

	} 
	elseif($request=="get_content_location"){
	/*GET CONTENT(Location)*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$rtu_sn_number = $rtu_list['rtu_sn_number'];
	$_rtu_site_name = $rtu_list['rtu_site_name'];
	$dq = '"';
	$rtu_site_name = "$dq" . $_rtu_site_name . "$dq\n";
	echo "location = $rtu_site_name";
	} 
	elseif($request=="reset_update_location"){
	/*RESET TO ZERO*/
	$this->rtu_model->rtu_location_reset_update($mac_addr);
	}

	/*********************************************************************/

	/*CHECK RTU IF REMOTE SSH ENABLE*/
	elseif($request=="rtu_remote_ssh"){
	/*CHECK IF UPDATE AVAILABLE*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$update_rtu_ssh = $rtu_list['update_rtu_ssh'];
	echo $update_rtu_ssh;

	} 

	/*********************************************************************/

	/*FORCE LOAD PROFILE*/
	elseif($request=="force_lp"){
	/*CHECK IF UPDATE AVAILABLE*/
	$rtu_list = $this->rtu_model->rtu_info($mac_addr);
	$update_rtu_force_lp = $rtu_list['update_rtu_force_lp'];
	echo $update_rtu_force_lp;

	} 
	elseif($request=="reset_force_lp"){
	/*RESET TO ZERO*/
	$this->rtu_model->rtu_lp_reset_update($mac_addr);
	} 

	} 
	
	
}