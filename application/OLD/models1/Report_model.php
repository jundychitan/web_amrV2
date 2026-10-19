<?php
class Report_model extends CI_Model {
	
	public function __construct()
	{
	$this->load->database();
	$this->load->helper('date');
	$this->load->library('session');
	}
 
	//SELECT METER
	public function get_selected_meter($meter_id){
	$query = $this->db->get_where('meter_details', array('id' => $meter_id));
	return $query->row_array();
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
 
	/*DISTINCT PHYSICAL LOCATION*/ 
	public function get_physical_location_meter_list($business_entity)
	{
		 
		$user_id = $_SESSION['user_id'];

		$query = $this->db->query("select
		 distinct
		  physical_location
		   from meter_details
		    where
		 meter_site_name = ".$this->db->escape($business_entity)."");
		
		return $query->result_array();

	}  

	/*DISTINCT PHYSICAL LOCATION*/ 
	public function get_building_list($business_entity)
	{
		
		$user_id = $_SESSION['user_id'];
		$query = $this->db->query("select
		 distinct
		  building as building_name,
		  building_description as building_description
		   from meter_details
		    where
		 meter_site_name = ".$this->db->escape($business_entity)." and building_description!='' ORDER BY building asc");
		return $query->result_array();
		
	}  
	
	/*GET METER INFO*/
	public function get_meter_info($meter_id, $location_id){
	$query = $this->db->get_where('meter_details', array('meter_name' => $meter_id, 'meter_site_name' => $location_id));
	return $query->row_array(); 
	}
	
	/*ALL LOGS*/
	public function meter_all_logs_report($meter_location)
	{
	//$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');
	//echo $this->input->post('date_log');
	$date_log = explode(' - ', $this->input->post('date_log'));
	@$date_start = $date_log[0];
	@$date_end = $date_log[1];	
	/* 
	QUERY METER LOGS
	QUERY METER LOGS BY LOCATION METER ID DATE
	*/
	
	$query_meter_logs = $this->db->query("SELECT * FROM meter_data WHERE 
	meter_id = ".$this->db->escape($meter_id)." and 
	location = ".$this->db->escape($meter_location)." and 
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

	public function fifteen_minutes_query($meter_id,$new_dt,$meter_location)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');

	$dt_from_A = ("$new_dt");
	$dt_from_w_time_A = "$dt_from_A";
	$date1_A=date_create($dt_from_w_time_A);
	date_sub($date1_A,date_interval_create_from_date_string("16 minutes"));
	$start_date_A = date_format($date1_A,"Y-m-d H:i:s"); 

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
	/*USE INDEX(idx_name)*/
	 where 
	`location`=".$this->db->escape($meter_location)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." ORDER BY `datetime` DESC LIMIT 0,1
	) as `min_wh_total`,
	(	
	SELECT 	
	(`datetime`)
	from `meter_data` 
	/*USE INDEX(idx_name)*/
	where 
	`location`=".$this->db->escape($meter_location)." and
	`datetime`<=".$this->db->escape($end_date)." and
	 `meter_id`=".$this->db->escape($meter_id)." ORDER BY `datetime` DESC LIMIT 0,1
	) as `min_datetime`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data` 
	/*USE INDEX(idx_name)*/
	where 
	`location`=".$this->db->escape($meter_location)." and
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime`
	from `meter_data` 
	/*USE INDEX(idx_name)*/
	where 
	`location`=".$this->db->escape($meter_location)." and
	`datetime`>=".$this->db->escape($end_date)." and
	`meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_datetime`");

	return $query_meter_logs_from->row_array();
	
	}	

	/*HOURLY*/
	public function hourly_query($meter_id,$new_dt,$meter_location)
	{
	$this->load->helper('url');
	$meter_id = $this->input->post('meter_name');

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
	a.`meter_id` AS meter_id, 
	a.`datetime` AS min_datetime, 
	a.`wh_total` AS min_wh_total,
	b.`datetime` AS max_datetime,
	b.`wh_total` AS max_wh_total,
	(SELECT COUNT(meter_id) AS interval_count
	FROM meter_data
	WHERE 
	meter_id = ".$this->db->escape($meter_id)." AND 
	location = ".$this->db->escape($meter_location)." AND 
	`datetime` >= ".$this->db->escape($start_date)." AND
	`datetime` <= ".$this->db->escape($end_date).") as interval_count
	FROM meter_data a
	INNER JOIN meter_data b
	WHERE 
	a.meter_id = ".$this->db->escape($meter_id)." AND 
	a.location = ".$this->db->escape($meter_location)." AND 
	a.`datetime` >= ".$this->db->escape($start_date)." AND 
	b.meter_id = ".$this->db->escape($meter_id)." AND 
	b.`datetime` >= ".$this->db->escape($end_date)." LIMIT 0,1");


	return $query_meter_logs_from->row_array();
	}	
	
	/*DAILY*/
	public function meter_daily_report_query($meter_id,$new_dt,$meter_location)
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
		 
	$query_meter_logs_from = $this->db->query(
	"SELECT
	(	
	SELECT 	 
	`wh_total` as min_wh_total
	from `meter_data`
	/*USE INDEX(idx_name)*/
	 where 
	 `location` = ".$this->db->escape($meter_location)." and 
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1   
	) as `min_wh_total`,
	(SELECT 	 
	`datetime` as min_date
	from `meter_data`
	/*USE INDEX(idx_name)*/
	 where  
	 `location` = ".$this->db->escape($meter_location)." and 
    `datetime`>=".$this->db->escape($start_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1   
	) as `min_date`,
	( SELECT  
	`wh_total` as max_wh_total
	from `meter_data`
	/*USE INDEX(idx_name)*/
	 where  
	 `location` = ".$this->db->escape($meter_location)." and 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_wh_total`,
	( SELECT  
	`datetime` as max_date
	from `meter_data` 
	/*USE INDEX(idx_name)*/
	where 
	 `location` = ".$this->db->escape($meter_location)." and 
	`datetime`>=".$this->db->escape($end_date)." and 
     `meter_id`=".$this->db->escape($meter_id)." LIMIT 0,1
    ) AS `max_date`");	


	return $query_meter_logs_from->row_array();
	
	}	

	public function meter_location_report_query_procedure_limit($meter_location, $date_start, $date_end, $meter_role, $sap_info, $buiding_list)
	{
	$this->load->helper('url');

	/*CURRENT DATE*/
	$dt_from = ("$date_start 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_sub($date1,date_interval_create_from_date_string("5 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:s");
	/*NEXT DATE*/
	$dt_to = ("$date_end 00:00:00");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_add($date2,date_interval_create_from_date_string("10 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	/*Optional Query for filtering only the meters with Valid SAP Info*/
	if( $sap_info == 'Yes' ){
		$sap_info_query = "and measuring_point != 0 and ro_valid_to >= CURRENT_TIMESTAMP AND ro_valid_to <= ro_valid_to and contract_number!= '' and customer_name!=''";
	}else{
		$sap_info_query = ' ';
	}

	/*Optional Query for filtering only the Building Code*/
	if( $buiding_list != 'All' ){
		$buiding_list_query = "and building = '$buiding_list' ";
	}else{
		$buiding_list_query = ' ';
	}
	
	  $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`building`,
		`measuring_point`,
		`meter_multiplier`,
		`rtu_sn_number`,
		`physical_location`,
			(
			SELECT 	 
			`wh_total`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				 location = '$meter_location' and 
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `min_wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				 location = '$meter_location' and 
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `date_start`,			
			(
			SELECT  
			max(`datetime`)
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
			 where
			 location = '$meter_location' and 
			`meter_id` = meter_name and 
			`datetime` > '$start_date'
			 and `datetime` < '$end_date'
		   LIMIT 0,1 
			) AS `date_end`,
			(
			SELECT  
			`wh_total`
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
			 where
			 location = '$meter_location' and 
			`meter_id` = meter_name 
			 and `datetime` = date_end
		   LIMIT 0,1 
			) AS `max_wh_total`
			from meter_details 
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			 and rtu_sn_number != 'unassigned'
			and meter_status = 'Active'
			 $buiding_list_query
			 $sap_info_query 
			 ORDER BY customer_name ASC";

    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();

	}		
	
	public function meter_location_report_query_procedure_limit_get_negative($meter_location, $date_start, $date_end, $meter_role)
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
	date_add($date2,date_interval_create_from_date_string("10 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	  $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`measuring_point`,
		`meter_multiplier`,
		`rtu_sn_number`,
		`physical_location`,
			(
			SELECT 	 
			`wh_total`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				 `meter_id` = meter_name and 
			`datetime` < '$end_date' and 
			wh_total <= 0
		   LIMIT 0,1 
			) as `min_wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				 `meter_id` = meter_name and 
			`datetime` < '$end_date'
			 and 
			wh_total <= 0
		   LIMIT 0,1 
			) as `date_start`,
			(
			SELECT  
			max(`wh_total`)
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
			 where
			`meter_id` = meter_name and 
			`datetime` > '$start_date'
			 and `datetime` < '$end_date'
		   LIMIT 0,1 
			) AS `max_wh_total`,
			(
			SELECT  
			max(`datetime`)
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
			 where
			`meter_id` = meter_name and 
			`datetime` > '$start_date'
			 and `datetime` < '$end_date'
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
				/*USE INDEX(idx_name)*/
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		   LIMIT 0,1 
			) as `min_wh_total`,
			(	
			SELECT 	 
			(`datetime`)
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				 `meter_id` = meter_name and 
			`datetime` > '$start_date'
		 LIMIT 0,1 
			) as `date_start`,
			(
			SELECT  
			(`wh_total`)
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
			 where
			`meter_id` = meter_name
			 and `datetime` < '$end_date'
		  /**/ order by datetime DESC LIMIT 0,1  
			) AS `max_wh_total`,
			(
			SELECT  
			(`datetime`)
			from `meter_data` 
			 /*USE INDEX(idx_name)*/
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
	
	public function meter_sap_report_query($meter_location,$meter_role,$cut_off,$building_list)
	{

	/*CURRENT DATE*/
    $dt_from = ("$cut_off 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_add($date1,date_interval_create_from_date_string("14 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:59");
	
	//NEXT DATE
	$dt_to = ("$cut_off 00:00:00");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("6 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	if($building_list == 'All'){
		$building_query = "";
	}else{
		$building_query = " and building = '$building_list'";
	}
	
	/**/
	 $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`contract_number` ,
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`measuring_point`,
		`measuring_point_description`,
		`ro_valid_from`,
		`ro_valid_to`,
		(
			SELECT 	 
			`wh_total`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				`location` = '$meter_location' and
				 `meter_id` = meter_name and 
			`datetime` <= '$start_date'
		  ORDER BY `datetime` DESC
			LIMIT 0,1 
			) as `wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data` 
				/*USE INDEX(idx_name)*/
				where  
				`location` = '$meter_location' and
				 `meter_id` = meter_name and 
			`datetime` <= '$start_date'
		   ORDER BY `datetime` DESC 
			LIMIT 0,1 
			) as `datetime` 
			from meter_details 
			/*USE INDEX(meter_name_characteristic_site)*/
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			 and measuring_point != 0 
			 and contract_number!= '' 
			 and customer_name!=''
			 and ro_valid_to >= CURRENT_TIMESTAMP AND ro_valid_to <= ro_valid_to 
			 and meter_status = 'Active'
			 $building_query
			 ORDER BY customer_name ASC";

    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();

	}	
	
	public function meter_sap_report_query_old_format($meter_location,$meter_role,$cut_off,$building_list)
	{

	/*CURRENT DATE*/
    $dt_from = ("$cut_off 00:00:00");
	$dt_from_w_time = "$dt_from";
	$date1=date_create($dt_from_w_time);
	date_add($date1,date_interval_create_from_date_string("14 minutes"));
	$start_date = date_format($date1,"Y-m-d H:i:59");
	
	//NEXT DATE
	$dt_to = ("$cut_off 00:00:00");
	$dt_to_w_time = "$dt_to";
	$date2=date_create($dt_to_w_time);
	date_sub($date2,date_interval_create_from_date_string("6 minutes"));
	$end_date = date_format($date2,"Y-m-d H:i:s");

	if($building_list == 'All' || $building_list == ''){
		$building_query = "";
	}else{
		$building_query = " and building = '$building_list'";
	}
	
	/**/
	 $query_meter_logs_from = "
		SELECT 	meter_name,
		`customer_name`,    
		`contract_number` ,
		`rental_object`,
		`company_no`,
		`meter_site_name`,
		`measuring_point`,
		`measuring_point_description`,
		`ro_valid_from`,
		`ro_valid_to`,
		(
			SELECT 	 
			`wh_total`
			from `meter_data`
				/*USE INDEX(idx_name)*/
				where  
				`location` = '$meter_location' and
				 `meter_id` = meter_name and 
			`datetime` <= '$start_date'
		  ORDER BY `datetime` DESC
			LIMIT 0,1 
			) as `wh_total`,
			(	
			SELECT 	 
			`datetime`
			from `meter_data` 
				/*USE INDEX(idx_name)*/
				where  
				`location` = '$meter_location' and
				 `meter_id` = meter_name and 
			`datetime` <= '$start_date'
		   ORDER BY `datetime` DESC 
			LIMIT 0,1 
			) as `datetime` 
			from meter_details 
			/*USE INDEX(meter_name_characteristic_site)*/
			where meter_site_name = '$meter_location'
			 and meter_role = '$meter_role'
			 and meter_status = 'Active'
			 $building_query
			 ORDER BY customer_name ASC";

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
	
	/*PHYSICAL LOCATION LIST*/
	public function meter_by_physical_location_list($physical_location,$site_code)
	{	

	@$query_gateway = $this->db->query("SELECT * FROM meter_details
	   WHERE
		meter_site_name = ".$this->db->escape($site_code)." and
			physical_location = ".$this->db->escape($physical_location)." and
				 rtu_sn_number !='unassigned'");
	return $query_gateway->result_array(); 
	
	}

	public function get_selected_site_by_code($meter_location){
	$query = $this->db->get_where('meter_site', array('site_code' => $meter_location));
	return $query->row_array();
	}
	
	/*SAP METER LIST*/
	public function meter_sap_list_report_query($meter_location)
	{
		
	 $query_meter_logs_from = "
		SELECT 	* from meter_details where meter_site_name = '$meter_location' and meter_role = 'Client Meter' ORDER BY rental_object ASC";
    $rr = $this->db->query($query_meter_logs_from);
	return $rr->result_array();

	}	
	
	public function offline_meters($site_id,$server_time)
	{ 
	
	@$query_check_meter_exist = $this->db->query("SELECT
	`meter_name`,
		 `last_log_update`,
	 			`customer_name`,
				rtu_sn_number,
				meter_status
				/*,DATEDIFF('$server_time', `last_log_update`) as date_diff*/
	 FROM meter_details where meter_site_id = ".$this->db->escape($site_id)." 
	 and /*meter_status = 'Active' and*/
	 TIMEDIFF('$server_time', `last_log_update`) >= '23:59:59'
	 order by  last_log_update DESC");
	
	return $query_check_meter_exist->result_array(); 
	 
	}
	
	public function offline_meters_per_gateway($site_id,$rtu_id,$server_time)
	{ 
	
	@$query_check_meter_exist = $this->db->query("SELECT
	`meter_name`,
		 `last_log_update`,
	 			`customer_name`,
				rtu_sn_number,
				meter_status
	 FROM meter_details where meter_site_id = ".$this->db->escape($site_id)." and
	 rtu_sn_number_id = ".$this->db->escape($rtu_id)." and 
	 TIMEDIFF('$server_time', `last_log_update`) >= '23:59:59'
	 order by  last_log_update DESC");
	
	return $query_check_meter_exist->result_array(); 
	 
	}
	
	public function get_selected_site_by_id($site_id){
	$query = $this->db->get_where('meter_site', array('id' => $site_id));
	return $query->row_array();
	}
	
	public function offline_gateway($server_time)
	{ 
		
		@$query_check_meter_exist = $this->db->query("SELECT
		`rtu_sn_number`,
			 `rtu_physical_location`,
					`phone_no_or_ip_address`,
						`mac_addr`,
							`rtu_site_name`,
								`soft_rev`,
									`last_log_update`
		 FROM
		  meter_rtu
			 where 
		 TIMEDIFF('$server_time', `last_log_update`) >= '23:59:59'
		 and last_log_update!='0000-00-00 00:00:00'
			order by  last_log_update DESC");
		
		return $query_check_meter_exist->result_array(); 
		 
	}
	
	public function offline_gateway_per_site($server_time,$site_id)
	{ 
		
		@$query_check_meter_exist = $this->db->query("SELECT
		`rtu_sn_number`,
			 `rtu_physical_location`,
					`phone_no_or_ip_address`,
						`mac_addr`,
							`rtu_site_name`,
								`soft_rev`,
									`last_log_update`
		 FROM
		  meter_rtu
			 where 
			 `rtu_site_id` = ".$this->db->escape($site_id)." and
		 TIMEDIFF('$server_time', `last_log_update`) >= '23:59:59'
		  and last_log_update!='0000-00-00 00:00:00'
			order by  last_log_update DESC");
		
		return $query_check_meter_exist->result_array(); 
		 
	}
	
	public function site_details($location_id){
	$query = $this->db->get_where('meter_site', array('site_code' => $location_id));
	return $query->row_array();
	}
}