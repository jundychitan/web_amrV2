<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/*SPREADSHEET*/	

  	use PhpOffice\PhpSpreadsheet\Spreadsheet;
	use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	use PhpOffice\PhpSpreadsheet\IOFactory;
		
	class Report_action extends CI_Controller {
	
	public function __construct() 
	{
	parent::__construct();
	$this->load->model('Report_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	}

	public function select_location_meter_list()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	$location_id = $this->input->post('location_id');
	/*QUERY METER LIST BY LOCATION*/
	$meter_list = $this->Report_model->get_location_meter_list($location_id);
	?>
    <label>Meter</label><br />
    <input type="text" class="form-control" id="meter" name="meter" list="meter_list" size="20" autocomplete="off"> 
    <datalist id="meter_list">
    <?php
    foreach ($meter_list as $meter_col): 
    $meter_id = $meter_col['meter_id'];
   	$client_name = $meter_col['customer_name'];
	//$other_description = $meter_col['other_description'];
    ?>
    <option value="<?php echo "$meter_id";?>"><?php echo "$client_name - $meter_id"; ?></option>
    <?php
    endforeach;
    ?>
    </datalist>
  
    <?php
	}

	/*GENERATING REPORT LOGS*/
	/*RAW DATA*/
	public function meter_logs_post()
	{
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');
	$this->load->library('form_validation');

	$this->form_validation->set_rules('date_log', 'Date', 'required');
	$this->form_validation->set_rules('meter_name', 'Meter', 'required');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{

	}
	//VALIDATION SUCCESS
	else{
	//METER DETAILS 
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	
	$data_result_row = $data['meter_all_logs_report']=$this->Report_model->meter_all_logs_report(); 
	
	$date_log = explode(' - ', $this->input->post('date_log'));

	$date_start = $date_log[0];
	$date_end = $date_log[1];	
	
	$data_result = $data_result_row;
	
	if($data_result==0){
	echo 'No Result Found';
	}
	else{
	$report_name = "$meter_location-$meter_id". "_ALLLOGS.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Report")
	->setLastModifiedBy("Meter Report")
	->setTitle("Meter Report")
	->setSubject("Meter Report")
	->setDescription("Meter Report")
	->setKeywords("Meter Report")
	->setCategory("Meter Report");
	
	
	echo '<div class="col-md-12">';
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name ) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	echo "<div align='left' style='font-size: 14px;display: flex;''><b style='width: 150px; display: block;'>Customer Name:</b> $meter_client_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;''><b style='width: 150px; display: block;'>Meter Name:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;''><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;''><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table table-striped' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th style='text-align:left !important;' >#</th>"; 
	echo "<th style='text-align:left !important;'>Datetime</th><th style='text-align:left !important;'>vrms_a</th>"; 
	echo "<th style='text-align:left !important;'>vrms_b</th><th style='text-align:left !important;'>vrms_c</th>"; 
	echo "<th style='text-align:left !important;'>irms_a</th><th style='text-align:left !important;'>irms_b</th>";
	echo "<th style='text-align:left !important;'>irms_c</th><th style='text-align:left !important;'>freq</th>";
	echo "<th style='text-align:left !important;'>pf</th><th style='text-align:left !important;'>kw</th><th style='text-align:left !important;'>kva</th>";
	echo "<th style='text-align:left !important;'>kvar</th><th style='text-align:left !important;'>kwh_del</th><th style='text-align:left !important;'>kwh_rec</th>";
	echo "<th style='text-align:left !important;'>kwh_net</th><th style='text-align:left !important;'>kwh_total</th><th style='text-align:left !important;'>kvarh_neg</th>";
	echo "<th style='text-align:left !important;'>kvarh_pos</th><th style='text-align:left !important;'>kvarh_net</th><th style='text-align:left !important;'>kvarh_total</th><th style='text-align:left !important;'>kvah_total</th>";
	echo "<th style='text-align:left !important;'>max_rec_kw_dmd</th><th style='text-align:left !important;'>max_rec_kw_dmd_time</th></tr></thead><tbody>";  
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
   
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $meter_location)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end);
	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A5', '#')
	->setCellValue('B5', 'Date Time')
	->setCellValue('C5', 'vrms_a')
	->setCellValue('D5', 'vrms_b')
	->setCellValue('E5', 'vrms_c')
	->setCellValue('F5', 'irms_a')
	->setCellValue('G5', 'irms_b')
	->setCellValue('H5', 'irms_c')
	->setCellValue('I5', 'freq')
	->setCellValue('J5', 'pf')
	->setCellValue('K5', 'kw')
	->setCellValue('L5', 'kva')
	->setCellValue('M5', 'kvar')
	->setCellValue('N5', 'kwh_del')
	->setCellValue('O5', 'kwh_rec')
	->setCellValue('P5', 'kwh_net')
	->setCellValue('Q5', 'kwh_total')
	->setCellValue('R5', 'kvarh_neg')
	->setCellValue('S5', 'kvarh_pos')
	->setCellValue('T5', 'kvarh_net')
	->setCellValue('U5', 'kvarh_total')
	->setCellValue('V5', 'kvah_total')
	->setCellValue('W5', 'max_rec_kw_dmd')
	->setCellValue('X5', 'max_rec_kw_dmd_time');
	/*
	REMOVE AUGUST 11, 2020
	->setCellValue('Y5', 'max_del_kw_dmd')
	->setCellValue('Z5', 'max_del_kw_dmd_time')
	->setCellValue('AA5', 'max_pos_kvar_dmd')
	->setCellValue('AB5', 'max_pos_kvar_dmd_time')
	->setCellValue('AC5', 'max_neg_kvar_dmd')
	->setCellValue('AD5', 'max_neg_kvar_dmd_time')
	*/
	
	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('o')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('p')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('q')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('r')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('s')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('t')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('u')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('v')->setAutoSize(true);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('w')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('x')->setAutoSize(true);
	/*
	$objPHPExcel->getActiveSheet()->getColumnDimension('y')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('z')->setAutoSize(true);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('aa')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('ab')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('ac')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('ad')->setAutoSize(true);
	*/
	/*styling excel */  
$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet(0)->getStyle('A1:A4')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A5:X5')->getFont()->setBold(true);

$style = array(
'alignment' => array(
	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A5:D5')->applyFromArray($style);

	$no_excl = 6;
	$n = 1;
	foreach ($data_result_row as $meter_all_logs_report_result){
	
	$meter_id_col = $meter_all_logs_report_result['meter_id'];
	$location_col = $meter_all_logs_report_result['location'];
	$datetime_col = $meter_all_logs_report_result['datetime'];
	$vrms_a_col = $meter_all_logs_report_result['vrms_a'];
	$vrms_b_col = $meter_all_logs_report_result['vrms_b'];
	$vrms_c_col = $meter_all_logs_report_result['vrms_c'];
	$irms_a_col = $meter_all_logs_report_result['irms_a'];
	$irms_b_col = $meter_all_logs_report_result['irms_b'];
	$irms_c_col = $meter_all_logs_report_result['irms_c'];
	$freq_col = $meter_all_logs_report_result['freq'];
	$pf_col = $meter_all_logs_report_result['pf'];
	$watt_col = $meter_all_logs_report_result['watt'];
	$va_col = $meter_all_logs_report_result['va'];
	$var_col = $meter_all_logs_report_result['var'];
	$wh_del_col = $meter_all_logs_report_result['wh_del'];
	$wh_rec_col = $meter_all_logs_report_result['wh_rec'];
	$wh_net_col = $meter_all_logs_report_result['wh_net'];
	$wh_total_col = $meter_all_logs_report_result['wh_total'];
	$varh_neg_col = $meter_all_logs_report_result['varh_neg'];
	$varh_pos_col = $meter_all_logs_report_result['varh_pos'];
	$varh_net_col = $meter_all_logs_report_result['varh_net'];
	$varh_total_col = $meter_all_logs_report_result['varh_total'];
	$vah_total_col = $meter_all_logs_report_result['vah_total'];
	
	$max_rec_kw_dmd_col = $meter_all_logs_report_result['max_rec_kw_dmd'];
	$max_rec_kw_dmd_time_col = $meter_all_logs_report_result['max_rec_kw_dmd_time'];
	$max_del_kw_dmd_col = $meter_all_logs_report_result['max_del_kw_dmd'];
	$max_del_kw_dmd_time_col = $meter_all_logs_report_result['max_del_kw_dmd_time'];
	
	$max_pos_kvar_dmd_col = $meter_all_logs_report_result['max_pos_kvar_dmd'];
	$max_pos_kvar_dmd_time_col = $meter_all_logs_report_result['max_pos_kvar_dmd_time'];
	$max_neg_kvar_dmd_col = $meter_all_logs_report_result['max_neg_kvar_dmd'];
	$max_neg_kvar_dmd_time = $meter_all_logs_report_result['max_neg_kvar_dmd_time'];
	
	echo "<tr>";
	echo "<td>$n</td>"; 
	echo "<td nowrap='nowrap'>$datetime_col</td>";
	echo "<td>$vrms_a_col</td>";
	echo "<td>$vrms_b_col</td>";
	echo "<td>$vrms_c_col</td>";
	echo "<td>$irms_a_col</td>";
	echo "<td>$irms_b_col</td>";
	echo "<td>$irms_c_col</td>";
	echo "<td>$freq_col</td>";
	echo "<td>$pf_col</td>";
	echo "<td>$watt_col</td>";
	echo "<td>$va_col</td>";
	echo "<td>$var_col</td>";
	echo "<td>$wh_del_col</td>";
	echo "<td>$wh_rec_col</td>";
	echo "<td>$wh_net_col</td>";
	echo "<td>$wh_total_col</td>";
	echo "<td>$varh_neg_col</td>";
	echo "<td>$varh_pos_col</td>";
	echo "<td>$varh_net_col</td>";
	echo "<td>$varh_total_col</td>";
	echo "<td>$vah_total_col</td>";
	echo "<td>$max_rec_kw_dmd_col</td>";
	echo "<td>$max_rec_kw_dmd_time_col</td>";
	/*
	REMOVE AUGUST 11, 2020
	echo "<td>$max_del_kw_dmd_col</td>";
	echo "<td>$max_del_kw_dmd_time_col</td>";
	echo "<td>$max_pos_kvar_dmd_col</td>";
	echo "<td>$max_pos_kvar_dmd_time_col</td>";
	echo "<td>$max_neg_kvar_dmd_col</td>";
	echo "<td>$max_neg_kvar_dmd_time</td>";
	*/
	echo "</tr>";
	
	$objPHPExcel->setActiveSheetIndex(0)
	 ->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $datetime_col)
	->setCellValue('C'.$no_excl, $vrms_a_col)
	->setCellValue('D'.$no_excl, $vrms_b_col)
	->setCellValue('E'.$no_excl, $vrms_b_col)
	->setCellValue('F'.$no_excl, $irms_a_col) 
	->setCellValue('G'.$no_excl, $irms_b_col) 
	->setCellValue('H'.$no_excl, $irms_c_col) 
	->setCellValue('I'.$no_excl, $freq_col)
	->setCellValue('J'.$no_excl, $pf_col)
	->setCellValue('K'.$no_excl, $watt_col)
	->setCellValue('L'.$no_excl, $va_col)
	->setCellValue('M'.$no_excl, $var_col)
	->setCellValue('N'.$no_excl, $wh_del_col)
	->setCellValue('O'.$no_excl, $wh_rec_col)
	->setCellValue('P'.$no_excl, $wh_net_col)
	->setCellValue('Q'.$no_excl, $wh_total_col)
	->setCellValue('R'.$no_excl, $varh_neg_col)
	->setCellValue('S'.$no_excl, $varh_pos_col)
	->setCellValue('T'.$no_excl, $varh_net_col)
	->setCellValue('U'.$no_excl, $varh_total_col)
	->setCellValue('V'.$no_excl, $vah_total_col)
	
	->setCellValue('w'.$no_excl, $max_rec_kw_dmd_col)
	->setCellValue('x'.$no_excl, $max_rec_kw_dmd_time_col);
/*
REMOVE AUGUST 11, 2020
->setCellValue('y'.$no_excl, $max_del_kw_dmd_col)
->setCellValue('z'.$no_excl, $max_del_kw_dmd_time_col)
->setCellValue('aa'.$no_excl, $max_pos_kvar_dmd_col)
->setCellValue('ab'.$no_excl, $max_pos_kvar_dmd_time_col)
->setCellValue('ac'.$no_excl, $max_neg_kvar_dmd_col)
->setCellValue('ad'.$no_excl, $max_neg_kvar_dmd_time)
*/
	

	$no_excl++;
	$n++;
	}
	echo "</tbody></table>";
	echo "<div";
	echo "<div";
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	
	$objPHPExcel->setActiveSheetIndex(0);
	
	$callStartTime = microtime(true);
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//SAVING
	
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	
	$callStartTime = microtime(true);
	
	}
	
	}}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	/*DEMAND*/
	public function meter_fifteen_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('date_log', 'Date', 'required');;
	$this->form_validation->set_rules('meter_name', 'Meter', 'required');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{
	 
	}
	//VALIDATION SUCCESS
	else{
	//METER DETAILS 
	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	
	 $date_log = explode(' - ', $this->input->post('date_log'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_HOURLY.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Hourly Report")
	->setLastModifiedBy("Meter Hourly Report")
	->setTitle("Meter Hourly Report")
	->setSubject("Meter Hourly Report")
	->setDescription("Meter Hourly Report")
	->setKeywords("Meter Hourly Report")
	->setCategory("Meter Hourly Report");
	
	$objPHPExcel->getActiveSheet()
	->setCellValue('B6','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A5', 'Business Entity')
	->setCellValue('B5', $meter_location)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('A6', 'Total KWh');

	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Date Time')
	->setCellValue('C9', 'Date Start')
	->setCellValue('D9', 'Start')
	->setCellValue('E9', 'Date End')
	->setCellValue('F9', 'End')
	->setCellValue('G9', 'KW');
	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

	//styling excel
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);
	
	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);

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
	}
	$consumption_data[] = array('consumption' => $_wh_total);
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$new_dt_excel</td><td>$min_datetime</td><td>$min_wh_total</td><td>$max_datetime</td><td>$max_wh_total</td><td title = '($max_wh_total-$min_wh_total) / ($_total_intervaltime/60) '>$_wh_total</td></tr>");
	
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $new_dt_excel)
	->setCellValue('C'.$no_excl, $min_datetime)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_datetime)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $_wh_total);
	
	$no_excl++;
	$n++;

	}
	
	$sum_consumption_data = 0;
	foreach($consumption_data as $item) {
	$sum_consumption_data += $item['consumption'];
	}

	echo '<div class="col-md-2"></div><div class="col-md-8">';
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Customer Name:</b> $meter_client_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter Name:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table table-striped' border='1' bordercolor='#bdbdbd'><thead>";
	echo "<tr class='report'>";
	echo "<th rowspan='2' scope='col'>#</th>";
	echo "<th rowspan='2' scope='col'>Date Time</th>";
	echo "<th scope='col'>Date Start</th>";
	echo "<th scope='col'>Start</th>";
	echo "<th scope='col'>Date End</th>";
	echo "<th scope='col'>End</th>";
	echo "<th scope='col'>KW</th>";
	echo "</tr></thead>";

	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;
	
	echo "<tr bgcolor='#0099CC'>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td align='right'><b>Total Delivered:<b></td>";
	echo "<td><b>$sum_consumption_data</b></td>";
	echo "</tr>";
	echo "</tbody></table>";
	echo "<div>";
	echo '</div><div class="col-md-2"></div>';
	
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	
	$callStartTime = microtime(true);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	$callStartTime = microtime(true);
	//$this->output->enable_profiler(TRUE);
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
		
	/*DEMAND*/	
	public function meter_hourly_demand_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('date_log', 'Date', 'required');;
	$this->form_validation->set_rules('meter_name', 'Meter', 'required');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{
	 
	}
	//VALIDATION SUCCESS
	else{
	//METER DETAILS 
	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	
	 $date_log = explode(' - ', $this->input->post('date_log'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_HOURLY.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Hourly Report")
	->setLastModifiedBy("Meter Hourly Report")
	->setTitle("Meter Hourly Report")
	->setSubject("Meter Hourly Report")
	->setDescription("Meter Hourly Report")
	->setKeywords("Meter Hourly Report")
	->setCategory("Meter Hourly Report");
	
	$objPHPExcel->getActiveSheet()
	->setCellValue('B6','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A5', 'Business Entity')
	->setCellValue('B5', $meter_location)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('A6', 'Total KWh');

	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Date Time')
	->setCellValue('C9', 'Date Start')
	->setCellValue('D9', 'Start')
	->setCellValue('E9', 'Date End')
	->setCellValue('F9', 'End')
	->setCellValue('G9', 'KW');
	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

	//styling excel
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);
	
	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);

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
		
	$_wh_total=number_format(( ($max_wh_total-$min_wh_total) * $meter_multiplier)+0, 2, '.', '');
	}
	$consumption_data[] = array('consumption' => $_wh_total);
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$new_dt_excel</td><td>$min_datetime</td><td>$min_wh_total</td><td>$max_datetime</td><td>$max_wh_total</td><td>$_wh_total</td></tr>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $new_dt_excel)
	->setCellValue('C'.$no_excl, $min_datetime)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_datetime)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $_wh_total);
	
	$no_excl++;
	$n++;

	}
	
	$sum_consumption_data = 0;
	foreach($consumption_data as $item) {
	$sum_consumption_data += $item['consumption'];
	}

	echo '<div class="col-md-2"></div><div class="col-md-8">';
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Customer Name:</b> $meter_client_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter Name:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table table-striped' border='1' bordercolor='#bdbdbd'><thead>";
	echo "<tr class='report'>";
	echo "<th rowspan='2' scope='col'>#</th>";
	echo "<th rowspan='2' scope='col'>Date Time</th>";
	echo "<th scope='col'>Date Start</th>";
	echo "<th scope='col'>Start</th>";
	echo "<th scope='col'>Date End</th>";
	echo "<th scope='col'>End</th>";
	echo "<th scope='col'>KW</th>";
	echo "</tr></thead>";

	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;
	
	echo "<tr bgcolor='#0099CC'>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td align='right'><b>Total Delivered:<b></td>";
	echo "<td><b>$sum_consumption_data</b></td>";
	echo "</tr>";
	echo "</tbody></table>";
	echo "<div>";
	echo '</div><div class="col-md-2"></div>';
	
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	
	$callStartTime = microtime(true);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	$callStartTime = microtime(true);
	//$this->output->enable_profiler(TRUE);
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	/*CONSUMPTION*/
	public function meter_hourly_consumption_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('date_log', 'Date', 'required');;
	$this->form_validation->set_rules('meter_name', 'Meter', 'required');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{
	 
	}
	//VALIDATION SUCCESS
	else{
	//METER DETAILS 
	
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	$meter_multiplier = $data['meter_details']['meter_multiplier'];
	
	 $date_log = explode(' - ', $this->input->post('date_log'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_HOURLY.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Hourly Report")
	->setLastModifiedBy("Meter Hourly Report")
	->setTitle("Meter Hourly Report")
	->setSubject("Meter Hourly Report")
	->setDescription("Meter Hourly Report")
	->setKeywords("Meter Hourly Report")
	->setCategory("Meter Hourly Report");
	
	$objPHPExcel->getActiveSheet()
	->setCellValue('B6','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A5', 'Business Entity')
	->setCellValue('B5', $meter_location)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('A6', 'Total KWh');

	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Date Time')
	->setCellValue('C9', 'Date Start')
	->setCellValue('D9', 'Start')
	->setCellValue('E9', 'Date End')
	->setCellValue('F9', 'End')
	->setCellValue('G9', 'KWH');
	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

	//styling excel
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);
	
	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);

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
	$consumption_data[] = array('consumption' => $_wh_total);
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$new_dt_excel</td><td>$min_datetime</td><td>$min_wh_total</td><td>$max_datetime</td><td>$max_wh_total</td><td>$_wh_total</td></tr>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $new_dt_excel)
	->setCellValue('C'.$no_excl, $min_datetime)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_datetime)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $_wh_total);
	
	$no_excl++;
	$n++;

	}
	
	$sum_consumption_data = 0;
	foreach($consumption_data as $item) {
	$sum_consumption_data += $item['consumption'];
	}

	echo '<div class="col-md-2"></div><div class="col-md-8">';
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Customer Name:</b> $meter_client_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter Name:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table table-striped' border='1' bordercolor='#bdbdbd'><thead>";
	echo "<tr class='report'>";
	echo "<th rowspan='2' scope='col'>#</th>";
	echo "<th rowspan='2' scope='col'>Date Time</th>";
	echo "<th scope='col'>Date Start</th>";
	echo "<th scope='col'>Start</th>";
	echo "<th scope='col'>Date End</th>";
	echo "<th scope='col'>End</th>";
	echo "<th scope='col'>KWH</th>";
	echo "</tr></thead>";

	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;
	
	echo "<tr bgcolor='#0099CC'>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td align='right'><b>Total Delivered:<b></td>";
	echo "<td><b>$sum_consumption_data</b></td>";
	echo "</tr>";
	echo "</tbody></table>";
	echo "<div>";
	echo '</div><div class="col-md-2"></div>';
	
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	
	$callStartTime = microtime(true);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	$callStartTime = microtime(true);
	//$this->output->enable_profiler(TRUE);
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
		
	public function meter_daily_report_post()
	{	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('date_log', 'Date', 'required');
	$this->form_validation->set_rules('meter_name', 'Meter', 'required');
	
	//VALIDATION FAILED
	if ($this->form_validation->run() === FALSE)
	{
	 
	}
	//VALIDATION SUCCESS
	else{
	//METER DETAILS 
	$meter_name = $this->input->post('meter_name');
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name); 

	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];

	 $date_log = explode(' - ', $this->input->post('date_log'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_DAILY.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Daily Report")
	->setLastModifiedBy("Meter Daily Report")
	->setTitle("Meter Daily Report")
	->setSubject("Meter Daily Report")
	->setDescription("Meter Daily Report")
	->setKeywords("Meter Daily Report")
	->setCategory("Meter Daily Report");
	
	$objPHPExcel->getActiveSheet()
	->setCellValue('B6','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
		
	->setCellValue('A5', 'Meter Location')
	->setCellValue('B5', $meter_location)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('A6', 'Total KWh');
	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Date')
	->setCellValue('C9', 'Date Start')
	->setCellValue('D9', 'Start')
	->setCellValue('E9', 'Date End')
	->setCellValue('F9', 'End')
	->setCellValue('G9', 'KWH');
	  	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

	/*styling excel*/
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);

	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);

	$no_excl = 10;
	$n = 1;
	
	$this->load->helper('date');
	
	$date1=date_create($date_start);
	$date2=date_create($date_end);
		
	$date = date_format($date1,"Y-m-d");
	$end = date_format($date2,"Y-m-d");
	
	while (strtotime($date) <= strtotime($end)) {

	$data['meter_details_from'] = $this->Report_model->meter_daily_report_query($meter_name,$date); 
	
	if($data['meter_details_from']['min_wh_total'] == 0){

	@$min_wh_total = number_format($data['meter_details_from']['max_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['meter_details_from']['max_wh_total']+0, 2, '.', '');

	@$min_date = $data['meter_details_from']['max_date'];
	@$max_date = $data['meter_details_from']['max_date'];
 
	}else{

	@$min_wh_total = number_format($data['meter_details_from']['min_wh_total']+0, 2, '.', '');
	@$max_wh_total = number_format($data['meter_details_from']['max_wh_total']+0, 2, '.', '');

	@$min_date = $data['meter_details_from']['min_date'];
	@$max_date = $data['meter_details_from']['max_date'];
 
	}

	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format((($max_wh_total-$min_wh_total))+0, 2, '.', '');
	}
	$consumption_data[] = array('consumption' => $_wh_total);
	
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$date</td><td>$min_date</td><td>$min_wh_total</td><td>$max_date</td><td>$max_wh_total</td><td>$_wh_total</td>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $date)
	->setCellValue('C'.$no_excl, $min_date)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_date)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $_wh_total);
	
	$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
	
	$no_excl++;
	$n++;
	}
	
	$sum_consumption_data = 0;
	foreach($consumption_data as $item) {
	$sum_consumption_data += $item['consumption'];
	} 
	
	echo '<div class="col-md-2"></div><div class="col-md-8">';
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Customer Name:</b> $meter_client_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter Name:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table table-striped' border='1' bordercolor='#bdbdbd'><thead>"; 
	echo " <tr class='report'><th rowspan='2' scope='col'>#</th><th rowspan='2' scope='col'>Date</th><th scope='col'>Date Start</th><th scope='col'>Start</th><th scope='col'>Date End</th><th scope='col'>End</th><th scope='col'>KWh</th></tr></thead>";   
	
	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;
	
	echo "<tr>";
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td align='right'><b>Total Delivered:<b></td>";
	echo "<td><b>$sum_consumption_data</b></td>";
	echo "</tr>";
	echo "</tbody></table>";
	echo "<div>";
	echo '</div><div class="col-md-2"></div>';
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	$callStartTime = microtime(true);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//SAVING
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	$callStartTime = microtime(true);
	//$this->output->enable_profiler(TRUE);
	
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	public function meter_location_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');

	//METER DETAILS 
	 $meter_location = $_GET['meter_name'];
	 $meter_role = $_GET['meter_role'];

	 $date_log = explode(' - ', $_GET['date_log']);

	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{

		$date_start = $date_log[0];
		$date_end = $date_log[1];
	
	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 
	$site_name = $cut_off_info['site_name'];
	
	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_location_report_query_procedure_limit($meter_location, $date_start, $date_end, $meter_role); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	echo "No Result Found";
	}else{
	$report_name = "$meter_location.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Location Report")
	->setLastModifiedBy("Meter Location Report")
	->setTitle("Meter Location Report")
	->setSubject("Meter Location Report")
	->setDescription("Meter Location Report")
	->setKeywords("Meter Location Report")
	->setCategory("Meter Location Report");

	$objPHPExcel->getActiveSheet()
	->setCellValue('B3','=SUM(H:H)');
 
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	->setCellValue('A2', 'Meter Location')
	->setCellValue('B2', $meter_location)
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	->setCellValue('A3', 'Total KWH');

	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:G1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Customer Name')
	->setCellValue('C9', 'Meter ID')
	->setCellValue('D9', 'Date Start')
	->setCellValue('E9', 'Start')
	->setCellValue('F9', 'Date End')
	->setCellValue('G9', 'End')
	->setCellValue('H9', 'KWH');
	  
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('D8:F8');
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('G8:I8');
	  
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

	/*styling excel*/
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);
	
	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));
	
	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);

	$no_excl = 10;
	
	$n = 1;
	$this->load->helper('date');
	$meter_id = $this->input->post('meter_name');

	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	$meter_location_col = $meter_location_meterid_col['meter_site_name'];
	$meter_id_col = $meter_location_meterid_col['meter_name'];
	$meter_multiplier_col = $meter_location_meterid_col['meter_multiplier'];

	$client_name = $meter_location_meterid_col['customer_name'];
	$meter_client_name = "$client_name";
	
	if($meter_location_meterid_col['min_wh_total'] == 0){

	$min_wh_total = $meter_location_meterid_col['max_wh_total'];
	$max_wh_total = $meter_location_meterid_col['max_wh_total'];

	$_date_start = $meter_location_meterid_col['date_end'];
	$_date_end = $meter_location_meterid_col['date_end'];
 
	}else{

	$min_wh_total = $meter_location_meterid_col['min_wh_total'];
	$max_wh_total = $meter_location_meterid_col['max_wh_total'];

	$_date_start = $meter_location_meterid_col['date_start'];
	$_date_end = $meter_location_meterid_col['date_end'];
 
	}
		
	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format(((($max_wh_total-$min_wh_total) * $meter_multiplier_col ))+0, 2, '.', '');
	}
	
	$consumption_data[] = array('consumption' => $_wh_total);
	
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$meter_client_name</td><td>$meter_id_col</td><td>$_date_start</td><td>$min_wh_total</td><td>$_date_end</td><td>$max_wh_total</td><td>$_wh_total</td></tr>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $meter_client_name)
	->setCellValue('C'.$no_excl, $meter_id_col)
	->setCellValue('D'.$no_excl, $_date_start)
	->setCellValue('E'.$no_excl, $min_wh_total)
	->setCellValue('F'.$no_excl, $_date_end)
	->setCellValue('G'.$no_excl, $max_wh_total)
	->setCellValue('H'.$no_excl, $_wh_total);
	
	$no_excl++;
	$n++;
	endforeach;
	@$sum_consumption_data = 0;
	foreach(@$consumption_data as $item) {
	@$sum_consumption_data += @$item['consumption'];
	}
		
	$objPHPExcel->getActiveSheet()->setTitle('Meter Logs');
	$objPHPExcel->setActiveSheetIndex(0);
	$callStartTime = microtime(true);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//SAVING
	
	$objWriter->save(str_replace(__FILE__,"./reports/$report_name",__FILE__));
	$callEndTime = microtime(true);
	$callTime = $callEndTime - $callStartTime;
	$callStartTime = microtime(true);
	
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Description:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th rowspan='2' scope='col'>#</th><th rowspan='2' scope='col'>Customer</th><th rowspan='2' scope='col'>Meter ID</th><th scope='col'>Date Start</th><th scope='col'>Start</th><th scope='col'>Date End</th><th scope='col'>End</th><th scope='col'>KWH</th></tr></thead><tbody>";  
	
	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;
	
	echo "<tr>";
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td></td>"; 
	echo "<td align='right'><b>Total KWh:</b></td>";
	echo "<td><b>$sum_consumption_data</b></td>";
	echo "</tr>";
	echo "</tbody></table>";
	echo "</div>";
	/*$this->output->enable_profiler(TRUE);*/
	}
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	public function meter_location_sap_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $meter_location = $_GET['meter_name'];
	 $meter_role = $_GET['meter_role'];
	 $date_start = $_GET['date_start'];
 
 	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{
 
	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_name = $cut_off_info['site_name'];
	$cut_off = $date_start ."-".$_cut_off;

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_report_query($meter_location,$meter_role,$cut_off); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
		echo "No Result Found";
	}else{
		
	$company_no = $cut_off_info['company_no'];
	$report_name_tsv = "$meter_location"."_$company_no". "_$cut_off.csv";

	$_report_name_tsv = str_replace(' ','%20',$report_name_tsv);
	$myfile = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name_tsv) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	$no_excl = 10;
	$n = 1;	
	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	$meter_location_col = $meter_location_meterid_col['meter_site_name'];
	$meter_id_col = $meter_location_meterid_col['meter_name'];
	
	$client_name = $meter_location_meterid_col['customer_name'];
	$measuring_point = $meter_location_meterid_col['measuring_point'];

	$meter_client_name = rtrim("$client_name", " ");
	
	if($meter_location_meterid_col['datetime']==''){
	
	$date_generated = '00/00/00';
	$time_generated = '00:00:00';
	}else{
	$datetime = $meter_location_meterid_col['datetime'];
	$date2=date_create($datetime);
	$date_generated = date_format($date2,"m/d/y");
	$time_generated = date_format($date2,"H:i:s");
	}

	$min_wh_total = number_format($meter_location_meterid_col['wh_total']+0, 2, '.', '');

	$_wh_total=number_format(($min_wh_total)+0, 2, '.', '');

	$consumption_data[] = array('consumption' => $_wh_total);
		
	$meter_id_add_zero = str_pad($meter_id_col, 10, '0', STR_PAD_LEFT);	

	$row_data[] = array('rows' => "<tr><td>$n</td><td>$meter_id_add_zero</td><td></td><td></td><td>$meter_client_name</td><td></td><td>$_wh_total</td><td>$date_generated</td><td>$time_generated</td><td></td><td>$measuring_point</td></tr>");	

	$book_no = '';
	$acc_no = '';
	$prev_read = '';
	$consumption_meter = '';
	@$tsv .= "$meter_id_add_zero,$book_no,$acc_no,$meter_client_name,$prev_read,$_wh_total,$date_generated,$time_generated,$consumption_meter,$measuring_point\r\n";
	$no_excl++;
	$n++;
	endforeach;

	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Description:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Cut-Off:</b> $cut_off</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th rowspan='2' scope='col'>#</th><th rowspan='2' scope='col'>Meter ID</th><th rowspan='2' scope='col'>Book #</th><th rowspan='2' scope='col'>Account #</th><th rowspan='2' scope='col'>Customer</th><th rowspan='2' scope='col'>Previous Reading</th><th scope='col'>Current Reading</th><th scope='col'>Date</th><th scope='col'>Time</th><th rowspan='2' scope='col'>Consumption</th><th rowspan='2' scope='col'>Measuring Point</th></tr></thead><tbody>";  
	
	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;

	$write_tsv = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	fwrite($write_tsv, @$tsv);
	fclose($write_tsv);
	//$this->output->enable_profiler(TRUE);
	}
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	public function meter_abp_report_post()
	{
	
	
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	
	
	$this->load->helper('form');

	//METER DETAILS 
	 $meter_location = $_GET['meter_name'];
		
		/*GET LIST OF GATEWAY BY LOCATION*/
		$location_gateway_list = $this->Report_model->location_gateway_list($meter_location); 
		
		$site_info = $this->Report_model->get_selected_site_by_code($meter_location);
		
		$site_code = $site_info['site_code'];
		$site_name = $site_info['site_name'];
		$device_ip_range = $site_info['device_ip_range'];
		$ip_netmask = $site_info['ip_netmask'];
		$ip_gateway = $site_info['ip_gateway'];
		
		$server = $_SERVER['SERVER_ADDR'];
		//<a href="<?php echo base_url();f ?f>index.php/admin/site_management">Site Management</a>
		echo "<div align='right'><a href=" . base_url("index.php/admin_action_log_report/meter_abp_report_download?meter_name=$site_code") . " class='btn btn-primary'>DOWNLOAD</a></div><br>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Device Ip Address Range:</b> $device_ip_range</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Netmask:</b> $ip_netmask</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway:</b> $ip_gateway</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Server:</b>$server</div><br><br>";

		foreach ($location_gateway_list as $gateway_cols): 

				$rtu_sn_number = $gateway_cols['rtu_sn_number'];
				$rtu_physical_location = $gateway_cols['rtu_physical_location'];
				$phone_no_or_ip_address = $gateway_cols['phone_no_or_ip_address'];
				$idf_number = $gateway_cols['idf_number'];
				$idf_port = $gateway_cols['idf_port'];
				$mac_addr = $gateway_cols['mac_addr'];

		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway Serial No.:</b> $rtu_sn_number</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway MAC Address:</b> $mac_addr</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway IP Address:</b> $phone_no_or_ip_address</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway Location:</b> $rtu_physical_location</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>IDF Number:</b> $idf_number</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>IDF Port:</b> $idf_port</div>";
				
				$id = $gateway_cols['id'];	
 
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th rowspan='2' scope='col'>Item</th><th rowspan='2' scope='col'>Meter Type</th><th rowspan='2' scope='col'>Meter Serial No</th><th scope='col'>Location</th><th scope='col'>Meter Multiplier</th><th scope='col'>Config File</th><th scope='col'>Modbus Address</th><th scope='col'>Tenant</th><th scope='col'>Status</th></tr></thead><tbody>";  

					$gateway_meter_list = $this->Report_model->gateway_meter_list($id); 
					
					$n = 1;
					
					foreach ($gateway_meter_list as $meter_cols): 
					
							$meter_name_addressable = $meter_cols['meter_name_addressable'];
							if($meter_name_addressable == '1'){
							$meter_default_name = '';
							}
							else{
							$meter_default_name = $meter_cols['meter_default_name'];
							}
							$meter_name = $meter_cols['meter_name'];
							$physical_location = $meter_cols['physical_location'];
							$meter_config_file = $meter_cols['meter_config_file'];
							$customer_name = $meter_cols['customer_name'];
							$meter_multiplier = $meter_cols['meter_multiplier'];
							$meter_model = $meter_cols['meter_model'];
							$meter_type = $meter_cols['meter_type'];
							$meter_status = $meter_cols['meter_status'];
							//$meter_default_name = $meter_cols['meter_default_name'];
							
							echo "<tr><td>$n</td><td>$meter_type</td><td>$meter_name</td><td>$physical_location</td><td>$meter_multiplier</td><td>$meter_config_file</td><td>$meter_default_name</td><td>$customer_name</td><td>$meter_status</td></tr>";
					$n++;
					endforeach;
					
		echo "</table>";
		echo "</div><br><br>";		
		
		endforeach;
	
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}	
	
	public function meter_abp_report_download()
	{

	if($this->session->userdata('currently_logged_in_as')!=''){

	$this->load->helper('form');

	//METER DETAILS 
	$meter_location = $_GET['meter_name'];
	$report_name_s  = "As Built Plan";
		
																	$spreadsheet = new Spreadsheet();
							
																	$spreadsheet->getProperties()->setCreator("$report_name_s")
																								->setLastModifiedBy("$report_name_s")
																								->setTitle("$report_name_s")
																								->setSubject("$report_name_s")
																								->setDescription("$report_name_s")
																								->setKeywords("$report_name_s")
																								->setCategory("$report_name_s");

		/*GET LIST OF GATEWAY BY LOCATION*/
		$location_gateway_list = $this->Report_model->location_gateway_list($meter_location); 
		
		$site_info = $this->Report_model->get_selected_site_by_code($meter_location);
		
		$site_code = $site_info['site_code'];
		$site_name = $site_info['site_name'];
		$device_ip_range = $site_info['device_ip_range'];
		$ip_netmask = $site_info['ip_netmask'];
		$ip_gateway = $site_info['ip_gateway'];
		
		$server = $_SERVER['SERVER_ADDR'];
		
		$no_excel_sheet = 0;
		foreach ($location_gateway_list as $gateway_cols): 

				$rtu_sn_number = $gateway_cols['rtu_sn_number'];
				$rtu_physical_location = $gateway_cols['rtu_physical_location'];
				$phone_no_or_ip_address = $gateway_cols['phone_no_or_ip_address'];
				$idf_number = $gateway_cols['idf_number'];
				$idf_port = $gateway_cols['idf_port'];
				$mac_addr = $gateway_cols['mac_addr'];

																		/*START EXCEL HEADER and SHEET TITLE*/
																		$spreadsheet->createSheet();
																													
																		$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																			->setCellValue('A1', 'Site Name')
																			->setCellValue('A2', 'Device IP Address Range')
																			->setCellValue('A3', 'Netmask')
																			->setCellValue('A4', 'Gateway')
																			->setCellValue('A5', 'Server');
																			
																		$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																			->setCellValue('B1', $site_name)
																			->setCellValue('B2', $device_ip_range)
																			->setCellValue('B3', $ip_netmask)
																			->setCellValue('B4', $ip_gateway)
																			->setCellValue('B5', $server);
																			
																		$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																		->setCellValue('D1', 'Gateway Serial No.')
																		->setCellValue('D2', 'Gateway MAC Address')
																		->setCellValue('D3', 'Gateway IP Address')
																		->setCellValue('D4', 'Gateway Location')
																		->setCellValue('D5', 'IDF Number')
																		->setCellValue('D6', 'IDF Port');
																			
																		$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																		->setCellValue('E1', $rtu_sn_number)
																		->setCellValue('E2', $mac_addr)
																		->setCellValue('E3', $phone_no_or_ip_address)
																		->setCellValue('E4', $rtu_physical_location)
																		->setCellValue('E5', $idf_number)
																		->setCellValue('E6', $idf_port);

					$id = $gateway_cols['id'];

																		$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																		->setCellValue('A8', 'Item')
																		->setCellValue('B8', 'Meter Type')
																		->setCellValue('C8', 'Meter Serial No')
																		->setCellValue('D8', 'Location')
																		->setCellValue('E8', 'Meter Multiplier')
																		->setCellValue('F8', 'Config File')
																		->setCellValue('G8', 'Modbus Address')
																		->setCellValue('H8', 'Tenant')
																		->setCellValue('I8', 'Status');
							
																		/*font bold*/
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('A1:A5')->getFont()->setBold(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('D1:D6')->getFont()->setBold(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('A8:I8')->getFont()->setBold(true);
																		
																		/*set autosize*/
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('A')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('B')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('C')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('D')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('E')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('F')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('G')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('H')->setAutoSize(true);
																		$spreadsheet->getActiveSheet($no_excel_sheet)->getColumnDimension('I')->setAutoSize(true);
							
					$gateway_meter_list = $this->Report_model->gateway_meter_list($id); 
					
											$n = 1;
											$no_excel_data = 9;
											foreach ($gateway_meter_list as $meter_cols): 
													
													$meter_name_addressable = $meter_cols['meter_name_addressable'];
													
													if($meter_name_addressable == '1'){
													$meter_default_name = '';
													}
													else{
													$meter_default_name = $meter_cols['meter_default_name'];
													}
													$meter_name = $meter_cols['meter_name'];
													$physical_location = $meter_cols['physical_location'];
													$meter_config_file = $meter_cols['meter_config_file'];
													$customer_name = $meter_cols['customer_name'];
													$meter_multiplier = $meter_cols['meter_multiplier'];
													$meter_model = $meter_cols['meter_model'];
													$meter_type = $meter_cols['meter_type'];
													$meter_status = $meter_cols['meter_status'];
																				
																	$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																	->setCellValue("A$no_excel_data", $n)
																	->setCellValue("B$no_excel_data", $meter_type)
																	->setCellValue("C$no_excel_data", $meter_name)
																	->setCellValue("D$no_excel_data", $physical_location)
																	->setCellValue("E$no_excel_data", $meter_multiplier)
																	->setCellValue("F$no_excel_data", $meter_config_file)
																	->setCellValue("G$no_excel_data", $meter_default_name)
																	->setCellValue("H$no_excel_data", $customer_name)
																	->setCellValue("I$no_excel_data", $meter_status);
													
											$no_excel_data++;	
											$n++;
											endforeach;

											$sheet_title = preg_replace("/[^A-Za-z0-9\-]/", "", $rtu_sn_number);		
											$spreadsheet->getActiveSheet($no_excel_sheet)->setTitle("$sheet_title");
			
		$no_excel_sheet++;
		endforeach;

																	// Redirect output to a client’s web browser (Xlsx)
																	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
																	header('Content-Disposition: attachment;filename="'.$report_name_s.'.xlsx"');
																	header('Cache-Control: max-age=0');
																	// If you're serving to IE 9, then the following may be needed
																	header('Cache-Control: max-age=1');
																	
																	// If you're serving to IE over SSL, then the following may be needed
																	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
																	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
																	header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
																	header('Pragma: public'); // HTTP/1.0
																	
																	/*to download*/
																	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
																	$writer->save('php://output');
																	exit;	

	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	public function meter_location_sap_list_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $meter_location = $_GET['meter_name'];
	  /*$meter_role = $_GET['meter_role'];
	$date_start = $_GET['date_start'];*/
 
 	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{
 
	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_name = $cut_off_info['site_name'];

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_list_report_query($meter_location); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
		echo "No Result Found";
	}else{
		
	$company_no = $cut_off_info['company_no'];
	$report_name_tsv = "$meter_location"."_$company_no". "_LIST.csv";
	$_report_name_tsv = str_replace(' ','%20',$report_name_tsv);
	$myfile = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name_tsv) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	$no_excl = 10;
	$n = 1;	
		
	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	
	$meter_id_col = $meter_location_meterid_col['meter_name'];
	/*1 COMPANY_CODE*/ 					$COMPANY_CODE 			= $meter_location_meterid_col['company_no'];
	/*2 BUSINESS_ENTITY*/				$BUSINESS_ENTITY 		= $meter_location_meterid_col['meter_site_name'];
	/*3 BUILDING*/						$BUILDING 				= $meter_location_meterid_col['building'];
	/*4 LAND*/							$LAND 					= $meter_location_meterid_col['land'];
	/*5 RENTAL_OBJECT_NO*/				$RENTAL_OBJECT_NO 		= $meter_location_meterid_col['rental_object'];
	/*6 RENTAL_OBJECT_NAME*/			$RENTAL_OBJECT_NAME 	= $meter_location_meterid_col['rental_object_name'];
	/*7 USAGE TYPE*/					$USAGE_TYPE 			= $meter_location_meterid_col['usage_type'];
	/*8 RO VALID FROM*/					$RO_VALID_FROM 			= $meter_location_meterid_col['ro_valid_from'];
	/*9 RO VALID TO*/					$RO_VALID_TO 			= $meter_location_meterid_col['ro_valid_to'];
	/*10 CONTRACT_NUMBER*/				$CONTRACT_NUMBER 		= $meter_location_meterid_col['contract_number'];
	/*11 CONTRACT_NAME (TENANT_NAME)*/ 	$CONTRACT_NAME 			= $meter_location_meterid_col['customer_name'];
	/*12 METER_CHARACTERISTIC*/			$METER_CHARACTERISTIC 	= $meter_location_meterid_col['meter_characteristic'];	
	/*13 MEASPOINT*/					$MEASPOINT 				= $meter_location_meterid_col['measuring_point'];
	/*14 METER DESCRIPTION*/			$METER_DESCRIPTION		= str_pad($meter_id_col, 10, '0', STR_PAD_LEFT);
	/*15 MEASUREMENT_SEQUENCE*/			$MEASUREMENT_SEQUENCE	= $meter_location_meterid_col['measurement_sequence'];
	/*16 MEASUREMENT_SEPARATOR*/		$MEASUREMENT_SEPARATOR	= $meter_location_meterid_col['measurement_separator'];
	/*17 MEASUREMENT_MULTIPLIER*/		$MEASUREMENT_MULTIPLIER	= $meter_location_meterid_col['meter_multiplier'];
	/*18 METER_READING_DATE*/			$METER_READING_DATE		= $meter_location_meterid_col['meter_reading_date'];
	/*19 METER_READING*/				$METER_READING			= $meter_location_meterid_col['last_wh_total'];/*NAG IIBA BASE SA LAST READING NG METRO 15 minute interval*/
	/*10 METER_STATUS*/					$METER_STATUS			= $meter_location_meterid_col['meter_status'];
	/*21 PARTICIPATION GROUP*/			$PARTICIPATION_GROUP	= $meter_location_meterid_col['participation_group'];
	/*22 CREATION_DATE*/				$CREATION_DATE			= $meter_location_meterid_col['creation_date'];
	/*23 CREATION_TIME*/				$CREATION_TIME			= $meter_location_meterid_col['creation_time'];
	/*24 LAST_CHANGE_ON*/				$LAST_CHANGE_ON			= $meter_location_meterid_col['last_change_on'];
	/*25 LAST_CHANGE_BY*/				$LAST_CHANGE_BY			= $meter_location_meterid_col['last_change_by'];

	$row_data[] = array('rows' => "<tr><td>$n</td>
	<td>$COMPANY_CODE</td>
	<td>$BUSINESS_ENTITY</td>
	<td>$BUILDING</td>
	<td>$LAND</td>
	<td>$RENTAL_OBJECT_NO</td>
	<td>$RENTAL_OBJECT_NAME</td>
	<td>$USAGE_TYPE</td>
	<td>$RO_VALID_FROM</td>
	<td>$RO_VALID_TO</td>
	<td>$CONTRACT_NUMBER</td>
	<td>$CONTRACT_NAME</td>
	<td>$METER_CHARACTERISTIC</td>
	<td>$MEASPOINT</td>
	<td>$METER_DESCRIPTION</td>
	<td>$MEASUREMENT_SEQUENCE</td>
	<td>$MEASUREMENT_SEPARATOR</td>
	<td>$MEASUREMENT_MULTIPLIER</td>
	<td>$METER_READING_DATE</td>
	<td>$METER_READING</td>
	<td>$METER_STATUS</td>
	<td>$PARTICIPATION_GROUP</td>
	<td>$CREATION_DATE</td>
	<td>$CREATION_TIME</td>
	<td>$LAST_CHANGE_ON</td>
	<td>$LAST_CHANGE_BY</td>
	</tr>");	
	@$tsv .= "$COMPANY_CODE,$BUSINESS_ENTITY,$BUILDING,$LAND,$RENTAL_OBJECT_NO,$RENTAL_OBJECT_NAME,$USAGE_TYPE,$RO_VALID_FROM,$RO_VALID_TO,$CONTRACT_NUMBER,$CONTRACT_NAME,$MEASPOINT,$METER_DESCRIPTION,$MEASUREMENT_SEQUENCE,$MEASUREMENT_SEPARATOR,$MEASUREMENT_MULTIPLIER,$METER_READING_DATE,$METER_READING_DATE,$METER_READING,$METER_STATUS,$PARTICIPATION_GROUP,$CREATION_DATE,$CREATION_TIME,$LAST_CHANGE_ON,$LAST_CHANGE_BY\r\n";
	$no_excl++;
	$n++;
	endforeach;

	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Description:</b> $site_name</div>";
	echo "<br>";
		
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'>
	<th rowspan='' scope='col'>#</th>
	<th rowspan='' scope='col'>COMPANY_CODE</th>
	<th rowspan='' scope='col'>BUSINESS_ENTITY</th>
	<th rowspan='' scope='col'>BUILDING</th>
	<th rowspan='' scope='col'>LAND</th>
	<th rowspan='' scope='col'>RENTAL_OBJECT_NO</th>
	<th scope='col'>RENTAL_OBJECT_NAME</th>
	<th scope='col'>USAGE TYPE</th>
	<th scope='col'>RO VALID FROM</th>
	<th rowspan='' scope='col'>RO VALID TO</th>
	<th rowspan='' scope='col'>CONTRACT_NUMBER</th>
	<th rowspan='' scope='col'>CONTRACT_NAME TENANT_NAME)</th>
	<th rowspan='' scope='col'>METER_CHARACTERISTIC</th>
	<th rowspan='' scope='col'>MEASPOINT</th>
	<th rowspan='' scope='col'>METER DESCRIPTION</th>
	<th rowspan='' scope='col'>MEASUREMENT_SEQUENCE</th>
	<th rowspan='' scope='col'>MEASUREMENT_SEPARATOR</th>
	<th rowspan='' scope='col'>MEASUREMENT_MULTIPLIER</th>
	<th rowspan='' scope='col'>METER_READING_DATE</th>
	<th rowspan='' scope='col'>METER_READING</th>
	<th rowspan='' scope='col'>METER_STATUS</th>
	<th rowspan='' scope='col'>PARTICIPATION GROUP</th>
	<th rowspan='' scope='col'>CREATION_DATE</th>
	<th rowspan='' scope='col'>CREATION_TIME</th>
	<th rowspan='' scope='col'>LAST_CHANGE_ON</th>
	<th rowspan='' scope='col'>LAST_CHANGE_BY</th>
	</tr></thead><tbody>";  
	
	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;

	$write_tsv = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	fwrite($write_tsv, @$tsv);
	fclose($write_tsv);
	//$this->output->enable_profiler(TRUE);
	}
	}
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}	
}