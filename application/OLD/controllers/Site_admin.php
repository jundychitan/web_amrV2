<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Site_admin extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('site_admin_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	$this->load->library('user_agent');
	}
	
	/*PAGE LOAD HERE*/
	
	public function account_settings()
	{
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

	$data['user_info'] = $this->site_admin_model->user_info();
	
	$data['title'] = 'Account Settings';
	$data['title_1'] = 'Account Settings';
	$data['title_2'] = 'Account Settings';
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/account_settings');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	

	public function welcome()
	{
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
	$data['title'] = 'Site Management';
	$data['title_1'] = 'Site List';
	$data['title_2'] = 'Site Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/welcome_page');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
		
	public function site_management()
	{
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/site_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function site_sub_group()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	
	$data['site_details'] = $this->site_admin_model->site_details($siteid);
	/*$data['sub_group_details'] = $this->site_admin_model->sub_group_list($siteid);*/
	$site_name = $data['site_details']['site_name'];
	
	$data['title'] = "Site Management ($site_name)";
	$data['title_1'] = 'Sub-Group List';
	$data['title_2'] = 'Sub-Group Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/site_sub_group');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	
	$data['site_details'] = $this->site_admin_model->site_details($siteid);
	//$data['sub_group_details'] = $this->site_admin_model->sub_group_list($siteid);
	$data['rtu_list'] = $this->site_admin_model->rtu_list($siteid);
	$data['meter_model_list'] = $this->site_admin_model->meter_model_list();
	//$data['client_list'] = $this->site_admin_model->get_client_list();
	
	if($rtuid!=''){
	$data['rtu_info'] = $this->site_admin_model->rtu_info($rtuid);
	}

	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/meter_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function rtu_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
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
	$data['site_list'] = $this->site_admin_model->logs_site_list();

	$data['title'] = 'Gateway Management';
	$data['title_1'] = 'Gateway List';
	$data['title_2'] = 'Gateway Properties';
	
	$data['site_details'] = $this->site_admin_model->site_details($siteid);
	 
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view("site_admin/". $php_file);
	$this->load->view('templates/footer');
	//echo $php_file;
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_model_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

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
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/meter_model_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function user_group_access()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	
	$data['user_details'] = $this->site_admin_model->get_selected_user($user_id);
	$data['site_list'] = $this->site_admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/user_group_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function user_group_access_sap()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	
	$data['user_details'] = $this->site_admin_model->get_selected_user_sap($user_id);
	$data['site_list'] = $this->site_admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/user_group_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
		
	public function client_meter_access()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
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
	
	$data['user_details'] = $this->site_admin_model->get_selected_user($user_id);
	$data['site_list'] = $this->site_admin_model->site_list();
	
	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('site_admin/client_meter_access');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	/* REPORT CRATION */
	
	
	public function report_kwh_consumption()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KWh Consumption Report';	
	$data['site_list'] = $this->site_admin_model->get_meter_location_from_meter_data();
	$user_id = $_SESSION['user_id'];
	 
	$this->load->view('templates/header_3', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_all_logs_report_chart_consumption');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function report_kw_demand()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KW Demand Report';	
	$data['site_list'] = $this->site_admin_model->get_meter_location_from_meter_data();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_all_logs_report_chart_demand');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function meter_logs()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'Meter Logs';	
	$data['site_list'] = $this->site_admin_model->get_meter_location_from_meter_data();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_2', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_all_logs_report');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/*As Built Plan*/
	public function meter_abp_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'As Built Plan';
	$data['meter_name'] = $this->site_admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('site_admin/navigation-site_admin');
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
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Location Report';
	$data['meter_name'] = $this->site_admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_location_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function meter_sap_list()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'SAP';
	$data['meter_name'] = $this->site_admin_model->logs_site_list();

	$this->load->view('templates/header_2', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_location_sap_list_report');
	$this->load->view('templates/footer');
	} 
	else{ 
	redirect('Login/invalid');  
	}
	}	
	
	public function meter_location_sap_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'SAP Report';
	$data['meter_name'] = $this->site_admin_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header', $data);
	$this->load->view('site_admin/navigation-site_admin');
	$this->load->view('report/meter_location_sap_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}
	
}