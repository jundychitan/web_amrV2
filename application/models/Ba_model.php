<?php
class Ba_model extends CI_Model {
	
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}
	
	/*USER MANAGEMENT*/
	/*START USER MANAGEMENT*/
	public function user_info(){
	$user_id = $_SESSION['user_id'];	
	$query = $this->db->get_where('user_tb', array('id' => $user_id));
	return $query->row_array();
	}	
	
	public function get_selected_user($user_id){
	$query = $this->db->get_where('user_tb', array('id' => $user_id));
	return $query->row_array();
	}
	
	public function get_selected_user_sap($user_id){
	$query = $this->db->get_where('user_tb', array('user_id_sap' => $user_id));
	return $query->row_array();
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
	
	public function delete_user_group_access($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('user_access_group');
	}	
	
	public function delete_user_meter_access($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('user_client_meter_access');
	}
	
	public function delete_user($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('user_tb');
	}
	
	/*END USER MANAGEMENT*/
	
	/*
	SITE MANAGEMENT
	START SITE MANAGEMENT
	*/
	public function get_selected_site($site_id){
	$query = $this->db->get_where('meter_site', array('id' => $site_id));
	return $query->row_array();
	}
	
	public function site_management_action()
	{
	$this->load->helper('url');
	$id = $this->input->post('site_id');
	
	$site_name = $this->input->post('site_name');
	$site_code = $this->input->post('site_code');
	$site_cut_off = $this->input->post('site_cut_off');

	$device_ip_range = $this->input->post('device_ip_range');
	$ip_netmask = $this->input->post('ip_netmask');
	$ip_gateway = $this->input->post('ip_gateway');
	
	if ($id == 0 || $id == '') {
	$data = array(
	'site_code' => $this->input->post('site_code'),
	'site_name' => $this->input->post('site_name'),
	'site_cut_off' => $this->input->post('site_cut_off'),
	'device_ip_range' => $this->input->post('device_ip_range'),
	'ip_netmask' => $this->input->post('ip_netmask'),
	'ip_gateway' => $this->input->post('ip_gateway'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id']
	);
	
	$query_check_site_name_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_name = ".$this->db->escape($site_name)."");
	$count_site_name = $query_check_site_name_exist->num_rows(); 
	
	$query_check_site_code_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_code = ".$this->db->escape($site_code)."");
	$count_site_code = $query_check_site_code_exist->num_rows(); 	
	
	if($count_site_name==0 || $count_site_code == 0){
		
	$this->db->insert('meter_site', $data);
	$_last_insert_id = $this->db->insert_id();
	$last_insert_id = "$_last_insert_id";

	$user_access_data = array(
	'user_id' => $_SESSION['user_id'],
	'business_entity' => $this->input->post('site_code'),
	'meter_site_id' => $last_insert_id,
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id']
	); 
	
	$this->db->insert('user_access_group', $user_access_data);

	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";

	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\t" . $g . "\n";
	
	}else{
		
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div class='message_style'><div class='message_style_content'><strong>***Site name/Code Already Exist***</strong></div></div>";
	
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\t" . $g . "\n";

	}
	}
	else {
		

	$query_check_site_name_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_name = ".$this->db->escape($site_name)." and id!=".$this->db->escape($id)."");
	$count_site_name = $query_check_site_name_exist->num_rows(); 
	
	$query_check_site_code_exist = $this->db->query("SELECT * FROM meter_site
	   WHERE site_code = ".$this->db->escape($site_code)." and id!=".$this->db->escape($id)."");
	$count_site_code = $query_check_site_code_exist->num_rows(); 	
	
	if($count_site_name==0 || $count_site_code ==0){
		
		
	$data_2 = array(
	'site_code' => $site_code,
	'site_name' => $site_name,
	'site_cut_off' => $site_cut_off,
	'device_ip_range' => $this->input->post('device_ip_range'),
	'ip_netmask' => $this->input->post('ip_netmask'),
	'ip_gateway' => $this->input->post('ip_gateway'),
	'modified_by' => $_SESSION['user_name'],
	'modified_by_id' => $_SESSION['user_id']);
			
	//var_dump($data_2);	
	$this->db->where('id', $id);
	$this->db->update('meter_site', $data_2);
	
	$user_access_data = array(
	'user_id' => $_SESSION['user_id'],
	'business_entity' => $this->input->post('site_code'),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	); 

	/*USER ACCESS*/
	$this->db->where('business_entity', $id);
	$this->db->update('user_access_group', $user_access_data);

	/*METER*/
	$query_update_meter_info = $this->db->query("UPDATE `meter_details` SET 
	 `meter_site_name` = ".$this->db->escape($this->input->post('site_code'))." 
	WHERE `meter_site_id` = ".$this->db->escape($id)."");
	/*RTU*/
	$query_update_meter_info = $this->db->query("UPDATE `meter_rtu` SET 
	 `rtu_site_name` = ".$this->db->escape($this->input->post('site_code'))." 
	WHERE `rtu_site_id` = ".$this->db->escape($id)."");
		
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\t" . $g . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div class='message_style'><div class='message_style_content'><strong>***Site Name/Code Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\t" . $g . "\n";
	}
	}
	}
	 
	public function delete_site($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_site');
	}
	//END SITE MANAGEMENT

	/*METER MODEL MANAGEMENT*/
	public function meter_model_list(){
	$query = $this->db->query("SELECT `id`,`meter_model`,`config_file` FROM meter_model_config order by `meter_model` ASC");		
	return $query->result_array();
	}

	//END Instantaneous ENERGY REPORT MANAGEMENT
	//START SUB-GROUP MANAGEMENT
	/*
	public function sub_group_list($siteid){
	$query = $this->db->get_where('meter_site_sub_group', array('site_id' => $siteid));

	return $query->result_array();
	}*/
	public function rtu_list($siteid){
	//$query = $this->db->select('id');
	//$query = $this->db->select('rtu_sn_number');
	//$query = $this->db->get_where('meter_rtu', array('rtu_site_id' => $siteid));
	//return $query->result_array();
	$query = $this->db->query("SELECT * FROM meter_rtu WHERE rtu_site_id = ".$this->db->escape($siteid)." order by `rtu_sn_number` ASC");		
	return $query->result_array();
	
	}
	//
	public function user_access_group_list($user_id){
	$query = $this->db->get_where('meter_site_user', array('user_id' => $user_id));
	return $query->result_array();
	}
	
	public function get_client_meter_access_list($user_id){
	$query = $this->db->get_where('user_client_meter_access', array('user_id' => $user_id));
	return $query->result_array();
	}
		
	public function site_details($siteid){
	$query = $this->db->get_where('meter_site', array('id' => $siteid));
	return $query->row_array();
	}
	public function get_selected_sub_group($sub_group_id){
	$query = $this->db->get_where('meter_site_sub_group', array('id' => $sub_group_id));
	return $query->row_array();
	}
	
	public function user_group_management_action()
	{
	$this->load->helper('url');
	//sub_group_name
	 $user_id = $this->input->post('user_id');
	 $user_group_id = $this->input->post('user_group_id');
	 
	$user_group_id_split = explode(",", $user_group_id);
	
	$business_entity = $user_group_id_split[2];
	$company_no = $user_group_id_split[1];
	$meter_site_id = $user_group_id_split[0];
	
	$data = array(
	'user_id' => $user_id,
	'meter_site_id' => $meter_site_id,
	'company_no' => $company_no,
	'business_entity' => $business_entity,
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name']
	);
	
	$query_check_username_exist = $this->db->query("SELECT * FROM user_access_group
	   WHERE user_id = ".$this->db->escape($user_id)." and
		meter_site_id = ".$this->db->escape($meter_site_id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	
	if($count_user==0){
	$this->db->insert('user_access_group', $data);
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo $a . "\t" . $h . "\n";
	}else{
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Location Already Exist***</strong></div></div>";
	echo  $a . "\t" . $h . "\n";
	}
	
	}
	
	public function user_meter_management_action()
	{
	$this->load->helper('url');
	$user_id = $this->input->post('user_id');
	$location_id = $this->input->post('location_id');
	$meter = $this->input->post('meter');
	
	$data = array(
	'user_id' => $user_id,
	'meter_name' => $meter,
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name']
	);
	$query_check_username_exist = $this->db->query("SELECT * FROM user_client_meter_access
	   WHERE user_id = ".$this->db->escape($user_id)." and
		meter_name = ".$this->db->escape($meter)."");
	$count_user = $query_check_username_exist->num_rows(); 
	
	if($count_user==0){
	$this->db->insert('user_client_meter_access', $data);
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo $a . "\t" . $b . "\t" . $c . "\n";
	}else{
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div class='message_style'><div class='message_style_content'><strong>***Location Already Exist***</strong></div></div>";
	echo $a . "\t" . $b . "\t" . $c . "\n";
	}
	}
	
	public function sub_group_management_action()
	{
	$this->load->helper('url');
	//sub_group_name
	$id = $this->input->post('sub_group_id');
	$site_id = $this->input->post('site_id');
	$sub_group_name = $this->input->post('sub_group_name');
	if ($id == 0 || $id == '') {
	$data = array(
	'site_id' => $site_id,
	'sub_group_name' => $sub_group_name,
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name']
	);
	$query_check_username_exist = $this->db->query("SELECT * FROM meter_site_sub_group
	   WHERE site_id = ".$this->db->escape($site_id)." and
		sub_group_name = ".$this->db->escape($sub_group_name)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->insert('meter_site_sub_group', $data);
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Sub-Group Name Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}
	}
	else {
	$data = array(
	'site_id' => $site_id,
	'sub_group_name' => $sub_group_name,
	'created_by' => $_SESSION['user_name'],
	'modified_by' => $_SESSION['user_name']
	);
	$query_check_username_exist = $this->db->query("SELECT * FROM meter_site_sub_group
	   WHERE site_id = ".$this->db->escape($site_id)." and
		sub_group_name = ".$this->db->escape($sub_group_name)." and
	 id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->where('id', $id);
	$this->db->update('meter_site_sub_group', $data);
	$data_meter_details = array(
	'meter_site_sub_group_name' => $sub_group_name
	);
	$this->db->where('meter_site_sub_group_id', $id);
	$this->db->update('meter_details', $data_meter_details);
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Sub-Group Name Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $h . "\n";
	}
	}
	}
	public function delete_sub_group($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_site_sub_group');
	}
	//END SUB-GROUP MANAGEMENT
	//START METER MANAGEMENT
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
	//SUB-GROUP METER LIST
	public function site_sub_group_meter_list($site_id,$meter_role)
	{
	$query = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($site_id)." and `meter_role` = ".$this->db->escape($meter_role)."	");
	return $query->result_array();
	}
	//SELECT METER
	public function get_selected_meter($meter_id){
	$query = $this->db->get_where('meter_details', array('id' => $meter_id));
	return $query->row_array();
	}
	public function get_meter_info($meter_id){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_id));
	return $query->row_array();
	}
	//DELETE METER
	public function delete_meter($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_details');
	}
	
	//ADD or UPDATE METER
	public function meter_management_action()
	{

	$this->load->helper('url');
	$id = $this->input->post('meter_id');
	$meter_addressable = $this->input->post('meter_addressable');
	$meter_addressable_id = $this->input->post('meter_addressable_id');
	$rtu_sn_number = explode(",", $this->input->post('rtu_sn_number'));
	$meter_model = explode(",", $this->input->post('meter_model'));
	$other_description = $this->input->post('other_description');
	$customer_name = $this->input->post('client_name');	
	$meter_role = $this->input->post('meter_role');
	$physical_location = $this->input->post('physical_location');
	$meter_seil = $this->input->post('meter_seil');	
	
	if($meter_addressable==1){ $meter_default_name = $this->input->post('serial_number'); }else{ $meter_default_name = $meter_addressable_id; }
		
	if ($id == 0 || $id == '') {
		
	@$data = array(
	'customer_name' => $customer_name,
	'physical_location' => $physical_location,
	'meter_site_id' => $this->input->post('site_id'),
	'meter_site_name' => $this->input->post('site_name'),
	'rtu_sn_number_id' => @$rtu_sn_number[0],
	'rtu_sn_number' => @$rtu_sn_number[1],
	'meter_name' => $this->input->post('serial_number'),
	'meter_seil' => $this->input->post('meter_seil'),
	'meter_name_addressable' => $meter_addressable,
	'meter_default_name' => $meter_default_name,
	'meter_multiplier' => $this->input->post('meter_multiplier'),
	'meter_model_id' => @$meter_model[0],
	'meter_model' => @$meter_model[1],
	'meter_config_file' => @$meter_model[2],
	'meter_type' => $this->input->post('meter_type'),
	'meter_status' => $this->input->post('meter_status'),
	'meter_role' => $this->input->post('meter_role'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id'],
	'modified_by' => $_SESSION['user_name']
	);
		
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		meter_name = ".$this->db->escape($this->input->post('serial_number'))."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	$this->db->insert('meter_details', $data);
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('physical_location')!=''){ $i = "<div style='color:red; display:inline-flex;'>" . form_error('physical_location') . "</div>"; } else{ $i = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$j = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$i = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$j = "<div class='message_style'><div class='message_style_content'><strong>***Meter Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	}
	}
	else {

	@$data = array(
	'customer_name' => $customer_name,
	'physical_location' => $physical_location,
	'meter_site_id' => $this->input->post('site_id'),
	'meter_site_name' => $this->input->post('site_name'),
	'rtu_sn_number_id' => @$rtu_sn_number[0],
	'rtu_sn_number' => @$rtu_sn_number[1],
	'meter_name' => $this->input->post('serial_number'),
	'meter_seil' => $this->input->post('meter_seil'),
	'meter_name_addressable' => $meter_addressable,
	'meter_default_name' => $meter_default_name,
	'meter_multiplier' => $this->input->post('meter_multiplier'),
	'meter_model_id' => @$meter_model[0],
	'meter_model' => @$meter_model[1],
	'meter_config_file' => @$meter_model[2],
	'meter_type' => $this->input->post('meter_type'),
	'meter_status' => $this->input->post('meter_status'),
	'meter_role' => $this->input->post('meter_role'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id'],
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	);
		
		
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_details
	   WHERE meter_site_id = ".$this->db->escape($this->input->post('site_id'))." and
		meter_name = ".$this->db->escape($this->input->post('serial_number'))." and
	 id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
	
	$this->db->where('id', $id);
	$this->db->update('meter_details', $data);

	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('physical_location')!=''){ $i = "<div style='color:red; display:inline-flex;'>" . form_error('physical_location') . "</div>"; } else{ $i = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$j = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$d = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$e = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$f = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$g = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$i = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$j = "<div class='message_style'><div class='message_style_content'><strong>***Meter Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	}
	}
	 
	}
	//END METER MANAGEMENT
	//ADD or UPDATE RTU
	public function rtu_management_action()
	{
	$this->load->helper('url');
	//$this->form_validation->set_rules('gateway_description', 'Gateway Description');
	
	$id = $this->input->post('rtu_id');
	$gateway_description = $this->input->post('gateway_description');
			
	@$data = array(
	'gateway_description' => $this->input->post('gateway_description'),
	'created_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id'],
	'modified_by' => $_SESSION['user_name']
	);	
		
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);

	$rtuid = ''; 
	if(form_error('gateway_description')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('gateway_description') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }	
	
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";

	echo  $rtuid . "\t" .$a . "\t" .$i . "\n";
	
	}
	
	public function rtu_set_update_action($enable_update)
	{
	$this->load->helper('url');

	$id = $this->input->post('rtu_id');
	@$data = array('update_rtu' => $enable_update);
		
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	
	if($enable_update == 1){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***CSV Update Enabled***</strong></div></div>";
	}else{
	$a = "<div class='message_style'><div class='message_style_content'><strong>***CSV Update Disabled***</strong></div></div>";
	}
	echo  $id . "\t" .$a . "\n";

	}
	
	public function rtu_location_set_update_action($enable_update)
	{
	$this->load->helper('url');

	$id = $this->input->post('rtu_id');
	@$data = array('update_rtu_location' => $enable_update);
		
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	
	if($enable_update == 1){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Location Update Enabled***</strong></div></div>";
	}else{
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Location Update Disabled***</strong></div></div>";
	}
	echo  $id . "\t" .$a . "\n";

	}
	
	public function rtu_remote_ssh_action($enable_update)
	{
	$this->load->helper('url');

	$id = $this->input->post('rtu_id');
	@$data = array('update_rtu_ssh' => $enable_update);
		
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	
	if($enable_update == 1){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Remote SSH Enabled***</strong></div></div>";
	}else{
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Remote SSH Disabled***</strong></div></div>";
	}
	echo  $id . "\t" .$a . "\n";

	}
	
	/*FORCE LOAD PROFILE*/
	public function rtu_force_load_profile_action($enable_update)
	{
	$this->load->helper('url');

	$id = $this->input->post('rtu_id');
	@$data = array('update_rtu_force_lp' => $enable_update);
		
	$this->db->where('id', $id);
	$this->db->update('meter_rtu', $data);
	
	if($enable_update == 1){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Force LP Enabled***</strong></div></div>";
	}else{
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Forde LP Disabled***</strong></div></div>";
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
	
	/*METER MODEL*/
	public function meter_model_management_action()
	{
	$this->load->helper('url');
	$this->form_validation->set_rules('meter_model', 'Meter Model', 'required');
	$this->form_validation->set_rules('config_file', 'Configuration File', 'required');
	
	$id = $this->input->post('meter_model_id');

	if ($id == 0 || $id == '') {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_model_config
	   WHERE meter_model = ".$this->db->escape($this->input->post('meter_model'))."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
		
	@$data = array(
	'meter_model' => $this->input->post('meter_model'),
	'config_file' => $this->input->post('config_file'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'added_by' => $_SESSION['user_id'],
	);
		
	$this->db->insert('meter_model_config', $data);
	 
	$id = '';
	if(form_error('meter_model')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	if(form_error('config_file')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('config_file') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$c = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\n";
	}else{
	$id = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div class='message_style'><div class='message_style_content'><strong>***RTU Already Exist***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\n";
	}
	}
	else {
	@$query_check_username_exist = $this->db->query("SELECT * FROM meter_model_config
	   WHERE meter_model = ".$this->db->escape($this->input->post('meter_model'))." and
		 
	 id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){
			
	@$data = array(
	'meter_model' => $this->input->post('meter_model'),
	'config_file' => $this->input->post('config_file'),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s")
	);
		
	$this->db->where('id', $id);
	$this->db->update('meter_model_config', $data);
	
	$query_update_meter_info = $this->db->query("UPDATE `meter_details` SET 
	`meter_model` = ".$this->db->escape($this->input->post('meter_model')).",
	 `meter_config_file` = ".$this->db->escape($this->input->post('config_file'))." 
	WHERE `meter_model_id` = ".$this->db->escape($id)."");
	 
	$id = '';
	if(form_error('meter_model')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	if(form_error('config_file')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('config_file') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$c = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\n";

	}else{
	$rtuid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$c = "<div class='message_style'><div class='message_style_content'><strong>***RTU Already Exist***</strong></div></div>";
	echo  $rtuid . "\t" .$a . "\t" .$b . "\t" .$c . "\n";
	}
	}
	}
	
	public function get_selected_meter_model($id){
	$query = $this->db->get_where('meter_model_config', array('id' => $id));
	return $query->row_array();
	}
	 
	public function delete_meter_model($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('meter_model_config');
	}
	
	
	//END RTU MANAGEMENT
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
	 
	public function logs_site_list(){
	$user_id = $_SESSION['user_id'];
	//$this->db->select('business_entity');
	//$this->db->select('meter_site_id'); 
	//$query = $this->db->get_where('user_access_group', array('user_id' => $user_id));
	//return $query->result_array();
	
	@$query_check_meter_exist = $this->db->query("SELECT id as meter_site_id, site_code as business_entity FROM meter_site");
	return $query_check_meter_exist->result_array(); 
	
	
	}
	 
	public function client_site_list(){
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
	

	public function get_meter_data_group_end($meter_id_group,$location_group,$meter_groups_cnt,$date_start,$date_end){
	$query = $this->db->query("SELECT * 
	from meter_data_view WHERE meter_site_sub_group_id=" . $this->db->escape($meter_groups_cnt) . " and
	location=" . $this->db->escape($location_group) . " and 
	meter_id=" . $this->db->escape($meter_id_group) . " and 
	datetime <= " . $this->db->escape($date_end) . "
	LIMIT 0,1");
	return $query->result_array();
	}
	public function get_meter_details_report($meter_id_group,$location_group){
	//echo "$location_group";
	@$query_check_meter_exist = $this->db->query("SELECT * FROM meter_details
	WHERE meter_name = ".$this->db->escape($meter_id_group)." and meter_site_name = ".$this->db->escape($location_group)."");
	return $query_check_meter_exist->result_array(); 
	}
	
	public function monthly_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	@$query_check_meter_exist = $this->db->query("SELECT * FROM monthly_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	public function daily_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	@$query_check_meter_exist = $this->db->query("SELECT * FROM daily_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	public function hourly_report_barchart($location,$datetime,$user_id,$cols,$dt_from,$dt_to){
	@$query_check_meter_exist = $this->db->query("SELECT * FROM hourly_report_request_data_value where 
	`report_name` = ".$this->db->escape($datetime)."");
	return $query_check_meter_exist->result_array(); 
	}
	
	public function meter_monthly_report_chart_data_table($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	
	@$cols .= "";
	
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	
	@$cols .= "(   
    select (`$month`*meter_multiplier) as `$month` from monthly_report_chart_data_table where year_data='$year' and `meter_id`='$meter_list_row'
    ) AS `$month`,";	
	 	
	endforeach; 

	@$cols= rtrim($cols,",");	
	@$cols .= ";";
	$cols_explode =  explode(";", $cols);

	foreach ($cols_explode as $columns):

	@$query = $this->db->query("SELECT  
	$columns
	");
	

	@$row = $query->row_array();
	if (isset($row))
	{
		foreach ($row as $row_cols):
		//number_format(0, 2, '.', '');
		 $monthly_consumption = number_format($row_cols, 2, '.', '');;
		 @$month_year_td .= "<td>$monthly_consumption</td>";
		
		endforeach;
	
	} 
	$month_year_td .= "";
	return $month_year_td; 
	endforeach; 

	}

	public function meter_monthly_report_chart_data_keys($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	foreach ($year_month_arr as $year_month_row):
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	@$query = $this->db->query("SELECT `$month`,`meter_multiplier` FROM monthly_report_chart_data_table where 
	`meter_id` = ".$this->db->escape($meter_list_row)." and
	 `year_data`= ".$this->db->escape($year)."");
	$row = $query->row_array();
	if (isset($row))
	{
	$monthly_consumption = $row["$month"]*$row['meter_multiplier'];
	} 
	endforeach; 
	return $monthly_consumption; 
	}


	public function meter_daily_report_chart_data_table($meter_list_row,$year_month_data){
//	ERROR COMPARE TWO DATES IF THE SAME
	$year_month_arr =  explode(",", $year_month_data);
	$year_month_data;
	
	@$cols .= "";
	foreach ($year_month_arr as $year_month_row):
	
	$year_month =  explode("-", $year_month_row);
	$year = $year_month[0];
	$month = $year_month[1];
	$day = $year_month[2];
	$ym = "$year-$month";

	@$cols .= "(   
    select (`$day`*meter_multiplier) as `$day` from daily_report_chart_data_table where year_month_data='$ym' and `meter_id`='$meter_list_row'
    ) AS `$day`,";	
	endforeach; 	
	
	@$cols= rtrim($cols,",");	
	@$cols .= ";";
	$cols_explode =  explode(";", $cols);

	foreach ($cols_explode as $columns):

	@$query = $this->db->query("SELECT  
	$columns
	");
	
	@$row = $query->row_array();
	if (isset($row))
	{
		foreach ($row as $row_cols):
		
		 $monthly_consumption = $row_cols;
		 @$month_year_td .= "<td>$monthly_consumption</td>";
		
		endforeach;
	
	} 
	$month_year_td .= "";
	return $month_year_td; 
	endforeach; 
	}
	
	public function meter_daily_report_chart_data_table_v2($meter_list_row,$month_year_day){
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
	
	}
	
	public function meter_hourly_report_chart_data_table($meter_list_row,$year_month_data){
	$year_month_arr =  explode(",", $year_month_data);
	$year_month_data;

	@$cols .= "";	
	foreach ($year_month_arr as $year_month_row):
	
	$year_month =  explode("-", $year_month_row);
	$dt = strtotime($year_month_row);
	$ymd =  date('Y-m-d', $dt);
	$hrs =  date('H', $dt);
	
	//@$cols .= "(`$hrs`*`meter_multiplier`) as `$hrs`,";	
	@$cols .= "(   
    select (`$hrs`*meter_multiplier) as `$hrs` from hourly_report_chart_data_table where year_month_day_data='$ymd' and `meter_id`='$meter_list_row'
    ) AS `$hrs`,";	

	endforeach; 

	@$cols= rtrim($cols,",");	
	@$cols .= ";";
	$cols_explode =  explode(";", $cols);

	foreach ($cols_explode as $columns):
	
	@$query = $this->db->query("SELECT  
	$columns
	");

	@$row = $query->row_array();

	if (isset($row))
	{
		foreach ($row as $row_cols):
		 $hourly_consumption = $row_cols;
		 @$hourly_consumption_td .= "<td>$hourly_consumption</td>";		
		endforeach;
	
	} 	
	$hourly_consumption_td .= "";
	return $hourly_consumption_td; 
	endforeach; 

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
	echo "$meter_id_group,
	$location_group,
	$meter_groups_cnt,
	$date_start,
	$date_end";
	}

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

	
	
	public function create_hourly_report_request_data(
	$year_month_day_hours,
	$report_name_template,
	$chart_meter_consumption_value,
	$chart_meter_label,
	$chart_meter_ykeys){
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$first_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
		$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("55 minutes"));
	$last_date = date_format($date2,"Y-m-d H:i:s");
	/**/
	@$check_report_name = $this->db->query("SELECT * FROM `hourly_report_request_data_value` where 
	`report_name` = ".$this->db->escape($report_name_template)." and
	 `year_month` = ".$this->db->escape($year_month_day_hours)." and
	  `created_by` = ".$this->db->escape($_SESSION['user_id'])."");
	  $count_res = $check_report_name->num_rows(); 
	if($count_res==0){
	$data = array(
	'report_name' => $report_name_template,
	'column_req' => $chart_meter_ykeys,
	'column_req_label' => $chart_meter_label,
	'column_req_data' => $chart_meter_consumption_value,
	'year_month' => $year_month_day_hours,
	'year_month_day' => $year_month_day_hours,
	'from_year_month' => $first_date,
	'to_year_month' => $last_date,
	'created_by' => $_SESSION['user_id']
	);
	$this->db->insert('hourly_report_request_data_value', $data);
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
	$this->db->where('year_month', $year_month_day_hours);
	$this->db->where('created_by',  $_SESSION['user_id']);
	$this->db->update('hourly_report_request_data_value', $data);
	}
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

	public function get_location_meter_list($location_id)
	{
		
		$user_id = $_SESSION['user_id'];
	
		$query = $this->db->query("select
		`meter_details`.`meter_name` AS `meter_id`,
		`meter_details`.`customer_name` AS `customer_name`
		 from `meter_details`
		 where
		 meter_site_name = ".$this->db->escape($location_id)."");
		
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

	public function get_meter_location_from_meter_data()
	{
	$user_id = $_SESSION['user_id'];
	//echo $_SESSION['user_access'];
	//echo "SELECT * FROM meter_site_user WHERE user_id='$user_id'";
	if($_SESSION['user_access'] == 'All'){
	
	@$query_check_meter_exist = $this->db->query("SELECT site_code as business_entity FROM meter_site");
	
	}else{
	
	@$query_check_meter_exist = $this->db->query("SELECT * FROM meter_site_user WHERE user_id='$user_id'");
	
	}
	return $query_check_meter_exist->result_array(); 
	
	
	}

	public function check_rtu_duplicate_info($rtu_id,$rtu_sn_number,$mac_addr){
	
	if($rtu_id == 0 || $rtu_id == ''){

	$query = $this->db->query("SELECT `rtu_site_name`,`rtu_sn_number`,`mac_addr`,`id` FROM meter_rtu
	where rtu_sn_number like ".$this->db->escape("%$rtu_sn_number%")." and mac_addr like ".$this->db->escape("%$mac_addr%")."");

	}else{

	$query = $this->db->query("SELECT `rtu_site_name`,`rtu_sn_number`,`mac_addr`,`id` FROM meter_rtu
	where rtu_sn_number like ".$this->db->escape("%$rtu_sn_number%")." or mac_addr like ".$this->db->escape("%$mac_addr%")."
	 and id != ".$this->db->escape($rtu_id)."");

	}
	return $query->row_array();
	
	}

	public function check_site_duplicate_info($site_id,$site_name,$site_code){
	
	if($site_id == 0 || $site_id == ''){

	$query = $this->db->query("SELECT `site_name`,`id`,site_code FROM meter_site
	where site_name like ".$this->db->escape("%$site_name%")." or 
	 site_code like ".$this->db->escape("%$site_code%")."");

	}else{

	$query = $this->db->query("SELECT `site_name`,`id`,site_code FROM meter_site
	where site_name like ".$this->db->escape("%$site_name%")." or 
	 site_code like ".$this->db->escape("%$site_code%")."
	 and id != ".$this->db->escape($site_id)."");
	
	}
	return $query->row_array();
	
	}

	public function get_selected_site_by_code($meter_location){
	$query = $this->db->get_where('meter_site', array('site_code' => $meter_location));
	return $query->row_array();
	}
}