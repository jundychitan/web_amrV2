<?php
class Admin_client_model extends CI_Model {
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}
	/*
	CLIENT MANAGEMENT
	START CLIENT MANAGEMENT
	*/
	public function get_selected_client($client_id){
	$query = $this->db->get_where('client_tb', array('id' => $client_id));
	return $query->row_array();
	}
	
	public function get_client_list(){
	$query = $this->db->query("SELECT `id`,`client_name`,`client_account_no` FROM client_tb");
	return $query->result_array();
	}

	public function client_management_action()
	{
	$this->load->helper('url');
	$id = $this->input->post('client_id');
	$client_name = $this->input->post('client_name');
	$client_account_no = $this->input->post('client_account_no');
	if ($id == 0 || $id == '') {

	$query_check_username_exist = $this->db->query("SELECT * FROM client_tb
	   WHERE client_name = ".$this->db->escape($client_name)." and client_account_no = ".$this->db->escape($client_account_no)." ");
	$count_user = $query_check_username_exist->num_rows(); 
	
	if($count_user==0){

	$data = array(
	'client_name' => $this->input->post('client_name'),
	'client_account_no' => $this->input->post('client_account_no'),
	'created_by' => $_SESSION['user_name'],
	'date_created' => date("Y-m-d H:i:s"),
	'modified_by' => $_SESSION['user_name'],
	'added_by' => $_SESSION['user_id']
	);
		
	$this->db->insert('client_tb', $data);
	$_last_insert_id = $this->db->insert_id();
	$last_insert_id = "$_last_insert_id";
	
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Save***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Client Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}
	}
	else {

	$data = array(
	'client_name' => $this->input->post('client_name'),
	'client_account_no' => $this->input->post('client_account_no'),
	'modified_by' => $_SESSION['user_name'],
	'date_modified' => date("Y-m-d H:i:s"),
	'modified_by_id' => $_SESSION['user_id']
	);

	$query_check_username_exist = $this->db->query("SELECT * FROM client_tb
	   WHERE client_name = ".$this->db->escape($client_name)."  and 
	   client_account_no = ".$this->db->escape($client_account_no)." and id!=".$this->db->escape($id)."");
	$count_user = $query_check_username_exist->num_rows(); 
	if($count_user==0){

	$this->db->where('id', $id);
	$this->db->update('client_tb', $data);

	$query_update_meter_info = $this->db->query("UPDATE `meter_details` SET 
	`client_name` = ".$this->db->escape($client_name).",
	 `client_account_no` = ".$this->db->escape($client_account_no)." 
	WHERE `client_id` = ".$this->db->escape($id)."");
		
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Updated***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}else{
	$userid = '';
	$a = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$b = "<div style='color:green; display:inline-flex;'>&#10004;</div>";
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Client Already Exist***</strong></div></div>";
	echo  $userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}
	}
	}
	 
	public function delete_client($id)
	{
	$this->db->where('id', $id);
	return $this->db->delete('client_tb');
	}
	//END CLIENT MANAGEMENT

}