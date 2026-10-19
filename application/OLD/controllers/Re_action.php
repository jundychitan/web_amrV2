<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Re_action extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('re_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	} 
	
	public function account_settings_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('user_real_name', 'Name', 'required');
	$this->form_validation->set_rules('user_name', 'Username', 'required');
	$this->form_validation->set_rules('old_password', 'Old Password', 'required|trim|min_length[5]|max_length[12]');
	$this->form_validation->set_rules('new_password', 'New Password', 'required|trim|min_length[5]|max_length[12]|matches[retype_new_password]');
	$this->form_validation->set_rules('retype_new_password', 'Confirm Password', 'required|trim|min_length[5]|max_length[12]');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{
	
	$a = "<div style='color:red; display:inline-flex;'>" . form_error('user_real_name') . "</div>";
	$b = "<div style='color:red; display:inline-flex;'>" . form_error('user_name') . "</div>";
	$c = "<div style='color:red; display:inline-flex;'>" . form_error('old_password') . "</div>";
	$d = "<div style='color:red; display:inline-flex;'>" . form_error('new_password') . "</div>"; 
	$e = "<div style='color:red; display:inline-flex;'>" . form_error('retype_new_password') . "</div>";
	$f = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";
	
	}
	//VALIDATION SUCCESS
	else{
	
	$username = $this->input->post('user_name');
	$user_password = md5($this->input->post('new_password'));
	//$user_type = 'RE';
	
	$account_settings = $this->re_model->user_info();
	$data['current_account_settings'] = $account_settings;
	
	$user_password_current = $data['current_account_settings']['user_password'];
	$user_password_old_from_input = md5($this->input->post('old_password'));
	
	if($user_password_current!=$user_password_old_from_input){
	//Current password did not match to old password input
	$a = "";
	$b = "";
	$c = "<div style='color:red;'>Wrong Password</div>";
	$d = "";
	$e = "";
	$f = "<div class='message_style'><div class='message_style_content'><strong>***Wrong Password!***</strong></div></div>";
	
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";
	
	}else{
	
	$this->re_model->user_management_action();
	
	$a = "";
	$b = "";
	$c = "";
	$d = "";
	$e = "<div class='message_style'><div class='message_style_content'><strong>***Saved!***</strong></div></div>";
	
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\n";
	}
	}
	}
	else{
	redirect('Login/invalid');  
	}
   
	}
	
	public function meter_info()
	{
		
		if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	$meter_id = $this->input->post('meter_id');
	
	if($meter_id!=''){
	
	$data['meter_info']=$this->re_model->get_selected_meter($meter_id);

	$client_name = $data['meter_info']['customer_name'];
	$measuring_point = $data['meter_info']['measuring_point'];

	$created_by = $data['meter_info']['created_by'];
	$date_created = $data['meter_info']['date_created'];
	$modified_by = $data['meter_info']['modified_by'];
	$date_modified = $data['meter_info']['date_modified'];

	$meter_site_id = $this->input->post('site_id');

	$rtu_sn_number_id = $data['meter_info']['rtu_sn_number_id'];
	$rtu_sn_number = $data['meter_info']['rtu_sn_number'];
	$meter_name = $data['meter_info']['meter_name'];
	$meter_model = $data['meter_info']['meter_model'];
	$meter_model_id = $data['meter_info']['meter_model_id'];
	$meter_config_file = $data['meter_info']['meter_config_file'];
	$meter_type = $data['meter_info']['meter_type'];
	$meter_multiplier = $data['meter_info']['meter_multiplier'];
	$meter_status = $data['meter_info']['meter_status'];
	$meter_role = $data['meter_info']['meter_role'];
	$meter_name_addressable = $data['meter_info']['meter_name_addressable'];
	$meter_default_name = $data['meter_info']['meter_default_name'];
	$physical_location = $data['meter_info']['physical_location'];
	$meter_list_src = $data['meter_info']['meter_list_src'];
	$siteid = $meter_site_id;

	?>
    <div class="col-md-2">
        
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
    
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Information</div>
    
	<?php

		$company_no = $data['meter_info']['company_no'];
		$building = $data['meter_info']['building'];
		$land = $data['meter_info']['land'];
		$meter_site_name = $data['meter_info']['meter_site_name'];

		$rental_object = $data['meter_info']['rental_object'];
		$rental_object_name = $data['meter_info']['rental_object_name'];
		$usage_type = $data['meter_info']['usage_type'];
		$ro_valid_from = $data['meter_info']['ro_valid_from'];

		$ro_valid_to = $data['meter_info']['ro_valid_to'];
		$contract_number = $data['meter_info']['contract_number'];
		$customer_name = $data['meter_info']['customer_name'];
		$meter_characteristic = $data['meter_info']['meter_characteristic'];
	
		$measuring_point = $data['meter_info']['measuring_point'];
		$measuring_point_description = $data['meter_info']['measuring_point_description'];
		$measurement_sequence = $data['meter_info']['measurement_sequence'];
		$measurement_separator = $data['meter_info']['measurement_separator'];

		$meter_reading_date = $data['meter_info']['meter_reading_date'];
		$measurement_separator = $data['meter_info']['measurement_separator'];
		$participation_group = $data['meter_info']['participation_group'];
		
		$creation_date = $data['meter_info']['creation_date'];
		$CREATION_BY = $data['meter_info']['creation_by'];
		$last_change_on = $data['meter_info']['last_change_on'];
		$last_change_by = $data['meter_info']['last_change_by'];
	
	?>
	<div class="row">    
    <div class="col-md-12">    
    <div align="right">
	<?php
    echo "<br><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button>"
    ?>
    </div>
    
    <h4>METER DESCRIPTION : <?=$meter_name;?></h4>
    <h6>GATEWAY SERIAL NUMBER: <?=$rtu_sn_number;?></h6>
    <h6>ALTERNATE ADDRESS : <?=$meter_default_name;?></h6>
    <h6>CONFIGURATION FILE : <?=$meter_config_file;?></h6>
    <h6>LOCATION : <?=$physical_location;?></h6>
    <h6>TYPE : <?=$meter_type;?></h6>
    <h6>ROLE : <?=$meter_role;?></h6>
	
    </div>
    </div>
    <div class="row">    
    <div class="col-md-2">
    </div>
    <div class="col-md-4">
    
    <h6>COMPANY CODE : <?=$company_no;?></h6>
    <h6>BUSINESS ENTITY : <?=$meter_site_name;?></h6>
    <h6>BUILDING : <?=$building;?></h6>
    <h6>LAND : <?=$land;?></h6>
    <h6>RENTAL OBJECT NO : <?=$rental_object;?></h6>
    <h6>RENTAL OBJECT NAME : <?=$rental_object_name;?></h6>
    <h6>USAGE TYPE : <?=$usage_type;?></h6>
    <h6>RO VALID FROM : <?=$ro_valid_from;?></h6>
    <h6>RO VALID TO : <?=$ro_valid_to;?></h6>
    <h6>CONTRACT NUMBER : <?=$contract_number;?></h6>
    <h6>CONTRACT NAME : <?=$customer_name;?></h6>
    </div>
    <div class="col-md-6">
    <h6>METER CHARACTERISTIC : <?=$meter_characteristic;?></h6>
    <h6>MEASPONT : <?=$measuring_point;?></h6>
    <h6>MEASUREMENT SEQUENCE : <?=$measurement_sequence;?></h6>
    <h6>MEASUREMENT SEPARATOR : <?=$measurement_separator;?></h6>
    <h6>METER MULTIPLIER : <?=$meter_multiplier;?></h6>
    <h6>METER STATUS : <?=$meter_status;?></h6>
    <h6>PARTICIPATION GROUP : <?=$participation_group;?></h6>
    <h6>CREATION DATE : <?=$creation_date;?></h6>
    <h6>CREATION BY : <?=$CREATION_BY;?></h6>
    <h6>LAST CHANGE ON : <?=$last_change_on;?></h6>
    <h6>LAST CHANGE BY : <?=$last_change_by;?></h6>    
    </div>
        <div class="col-md-2">
    </div>
    </div>
    
    </div>
    </div>
    <div class="col-md-2">
  
    </div>
    <?php
	}
	else{
	}
		}else{
		}
	
	}
} 