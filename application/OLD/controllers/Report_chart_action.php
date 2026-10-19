<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Report_chart_action extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('Report_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	}

	public function meter_fifteen_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	//METER DETAILS 	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	 $date_log = explode(' - ', $this->input->post('date'));

		 $date_start = $date_log[0];
		 $date_end = $date_log[1];	

	$no_excl = 10;
	$n = 1;
	$this->load->helper('date');

	$begin = new DateTime( "$date_start" );
	$end = new DateTime( "$date_end" );

	$interval = DateInterval::createFromDateString('15 minutes');
	$period = new DatePeriod($begin, $interval, $end);
	 
	foreach ( $period as $dt ){
	 $dt->format( "Y-m-d H" );
	 $new_dt = date_format($dt, 'Y-m-d H:i:s');
	 $new_dt_excel = date_format($dt, 'Y-m-d H:i');
	 
	//QUERY METER LOG HOURLY(START)
	
	$data['hourly_result'] = $this->Report_model->fifteen_minutes_query($meter_name,$new_dt); 

	if($data['hourly_result']['min_wh_total'] == 0){

	@$min_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['max_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}else{

	@$min_wh_total = number_format($data['hourly_result']['min_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['min_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}

	$to_time = strtotime("$min_datetime");
	$from_time = strtotime("$max_datetime");
	$_total_intervaltime = round( (abs($to_time - $from_time)) / 60 ,0);
	
	/*
	OLD FORMULA
	@$total_intervaltime = round(60/$_total_intervaltime,0);
	*/
	$total_intervaltime = round($_total_intervaltime/60,2);

	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format( ( ( ($max_wh_total-$min_wh_total) * $meter_multiplier) / $total_intervaltime)+0, 2, '.', '');
	//$_wh_total=number_format( ( ($max_wh_total-$min_wh_total) / $total_intervaltime)+0, 2, '.', '');
	}

	$uts=strtotime($new_dt_excel); //convertir a Unix Timestamp
	$date_t=date('l, F j, Y H:i:s',$uts);
	
	echo $new_dt_excel . "\t" . $_wh_total . "\t" . $client_name . "\t" . ($max_wh_total) . "\t" . ($min_wh_total) . "\t" . $total_intervaltime . "\n";
	
	$no_excl++;
	$n++;

	}

	}
	
	else{
	
	}
	   
	}

	public function meter_hourly_demand_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	//METER DETAILS 	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	 $date_log = explode(' - ', $this->input->post('date'));

		 $date_start = $date_log[0];
		 $date_end = $date_log[1];	

	$no_excl = 10;
	$n = 1;
	$this->load->helper('date');

	$begin = new DateTime( "$date_start" );
	$end = new DateTime( "$date_end" );

	$interval = DateInterval::createFromDateString('1 hour');
	$period = new DatePeriod($begin, $interval, $end);
	
	foreach ( $period as $dt ){
	 $dt->format( "Y-m-d H" );
	 $new_dt = date_format($dt, 'Y-m-d H');
	 $new_dt_excel = date_format($dt, 'Y-m-d H:i');
	
	//QUERY METER LOG HOURLY(START)
	$data['hourly_result'] = $this->Report_model->hourly_query($meter_name,$new_dt); 

	if($data['hourly_result']['min_wh_total'] == 0){

	@$min_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['max_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}else{

	@$min_wh_total = number_format($data['hourly_result']['min_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['min_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}
 
	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format((($max_wh_total-$min_wh_total) * $meter_multiplier)+0, 2, '.', '');
	}

	$uts=strtotime($new_dt_excel); //convertir a Unix Timestamp
	$date_t=date('l, F j, Y H:i:s',$uts);
	
	echo $new_dt_excel . "\t" . $_wh_total . "\t" . $client_name . "\n";

	$no_excl++;
	$n++;

	}

	}
	
	else{
	
	}
	   
	}
	
	public function meter_hourly_consumption_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	//METER DETAILS 	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	 $date_log = explode(' - ', $this->input->post('date'));

		 $date_start = $date_log[0];
		 $date_end = $date_log[1];	

	$no_excl = 10;
	$n = 1;
	$this->load->helper('date');

	$begin = new DateTime( "$date_start" );
	$end = new DateTime( "$date_end" );

	$interval = DateInterval::createFromDateString('1 hour');
	$period = new DatePeriod($begin, $interval, $end);
	
	foreach ( $period as $dt ){
	 $dt->format( "Y-m-d H" );
	 $new_dt = date_format($dt, 'Y-m-d H');
	 $new_dt_excel = date_format($dt, 'Y-m-d H:i');
	
	//QUERY METER LOG HOURLY(START)
	$data['hourly_result'] = $this->Report_model->hourly_query($meter_name,$new_dt); 

	if($data['hourly_result']['min_wh_total'] == 0){

	@$min_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['max_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}else{

	@$min_wh_total = number_format($data['hourly_result']['min_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['hourly_result']['max_wh_total']+0, 2, '.', '');

	@$min_datetime = $data['hourly_result']['min_datetime'];
	@$max_datetime = $data['hourly_result']['max_datetime'];
 
	}
 
	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format(((($max_wh_total-$min_wh_total) * $meter_multiplier))+0, 2, '.', '');
	}

	$uts=strtotime($new_dt_excel); //convertir a Unix Timestamp
	$date_t=date('l, F j, Y H:i:s',$uts);
	
	echo $new_dt_excel . "\t" . $_wh_total . "\t" . $client_name . "\n";

	$no_excl++;
	$n++;

	}

	}
	
	else{
	
	}
	   
	}
	
	public function meter_daily_report_post()
	{	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	//METER DETAILS 
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 

	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];

	 $date_log = explode(' - ', $this->input->post('date'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_DAILY.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);

	$no_excl = 10;
	$n = 1;
	
	$this->load->helper('date');
	
	$date1=date_create($date_start);
	$date2=date_create($date_end);
		
	$date = date_format($date1,"Y-m-d");
	$end = date_format($date2,"Y-m-d");
	
	while (strtotime($date) <= strtotime($end)) {

	$data['meter_details_from'] = $this->Report_model->meter_daily_report_query($meter_name,$date); 
		
	$max_date = $data['meter_details_from']['max_date'];
	$min_date = $data['meter_details_from']['min_date'];

	@$min_wh_total = number_format($data['meter_details_from']['min_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['meter_details_from']['max_wh_total']+0, 2, '.', '');
		
	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format((($max_wh_total-$min_wh_total))+0, 2, '.', '');
	}
	$consumption_data[] = array('consumption' => $_wh_total);

	echo $date . "\t" . $_wh_total . "\t" . $client_name . "\n";

	$date = date ("Y-m-d 00:00", strtotime("+1 day", strtotime($date)));
	
	$no_excl++;
	$n++;
	}
	
	}
	
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
}