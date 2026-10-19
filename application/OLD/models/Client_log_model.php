<?php
	class Client_log_model extends CI_Model {
	
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}

	/*GET METER INFO*/
	public function get_meter_info($meter_id){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_id));
	return $query->row_array();
	}
	
	/*ALL LOGS*/
	public function meter_all_logs_report()
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');

	/*
	QUERY METER LOGS
	QUERY METER LOGS BY LOCATION METER ID DATE
	*/
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
	public function meter_hourly_report_query($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);
	@$meter_location = @$meter_name_location_split_details[0]; // piece1
	@$meter_id_name = @$meter_name_location_split_details[1]; // piece2
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
	((max(`wh_total`))- (min(`wh_total`))) as wh_total,
	`location`,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)."");
	 
	 
	return $query_meter_logs_from->row_array();
	}
	public function hourly_query_max_tabular($meter_id,$new_dt)
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
	public function hourly_query_min_tabular($meter_id,$new_dt)
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
	
}