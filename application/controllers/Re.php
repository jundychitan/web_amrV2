<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Re extends CI_Controller {
	
	public function __construct()
	{
			parent::__construct();
			$this->load->model('re_model');
			$this->load->helper('url_helper');
			$this->load->library('session');
			$this->load->helper('html');
			$this->load->helper('date');
			$this->load->library('user_agent');
	}
	
	/*PAGE LOAD HERE*/
	
	public function welcome()
	{
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	$data['title'] = 'Site Management';
	$data['title_1'] = 'Site List';
	$data['title_2'] = 'Site Properties';
	
	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('re/welcome_page');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}		
	
	public function account_settings()
	{
	if($this->session->userdata('currently_logged_in_as')=='RE'){

	$data['user_info'] = $this->re_model->user_info();
	
	$data['title'] = 'Account Settings';
	$data['title_1'] = 'Account Settings';
	$data['title_2'] = 'Account Settings';
	
	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('re/account_settings');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function rtu_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
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
	
	$data['title'] = 'Gateway Management';
	$data['title_1'] = 'Gateway List';
	$data['title_2'] = 'Gateway Properties';
	
	$data['site_details'] = $this->re_model->site_details($siteid);
	
	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('re/rtu_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function site_management()
	{
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
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
	$this->load->view('re/navigation-re');
	$this->load->view('re/site_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
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
	$data['rtu_id'] = $rtuid+0; 
	
	$data['title'] = 'Meter Management';
	$data['title_1'] = 'Meter List';
	$data['title_2'] = 'Meter Properties';
	
	$data['site_details'] = $this->re_model->site_details($siteid);
	//$data['sub_group_details'] = $this->re_model->sub_group_list($siteid);
	$data['rtu_list'] = $this->re_model->rtu_list($siteid);
	$data['meter_model_list'] = $this->re_model->meter_model_list();
	
	if($rtuid!=''){
	$data['rtu_info'] = $this->re_model->rtu_info($rtuid);
	}
	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('re/meter_management');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management_unassigned()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
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
	$data['rtu_id'] = $rtuid+0; 
	
	$data['title'] = 'Meter Management(Unassigned)';
	$data['title_1'] = 'Meter List';
	$data['title_2'] = 'Meter Properties';
	
	$data['site_details'] = $this->re_model->site_details($siteid);
	$data['rtu_list'] = $this->re_model->rtu_list($siteid);
	$data['meter_model_list'] = $this->re_model->meter_model_list();
	
	if($rtuid!=''){
	$data['rtu_info'] = $this->re_model->rtu_info($rtuid);
	}
	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('re/meter_management_unassigned');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/* REPORT CRATION */

	public function report_kwh_consumption()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KWh Consumption Report';	
	$data['site_list'] = $this->re_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_all_logs_report_chart_consumption');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}	
	
	public function report_kw_demand()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'KW Demand Report';	
	$data['site_list'] = $this->re_model->logs_site_list();
	$user_id = $_SESSION['user_id'];
	
	$this->load->view('templates/header_3', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_all_logs_report_chart_demand');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_logs()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Report';
	$data['site_list'] = $this->re_model->logs_site_list();
	
	$user_id = $_SESSION['user_id'];
	$this->load->view('templates/header_2', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_all_logs_report');
	$this->load->view('templates/footer');
	
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	/*Location*/
	public function meter_location_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){
	
	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}
	
	$data['title'] = 'Location Report';
	$data['meter_name'] = $this->re_model->get_meter_location_from_meter_data();	
	$this->load->view('templates/header_2', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_location_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_location_sap_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'SAP';
	$data['meter_name'] = $this->re_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_location_sap_report');
	$this->load->view('templates/footer'); 
	} 
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_sap_list()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'Meter List';
	$data['meter_name'] = $this->re_model->logs_site_list();

	$this->load->view('templates/header_2', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_location_sap_list_report');
	$this->load->view('templates/footer');
	} 
	else{ 
	redirect('Login/invalid');  
	}
	}	
	
	/*As Built Plan*/
	public function meter_abp_report()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='RE'){

	/*IF referrer is Empty Use to go back to prev page*/
	if ($this->agent->is_referral())
	{
		$data['back_prev_page'] =  $this->agent->referrer();
	}	
	else{
		$data['back_prev_page'] =  "[ <a href='" . $this->agent->referrer() ."' class=''>&larr;Back</a> ]";
	}

	$data['title'] = 'As Built Plan';
	$data['meter_name'] = $this->re_model->get_meter_location_from_meter_data();

	$this->load->view('templates/header_2', $data);
	$this->load->view('re/navigation-re');
	$this->load->view('report/meter_abp_report');
	$this->load->view('templates/footer');
	} 
	else{
	redirect('Login/invalid');  
	}
	}

}