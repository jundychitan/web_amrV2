<?php
	class Site_admin_log_model extends CI_Model {
	
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
	$date_log = explode(' - ', $this->input->post('date_log'));
	$date_start = $date_log[0];
	$date_end = $date_log[1];	
	/*
	QUERY METER LOGS
	QUERY METER LOGS BY LOCATION METER ID DATE
	*/
	$query_meter_logs = $this->db->query("SELECT * FROM meter_data WHERE 
	meter_id = ".$this->db->escape($meter_id)." and 
	datetime >= ".$this->db->escape($date_start)." and
	datetime <= ".$this->db->escape($date_end)."
	order by datetime ASC
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
	
	public function fifteen_minutes_query($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);

	/*TEST*/
	$dt_from_A = ("$new_dt");
	$dt_from_w_time_A = "$dt_from_A";
	$date1_A=date_create($dt_from_w_time_A);
	date_sub($date1_A,date_interval_create_from_date_string("16 minutes"));
	$start_date_A = date_format($date1_A,"Y-m-d H:i:s"); 
	//echo $start_date_A;
	/*CURRENT DATE MIN*/
	$dt_from = ("$new_dt");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("1 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s"); 
		
	/*NEXT DATE MAX*/
	$dt_to = ("$new_dt");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("14 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	$query_meter_logs_from = $this->db->query("SELECT 
	(	
	SELECT 	
	(`wh_total`) as min_wh_total
	from `meter_data`
	USE INDEX(idx_name)
	 where 
	/*`datetime`>=".$this->db->escape($start_date_A)." and*/
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." ORDER BY `datetime` DESC LIMIT 0,1
	) as `min_wh_total`,
	(	
	SELECT 	
	(`datetime`)
	from `meter_data` 
	USE INDEX(idx_name)
	where 
	/*`datetime`>=".$this->db->escape($start_date_A)." and*/
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." ORDER BY `datetime` DESC LIMIT 0,1
	) as `min_datetime`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data` 
	USE INDEX(idx_name)
	where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime`
	from `meter_data` 
	USE INDEX(idx_name)
	where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1 
    ) AS `max_datetime`");


	return $query_meter_logs_from->row_array();
	
	}	
		
	/*HOURLY*/
	public function hourly_query($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	$date_start = $this->input->post('date_start');
	$date_end = $this->input->post('date_end');
	$meter_name_location_split_details = explode("-", $meter_id);

	/*CURRENT DATE MIN*/
	$dt_from = ("$new_dt:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	
	/*NEXT DATE MAX*/
	$dt_to = ("$new_dt:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	$query_meter_logs_from = $this->db->query("SELECT 
	(	
	SELECT 	
	`wh_total` as min_wh_total
	from `meter_data` where 
	`datetime`>=".$this->db->escape($start_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
	) as `min_wh_total`,
	(	
	SELECT 	
	`datetime`
	from `meter_data` where 
	`datetime`>=".$this->db->escape($start_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
	) as `min_datetime`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime`
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_datetime`");
	
	return $query_meter_logs_from->row_array();
	}	
	
	/*DAILY*/
	public function meter_daily_report_query($meter_id,$new_dt)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');

	/*CURRENT DATE*/
	$dt_from = ("$new_dt");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
	$dt_to = (date ("Y-m-d", strtotime($new_dt)));
	$dt_to_w_time = "$dt_to 23:59:59";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("5 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
		 
	$query_meter_logs_from = $this->db->query("SELECT
	(	
	SELECT 	 
	`wh_total` as min_wh_total
	from `meter_data` where  
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1   
	) as `min_wh_total`,
	(SELECT 	 
	`datetime` as min_date
	from `meter_data` where  
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1   
	) as `min_date`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime` as max_date
	from `meter_data` where 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_date`");	
	
	return $query_meter_logs_from->row_array();
	}	

	public function meter_location_report_query_procedure_limit($meter_location, $date_start, $date_end, $meter_role)
	{
	$this->load->helper('url');
	//ini_set('memory_limit','100M');

	/*CURRENT DATE*/
	$dt_from = ("$date_start 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
	$dt_to = ("$date_end 23:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("6 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	  $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`measuring_point`,
			(
			SELECT 	 
			`wh_total`
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `min_wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `date_start`,
			(
			SELECT  
			`wh_total`
			from `meter_data` 
			 USE INDEX(idx_name)
			 where
			`meter_id` = meter_name
			 and `datetime` > '$end_date'
		   LIMIT 0,1 
			) AS `max_wh_total`,
			(
			SELECT  
			`datetime`
			from `meter_data` 
			 USE INDEX(idx_name)
			 where
			`meter_id` = meter_name
			 and `datetime` > '$end_date'
		   LIMIT 0,1 
			) AS `date_end`
			from meter_details 
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			and meter_status = 'Active' ORDER BY customer_name ASC";

    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();

	}		
	
	public function meter_location_report_query_procedure_min($meter_location, $date_start, $date_end, $meter_role)
	{
	$this->load->helper('url');
	ini_set('memory_limit','100M');

	/*CURRENT DATE*/
	$dt_from = ("$date_start 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
	$dt_to = ("$date_end 23:59:59");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("13 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

		$query_meter_logs_from = "
		SELECT meter_name,
		`meter_multiplier`, 
		`customer_name`,    
		`space_code`,
			(	
			SELECT 	 
			(`wh_total`)
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `min_wh_total`,
			(	
			SELECT 	 
			(`datetime`)
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		 LIMIT 0,1 
			) as `date_start`,
			(
			SELECT  
			(`wh_total`)
			from `meter_data` 
			 USE INDEX(idx_name)
			 where
			`meter_id` = meter_name
			 and `datetime` < '$end_date'
		  /**/ order by datetime DESC LIMIT 0,1  
			) AS `max_wh_total`,
			(
			SELECT  
			(`datetime`)
			from `meter_data` 
			 USE INDEX(idx_name)
			 where
			`meter_id` = meter_name
			 and `datetime` < '$end_date'
		  /**/ order by datetime DESC LIMIT 0,1 
			) AS `date_end`
			from meter_details 
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			and meter_status = 'Active'";

    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();


	}
	
	public function meter_sap_report_query($meter_location,$meter_role,$cut_off)
	{

	/*CURRENT DATE*/
    $dt_from = ("$cut_off 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("6 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	//NEXT DATE
	$dt_to = ("$cut_off 00:00:00");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("6 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");
	/**/
	 $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`measuring_point`,
			(
			SELECT 	 
			`wh_total`
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1
			) as `wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data`
				USE INDEX(idx_name)
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `datetime`
			from meter_details 
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			and meter_status = 'Active' ORDER BY customer_name ASC";

    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();

	}		
	
	public function query_site_cut_off($meter_location)
	{	
	/*QUERY CUT-OFF*/
	@$query_cut_off = $this->db->query("SELECT * FROM meter_site
	   WHERE
		site_code = ".$this->db->escape($meter_location)."");
	return $cut_off_info = $query_cut_off->row_array(); 
	}
	
	/*GATEWAY LIST*/
	public function location_gateway_list($meter_location)
	{	
	
	@$query_gateway = $this->db->query("SELECT * FROM meter_rtu
	   WHERE
		rtu_site_name = ".$this->db->escape($meter_location)."");
	return $query_gateway->result_array(); 
	
	}
	
	/*GATEWAY LIST*/
	public function gateway_meter_list($id)
	{	
	
	@$query_gateway = $this->db->query("SELECT * FROM meter_details
	   WHERE
		rtu_sn_number_id = ".$this->db->escape($id)."");
	return $query_gateway->result_array(); 
	
	}

	public function get_selected_site_by_code($meter_location){
	$query = $this->db->get_where('meter_site', array('site_code' => $meter_location));
	return $query->row_array();
	}
}