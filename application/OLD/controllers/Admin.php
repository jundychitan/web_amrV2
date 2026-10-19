<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Admin extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('admin_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	$this->load->library('user_agent');
	}
	
	/*PAGE LOAD HERE*/
	public function account_settings()
	{
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	$data['user_info'] = $this->admin_model->user_info();
	
	$data['title'] = 'Account Settings';
	$data['title_1'] = 'Account Settings';
	$data['title_2'] = 'Account Settings';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/account_settings');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function user_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'User Management';
	$data['title_1'] = 'User List';
	$data['title_2'] = 'User Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/user_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	} 
		
	public function client_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	} 
	
	$data['title'] = 'Client Management';
	$data['title_1'] = 'Client List';
	$data['title_2'] = 'Client Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/client_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	} 
	
	public function welcome()
	{
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$data['title'] = 'Site Management';
	$data['title_1'] = 'Site List';
	$data['title_2'] = 'Site Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/welcome_page');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
		
	public function site_management()
	{
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Site Management';
	$data['title_1'] = 'Site List';
	$data['title_2'] = 'Site Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/site_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function site_sub_group()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$siteid = $this->uri->segment(3);
	if (empty($siteid))
	{
	show_404();
	}
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['site_id'] = $siteid;
	
	$data['site_details'] = $this->admin_model->site_details($siteid);
	/*$data['sub_group_details'] = $this->admin_model->sub_group_list($siteid);*/
	$site_name = $data['site_details']['site_name'];
	
	$data['title'] = "Site Management ($site_name)";
	$data['title_1'] = 'Sub-Group List';
	$data['title_2'] = 'Sub-Group Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/site_sub_group');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$this->uri->segment(2);
	$siteid = $this->uri->segment(3);
	$rtuid = $this->uri->segment(4);
	if (empty($siteid))
	{
	show_404();
	}
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['site_id'] = $siteid;
	$data['rtu_id'] = $rtuid; 
	
	$data['title'] = 'Meter Management';
	$data['title_1'] = 'Meter List';
	$data['title_2'] = 'Meter Properties';
	
	$data['site_details'] = $this->admin_model->site_details($siteid);
	//$data['sub_group_details'] = $this->admin_model->sub_group_list($siteid);
	$data['rtu_list'] = $this->admin_model->rtu_list($siteid);
	$data['meter_model_list'] = $this->admin_model->meter_model_list();
	//$data['client_list'] = $this->admin_model->get_client_list();
	
	if($rtuid!=''){
	$data['rtu_info'] = $this->admin_model->rtu_info($rtuid);
	}
	
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/meter_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function rtu_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	$siteid = $this->uri->segment(3);
	if($siteid == ''){
		$php_file = 'rtu_management_all';
	}else{
		$php_file = 'rtu_management';
	}

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['site_id'] = $siteid;
	$data['site_list'] = $this->admin_model->logs_site_list();

	$data['title'] = 'Gateway Management';
	$data['title_1'] = 'Gateway List';
	$data['title_2'] = 'Gateway Properties';
	
	$data['site_details'] = $this->admin_model->site_details($siteid);
	 
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view("admin/". $php_file);
	$this->load->view('templates/footer');
	//echo $php_file;
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_model_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Meter Model Management';
	$data['title_1'] = 'Meter Model List';
	$data['title_2'] = 'Meter Model Properties';
		
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/meter_model_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function user_group_access()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$user_id = $this->uri->segment(3);
	if (empty($user_id))
	{
	show_404();
	}
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['user_id'] = $user_id;
	
	$data['title'] = 'User Access Management';
	$data['title_1'] = 'User Access Management';
	$data['title_2'] = 'Add Access';
	
	$data['user_details'] = $this->admin_model->get_selected_user($user_id);
	$data['site_list'] = $this->admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/user_group_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function user_group_access_sap()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$user_id = $this->uri->segment(3);
	if (empty($user_id))
	{
	show_404();
	}
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['user_id'] = $user_id;
	
	$data['title'] = 'User Access Management';
	$data['title_1'] = 'User Access Management';
	$data['title_2'] = 'Add Access';
	
	$data['user_details'] = $this->admin_model->get_selected_user_sap($user_id);
	$data['site_list'] = $this->admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/user_group_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
		
	public function client_meter_access()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	$user_id = $this->uri->segment(3);
	if (empty($user_id))
	{
	show_404();
	}
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['user_id'] = $user_id;
	
	$data['title'] = 'User Access Management';
	$data['title_1'] = 'User Access Management';
	$data['title_2'] = 'Add Access';
	
	$data['user_details'] = $this->admin_model->get_selected_user($user_id);
	$data['site_list'] = $this->admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/client_meter_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	/* REPORT CRATION */
	
	public function report_kwh_consumption()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KWh Consumption Report';	
	$data['site_list'] = $this->admin_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_all_logs_report_chart_consumption');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function report_kw_demand()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KW Demand Report';	
	$data['site_list'] = $this->admin_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_all_logs_report_chart_demand');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}

	public function chart_demo()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KW Demand Report';	
	$data['site_list'] = $this->admin_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/meter_chart_demo_refresh');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
			
	public function meter_logs()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'Meter Logs';	
	$data['site_list'] = $this->admin_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_2', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_all_logs_report');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/*Hourly*/
	public function meter_hourly_report()
	{
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'Hourly Report';
	$data['site_list'] = $this->admin_model->logs_site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_hourly_report');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/*Daily*/
	public function meter_daily_report()
	{
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Daily Report';
	$data['site_list'] = $this->admin_model->logs_site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_daily_report');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/*As Built Plan*/
	public function meter_abp_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'As Built Plan';
	$data['meter_name'] = $this->admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_abp_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}
		
	/*Location*/
	public function meter_location_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Location Report';
	$data['meter_name'] = $this->admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_location_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_sap_list()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'SAP';
	$data['meter_name'] = $this->admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_location_sap_list_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_location_sap_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'SAP Report';
	$data['meter_name'] = $this->admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('report/meter_location_sap_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}

	/*Meter List Uploading*/
	public function rtu_upload_meter_list()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Upload Meter List';
	$data['meter_name'] = $this->admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/upload_meter_list');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}

	public function do_upload_meter_list()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
	/*Delete meter_batch_backup.csv */
	@unlink("meter_upload_list/meter_list.csv");

	$csv_name = "meter_list";
	
	$data['title'] = 'Upload CSV file';
	
	$config['upload_path']  = 'meter_upload_list/';
	$config['allowed_types']= 'csv';
	$config['max_size'] = 90000;
	// $config['max_width']= 1024;
	// $config['max_height']   = 768;
	$config['file_name']   = $csv_name;
	
	$this->load->library('upload', $config);
	
	if ( ! $this->upload->do_upload('userfile'))
	{
	$error = $this->upload->display_errors();
	$this->session->set_flashdata('msg_success',"<div class='alert alert-warning  alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>×</a><strong>".$error."</strong></div>");  
	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/upload_meter_list', $error);
	$this->load->view('templates/footer');
	}
	else
	{
	//copy('/projects/amr/upload/meter_batch_backup.csv', '/projects/amr/upload/meter_batch.csv');
	
	$error = $this->upload->display_errors();
	$this->session->set_flashdata('msg_success',"<div class='alert alert-info  alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>×</a><strong>File Successfully Uploaded</strong></div>");  
	//redirect( base_url() . "index.php/admin/upload_file/");   
	
	/*IF SUCCESS UPLOADING READ FILE*/
	
	/*Upload Per RTU*/
	/*FILE FORMAT FOR UPLOADING are the FF
	meter_serial
	config file Id form Table(meter_model_config)
	sequence number(additional COLUMN for meter_details)
	*/
	
	$file_to_import = "meter_upload_list/meter_list.csv";
	@$import_data = file("$file_to_import");
	$m = 1;

	@$meter_list = '';
	foreach (@$import_data as $import_data_rows):

	@$meter_info_cols = explode(",", $import_data_rows);
	
	$meter_id = @$meter_info_cols[0];
	$config_id = @$meter_info_cols[1];
	$sequence_no = @$meter_info_cols[2];
	
	@$meter_list .= "$import_data_rows|";
	

	endforeach;

	$data['meter_list_uploaded'] = $meter_list;

	$this->load->view('templates/header', $data);
	$this->load->view('admin/navigation-admin');
	$this->load->view('admin/upload_success', $error);
	$this->load->view('templates/footer');

	/*OUTPUT THE LIST OF IMPORTED OR UPDATED METERS*/

	

	/*OUTPUT THE LIST OF IMPORTED OR UPDATED METERS*/
	
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	
	}

	
}