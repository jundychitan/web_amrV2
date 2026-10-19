<?php
class Admin_chart_log_model extends CI_Model {
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	$this->load->helper('url');
	}
	
	/*Get Meter Info*/
	public function meter_info($meter_name){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_name));
	return $query->row_array();
	}

	
	public function get_meter_data_monthly($meter_id,$first_date,$last_date){
	
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
	
	/*ADDED 24 hours and 10 minutes*/
	date_add($date2,date_interval_create_from_date_string("1435 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	/*
	
	*/
	$query = $this->db->query("Select min(`wh_total`) as min_wh_total, min(`datetime`) as min_date ,
	max(`wh_total`) as max_wh_total, max(`datetime`) as max_date, ((max(`wh_total`))- (min(`wh_total`))) as wh_total, `meter_id`
	from meter_data where `datetime`>=".$this->db->escape($start_date)." and `datetime`<=".$this->db->escape($end_date)."
	 and `meter_id`=".$this->db->escape($meter_id)."");
	return $query->row_array();
	}
	
	public function monthly_report_chart_data_table_create($year_month,$meter_id,$consumption,$meter_multiplier){
	
	$year_month_spl = (explode("-",$year_month));
	$month_col = "`$year_month_spl[1]`";
	$year_col = $year_month_spl[0];
	$year_month_col = "$year_col/$month_col";
	
	@$check_col = $this->db->query("SELECT * FROM `monthly_report_chart_data_table` where `meter_id` = ".$this->db->escape($meter_id)." and `year_data` = ".$this->db->escape($year_col)."");
	$count_res = $check_col->num_rows(); 
	
	if($count_res==0){
		
	$data = array('meter_id' => $meter_id,'year_data' => $year_col,'meter_multiplier' => $meter_multiplier,"$month_col" => $consumption);
	$this->db->insert('monthly_report_chart_data_table', $data);
	
	}
	else{
	
	$data = array("$month_col" => $consumption, 'meter_multiplier' => $meter_multiplier);
	$this->db->where('meter_id', $meter_id);
	$this->db->where('year_data', $year_col);
	$this->db->update('monthly_report_chart_data_table', $data);
	
	//$this->db->query("SELECT * FROM `monthly_report_chart_data_table` where `meter_id` = ".$this->db->escape($meter_id)." and `year_data` = ".$this->db->escape($year_col)."");
	//`monthly_report_chart_data_table` SET `meter_multiplier` = '1', WHERE `monthly_report_chart_data_table`.`id` = 15;
	
	}
	}
	
	public function create_monthly_report_request_data_info(
	$year_month_selected_value,
	$report_name_template,
	$date_start,
	$date_end,
	$chart_meter_ykeys,
	$chart_meter_label){
		
	$data = array(
	'report_name' => $report_name_template,
	'chart_meter_ykeys' => $chart_meter_ykeys,
	'chart_meter_label' => $chart_meter_label,
	'year_month' => $year_month_selected_value,
	'from_year_month_day' => $date_start,
	'to_year_month_day' => $date_end,
	'created_by' => $_SESSION['user_id']
	);
	
	$this->db->insert('monthly_report_request_data', $data);
	
	}
	
	public function get_meter_data_daily($meter_id,$year_month_day){
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day");
	$dt_from_w_time = "$dt_from";
	
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	
	/*NEXT DATE*/
	$dt_to = ("$year_month_day");
	$dt_to_w_time = "$dt_to";
	
	/*1 day = 1440 minutes*/
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("1435 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	echo "$start_date $end_date<br>";
	/*
	"Select min(`wh_total`) as min_wh_total, min(`datetime`) as min_date, max(`wh_total`) as max_wh_total, max(`datetime`) as max_date, ((max(`wh_total`))- (min(`wh_total`))) as wh_total,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id).""
	*/
	
	$query = $this->db->query("SELECT
	(	
	SELECT 	 
	`wh_total` as min_wh_total
	from `meter_data` where  
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." order by `datetime` ASC LIMIT 0,1   
	) as `min_wh_total`,
	(SELECT 	 
	`datetime` as min_date
	from `meter_data` where  
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." order by `datetime` ASC LIMIT 0,1   
	) as `min_date`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." order by datetime ASC LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime` as max_date
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_date`");
	
	return $query->row_array();
	
	}
	
	public function daily_report_chart_data_table_create($year_month_daily,$meter_id,$consumption,$meter_multiplier){
	
	$year_month_spl = (explode("-",$year_month_daily));
	$daily_col = "$year_month_spl[2]";
	$month_col = "$year_month_spl[1]";
	$year_col = $year_month_spl[0];
	$year_month_col = "$year_col/$month_col/$daily_col";
	$year_month_col_data = "$year_col-$month_col";
	
	@$check_col = $this->db->query("SELECT * FROM `daily_report_chart_data_table` where 
	`meter_id` = ".$this->db->escape($meter_id)." and `year_month_data` = ".$this->db->escape($year_month_col_data)."");
	$count_res = $check_col->num_rows(); 
	
	/*Insert*/
	if($count_res==0){
	
	$data = array('meter_id' => $meter_id,'year_month_data' => $year_month_col_data,'meter_multiplier' => $meter_multiplier,"`$daily_col`" => $consumption);
	$this->db->insert('daily_report_chart_data_table', $data);

	}
	/*Update Column*/
	else{
	$data = array("`$daily_col`" => $consumption);

	$this->db->where('meter_id', $meter_id);
	$this->db->where('year_month_data', $year_month_col_data);
	$this->db->update('daily_report_chart_data_table', $data);

	}
	}
	
	public function create_daily_report_request_data_info($year_month_selected_value,$report_name_template,$date_start,$date_end,$chart_meter_ykeys,$chart_meter_label){
	
	$data = array(
	'report_name' => $report_name_template,
	'chart_meter_ykeys' => $chart_meter_ykeys,
	'chart_meter_label' => $chart_meter_label,
	'year_month' => $year_month_selected_value,
	'from_year_month_day' => $date_start,
	'to_year_month_day' => $date_end,
	'created_by' => $_SESSION['user_id']
	);
	
	$this->db->insert('daily_report_request_data', $data);
	
	}
	
	/*Hourly*/
	public function get_meter_data_hourly($meter_id,$year_month_day_hours){
	
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	
	/*NEXT DATE*/
	$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("65 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	
	$query = $this->db->query("Select min(`datetime`) as min_date ,max(`datetime`) as max_date,
	 min(`wh_total`) as min_wh_total,
	 max(`wh_total`) as max_wh_total,
	((max(`wh_total`)) - (min(`wh_total`))) as wh_total,
	`meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`datetime`<=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)."");
	return $query->row_array();
	}
	
	public function hourly_query_max($meter_id,$year_month_day_hours)
	{
	
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	
	/*NEXT DATE*/
	$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("55 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	
	$query_meter_logs_from = $this->db->query("SELECT 
	`datetime` as max_date, `wh_total` as max_wh_total, `wh_rec` as max_wh_rec,
	`location`, `meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." 
	order by `datetime` ASC LIMIT 0,1");
	return $query_meter_logs_from->row_array();
	
	}
	
	public function hourly_query_min($meter_id,$year_month_day_hours)
	{
	
	/*CURRENT DATE*/
	$dt_from = ("$year_month_day_hours");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	
	/*NEXT DATE*/
	$dt_to = ("$year_month_day_hours");
	$dt_to_w_time = "$dt_to";
	
	// 1 day = 1440 minutes
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("65 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	
	$query_meter_logs_from = $this->db->query("
	SELECT `datetime` as min_date, `wh_total` as min_wh_total, `wh_rec` as min_wh_rec,
	`location`, `meter_id` from meter_data where 
	`datetime`>=".$this->db->escape($start_date)." and
	`meter_id`=".$this->db->escape($meter_id)." order by `datetime` ASC LIMIT 0,1");
	if($query_meter_logs_from->num_rows() > 0){
	return $query_meter_logs_from->row_array();
	
	}
	else{
	return 'NULL';
	}
	}
	
	public function hourly_report_chart_data_table_create($year_month_day,$meter_id,$consumption,$hour_col,$meter_multiplier){
	
	$year_month_spl = (explode("-",$year_month_day));
	$daily_col = "$year_month_spl[2]";
	$month_col = "$year_month_spl[1]";
	$year_col = $year_month_spl[0];
	$year_month_col = "$year_col/$month_col/$daily_col";
	$year_month_col_data = "$year_col-$month_col-$daily_col";
	
	@$check_col = $this->db->query("SELECT * FROM `hourly_report_chart_data_table` where 
	`meter_id` = ".$this->db->escape($meter_id)." and `year_month_day_data` = ".$this->db->escape($year_month_col_data)."");
	$count_res = $check_col->num_rows(); 
	
	if($count_res==0){
	$data = array('meter_id' => $meter_id,'year_month_day_data' => $year_month_col_data,'meter_multiplier' => $meter_multiplier,"`$hour_col`" => $consumption);
	$this->db->insert('hourly_report_chart_data_table', $data);
	}
	else{
	
	$data = array("`$hour_col`" => $consumption);
	
	$this->db->where('meter_id', $meter_id);
	$this->db->where('year_month_day_data', $year_month_col_data);
	$this->db->update('hourly_report_chart_data_table', $data);
	}
	}
	
	public function create_hourly_report_request_data_info($year_month_selected_value,$report_name_template,
	$date_start,$date_end,
	$chart_meter_ykeys,$chart_meter_label){
	
	$data = array(
	'report_name' => $report_name_template,
	'chart_meter_ykeys' => $chart_meter_ykeys,
	'chart_meter_label' => $chart_meter_label,
	'year_month_day_hour' => $year_month_selected_value,
	'from_year_month_day' => $date_start,
	'to_year_month_day' => $date_end,
	'created_by' => $_SESSION['user_id']
	);
	
	$this->db->insert('hourly_report_request_data', $data);
	$error = $this->db->error();

	}

	
}