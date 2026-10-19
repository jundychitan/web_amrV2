<?php
	class Re_model extends CI_Model {
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}
	
	public function user_management_action()
	{
	$this->load->helper('url');
	$id = $_SESSION['user_id'];
	$username = $this->input->post('user_name');

	$user_password = md5($this->input->post('new_password'));
	
	$data = array(
	'user_name' => $this->input->post('user_name'),
	'user_password' => $user_password,
	'user_real_name' => $this->input->post('user_real_name'),
	'modified_by' => $_SESSION['user_name']
	);
	
	$query_check_username_exist = $this->db->query("SELECT * FROM user_tb
	   WHERE user_name = ".$this->db->escape($username)." and id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	
	if($count_user==0){
	
	$this->db->where('id', $id); 
	$this->db->update('user_tb', $data);
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:red; display:inline-flex;'>Username Already Exist</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div class='message_style'><div class='message_style_content'><strong>***Username Already Exist***</strong></div></div>";
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";
	
	}
	}
	
	public function user_info(){
	$user_id = $_SESSION['user_id'];	
	$query = $this->db->get_where('user_tb', array('id' => $user_id));
	return $query->row_array();
	}
	
	public function rtu_list($siteid){
	$query = $this->db->select('id');
	$query = $this->db->select('rtu_sn_number');
	$query = $this->db->get_where('meter_rtu', array('rtu_site_id' => $siteid));
	return $query->result_array();
	}
 
 
	/*METER MODEL MANAGEMENT*/
	public function meter_model_list(){
	$query = $this->db->query("SELECT `id`,`meter_model`,`config_file` FROM meter_model_config");		
	return $query->result_array();
	}

	/*
	SITE MANAGEMENT
	START SITE MANAGEMENT
	*/ 
	public function get_selected_site($site_id){
	$query = $this->db->get_where('meter_site', array('id' => $site_id));
	return $query->row_array();
	}
	
	public function site_details($siteid){
	$query = $this->db->get_where('meter_site', array('id' => $siteid));
	return $query->row_array();
	}
	
	public function site_management_action()
	{
	$this->load->helper('url');
	$id = $this->input->post('site_id');
	$username = $this->input->post('site_name');
	if ($id == 0 || $id == '') {
	$data = array(
	'site_name' => $this->input->post('site_name'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id']
	);
	$query_check_username_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_name = ".$this->db->escape($username)."");
	$count_user = $query_check_username_exist->num_rows(); /*counting result from query*/
	if($count_user==0){
	$this->db->insert('meter_site', $data);
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Site name Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}
	}
	else {
	$data = array(
	'site_name' => $this->input->post('site_name'),
	'modified_by' => $_SESSION['user_name'],
	'modified_by_id' => $_SESSION['user_id']
	);
	$query_check_username_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_name = ".$this->db->escape($username)." and id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); /*counting result from query*/
	if($count_user==0){
	$this->db->where('id', $id);
	$this->db->update('meter_site', $data);
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Site name Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}
	}
	}
	
	public function delete_site($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_site');
	}
	
	/*END SITE MANAGEMENT*/
	
	/*START METER MANAGEMENT*/
	public function site_meter_group_list($site_id)
	{
	$query = $this->db->get_where('meter_site_sub_group', array('site_id' => $site_id));
	return $query->result_array();
	}
	public function site_meter_group_select($site_id,$meter_site_sub_group_id)
	{
	$query = $this->db->get_where('meter_site_sub_group', array('site_id' => $site_id));
	$sub_group = $query->result_array();
	?>
					<select class="form-control" id="sub_group" name="sub_group"/>
					  <?php foreach ($sub_group as $sub_group_details_cols):  ?>
					  <option value="<?=$sub_group_details_cols['id'];?>" 
					  <?php if($sub_group_details_cols['id']==$meter_site_sub_group_id){ echo "selected"; } else{} ?>><?=$sub_group_details_cols['sub_group_name'];?></option>
					  <?php endforeach;  ?>
					</select>
				 <?php
	}
	/*SUB-GROUP METER LIST*/
	public function site_sub_group_meter_list($site_id,$meter_role)
	{
	$query = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($site_id)." and `meter_role` = ".$this->db->escape($meter_role)."	");
	return $query->result_array();
	}
	/*SELECT METER*/
	public function get_selected_meter($meter_id){
	$query = $this->db->get_where('meter_details', array('id' => $meter_id));
	return $query->row_array();
	}
	public function get_meter_info($meter_id){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_id));
	return $query->row_array();
	}
	/*DELETE METER*/
	public function delete_meter($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_details');
	}
	/*ADD or UPDATE METER*/
	public function meter_management_action()
	{
	$this->load->helper('url');
	$id = $this->input->post('meter_id');
	$meter_addressable = $this->input->post('meter_addressable');
	$sub_group = explode(",", $this->input->post('sub_group'));
	$rtu_sn_number = explode(",", $this->input->post('rtu_sn_number'));
	$meter_model = explode(",", $this->input->post('meter_model'));
	if($meter_addressable==1){ $meter_default_name = $this->input->post('serial_number'); }else{ $meter_default_name = '1'; }
	@$data = array(
	'client_name' => $this->input->post('client_name'),
	'meter_site_id' => $this->input->post('site_id'),
	'meter_site_name' => $this->input->post('site_name'),
	'meter_site_sub_group_id' => $sub_group[0],
	'meter_site_sub_group_name' => $sub_group[1],
	'rtu_sn_number_id' => $rtu_sn_number[0],
	'rtu_sn_number' => $rtu_sn_number[1],
	'meter_name' => $this->input->post('serial_number'),
	'meter_name_addressable' => $meter_addressable,
	'meter_default_name' => $meter_default_name,
	'meter_multiplier' => $this->input->post('meter_multiplier'),
	'meter_model_id' => $meter_model[0],
	'meter_model' => $meter_model[1],
	'meter_config_file' => $meter_model[2],
	'meter_type' => $this->input->post('meter_type'),
	'meter_status' => $this->input->post('meter_status'),
	'created_by' => $_SESSION['user_name'],
	'added_by' => $_SESSION['user_id'],
	'modified_by' => $_SESSION['user_name']
	);
	if ($id == 0 || $id == '') {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		meter_site_sub_group_id = ".$this->db->escape($this->input->post('sub_group'))." and
		meter_name = ".$this->db->escape($this->input->post('serial_number'))."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->insert('meter_details', $data);
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('sub_group')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Meter Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	}
	}
	else {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		meter_site_sub_group_id = ".$this->db->escape($this->input->post('sub_group'))." and
		meter_name = ".$this->db->escape($this->input->post('serial_number'))." and
	 id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->where('id', $id);
	$this->db->update('meter_details', $data);
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('sub_group')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Meter Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	}
	}
	 
	}
	//END METER MANAGEMENT
	//REPORT TYPE LIST
	public function report_type_list(){
	$report_type = $this->input->post('report_type');
	if($report_type=='energy_report'){
	$query = $this->db->get_where('meter_energy_report_name');
	return $query->result_array();
	}
	else{
	$query = $this->db->get_where('meter_instantaneous_energy_report_name');
	return $query->result_array();
	}
	}
	public function site_list(){
	$query = $this->db->get_where('meter_site');
	return $query->result_array();
	}
	 
	public function site_admin_site_list(){
	$user_id = $_SESSION['user_id'];
	$this->db->distinct();
	$this->db->select('group_to_access');
	$this->db->select('meter_site_id');
	$query = $this->db->get_where('user_meter_group_access', array('user_id' => $user_id));
	return $query->result_array();
	}
	/*REPORTING*/
	public function get_report_details($report_type_table,$report_template)
	{
	$query = $this->db->get_where($report_type_table, array('id' => $report_template));
	return $query->row_array();
	}

	public function meter_groups_count($meter_groups_cnt,$date_start,$date_end){
	$query = $this->db->query("SELECT DISTINCT 
	meter_site_sub_group_id as meter_group_id,
	meter_site_sub_group_name as meter_sub_group,
	COUNT(meter_site_sub_group_id) as group_data_count 
	from meter_data_view WHERE meter_site_sub_group_id=" . $this->db->escape($meter_groups_cnt) . " and 
	datetime >= " . $this->db->escape($date_start) . " and
	datetime <= " . $this->db->escape($date_end) . "");
	return $query->result_array();
	}
	public function get_meter_groups($meter_groups_cnt,$date_start,$date_end){
	$query = $this->db->query("SELECT DISTINCT 
	location as location,
	meter_id as meter_id
	from meter_data_view WHERE meter_site_sub_group_id=" . $this->db->escape($meter_groups_cnt) . " and 
	datetime >= " . $this->db->escape($date_start) . " and
	datetime <= " . $this->db->escape($date_end) . "");
	return $query->result_array();
	}
	public function get_meter_data_group_start($meter_id,$first_date,$last_date){
	/*CURRENT DATE*/
	$dt_from = ("$first_date");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
		$dt_to = ("$last_date");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query = $this->db->query("
	Select min(`wh_total`) as min_wh_total,
	min(`datetime`) as min_date ,
	max(`wh_total`) as max_wh_total,
	max(`datetime`) as max_date,
	 ((max(`wh_total`))- (min(`wh_total`))) as wh_total,
	`meter_id`,
	client_name as client_name,meter_site_sub_group_id as site_id,meter_site_sub_group_name as subgroup_name from meter_data_view where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)."");
	return $query->row_array();
	}


	public function get_meter_details_report($meter_id_group,$location_group){
	//echo "$location_group";
	@$query_check_meter_exist = $this->db->query("SELECT * FROM meter_details
	WHERE meter_name = ".$this->db->escape($meter_id_group)." and meter_site_name = ".$this->db->escape($location_group)."");
	return $query_check_meter_exist->result_array(); 
	}
	/*
	public function monthly_data_insert(
	$client_name,
	$location_group,
	$meter_sub_group,
	$meter_id,
	$date_start_data,
	$date_end_data,
	$energy_start,
	$energy_end,
	$consumption,
	$report_name_template){
	$data = array(
	'client_name' => @$client_name,
	'location' => @$location_group,
	'sub_group_name' => @$meter_sub_group,
	'meter_id' => @$meter_id,
	'date_from' => @$date_start_data,
	'date_to' => @$date_end_data,
	'previous_energy' => @$energy_start,
	'present_energy' => @$energy_end,
	'wh_total_consumption' => @$consumption,
	'report_name' => @$report_name_template,
	'created_by' => $_SESSION['user_id']
	);
	$this->db->insert('monthly_report_chart_data', $data);
	}
	public function delete_last_montly_data_created(){
	$this->db->query("DELETE FROM `monthly_report_chart_data` WHERE `created_by`='$_SESSION[user_id]'");
	}
	*/
	public function monthly_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	//SELECT * FROM monthly_report_chart_data where report_name='$datetime' and created_by='$user_id' and location='$location'
	//SELECT * FROM meter_details
	//WHERE meter_name = ".$this->db->escape($meter_id_group)." and meter_site_name = ".$this->db->escape($location_group)."
	@$query_check_meter_exist = $this->db->query("SELECT * FROM monthly_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	public function daily_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	//SELECT * FROM monthly_report_chart_data where report_name='$datetime' and created_by='$user_id' and location='$location'
	//SELECT * FROM meter_details
	//WHERE meter_name = ".$this->db->escape($meter_id_group)." and meter_site_name = ".$this->db->escape($location_group)."
	@$query_check_meter_exist = $this->db->query("SELECT * FROM daily_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	public function hourly_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	//SELECT * FROM monthly_report_chart_data where report_name='$datetime' and created_by='$user_id' and location='$location'
	//SELECT * FROM meter_details
	//WHERE meter_name = ".$this->db->escape($meter_id_group)." and meter_site_name = ".$this->db->escape($location_group)."
	@$query_check_meter_exist = $this->db->query("SELECT * FROM hourly_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	public function meter_monthly_report_chart_data_table($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	@$query = $this->db->query("SELECT `$month`,`sub_group`,`client_name` FROM monthly_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_data`= ".$this->db->escape($year)."");
	$row = $query->row_array();
	if (isset($row))
	{
	$sub_group = $row['sub_group'];
	$client_name = $row['client_name'];
	$monthly_consumption = $row["$month"];
	} 
	@$month_year_td .= "<td>$monthly_consumption</td>";
	endforeach; 
	$month_year_td .= "";
	return $month_year_td; 
	}
	public function meter_monthly_report_chart_data_keys($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	@$query = $this->db->query("SELECT `$month`,`sub_group`,`client_name` FROM monthly_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_data`= ".$this->db->escape($year)."");
	$row = $query->row_array();
	if (isset($row))
	{
	//$sub_group = $row['sub_group'];
	//$client_name = $row['client_name'];
	$monthly_consumption = $row["$month"];
	} 
	endforeach; 
	return $monthly_consumption; 
	}
	public function meter_daily_report_chart_data_table($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	$year_month_data;
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	$day = $year_month[2];
	$ym = "$year-$month";
	@$query = $this->db->query("SELECT `$day`,`meter_multiplier` FROM daily_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_month_data`= ".$this->db->escape($ym)."");
	@$row = $query->row_array();
	if (isset($row))
	{
	$monthly_consumption = $row["$day"]*$row['meter_multiplier'];
	} 
	@$month_year_td .= "<td>$monthly_consumption</td>";
	endforeach; 
	$month_year_td .= "";
	return $month_year_td; 
	}
	/*public function meter_daily_report_chart_data_table_keys($meter_list_row,$month_year_day){
	$year_month =  explode("-", $month_year_day);
	$year = $year_month[0];
	$month = $year_month[1];
	$day = $year_month[2];
	$ym = "$year-$month";
	@$query = $this->db->query("SELECT `$day` FROM daily_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_month_data`= ".$this->db->escape($ym)."");
	@$row = $query->row_array();
	if (isset($row))
	{
	return $monthly_consumption = $row["$day"];
	} 
	//@$month_year_td .= "<td>$monthly_consumption</td>";
	//endforeach; 
	//$month_year_td .= "";
	//return $month_year_td; 
	}
	*/
	public function meter_daily_report_chart_data_table_keys($meter_list_row,$month_year_day){
	$year_month =  explode("-", $month_year_day);
	$year = $year_month[0];
	$month = $year_month[1];
	$day = $year_month[2];
	$ym = "$year-$month";
	@$query = $this->db->query("SELECT `$day`,`meter_multiplier` FROM daily_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_month_data`= ".$this->db->escape($ym)."");
	@$row = $query->row_array();
	if (isset($row))
	{
	return $monthly_consumption = $row["$day"]*$row['meter_multiplier'];
	} 
	//@$month_year_td .= "<td>$monthly_consumption</td>";
	//endforeach; 
	//$month_year_td .= "";
	//return $month_year_td; 
	}
	public function meter_hourly_report_chart_data_table($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	$year_month_data;
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$dt = strtotime($year_month_row);
	$ymd =  date('Y-m-d', $dt);
	$hrs =  date('H', $dt);
	@$query = $this->db->query("SELECT `$hrs`,`sub_group`,`client_name`,`meter_multiplier` FROM hourly_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_month_day_data`= ".$this->db->escape($ymd)."");

	@$row = $query->row_array();
	if (isset($row))
	{
	$sub_group = $row['sub_group'];
	$client_name = $row['client_name'];
	$monthly_consumption = $row["$hrs"]*$row['meter_multiplier'];
	} 
	@$month_year_td .= "<td>$monthly_consumption</td>";
	endforeach; 
	$month_year_td .= "";
	return $month_year_td; 
	}
	public function meter_hourly_report_chart_data_keys($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	$year_month_data;
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$dt = strtotime($year_month_row);
	$ymd =  date('Y-m-d', $dt);
	$hrs =  date('H', $dt);
	@$query = $this->db->query("SELECT `$hrs`,`meter_multiplier` FROM hourly_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_month_day_data`= ".$this->db->escape($ymd)."");

	@$row = $query->row_array();
	if (isset($row))
	{
	$monthly_consumption = $row["$hrs"]*$row['meter_multiplier'];
	} 
	//@$month_year_td .= "<td>$monthly_consumption</td>";
	endforeach; 
	//$month_year_td .= "";
	return $monthly_consumption; 
	}
	/*ALL LOGS*/
	public function insert_meter_details_to_query($meter_id_group,
	$location_group,
	$meter_groups_cnt,
	$date_start,
	$date_end){
	/**/
	echo "$meter_id_group,
	$location_group,
	$meter_groups_cnt,
	$date_start,
	$date_end";
	}
	/*
	public function create_column_montly_report($meter_report_cols){
	$db_name = 'meter_reading';
	$table_name = 'monthly_report_chart_data_column';
	@$check_col = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND 
	TABLE_NAME = '$table_name' AND COLUMN_NAME = ".$this->db->escape($meter_report_cols)."");
	$count_res = $check_col->num_rows(); 
	//return $count_res;
	//ALTER TABLE `monthly_report_chart_data_column` ADD `ddewfr/5645/43` DOUBLE NOT NULL AFTER `id`;
	if($count_res==0){
	@$create_col = $this->db->query("ALTER TABLE $table_name ADD `$meter_report_cols` DOUBLE NOT NULL AFTER `year_month_day`");
	}
	else{
	}
	}
	*/
	public function create_monthly_report_request_data(
	$year_month,
	$year_month_date,
	$report_name_template,
	$first_date,
	$last_date,
	$chart_meter_consumption_value,
	$chart_meter_label,$chart_meter_ykeys){
	@$check_report_name = $this->db->query("SELECT * FROM `monthly_report_request_data_value` where 
	`report_name` = ".$this->db->escape($report_name_template)." and
	 `year_month` = ".$this->db->escape($year_month)." and
	  `created_by` = ".$this->db->escape($_SESSION['user_id'])."");
	  $count_res = $check_report_name->num_rows(); 
	if($count_res==0){
	$data = array(
	'report_name' => $report_name_template,
	'column_req' => $chart_meter_ykeys,
	'column_req_label' => $chart_meter_label,
	'column_req_data' => $chart_meter_consumption_value,
	'year_month' => $year_month,
	'year_month_day' => $year_month_date,
	'from_year_month' => $first_date,
	'to_year_month' => $last_date,
	'created_by' => $_SESSION['user_id']
	);
	$this->db->insert('monthly_report_request_data_value', $data);
	}
	else{
	$data = array(
	'column_req' => $chart_meter_ykeys,
	'column_req_label' => $chart_meter_label,
	'column_req_data' => $chart_meter_consumption_value,
	'from_year_month' => $first_date,
	'to_year_month' => $last_date,
	);
	$this->db->where('report_name', $report_name_template);
	$this->db->where('year_month', $year_month);
	$this->db->where('created_by',  $_SESSION['user_id']);
	$this->db->update('monthly_report_request_data_value', $data);
	}
	}

	public function check_monthly_report_request_data($datetime,$user_id){
	@$request_data = $this->db->query("SELECT * FROM `monthly_report_request_data` where 
	`report_name` = ".$this->db->escape($datetime)." and `created_by` = ".$this->db->escape($user_id)." ");
	return $request_data->row_array();
	}
	public function check_daily_report_request_data($datetime,$user_id){
	@$request_data = $this->db->query("SELECT * FROM `daily_report_request_data` where 
	`report_name` = ".$this->db->escape($datetime)." and `created_by` = ".$this->db->escape($user_id)." ");
	return $request_data->row_array();
	}
	public function check_hourly_report_request_data($datetime,$user_id){
	@$request_data = $this->db->query("SELECT * FROM `hourly_report_request_data` where 
	`report_name` = ".$this->db->escape($datetime)." and `created_by` = ".$this->db->escape($user_id)." ");
	return $request_data->row_array();
	}
	/*
	public function create_column_monthly_report_chart_data_table($year_month_col){
	$db_name = 'meter_reading';
	$table_name = 'monthly_report_chart_data_table';
	@$check_col = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND 
	TABLE_NAME = '$table_name' AND COLUMN_NAME = ".$this->db->escape($meter_report_cols)."");
	$count_res = $check_col->num_rows(); 
	//return $count_res;
	//ALTER TABLE `monthly_report_chart_data_column` ADD `ddewfr/5645/43` DOUBLE NOT NULL AFTER `id`;
	if($count_res==0){
	@$create_col = $this->db->query("ALTER TABLE $table_name ADD `$year_month_col` DOUBLE NOT NULL AFTER `meter_id`");
	}
	else{
	}
	}
	*/
	public function hourly_query_max($meter_id,$year_month_day_hours)
	{
	$this->load->helper('url');
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	//echo "<br>";
	/*NEXT DATE*/
		$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("55 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query_meter_logs_from = $this->db->query("
	SELECT 
	`datetime` as max_date,
	`wh_total` as max_wh_total,
	`wh_rec` as max_wh_rec,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." 
	order by `datetime` ASC LIMIT 0,1");
	return $query_meter_logs_from->row_array();
	}
	public function hourly_query_min($meter_id,$year_month_day_hours)
	{
	$this->load->helper('url');
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	//echo "<br>";
	/*NEXT DATE*/
		$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("65 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query_meter_logs_from = $this->db->query("
	SELECT 
	`datetime` as min_date,
	`wh_total` as min_wh_total,
	`wh_rec` as min_wh_rec,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." order by `datetime` ASC LIMIT 0,1");
	if($query_meter_logs_from->num_rows() > 0){
	return $query_meter_logs_from->row_array();
	}
	else{
	return 'NULL';
	}
	}
	public function get_location_meter_list($location_id)
	{
	$user_id = $_SESSION['user_id_sap'];

	$query = $this->db->query("select
	`meter_details`.`meter_name` AS `meter_id`,
	`meter_details`.`customer_name` AS `client_name`
	 from (`user_access_group` join `meter_details`
 	 on(((`user_access_group`.`business_entity`) = (`meter_details`.`meter_site_name`))))
	 where
	`user_access_group`.`user_id` = ".$this->db->escape($user_id)." and business_entity = ".$this->db->escape($location_id)."");
	
	/*echo "select
	`meter_details`.`meter_name` AS `meter_id`,
	`meter_details`.`customer_name` AS `client_name`,
	`meter_details`.`other_description` AS `other_description`
	 from (`user_access_group` join `meter_details`
 	 on(((`user_access_group`.`group_to_access_id`) = (`meter_details`.`meter_site_id`))))
	 where
	`user_access_group`.`user_id` = ".$this->db->escape($user_id)." and business_entity = ".$this->db->escape($location_id)."";
	*/
	
	return $query->result_array();

	}
	 
	public function get_meter_last_update($meter_name)
	{
	$this->load->helper('url');
	$query_meter_logs = $this->db->query("SELECT COALESCE(max(`datetime`),'NO DATA FOUND') as last_update FROM meter_data WHERE 
	meter_id = ".$this->db->escape($meter_name)." 
	" );
	$row_count = $query_meter_logs->num_rows();
	if($query_meter_logs->num_rows()>0)
	{
	return $query_meter_logs->row_array(); 
	}
	else
	{
	return "NO DATA FOUND";
	}
	}
	public function get_meter_detail_from_meter_data()
	{
	$user_id = $_SESSION['user_id'];
	$this->db->distinct();  
	$this->db->select('location');
	$this->db->select('meter_id');
	$this->db->select('client_name');
	$query = $this->db->get_where('user_meter_group_access', array('user_id' => $user_id));
	return $query->result_array();
	}
	
	public function logs_site_list(){
	$user_id = $_SESSION['user_id_sap'];
	
	if($_SESSION['user_access'] == 'All'){
	
	@$query = $this->db->query("SELECT site_code as business_entity FROM meter_site");
	
	}else{
	
	$this->db->select('meter_site_id');
	$this->db->select('business_entity');
	$query = $this->db->get_where('user_access_group', array('user_id' => $user_id));
	
	}
	
	return $query->result_array();
	}
	  
	public function meter_info($meter_name){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_name));
	return $query->row_array();
	}
	public function rtu_info($rtuid){
	$query = $this->db->get_where('meter_rtu', array('id' => $rtuid));
	return $query->row_array();
	}
	public function meter_all_logs_report()
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	/*QUERY METER LOGS BY METER ID DATE*/
	$query_meter_logs = $this->db->query("SELECT * FROM meter_data WHERE 
	meter_id = ".$this->db->escape($meter_id)." and 
	datetime >= ".$this->db->escape($date_start)." and
	datetime <= ".$this->db->escape($date_end)."
	" );
	$row_count = $query_meter_logs->num_rows();
	if($query_meter_logs->num_rows()>0)
	{
	return $query_meter_logs->result_array(); 
	}
	else
	{
	/*return NULL;*/
	}
	}
	/*HOURLY*/
	public function meter_hourly_report_query($meter_name,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	//$meter_name_location_split_details = explode("-", $meter_id);
	//@$meter_location = @$meter_name_location_split_details[0]; // piece1
	//@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
	/*CURRENT DATE*/
	$dt_from = ("$new_dt:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
		$dt_to = ("$new_dt:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	$query_meter_logs_from = $this->db->query("
	SELECT 
	min(`datetime`) as min_date,
	max(`datetime`) as max_date,
	min(`wh_total`) as min_wh_total,
	max(`wh_total`) as max_wh_total,
	min(`wh_rec`) as min_wh_rec,
	max(`wh_rec`) as max_wh_rec,
	((max(`wh_total`))- (min(`wh_total`))) as wh_total,
	((max(`wh_rec`))- (min(`wh_rec`))) as wh_rec_total,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)."");
	return $query_meter_logs_from->row_array();
	}
	public function hourly_query_max_tabular($meter_name,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	//$meter_name_location_split_details = explode("-", $meter_id);
	//@$meter_location = @$meter_name_location_split_details[0]; // piece1
	//@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
	/*CURRENT DATE*/
	$dt_from = ("$new_dt:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_add($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
		$dt_to = ("$new_dt:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query_meter_logs_from = $this->db->query("
	SELECT 
	`datetime` as max_date,
	`wh_total` as max_wh_total,
	`wh_rec` as max_wh_rec,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." 
	order by `datetime` ASC LIMIT 0,1");
	return $query_meter_logs_from->row_array();
	}
	public function hourly_query_min_tabular($meter_name,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);
	//@$meter_location = @$meter_name_location_split_details[0]; // piece1
	//@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
	/*CURRENT DATE*/
	$dt_from = ("$new_dt:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
		$dt_to = ("$new_dt:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query_meter_logs_from = $this->db->query("
	SELECT 
	`datetime` as min_date,
	`wh_total` as min_wh_total,
	`wh_rec` as min_wh_rec,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." order by `datetime` ASC LIMIT 0,1");
	if($query_meter_logs_from->num_rows() > 0){
	return $query_meter_logs_from->row_array();
	}
	else{
	return 'NULL';
	}
	}
	public function meter_hourly_report_to($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);
	@$meter_location = @$meter_name_location_split_details[0];
	@$meter_id_name = @$meter_name_location_split_details[1];
	$date = ($new_dt);
	$dt_from_w_time = "$date:00:00";
	$dt_to_w_time = "$date:59:59";
	$query_meter_logs_from = $this->db->query("SELECT * FROM meter_data WHERE 
	location = ".$this->db->escape($meter_location)." and 
	meter_id = ".$this->db->escape($meter_id_name)." and 
	datetime >= '$dt_from_w_time'  and 
	datetime <= '$dt_to_w_time' ORDER BY datetime DESC LIMIT 0,1 ");
	if($query_meter_logs_from->num_rows() > 0){
	return $query_meter_logs_from->row_array();
	}
	else{
	return 'NULL';
	}
	}
	/*DAILY*/
	public function meter_daily_report_query($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	//$meter_name_location_split_details = explode("-", $meter_id);
	//@$meter_location = @$meter_name_location_split_details[0]; // piece1
	//@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
	/*CURRENT DATE*/
	$dt_from = ("$new_dt 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
	$dt_to = ("$new_dt 23:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	$query_meter_logs_from = $this->db->query("
	Select 
	min(`datetime`) as min_date ,
	max(`datetime`) as max_date,
	min(`wh_total`) as min_wh_total,
	max(`wh_total`) as max_wh_total,
	min(`wh_rec`) as min_wh_rec,
	max(`wh_rec`) as max_wh_rec,
	((max(`wh_total`))- (min(`wh_total`))) as wh_total,
	((max(`wh_rec`))- (min(`wh_rec`))) as wh_rec_total,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)."");
	return $query_meter_logs_from->row_array();
	}
	public function meter_daily_report_to($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);
	@$meter_location = @$meter_name_location_split_details[0]; // piece1
	@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
	$date = ($new_dt);
	$dt_from_w_time = "$date 00:00:00";
	$dt_to_w_time = "$date 23:59:59";
	$query_meter_logs_from = $this->db->query("SELECT * FROM meter_data WHERE 
	location = ".$this->db->escape($meter_location)." and 
	meter_id = ".$this->db->escape($meter_id_name)." and 
	datetime >= '$dt_from_w_time'  and 
	datetime <= '$dt_to_w_time' ORDER BY datetime DESC LIMIT 0,1 ");
	if($query_meter_logs_from->num_rows() > 0){
	return $query_meter_logs_from->row_array();
	}
	else{
	return 'NULL';
	}
	}

	//ADD or UPDATE RTU
	public function rtu_management_action()
	{
	$this->load->helper('url');
	$this->form_validation->set_rules('rtu_sn_number', 'rtu_sn_number', 'required');
	$this->form_validation->set_rules('phone_no_or_ip_address', 'phone_no_or_ip_address', 'required');
	$this->form_validation->set_rules('connection_type', 'connection_type');
	$id = $this->input->post('rtu_id');
	$site_id = $this->input->post('site_id');
	@$data = array(
	'rtu_sn_number' => $this->input->post('rtu_sn_number'),
	'connection_type' => $this->input->post('connection_type'),
	'phone_no_or_ip_address' => $this->input->post('phone_no_or_ip_address'),
	'rtu_site_id' => $site_id,
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id'],
	'modified_by' => $_SESSION['user_name']
	);
	if ($id == 0 || $id == '') {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_rtu
	   WHERE rtu_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		rtu_sn_number = ".$this->db->escape($this->input->post('rtu_sn_number'))."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->insert('meter_rtu', $data);
	 
	$rtuid = '';
	if(form_error('rtu_sn_number')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('phone_no_or_ip_address')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('phone_no_or_ip_address') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('connection_type')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('connection_type') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$d = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $rtuid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div class='message_style'><div class='message_style_content'><strong>***RTU Already Exist(1)***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\n";
	}
	}
	else {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_rtu
	   WHERE rtu_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		rtu_sn_number = ".$this->db->escape($this->input->post('rtu_sn_number'))." and
	 id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	$rtuid = '';
	if(form_error('rtu_sn_number')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('phone_no_or_ip_address')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('phone_no_or_ip_address') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('connection_type')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('connection_type') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$d = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $rtuid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div class='message_style'><div class='message_style_content'><strong>***RTU Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\n";
	}
	}
	}
	
	public function rtu_set_update_action($enable_update)
	{
	$this->load->helper('url');

	$id = $this->input->post('rtu_id');
	@$data = array('update_rtu' => $enable_update);
		$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	if($enable_update == 1){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Update Enabled***</strong></div></div>";
	}else{
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Update Disabled***</strong></div></div>";
	}
	echo  $id . "\t" .$a . "\n";
	}
	
	public function get_selected_rtu($rtu_id){
	$query = $this->db->get_where('meter_rtu', array('id' => $rtu_id));
	return $query->row_array();
	}
	public function delete_rtu($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_rtu');
	}
	//END RTU MANAGEMENT
	public function get_meter_location_from_meter_data()
	{
	$user_id = $_SESSION['user_id_sap'];
	$user_id = $_SESSION['user_id'];
	if($_SESSION['user_access'] == 'All'){
	
	@$query_check_meter_exist = $this->db->query("SELECT site_code as business_entity FROM meter_site");
	
	}else{
	
	@$query_check_meter_exist = $this->db->query("SELECT * FROM meter_site_user WHERE user_id='$user_id'");
	
	}
	return $query_check_meter_exist->result_array(); 
	}

}