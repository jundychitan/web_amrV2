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
	
	public function distinct_meter_physical_location()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	$business_entity = $this->input->post('business_entity');
	/*QUERY METER LIST BY LOCATION*/
	$meter_list = $this->Report_model->get_physical_location_meter_list($business_entity);
	?>

   <div class="col-md-2"> 
  
                    <div class="form-group">
                    <label>Location</label>
                    <select class="form-control" id="meter_physical_location" name="meter_physical_location">
                    <option value="All" selected>All</option>
					<?php
                    foreach ($meter_list as $meter_col): 
                    $physical_location = $meter_col['physical_location'];
                    ?>
                    <option value="<?php echo "$physical_location";?>"><?php echo "$physical_location"; ?></option>
                    <?php
                    endforeach;
                    ?>
                    </select>
                    </div>
  </div>
  
    <?php
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
	
	public function get_building_list()
	{

	$this->load->helper('form');
	$this->load->library('form_validation');
	$location_id = $this->input->post('location_id');
	/*QUERY METER LIST BY LOCATION*/
	$meter_list = $this->Report_model->get_building_list($location_id);
	?>
    <select class="form-control" id="building_list" name="building_list">
    <option value="All" selected="selected" title="Default/One Building Only">All</option>
	<?php
    echo count($meter_list);
    foreach ($meter_list as $meter_col): 
    $building_name = $meter_col['building_name'];
    $_building_description = $meter_col['building_description'];
	
	if($_building_description == ''){
    	$building_description = "" ;
	}else{
    	$building_description = " | $_building_description" ;
	}
	
	if($building_name == ''){
    ?>
    
    <?php
	}else{
    ?>
    <option value="<?=$building_name;?>"><?=$building_name;?><?=$building_description;?></option>
    <?php
	}
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
	$location_id = $this->input->post('location_id');
	
	$data['site_details'] = $this->Report_model->site_details($location_id);
	$site_name = $data['site_details']['site_name'];
	
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name,$location_id); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$business_entity = $data['meter_details']['business_entity'];
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	@$rtu_sn_number = $data['meter_details']['rtu_sn_number'];
	
	$data_result_row = $data['meter_all_logs_report']=$this->Report_model->meter_all_logs_report($meter_location); 
	
	$date_log = explode(' - ', $this->input->post('date_log'));

	$date_start = $date_log[0];
	$date_end = $date_log[1];	
	
	$data_result = $data_result_row;
	
	if($data_result==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
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
	echo "<div align='left' style='font-size: 14px;display: flex;''><b style='width: 150px; display: block;'>Meter S/N:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Description:</b> $site_name</div>";	
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway S/N:</b> $rtu_sn_number</div>";
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
	echo "<th style='text-align:left !important;'>max_rec_kw_dmd</th><th style='text-align:left !important;'>max_rec_kw_dmd_time</th><th style='text-align:left !important;'>mac_address</th></tr></thead><tbody>";  
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
   
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $business_entity)

	->setCellValue('A5', 'Site Code')
	->setCellValue('B5', $meter_location)

	->setCellValue('A6', 'Site Description')
	->setCellValue('B6', $site_name)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)

	->setCellValue('D4', 'Gateway S/N')
	->setCellValue('E4', $rtu_sn_number);
	//merge
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A7', '#')
	->setCellValue('B7', 'Date Time')
	->setCellValue('C7', 'vrms_a')
	->setCellValue('D7', 'vrms_b')
	->setCellValue('E7', 'vrms_c')
	->setCellValue('F7', 'irms_a')
	->setCellValue('G7', 'irms_b')
	->setCellValue('H7', 'irms_c')
	->setCellValue('I7', 'freq')
	->setCellValue('J7', 'pf')
	->setCellValue('K7', 'kw')
	->setCellValue('L7', 'kva')
	->setCellValue('M7', 'kvar')
	->setCellValue('N7', 'kwh_del')
	->setCellValue('O7', 'kwh_rec')
	->setCellValue('P7', 'kwh_net')
	->setCellValue('Q7', 'kwh_total')
	->setCellValue('R7', 'kvarh_neg')
	->setCellValue('S7', 'kvarh_pos')
	->setCellValue('T7', 'kvarh_net')
	->setCellValue('U7', 'kvarh_total')
	->setCellValue('V7', 'kvah_total')
	->setCellValue('W7', 'max_rec_kw_dmd')
	->setCellValue('X7', 'max_rec_kw_dmd_time');
	  
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

	/*styling excel */  
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D6')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A7:X7')->getFont()->setBold(true);

	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));

	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	
	$no_excl = 8;
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
	$mac_addr = $meter_all_logs_report_result['mac_addr'];
	
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
	echo "<td>$mac_addr</td>";
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
	/*15 Minute DEMAND*/
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
	
	 $location_id = $this->input->post('location_id');
	 $meter_name = $this->input->post('meter_name');
	
	$data['site_details'] = $this->Report_model->site_details($location_id);
	$site_name = $data['site_details']['site_name'];
	
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name, $location_id); 

	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$business_entity = $data['meter_details']['business_entity'];
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	@$meter_multiplier = $data['meter_details']['meter_multiplier'];
	@$rtu_sn_number = $data['meter_details']['rtu_sn_number'];

	 $date_log = explode(' - ', $this->input->post('date_log'));

		$date_start = $date_log[0];
		$date_end = $date_log[1];	
	
	$report_name = "$meter_location-$meter_id". "_15min.xlsx";
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
	
	#$objPHPExcel->getActiveSheet()
	#->setCellValue('B5','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $business_entity)

	->setCellValue('A5', 'Site Code')
	->setCellValue('B5', $meter_location)

	->setCellValue('A6', 'Site Description')
	->setCellValue('B6', $site_name)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('D4', 'Gateway S/N')
	->setCellValue('E4', $rtu_sn_number);

	#->setCellValue('A5', 'Total KW Demand');

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
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D4')->getFont()->setBold(true);
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
	$data['hourly_result'] = $this->Report_model->fifteen_minutes_query($meter_name,$new_dt,$meter_location); 

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
	
	$total_intervaltime = round($_total_intervaltime/15,2);
	
	if($max_wh_total == '0' || $max_wh_total==NULL || ($max_wh_total-$min_wh_total)==0){
	@$_wh_total=0.00;
	$new_formula_15_demand = 0;	
	}else{
	@$_wh_total = number_format(($max_wh_total-$min_wh_total)+0, 2, '.', '');
	$new_formula_15_demand = number_format((60/$_total_intervaltime)*($_wh_total)*$meter_multiplier +0, 2, '.', ''); 	
	}
	$consumption_data[] = array('consumption' => $new_formula_15_demand+0);
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$new_dt_excel</td><td>$min_datetime</td><td>$min_wh_total</td><td>$max_datetime</td><td>$max_wh_total</td><td title = '(60/$_total_intervaltime)*($_wh_total)*$meter_multiplier'>$new_formula_15_demand</td></tr>");

	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $new_dt_excel)
	->setCellValue('C'.$no_excl, $min_datetime)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_datetime)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $new_formula_15_demand);
	
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
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter S/N:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway S/N:</b> $rtu_sn_number</div>";
	#echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KW:</b> $sum_consumption_data</div>";
	
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
	/*Hourly DEMAND*/	
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
	
	$location_id = $this->input->post('location_id');
	$meter_name = $this->input->post('meter_name');

	$data['site_details'] = $this->Report_model->site_details($location_id);
	$site_name = $data['site_details']['site_name'];
	
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name, $location_id); 
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$business_entity = $data['meter_details']['business_entity'];
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	@$meter_multiplier = $data['meter_details']['meter_multiplier'];
	@$rtu_sn_number = $data['meter_details']['rtu_sn_number'];

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
	
	#$objPHPExcel->getActiveSheet()
	#->setCellValue('B5','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $business_entity)

	->setCellValue('A5', 'Site Code')
	->setCellValue('B5', $meter_location)

	->setCellValue('A6', 'Site Description')
	->setCellValue('B6', $site_name)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('D4', 'Gateway S/N')
	->setCellValue('E4', $rtu_sn_number);

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
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D4')->getFont()->setBold(true);
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
	$data['hourly_result'] = $this->Report_model->hourly_query($meter_name,$new_dt,$meter_location); 

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

	$total_intervaltime = round($_total_intervaltime/15,0);

	if($max_wh_total == '0' || $max_wh_total==NULL || ($max_wh_total-$min_wh_total)==0){
	@$_wh_total=0.00;
	$new_formula_hr_demand = 0;	
	}else{
	@$_wh_total = number_format(($max_wh_total-$min_wh_total)+0, 2, '.', '');
	$new_formula_hr_demand = number_format((60/$_total_intervaltime)*($_wh_total)*$meter_multiplier +0, 2, '.', ''); 	
	}


	$consumption_data[] = array('consumption' => $new_formula_hr_demand);
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$new_dt_excel</td><td>$min_datetime</td><td>$min_wh_total</td><td>$max_datetime</td><td>$max_wh_total</td><td title = '(60/$_total_intervaltime)*($_wh_total)*$meter_multiplier'>$new_formula_hr_demand</td></tr>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $new_dt_excel)
	->setCellValue('C'.$no_excl, $min_datetime)
	->setCellValue('D'.$no_excl, $min_wh_total)
	->setCellValue('E'.$no_excl, $max_datetime)
	->setCellValue('F'.$no_excl, $max_wh_total)
	->setCellValue('G'.$no_excl, $new_formula_hr_demand);
	
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
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Meter S/N:</b> $meter_id</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway S/N:</b> $rtu_sn_number</div>";

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
	/*HOURLY CONSUMPTION*/
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
	
	 $location_id = $this->input->post('location_id');
	 $meter_name = $this->input->post('meter_name');
	
	$data['site_details'] = $this->Report_model->site_details($location_id);
	$site_name = $data['site_details']['site_name'];
		
	$data['meter_details']=$this->Report_model->get_meter_info($meter_name, $location_id); 
	
	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$business_entity = $data['meter_details']['business_entity'];
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	@$meter_multiplier = $data['meter_details']['meter_multiplier'];
	@$rtu_sn_number = $data['meter_details']['rtu_sn_number'];
	
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
	->setCellValue('B7','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
	
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $business_entity)

	->setCellValue('A5', 'Site Code')
	->setCellValue('B5', $meter_location)

	->setCellValue('A6', 'Site Description')
	->setCellValue('B6', $site_name)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('D4', 'Gateway S/N')
	->setCellValue('E4', $rtu_sn_number)

	->setCellValue('A7', 'Total KWh Consumption');

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
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A7')->getFont()->setBold(true);
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
	$data['hourly_result'] = $this->Report_model->hourly_query($meter_name,$new_dt,$meter_location); 

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
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway S/N:</b> $rtu_sn_number</div>";
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
	
	echo "<tr>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td></td>";
	echo "<td align='right'><b>Total KWh Consumption:<b></td>";
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
	/*DAILY CONSUMPTION*/
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
	 $location_id = $this->input->post('location_id');
	 $meter_name = $this->input->post('meter_name');

	$data['site_details'] = $this->Report_model->site_details($location_id);
	$site_name = $data['site_details']['site_name'];

	$data['meter_details']=$this->Report_model->get_meter_info($meter_name, $location_id); 

	$client_name = $data['meter_details']['customer_name'];
	$meter_client_name = "$client_name";
	@$business_entity = $data['meter_details']['business_entity'];
	@$meter_location = $data['meter_details']['meter_site_name'];
	@$meter_id = $data['meter_details']['meter_name'];
	@$meter_multiplier = $data['meter_details']['meter_multiplier'];
	@$rtu_sn_number = $data['meter_details']['rtu_sn_number'];
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
	->setCellValue('B7','=SUM(G:G)');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')
	
	->setCellValue('A2', 'Customer Name')
	->setCellValue('B2', $meter_client_name)
	
	->setCellValue('A3', 'Meter Name')
	->setCellValue('B3', $meter_id)
		
	->setCellValue('A4', 'Business Entity')
	->setCellValue('B4', $business_entity)

	->setCellValue('A5', 'Site Code')
	->setCellValue('B5', $meter_location)

	->setCellValue('A6', 'Site Description')
	->setCellValue('B6', $site_name)
	
	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	
	->setCellValue('A7', 'Total KWh Consumption');
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
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D4')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A7')->getFont()->setBold(true);
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

	$data['meter_details_from'] = $this->Report_model->meter_daily_report_query($meter_name,$date,$meter_location); 
	
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
	$_wh_total=number_format((($max_wh_total-$min_wh_total) * $meter_multiplier)+0, 2, '.', '');
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
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Gateway S/N:</b> $rtu_sn_number</div>";
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
	echo "<td align='right'><b>Total KWh Consumption:<b></td>";
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
	/*SITE REPORT in DATE RANGE*/
	public function meter_location_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	$this->load->helper('form');

	 //METER DETAILS 
	 $meter_location = $_GET['meter_name'];
	 $meter_role = $_GET['meter_role'];

	 $sap_info = $_GET['sap_info'];
	 $building_list = $_GET['building_list'];

	 $date_log = explode(' - ', $_GET['date_log']);

	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{

		$date_start = $date_log[0];
		$date_end = $date_log[1];
	
	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 
	$site_name = $cut_off_info['site_name'];
	$business_entity = $cut_off_info['business_entity'];
	$site_code = $cut_off_info['site_code'];
	
	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_location_report_query_procedure_limit($meter_location, $date_start, $date_end, $meter_role, $sap_info, $building_list); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
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
	->setCellValue('B5','=SUM(M:M)');
 
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'Report')

	->setCellValue('A2', 'Business Entity')
	->setCellValue('B2', $business_entity)

	->setCellValue('A3', 'Site Code')
	->setCellValue('B3', $site_code)

	->setCellValue('A4', 'Site Description')
	->setCellValue('B4', $site_name)

	->setCellValue('D2', 'Date Start')
	->setCellValue('E2', $date_start)
	->setCellValue('D3', 'Date End')
	->setCellValue('E3', $date_end)
	->setCellValue('A5', 'Total KWH');

	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:G1');
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A9', '#')
	->setCellValue('B9', 'Measuring Point')
	->setCellValue('C9', 'Customer Name')
	->setCellValue('D9', 'Meter ID')
	->setCellValue('E9', 'Gateway')
	->setCellValue('F9', 'Building')
	->setCellValue('G9', 'Physical Location')
	->setCellValue('H9', 'Date Start')
	->setCellValue('I9', 'Start')
	->setCellValue('J9', 'Date End')
	->setCellValue('K9', 'End')
	->setCellValue('L9', 'Multiplier')
	->setCellValue('M9', 'KWH');
	  
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
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
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
	@$meter_multiplier = $meter_location_meterid_col['meter_multiplier'];

	@$building = $meter_location_meterid_col['building'];
	@$measuring_point = $meter_location_meterid_col['measuring_point'];

	$client_name = $meter_location_meterid_col['customer_name'];
	$meter_client_name = "$client_name";
	
	$rtu_sn_number_col = $meter_location_meterid_col['rtu_sn_number'];
	$physical_location_col = $meter_location_meterid_col['physical_location'];

	if($meter_location_meterid_col['min_wh_total'] == 0){

	$_min_wh_total = $meter_location_meterid_col['max_wh_total'];
	$_max_wh_total = $meter_location_meterid_col['max_wh_total'];

	$min_wh_total = number_format(( $_min_wh_total )+0, 2, '.', '');
	$max_wh_total = number_format(( $_max_wh_total )+0, 2, '.', '');

	$_date_start = $meter_location_meterid_col['date_end'];
	$_date_end = $meter_location_meterid_col['date_end'];
 
	}else{

	$_min_wh_total = $meter_location_meterid_col['min_wh_total'];
	$_max_wh_total = $meter_location_meterid_col['max_wh_total'];

	$min_wh_total = number_format(( $_min_wh_total )+0, 2, '.', '');
	$max_wh_total = number_format(( $_max_wh_total )+0, 2, '.', '');

	$_date_start = $meter_location_meterid_col['date_start'];
	$_date_end = $meter_location_meterid_col['date_end'];
 
	}
		
	if($max_wh_total == '0' || $max_wh_total==NULL){
	$_wh_total=0.00;
	}else{
	$_wh_total=number_format(( ($max_wh_total-$min_wh_total) * $meter_multiplier )+0, 2, '.', '');
	}
	
	$consumption_data[] = array('consumption' => $_wh_total);
	
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$measuring_point</td><td>$meter_client_name</td><td>$meter_id_col</td><td>$rtu_sn_number_col</td><td>$building</td><td>$physical_location_col</td><td>$_date_start</td><td>$min_wh_total</td><td>$_date_end</td><td>$max_wh_total</td><td>$meter_multiplier</td><td>$_wh_total</td></tr>");
	
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $measuring_point)
	->setCellValue('C'.$no_excl, $meter_client_name)
	->setCellValue('D'.$no_excl, $meter_id_col)
	->setCellValue('E'.$no_excl, $rtu_sn_number_col)
	->setCellValue('F'.$no_excl, $building)
	->setCellValue('G'.$no_excl, $physical_location_col)
	->setCellValue('H'.$no_excl, $_date_start)
	->setCellValue('I'.$no_excl, $min_wh_total)
	->setCellValue('J'.$no_excl, $_date_end)
	->setCellValue('K'.$no_excl, $max_wh_total)
	->setCellValue('L'.$no_excl, $max_wh_total)
	->setCellValue('M'.$no_excl, $_wh_total);
	
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
	
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Name:</b> $site_name</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date Start:</b> $date_start</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Date End:</b> $date_end</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Total KWh:</b> $sum_consumption_data</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th rowspan='2' scope='col'>#</th><th rowspan='2' scope='col'>Measuring Point</th><th rowspan='2' scope='col'>Customer</th><th rowspan='2' scope='col'>Meter ID</th><th rowspan='2' scope='col'>Gateway</th><th rowspan='2' scope='col'>Building</th><th scope='col'>Physical Location</th><th scope='col'>Date Start</th><th scope='col'>Start</th><th scope='col'>Date End</th><th scope='col'>End</th><th scope='col'>Multiplier</th><th scope='col'>KWh</th></tr></thead><tbody>";  
	
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
	/*
	METER REPORT PER CUT OFF SAP FORMAT
	OLD VERSION
	*/
	public function meter_location_sap_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $meter_location = $_GET['meter_name'];
	 $meter_role = $_GET['meter_role'];
	 $date_start = $_GET['date_start'];
	 $building_list = $_GET['building_list'];

	 if($meter_role=='Client Meter'){
	 	$default_meter_info = 'meter_name';
	 }
	 else{
	 	$default_meter_info = 'meter_name';
	 }

 	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{
 
	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_name = $cut_off_info['site_name'];
	$cut_off = $date_start;

	$_cut_off = date('d.m.Y', strtotime($cut_off));

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_report_query_old_format($meter_location,$meter_role,$cut_off,$building_list); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
	}else{
		
	$company_no = $cut_off_info['company_no'];
	$report_name_tsv = "$meter_location"."_$company_no". "_$cut_off.txt";

	$_report_name_tsv = str_replace(' ','%20',$report_name_tsv);
	$myfile = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	
	echo "<div align='right'><a href=" . base_url("index.php/report_action/meter_location_sap_report_download?file_name=$report_name_tsv") . " class='btn btn-primary'>DOWNLOAD</a></div>";
	
	//echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name_tsv) . " class='btn btn-primary' target='_blank'>DOWNLOAD</a></div>";
	$no_excl = 10; 
	$n = 1;	
	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	$meter_location_col = $meter_location_meterid_col['meter_site_name'];
	$meter_id_col = $meter_location_meterid_col["$default_meter_info"];
	
	$client_name = $meter_location_meterid_col['customer_name'];
	$measuring_point = $meter_location_meterid_col['measuring_point'];
	$contract_number = $meter_location_meterid_col['contract_number'];
	$ro_valid_to = $meter_location_meterid_col['ro_valid_to'];
	
	$meter_client_name = substr($client_name,0,23) . " r";

	if($meter_location_meterid_col['datetime']==''){
	
	$date_generated = '00/00/00';
	$time_generated = '00:00:00';
	
	}else{
	
	$datetime = $meter_location_meterid_col['datetime'];
	$date2=date_create($_cut_off);
	$date_generated = date_format($date2,"d/m/y");
	$time_generated = date_format($date2,"H:i:s");
	
	}

	$min_wh_total = number_format($meter_location_meterid_col['wh_total']+0, 2, '.', '');

	$_wh_total=number_format(($min_wh_total)+0, 2, '.', '');

	$consumption_data[] = array('consumption' => $_wh_total);
		
	//$_meter_id_add_zero = str_pad($meter_id_col, 2, 0, STR_PAD_LEFT);			
	//$_meter_id_add_zero = sprintf('%04d', $meter_id_col);
	//echo $_meter_id_add_zero;
	
	$meter_id_add_zero = str_pad("00$meter_id_col", 8, ' ', STR_PAD_RIGHT);	
	//$meter_id_add_zero = $meter_id_col;	 

	$book_no = '';
	$acc_no = '';
	$prev_read = '';
	$consumption_meter = '';

	$book_no_add_space 					= str_pad($book_no, 5, ' ');	
	$acc_no_add_space 					= str_pad($acc_no, 12, ' ');	
	$meter_client_name_add_space 		= str_pad($meter_client_name, 25, ' ');	
	$prev_read_add_space 				= str_pad($prev_read, 11, ' ');	
	$_wh_total_add_space 				= str_pad($_wh_total, 11, ' ');	
	$date_generated_add_space 			= str_pad($date_generated, 8, ' ');	
	$time_generated_add_space 			= str_pad($time_generated, 8, ' ');	
	$consumption_meter_add_space 		= str_pad($consumption_meter, 13, ' ');	
	
						/*RO_VALID_TO for checking if SAP Entry are Valid or Invalid*/
						$_date_format = strtotime($ro_valid_to);
						$_server_time=date('Y-m-d');
						$server_time_current = strtotime($_server_time);
						$date_format = date('Y-m-d',$_date_format);			
				
		/**/
		/**/
		if((( $_wh_total != 0 )))
		{
						
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$meter_id_add_zero</td><td></td><td></td><td>$meter_client_name</td><td></td><td>$_wh_total</td><td>$date_generated</td><td>$time_generated</td><td></td><td>$measuring_point</td></tr>");	
	
	@$tsv .= $meter_id_add_zero.$book_no_add_space.$acc_no_add_space.$meter_client_name_add_space.$prev_read_add_space.$_wh_total_add_space.$date_generated_add_space.$time_generated_add_space."\r\n";
	
		$n++;
		}else{
		$row_data[] = array('rows' => "");	
		//@$tsv .= "";			
		}
		
	$no_excl++;
	
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
	
	/*
	$data = 'some data'.PHP_EOL;
$fp = fopen('somefile', 'a');
fwrite($fp, $data);
	*/

	//new_txt_line = preg_replace('/[[:cntrl:]]/', '', @$tsv);

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
	
	/*DOWNLOAD*/
	public function meter_location_sap_report_download()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $file_name = $_GET['file_name'];
	
	header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename("$file_name"));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize("reports/$file_name"));
    readfile("reports/$file_name");
    exit;
	//$this->output->enable_profiler(TRUE);
	

	}
	else{
	redirect('Login/invalid');  
	}
	   
	}	
	
	/*
	METER REPORT PER CUT OFF SAP FORMAT
	NEW VERSION
	*/
	public function meter_location_sap_report_post_new()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $meter_location = $_GET['meter_name'];
	 $meter_role = $_GET['meter_role'];
	 $date_start = $_GET['date_start'];
	 $building_list = $_GET['building_list'];
	 
	 if($meter_role=='Client Meter'){
	 	$default_meter_info = 'measuring_point_description';
	 }
	 else{
	 	$default_meter_info = 'meter_name';
	 }
	 
 	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{
	if($meter_location=='ROBINSONS'){

	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_name = $cut_off_info['site_name'];
	$cut_off = $date_start;

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_report_query_robinsons($meter_location,$meter_role,$cut_off,$building_list); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
	}else{

	$_cut_off = date('d.m.Y', strtotime($cut_off));
		
	$company_no = $cut_off_info['company_no'];
	$business_entity = $cut_off_info['business_entity'];
	$site_code = $cut_off_info['site_code'];

	$report_name_tsv = "$business_entity"."_$company_no". "_$_cut_off.csv";

	$_report_name_tsv = str_replace(' ','%20',$report_name_tsv);
	$myfile = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name_tsv) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	
	$n = 1;	

	@$tsv .= "Meter No.,Date,Time,Initial,Building,Tenant Name,Meter Type,Current Reading,Previous Reading,Multiplier,Current Cons.,Previous Cons.,% Diff\r\n";

	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_details): 
	
	//number_format($meter_location_meterid_col['wh_total']+0, 2, '.', '');
	
	$meter_location 			= $meter_details['meter_site_name'];	
	$meter_id 					= $meter_details['meter_name'];
	$client_name 				= $meter_details['customer_name'];
	$building 					= $meter_details['building'];
	$meter_type 				= $meter_details['meter_type'];
	$current_reading 			= number_format($meter_details['current_reading']+0, 2, '.', '');
	$current_reading_datetime 	= $meter_details['current_reading_datetime'];
	$prev_reading 				= number_format($meter_details['prev_reading']+0, 2, '.', '');
	$prev_reading_datetime 		= $meter_details['prev_reading_datetime'];
	$meter_multiplier 			= $meter_details['meter_multiplier'];
	
	$past_two_months_reading 			= number_format($meter_details['past_two_months_reading']+0, 2, '.', '');
	$past_two_months_reading_datetime 	= $meter_details['past_two_months_reading_datetime'];
		
	$meter_client_name = rtrim("$client_name", " ");
	
	$current_consumption = number_format(($current_reading - $prev_reading) * $meter_multiplier+0, 2, '.', '');	
	$previous_consumption = number_format(($prev_reading-$past_two_months_reading) * $meter_multiplier+0, 2, '.', '');

	if($current_consumption==0 ||$previous_consumption==0){
		$_difference_consumption = 0;
		$difference_consumption = 0;
	}else{
		$_difference_consumption =  number_format(($previous_consumption/$current_consumption)+0, 2, '.', '');
		$difference_consumption = 100 - ($_difference_consumption*100);
	}
	
	if($meter_details['current_reading_datetime']==''){
	
	$date2=date_create($_cut_off);
	$date_generated = date_format($date2,"m/d/Y");
	$time_generated = date_format($date2,"00:00:00");
	
	}else{
		
	$datetime = $meter_details['current_reading_datetime'];
	$date2=date_create($datetime);
	$date_generated = date_format($date2,"m/d/Y");
	$time_generated = date_format($date2,"H:i:s");
	
	}
			
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$meter_id</td><td>$current_reading</td><td>$date_generated</td><td>$time_generated</td><td></td>$building<td></td><td>$client_name</td><td>$meter_type</td><td>$current_reading</td><td>$prev_reading</td><td>$meter_multiplier</td><td>$current_consumption</td><td>$previous_consumption</td><td>$difference_consumption %</td></tr>");	
		
		@$tsv .= "$meter_id,$current_reading,$date_generated,$time_generated,,$building,$client_name,$meter_type,$current_reading,$prev_reading,$meter_multiplier,$current_consumption,$previous_consumption,$difference_consumption\r\n";
		$n++;
	
	endforeach;

	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $site_code</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Description:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Cut-Off:</b> $cut_off</div>";
	echo "<br>";
	
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'>
	<th scope='col'>#</th>
	<th scope='col'>Meter No.</th>
	<th scope='col'>Reading</th>
	<th scope='col'>Date</th>
	<th scope='col'>Time</th>
	<th scope='col'>Initial</th>
	<th scope='col'>Building</th>
	<th scope='col'>Tenant Name</th>
	<th scope='col'>Meter Type</th>
	<th scope='col'>Current Reading</th>
	<th scope='col'>Previous Reading</th>
	<th scope='col'>Multiplier</th>
	<th scope='col'>Current Cons.</th>
	<th scope='col'>Previous Cons.</th>
	<th scope='col'>% Dif</th>	
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
	else{
		
		
  
	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_name = $cut_off_info['site_name'];
	$cut_off = $date_start;

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_report_query($meter_location,$meter_role,$cut_off,$building_list); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
	}else{

	$_cut_off = date('d.m.Y', strtotime($cut_off));
		
	$company_no = $cut_off_info['company_no'];
	$business_entity = $cut_off_info['business_entity'];
	$site_code = $cut_off_info['site_code'];

	$report_name_tsv = "$business_entity"."_$company_no". "_$_cut_off.csv";

	$_report_name_tsv = str_replace(' ','%20',$report_name_tsv);
	$myfile = fopen("reports/$report_name_tsv", "w") or die("Unable to open file!");
	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name_tsv) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	$no_excl = 10;
	$n = 1;	

	@$tsv .= "Meter ID,Customer,Current Reading (KWh),Date,Time,Measuring Point\r\n";

	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	$meter_location_col = $meter_location_meterid_col['meter_site_name'];
		
	$meter_id_col = $meter_location_meterid_col["$default_meter_info"];
	
	$client_name 		= $meter_location_meterid_col['customer_name'];
	$measuring_point 	= $meter_location_meterid_col['measuring_point'];
	$contract_number 	= $meter_location_meterid_col['contract_number'];
	$ro_valid_to 		= $meter_location_meterid_col['ro_valid_to'];

	$meter_client_name = rtrim("$client_name", " ");
	
	if($meter_location_meterid_col['datetime']==''){
	
	$date2=date_create($_cut_off);
	$date_generated = date_format($date2,"d.m.Y");
	$time_generated = date_format($date2,"H:i:s");
	}else{
	$datetime = $meter_location_meterid_col['datetime'];
	$date2=date_create($_cut_off);
	$date_generated = date_format($date2,"d.m.Y");
	$time_generated = date_format($date2,"H:i:s");
	}

	$min_wh_total = number_format($meter_location_meterid_col['wh_total']+0, 2, '.', '');
	$_chk_alpha = $meter_location_meterid_col['wh_total']+0;
	$chk_alpha = "$_chk_alpha";
							
					#CHECK alphabet E -exponent
					if(preg_match('/E/', $chk_alpha)){
						$wh_total_type = "Y";
					}else{
						$wh_total_type = "N";
					} 
	 
	$_wh_total=$min_wh_total;
	$consumption_data[] = array('consumption' => $_wh_total);
	$meter_id_add_zero = $meter_id_col;	

		if($_wh_total != 0 && $wh_total_type=='N')
		{
	$row_data[] = array('rows' => "<tr><td>$n</td><td>$meter_id_add_zero</td><td>$meter_client_name</td><td>$_wh_total</td><td>$date_generated</td><td>$time_generated</td><td>$measuring_point</td></tr>");	
		@$tsv .= "$meter_id_add_zero,$meter_client_name,$_wh_total,$date_generated,$time_generated,$measuring_point\r\n";
		$n++;
		}
		else{	
		$row_data[] = array('rows' => "");	
		}

	$book_no = '';
	$acc_no = '';
	$prev_read = '';
	$consumption_meter = '';
	$no_excl++;
	#$n++;
	endforeach;

	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $site_code</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Description:</b> $meter_location</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Cut-Off:</b> $cut_off</div>";
	echo "<br>";
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'><th rowspan='2' scope='col'>#</th><th rowspan='2' scope='col'>Meter ID</th><th rowspan='2' scope='col'>Customer</th><th scope='col'>Current Reading (KWh)</th><th scope='col'>Date</th><th scope='col'>Time</th><th rowspan='2' scope='col'>Measuring Point</th></tr></thead><tbody>";  
	
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
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	/*METER LIST PER GATEWAY or AS BUILT PLAN*/	
	public function meter_abp_report_post()
	{
	
	
	
	if($this->session->userdata('currently_logged_in_as')!=''){
	
	
	
	$this->load->helper('form');

	//METER DETAILS 
	 $meter_location = $_GET['business_entity'];
		
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
		echo "<div align='right'><a href=" . base_url("index.php/report_action/meter_abp_report_download?meter_name=$site_code") . " class='btn btn-primary'>DOWNLOAD</a></div><br>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Site Name:</b> $site_name</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Device IP Address Range:</b> $device_ip_range</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Netmask:</b> $ip_netmask</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Gateway:</b> $ip_gateway</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Server:</b>$server</div><br><br>";

		foreach ($location_gateway_list as $gateway_cols): 

				$rtu_sn_number = $gateway_cols['rtu_sn_number'];
				$rtu_physical_location = $gateway_cols['rtu_physical_location'];
				$phone_no_or_ip_address = $gateway_cols['phone_no_or_ip_address'];
				$idf_number = $gateway_cols['idf_number'];
				$idf_port = $gateway_cols['idf_port'];
				$mac_addr = $gateway_cols['mac_addr'];

		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Gateway Serial No.:</b> $rtu_sn_number</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Gateway MAC Address:</b> $mac_addr</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Gateway IP Address:</b> $phone_no_or_ip_address</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>Gateway Location:</b> $rtu_physical_location</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>IDF Number:</b> $idf_number</div>";
		echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 180px; display: block;'>IDF Port:</b> $idf_port</div>";
				
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
	/*METER LIST PER GATEWAY or AS BUILT PLAN DL*/	
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
	/*METER LIST SAP VERION*/
	public function meter_location_sap_list_report_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')!=''){

	 $meter_location = $_GET['business_entity'];

 	if ($meter_location=='')
	{
		echo '<center><b>PLEASE SELECT LOCATION</b></center>';
	}else{
 
	$this->load->helper('date');

	$cut_off_info = $this->Report_model->query_site_cut_off($meter_location); 

	$_cut_off = $cut_off_info['site_cut_off'];
	$site_business_entity = $cut_off_info['business_entity'];
	$site_code = $cut_off_info['site_code'];
	$site_name = $cut_off_info['site_name'];

	$data['meter_distinct_by_location_and_meterid'] = $this->Report_model->meter_sap_list_report_query($meter_location); 
	if(count($data['meter_distinct_by_location_and_meterid'])==0){
	?>
    <div class="col-md-12">
    	<div class="row">
    		<div class="col-md-4"> 
            </div>
            <div class="col-md-4"> 
			    <div align="center">
                <table width="100%">
                <tr>
                <td align="center">
                No Result Found
                </td>
                </tr>
                </table>
                </div>
    		<div class="col-md-4"> 
            </div>
    		</div>
    	</div>
    </div>
	<?php
	}else{
		
	$company_no = $cut_off_info['company_no'];

	$report_name = "$meter_location".'_'."$company_no"."_METER_MASTER.xlsx";
	$_report_name = str_replace(' ','%20',$report_name);
	
	require_once APPPATH."/third_party/PHPExcel/PHPExcel.php";

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Meter Master")
	->setLastModifiedBy("Meter Master")
	->setTitle("Meter Master")
	->setSubject("Meter Master")
	->setDescription("Meter Master")
	->setKeywords("Meter Master")
	->setCategory("Meter Master");

	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', 'COMPANY_CODE')
	->setCellValue('B1', 'BUSINESS_ENTITY')
	->setCellValue('C1', 'BUILDING')
	->setCellValue('D1', 'BUILDING DESCRIPTION')
	->setCellValue('E1', 'LAND')
	->setCellValue('F1', 'LAND DESCRIPTION')
	->setCellValue('G1', 'RENTAL_OBJECT_NO')
	->setCellValue('H1', 'RENTAL_OBJECT_NAME')
	->setCellValue('I1', 'USAGE_TYPE')
	->setCellValue('J1', 'RO_VALID_FROM')
	->setCellValue('K1', 'RO_VALID_TO')
	->setCellValue('L1', 'CONTRACT_NUMBER')
	->setCellValue('M1', 'CONTRACT_NAME')
	->setCellValue('N1', 'METER_CHARACTERISTIC')
	->setCellValue('O1', 'MEASPOINT')
	->setCellValue('P1', 'METER_DESCRIPTION')
	->setCellValue('Q1', 'MEASUREMENT_SEQUENCE')
	->setCellValue('R1', 'MEASUREMENT_SEPARATOR')
	->setCellValue('S1', 'MEASUREMENT_MULTIPLIER')
	->setCellValue('T1', 'METER_READING_DATE')
	->setCellValue('U1', 'METER_READING')
	->setCellValue('V1', 'METER_STATUS')
	->setCellValue('W1', 'PARTICIPATION_GROUP')
	->setCellValue('X1', 'CREATION_DATE')
	->setCellValue('Y1', 'creation_by')
	->setCellValue('Z1', 'LAST_CHANGE_ON')
	->setCellValue('AA1', 'LAST_CHANGE_BY');
	  	  
	/**/
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
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
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);

	/*styling excel
	$objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFont()->setBold(true);   
	$objPHPExcel->getActiveSheet(0)->getStyle('D2:D3')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet(0)->getStyle('G2:G3')->getFont()->setBold(true);

	$objPHPExcel->getActiveSheet(0)->getStyle('A1:A6')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->getFont()->setBold(true);
	*/
	/*	$style = array(
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,));


	$objPHPExcel->getActiveSheet()->getStyle('A1:N1')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A9:V9')->applyFromArray($style);
	$objPHPExcel->getActiveSheet()->getStyle('A8:V8')->applyFromArray($style);
*/

	echo "<div align='right'><a href=" . base_url('/reports/'. $_report_name) . " class='btn btn-primary'>DOWNLOAD</a></div>";
	$no_excl = 2;
	$n = 1;	
		
	foreach ($data['meter_distinct_by_location_and_meterid'] as $meter_location_meterid_col): 
	
	$meter_id_col = $meter_location_meterid_col['meter_name'];
	/*1 COMPANY_CODE*/ 					$COMPANY_CODE 			= $meter_location_meterid_col['company_no'];
	/*2 BUSINESS_ENTITY*/				$BUSINESS_ENTITY 		= $meter_location_meterid_col['business_entity'];
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
	/*14 METER DESCRIPTION				$METER_DESCRIPTION		= str_pad($meter_id_col, 10, '0', STR_PAD_LEFT);*/
	/*14 METER DESCRIPTION*/			$METER_DESCRIPTION		= $meter_id_col;
	/*15 MEASUREMENT_SEQUENCE*/			$MEASUREMENT_SEQUENCE	= $meter_location_meterid_col['measurement_sequence'];
	/*16 MEASUREMENT_SEPARATOR*/		$MEASUREMENT_SEPARATOR	= $meter_location_meterid_col['measurement_separator'];
	/*17 MEASUREMENT_MULTIPLIER*/		$MEASUREMENT_MULTIPLIER	= $meter_location_meterid_col['meter_multiplier'];
	/*18 METER_READING_DATE*/			$METER_READING_DATE		= $meter_location_meterid_col['meter_reading_date'];
	/*19 METER_READING*/				$METER_READING			= $meter_location_meterid_col['meter_reading'];/*NAG IIBA BASE SA LAST READING NG METRO 15 minute interval*/
	/*10 METER_STATUS*/					$METER_STATUS			= $meter_location_meterid_col['meter_status'];
	/*21 PARTICIPATION GROUP*/			$PARTICIPATION_GROUP	= $meter_location_meterid_col['participation_group'];
	/*22 CREATION_DATE*/				$CREATION_DATE			= $meter_location_meterid_col['creation_date'];
	/*23 CREATION_BY*/					$CREATION_BY			= $meter_location_meterid_col['creation_by'];
	/*24 LAST_CHANGE_ON*/				$LAST_CHANGE_ON			= $meter_location_meterid_col['last_change_on'];
	/*25 LAST_CHANGE_BY*/				$LAST_CHANGE_BY			= $meter_location_meterid_col['last_change_by'];

	/*NEW Column Jan 4 2021*/
	/*26 BUILDING_DESCRIPTION*/			$BUILDING_DESCRIPTION	= $meter_location_meterid_col['building_description'];
	/*27 LAND_DESCRIPTION*/				$LAND_DESCRIPTION 		= $meter_location_meterid_col['land_description'];

	$row_data[] = array('rows' => "<tr><td>$n</td>
	<td>$COMPANY_CODE</td>
	<td>$BUSINESS_ENTITY</td>
	<td>$BUILDING</td>
	<td>$BUILDING_DESCRIPTION</td>
	<td>$LAND</td>
	<td>$LAND_DESCRIPTION</td>
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
	<td>$CREATION_BY</td>
	<td>$LAST_CHANGE_ON</td>
	<td>$LAST_CHANGE_BY</td>
	</tr>");	

	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $COMPANY_CODE)
	->setCellValue('B'.$no_excl, $BUSINESS_ENTITY)
	->setCellValue('C'.$no_excl, $BUILDING_DESCRIPTION)
	->setCellValue('D'.$no_excl, $BUILDING)
	->setCellValue('E'.$no_excl, $LAND)
	->setCellValue('F'.$no_excl, $LAND_DESCRIPTION)
	->setCellValue('G'.$no_excl, $RENTAL_OBJECT_NO)
	->setCellValue('H'.$no_excl, $RENTAL_OBJECT_NAME)
	->setCellValue('I'.$no_excl, $USAGE_TYPE)
	->setCellValue('J'.$no_excl, $RO_VALID_FROM)
	->setCellValue('K'.$no_excl, $RO_VALID_TO)
	->setCellValue('L'.$no_excl, $CONTRACT_NUMBER)
	->setCellValue('M'.$no_excl, $CONTRACT_NAME)
	->setCellValue('N'.$no_excl, $METER_CHARACTERISTIC)
	->setCellValue('O'.$no_excl, $MEASPOINT)
	->setCellValue('P'.$no_excl, $METER_DESCRIPTION)
	->setCellValue('Q'.$no_excl, $MEASUREMENT_SEQUENCE)
	->setCellValue('R'.$no_excl, $MEASUREMENT_SEPARATOR)
	->setCellValue('S'.$no_excl, $MEASUREMENT_MULTIPLIER)
	->setCellValue('T'.$no_excl, $METER_READING_DATE)
	->setCellValue('U'.$no_excl, $METER_READING)
	->setCellValue('V'.$no_excl, $METER_STATUS)
	->setCellValue('W'.$no_excl, $PARTICIPATION_GROUP)
	->setCellValue('X'.$no_excl, $CREATION_DATE)
	->setCellValue('Y'.$no_excl, $CREATION_BY)
	->setCellValue('Z'.$no_excl, $LAST_CHANGE_ON)
	->setCellValue('AA'.$no_excl, $LAST_CHANGE_BY);
	
	$no_excl++;
	$n++;
	endforeach;

	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Business Entity:</b> $site_business_entity</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Code:</b> $site_code</div>";
	echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: 150px; display: block;'>Site Description:</b> $site_name</div>";
	echo "<br>";
		
	echo "<div class='table-data-span'>";
	echo "<table class='table' border='1' bordercolor='#bdbdbd'><thead><tr class='report'>
	<th>#</th>
	<th>COMPANY_CODE</th>
	<th>BUSINESS_ENTITY</th>
	<th>BUILDING</th>
	<th>BUILDING DESCRIPTION</th>
	<th>LAND</th>
	<th>LAND DESCRIPTION</th>
	<th>RENTAL_OBJECT_NO</th>
	<th>RENTAL_OBJECT_NAME</th>
	<th>USAGE TYPE</th>
	<th>RO VALID FROM</th>
	<th>RO VALID TO</th>
	<th>CONTRACT_NUMBER</th>
	<th>CONTRACT_NAME TENANT_NAME)</th>
	<th>METER_CHARACTERISTIC</th>
	<th>MEASPOINT</th>
	<th>METER DESCRIPTION</th>
	<th>MEASUREMENT_SEQUENCE</th>
	<th>MEASUREMENT_SEPARATOR</th>
	<th>MEASUREMENT_MULTIPLIER</th>
	<th>METER_READING_DATE</th>
	<th>METER_READING</th>
	<th>METER_STATUS</th>
	<th>PARTICIPATION GROUP</th>
	<th>CREATION_DATE</th>
	<th>CREATION_BY</th>
	<th>LAST_CHANGE_ON</th>
	<th>LAST_CHANGE_BY</th>
	</tr></thead><tbody>";  
	
	foreach ($row_data as $row_cols): 
	echo $row_cols['rows'];
	endforeach;

	$objPHPExcel->getActiveSheet()->setTitle('METER MASTER');
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
	}
	else{
	redirect('Login/invalid');  
	}
	   
	}		
	/*VIEW OFFLINE METERS*/
	public function view_offline_meters()
	{
	
	$site_id = $this->input->post('site_id');
	$rtu_id = $this->input->post('rtu_id');
	
	?>
    <div class="col-md-2">        
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Offline Meters</div>
    <div align="right">
	<?php
	echo "<br><a href=" . base_url("index.php/report_action/download_offline_meters?site_id=$site_id&rtu_id=$rtu_id") . " class='btn btn-primary'>DOWNLOAD</a>&nbsp;";
    echo "<button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button><br><br>";
    ?>
    </div>
	<div class='table-data-span'>
	<table class='table table-striped'>
    <thead>
    <tr class='report'>
    <th style='text-align:left !important;' >#</th>
    <th style='text-align:left !important;' >Meter ID</th>
    <th style='text-align:left !important;' >Description</th>
    <th style='text-align:center !important;' >Gateway Serial #</th>
    <th style='text-align:center !important;' >Status</th>
    <th style='text-align:center !important;'>Last Reading Date</th>
    <th style='text-align:center !important;'>Offline Duration</th>
	</tr>
    </thead>
    
    <tbody>
    <?php
	/* view_offline_meters */
	date_default_timezone_set('Asia/Manila');
	$server_time=date('Y-m-d H:i:s');

	if($rtu_id == 0){
	$offline_meters_row = $this->Report_model->offline_meters($site_id,$server_time); 
	}else{
	$offline_meters_row = $this->Report_model->offline_meters_per_gateway($site_id,$rtu_id,$server_time); 
	}
	
	$no = 1;
	foreach ($offline_meters_row as $offline_meters_cols){
	
	$meter_id_col = $offline_meters_cols['meter_name'];
	$customer_name = $offline_meters_cols['customer_name'];
	$last_log_update = $offline_meters_cols['last_log_update'];
	$rtu_serial_no = $offline_meters_cols['rtu_sn_number'];
	$meter_status = $offline_meters_cols['meter_status'];

	$date1=date_create("$server_time");
	$date2=date_create("$last_log_update");

	$diff=date_diff($date1,$date2);
	if($diff->format("%a")>1){
		$day_txt = 'Days';
	}else{
		$day_txt = 'Day';
	}

	if($diff->format("%h")>1){
		$hr_txt = 'Hours';
	}else{
		$hr_txt = 'Hour';
	}

	if($diff->format("%i")>1){
		$min_txt = 'Minutes';
	}else{
		$min_txt = 'Minute';
	}
	
	$days_last_active 		= $diff->format("%a $day_txt");
	$hours_last_active 		= $diff->format("%h $hr_txt");
	$minutes_last_active 	= $diff->format("%i $min_txt");

	if($last_log_update=='0000-00-00 00:00:00'){
		$date_last_active = "<div align='center'>-</div>";
	}else{
		$date_last_active = $days_last_active.", ".$hours_last_active." and ".$minutes_last_active;
	}

	?>
    <tr>
    <td><?=$no;?></td>
    <td><?=$meter_id_col;?></td>
    <td><?=$customer_name;?></td>
    <td><?=$rtu_serial_no;?></td>
    <td><?php echo $meter_status; ?></td>
    <td><?=$last_log_update;?></td>
   	<td><?php echo $date_last_active; ?></td>
    </tr>
    <?php 
	$no++;
	} 
	
	?>
    </tbody>
    </table>

    </div>
    </div>
    <div class="col-md-2">
  
    </div>
    
 	<?php
    }
	/*DOWNLOAD OFFLINE METERS*/	
	public function download_offline_meters()
	{

	if($this->session->userdata('currently_logged_in_as')!=''){

		$this->load->helper('form');

		$site_id = $_GET['site_id'];
		$rtu_id = $_GET['rtu_id'];

		$site_info = $this->Report_model->get_selected_site_by_id($site_id);
		
		$site_code = $site_info['site_code'];
		$site_name = $site_info['site_name'];
		
		$report_name_s  = "$site_name Offline Meters";
		
		$spreadsheet = new Spreadsheet();
							
		$spreadsheet->getProperties()->setCreator("$report_name_s")
		->setLastModifiedBy("$report_name_s")
		->setTitle("$report_name_s")
		->setSubject("$report_name_s")
		->setDescription("$report_name_s")
		->setKeywords("$report_name_s")
		->setCategory("$report_name_s");
		
		$no_excel_sheet = 0;
		$spreadsheet->setActiveSheetIndex(0)
		->setCellValue('A1', '#')
		->setCellValue('B1', 'Meter ID')
		->setCellValue('C1', 'Description')
		->setCellValue('D1', 'Gateway Serial #')
		->setCellValue('E1', 'Status')
		->setCellValue('F1', 'Last Reading Date')
		->setCellValue('g1', 'Offline Duration');
	
	$no_excl = 2;
	$n = 1;
	
	date_default_timezone_set('Asia/Manila');
	$server_time=date('Y-m-d H:i:s');

	if($rtu_id == 0){
	$offline_meters_row = $this->Report_model->offline_meters($site_id,$server_time); 
	}else{
	$offline_meters_row = $this->Report_model->offline_meters_per_gateway($site_id,$rtu_id,$server_time); 
	}

	foreach ($offline_meters_row as $offline_meters_cols){
	
	$meter_id_col = $offline_meters_cols['meter_name'];
	$customer_name = $offline_meters_cols['customer_name'];
	$last_log_update = $offline_meters_cols['last_log_update'];
	$rtu_serial_no = $offline_meters_cols['rtu_sn_number'];
	$meter_status = $offline_meters_cols['meter_status'];

	$date1=date_create("$server_time");
	$date2=date_create("$last_log_update");

	$diff=date_diff($date1,$date2);
	if($diff->format("%a")>1){
		$day_txt = 'Days';
	}else{
		$day_txt = 'Day';
	}

	if($diff->format("%h")>1){
		$hr_txt = 'Hours';
	}else{
		$hr_txt = 'Hour';
	}

	if($diff->format("%i")>1){
		$min_txt = 'Minutes';
	}else{
		$min_txt = 'Minute';
	}
	
	$days_last_active 		= $diff->format("%a $day_txt");
	$hours_last_active 		= $diff->format("%h $hr_txt");
	$minutes_last_active 	= $diff->format("%i $min_txt");
	    
	if($last_log_update=='0000-00-00 00:00:00'){
		$date_last_active = "N/A";
	}else{
		$date_last_active = $days_last_active.", ".$hours_last_active." and ".$minutes_last_active;
	}

	$spreadsheet->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $meter_id_col)
	->setCellValue('C'.$no_excl, $customer_name)
	->setCellValue('D'.$no_excl, $rtu_serial_no)
	->setCellValue('E'.$no_excl, $meter_status)
	->setCellValue('F'.$no_excl, $last_log_update)
	->setCellValue('G'.$no_excl, $date_last_active); 
	
	$no_excl++;
	$n++;
	}
	
	$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	
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
	/*VIEW OFFLINE METERS*/
	public function view_offline_gateway()
	{
	
	$site_id = $this->input->post('site_id');
	
	?>
    <div class="col-md-2">        
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Offline Gateway</div>
    <div align="right">
	<?php
	echo "<br><a href=" . base_url("index.php/report_action/download_offline_gateway?site_id=$site_id") . " class='btn btn-primary'>DOWNLOAD</a>&nbsp;";
    echo "<button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button><br><br>";
    ?>
    </div>
	<div class='table-data-span'>
	<table class='table table-striped'>
    <thead>
    <tr class='report'>
    <th style='text-align:left !important;' >#</th>
    <th style='text-align:left !important;' >Site</th>
    <th style='text-align:left !important;' >Serial #</th>
    <th style='text-align:left !important;' >Physical Location</th>
    <th style='text-align:center !important;' >IP Address</th>
    <th style='text-align:left !important;' >MAC Address</th>
    <th style='text-align:center !important;' >Firmware Version</th>
    <th style='text-align:center !important;' colspan='2'>Last Update</th>
	</tr>
    </thead>
    
    <tbody>
    <?php
	/* view_offline_meters */
	date_default_timezone_set('Asia/Manila');
	$server_time=date('Y-m-d H:i:s');

	if($site_id == 0){
	$offline_gateway_row = $this->Report_model->offline_gateway($server_time); 
	}else{
	$offline_gateway_row = $this->Report_model->offline_gateway_per_site($server_time,$site_id); 
	}
	$no = 1;
	foreach ($offline_gateway_row as $offline_gateway_cols){

	$rtu_sn_number 				= $offline_gateway_cols['rtu_sn_number'];
	$rtu_physical_location 		= $offline_gateway_cols['rtu_physical_location'];
	$phone_no_or_ip_address 	= $offline_gateway_cols['phone_no_or_ip_address'];
	$mac_addr 					= $offline_gateway_cols['mac_addr'];

	$rtu_site_name 				= $offline_gateway_cols['rtu_site_name'];
	$soft_rev 					= $offline_gateway_cols['soft_rev'];
	$last_log_update 			= $offline_gateway_cols['last_log_update'];

	$date1=date_create("$server_time");
	$date2=date_create("$last_log_update");

	$diff=date_diff($date1,$date2);
	
	if($diff->format("%a")>1){
		$day_txt = 'Days';
	}else{
		$day_txt = 'Day';
	}

	if($diff->format("%h")>1){
		$hr_txt = 'Hours';
	}else{
		$hr_txt = 'Hour';
	}

	if($diff->format("%i")>1){
		$min_txt = 'Minutes';
	}else{
		$min_txt = 'Minute';
	}
	
	$days_last_active 		= $diff->format("%a $day_txt");
	$hours_last_active 		= $diff->format("%h $hr_txt");
	$minutes_last_active 	= $diff->format("%i $min_txt");


	if($last_log_update=='0000-00-00 00:00:00'){
		$gateway_status = "<div align='center'>-</div>";
	}else{
		$gateway_status = $days_last_active.", ".$hours_last_active." and ".$minutes_last_active;
	}

	?>
    <tr>
    <td><?=$no;?></td>
    <td><?=$rtu_site_name;?></td>
    <td><?=$rtu_sn_number;?></td>
    <td><?=$rtu_physical_location;?></td>
    <td><?=$phone_no_or_ip_address;?></td>
    <td><?=$mac_addr;?></td>
    <td><?=$soft_rev;?></td>
    <td><?=$gateway_status;?></td>
    <td><?=$last_log_update;?></td>
    </tr>
    <?php 
	$no++;
	} 
	
	?>
    </tbody>
    </table>
    
  
    
    </div>
    </div>
    <div class="col-md-2">
  
    </div>
    
 	<?php
    }
	/*DOWNLOAD OFFLINE METERS*/	
	public function download_offline_gateway()
	{

	if($this->session->userdata('currently_logged_in_as')!=''){

		$this->load->helper('form');

		/* view_offline_meters */
		date_default_timezone_set('Asia/Manila');
		$server_time=date('Y-m-d H:i:s');
		$site_id = $_GET['site_id'];
	
		if($site_id == 0){
		$offline_gateway_row = $this->Report_model->offline_gateway($server_time); 
		}else{
		$offline_gateway_row = $this->Report_model->offline_gateway_per_site($server_time,$site_id); 
		}
		
		$report_name_s  = "Offline Gateway";
		
		$spreadsheet = new Spreadsheet();
							
		$spreadsheet->getProperties()->setCreator("$report_name_s")
		->setLastModifiedBy("$report_name_s")
		->setTitle("$report_name_s")
		->setSubject("$report_name_s")
		->setDescription("$report_name_s")
		->setKeywords("$report_name_s")
		->setCategory("$report_name_s");
		
		$no_excel_sheet = 0;

		$spreadsheet->setActiveSheetIndex(0)
		->setCellValue('A1', 'Datetime')
		->setCellValue('B1', $server_time);

		$spreadsheet->setActiveSheetIndex(0)
		->setCellValue('A2', '#')
		->setCellValue('B2', 'Site Code')
		->setCellValue('C2', 'Serial #')
		->setCellValue('D2', 'Physical Location')
		->setCellValue('E2', 'IP Address')
		->setCellValue('F2', 'MAC Address')
		->setCellValue('G2', 'Firmware Version')
		->setCellValue('H2', 'Last Update')
		->setCellValue('I2', 'Datetime');
	
	$no_excl = 3;
	$n = 1;
	
	foreach ($offline_gateway_row as $offline_gateway_cols){

	$rtu_sn_number 				= $offline_gateway_cols['rtu_sn_number'];
	$rtu_physical_location 		= $offline_gateway_cols['rtu_physical_location'];
	$phone_no_or_ip_address 	= $offline_gateway_cols['phone_no_or_ip_address'];
	$mac_addr 					= $offline_gateway_cols['mac_addr'];

	$rtu_site_name 				= $offline_gateway_cols['rtu_site_name'];
	$soft_rev 					= $offline_gateway_cols['soft_rev'];
	$last_log_update 			= $offline_gateway_cols['last_log_update'];

	$date1=date_create("$server_time");
	$date2=date_create("$last_log_update");

	$diff=date_diff($date1,$date2);
	
	if($diff->format("%a")>1){
		$day_txt = 'Days';
	}else{
		$day_txt = 'Day';
	}

	if($diff->format("%h")>1){
		$hr_txt = 'Hours';
	}else{
		$hr_txt = 'Hour';
	}

	if($diff->format("%i")>1){
		$min_txt = 'Minutes';
	}else{
		$min_txt = 'Minute';
	}
	
	$days_last_active 		= $diff->format("%a $day_txt");
	$hours_last_active 		= $diff->format("%h $hr_txt");
	$minutes_last_active 	= $diff->format("%i $min_txt");


	if($last_log_update=='0000-00-00 00:00:00'){
		$gateway_status = "N/A";
	}else{
		$gateway_status = $days_last_active.", ".$hours_last_active." and ".$minutes_last_active;
	}

	$spreadsheet->setActiveSheetIndex(0)
	->setCellValue('A'.$no_excl, $n)
	->setCellValue('B'.$no_excl, $rtu_site_name)
	->setCellValue('C'.$no_excl, $rtu_sn_number)
	->setCellValue('D'.$no_excl, $rtu_physical_location)
	->setCellValue('E'.$no_excl, $phone_no_or_ip_address)
	->setCellValue('F'.$no_excl, $mac_addr)
	->setCellValue('G'.$no_excl, $soft_rev)
	->setCellValue('H'.$no_excl, $gateway_status)
	->setCellValue('I'.$no_excl, $last_log_update); 
	
	$no_excl++;
	$n++;
	}
	
	$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
	/**/	
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
	/**/	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');
		exit;	

	}
	else{
	redirect('Login/invalid');  
	}
	   
	}
	
	/*METER LIST PER PHYSICAL LOCATION or AS BUILT PLAN*/	
	public function meter_list_per_location_report_post()
	{	

	if($this->session->userdata('currently_logged_in_as')!=''){

	$this->load->helper('form');

	//METER DETAILS 
	 $business_entity = $_GET['business_entity'];
		
		/*GET LIST OF GATEWAY BY LOCATION*/
		$location_gateway_list = $this->Report_model->get_physical_location_meter_list($business_entity); 
		
		$site_info = $this->Report_model->get_selected_site_by_code($business_entity);
		
		$business_entity = $site_info['business_entity'];
		$site_code = $site_info['site_code'];
		$site_name = $site_info['site_name'];
		$device_ip_range = $site_info['device_ip_range'];
		$ip_netmask = $site_info['ip_netmask'];
		$ip_gateway = $site_info['ip_gateway'];
		
		$server = $_SERVER['SERVER_ADDR'];
		//<a href="<?php echo base_url();f ?f>index.php/admin/site_management">Site Management</a>
				echo "<div align='right'><a href=" . base_url("index.php/report_action/meter_list_per_location_report_download?business_entity=$site_code") . " class='btn btn-primary'>DOWNLOAD</a></div><br>";

		foreach ($location_gateway_list as $gateway_cols): 

				 $physical_location = $gateway_cols['physical_location'];

				echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: auto; display: block;'>Business Entity:</b> $business_entity</div>";
				echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: auto; display: block;'>Site Code:</b> $site_code</div>";
				echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: auto; display: block;'>Site Description:</b> $site_name</div>";
				echo "<div align='left' style='font-size: 14px;display: flex;'><b style='width: auto; display: block;'>Physical Location:</b> $physical_location</div>";
			
				echo "<div class='table-data-span'>";
				echo "<table class='table' border='1' bordercolor='#bdbdbd'>
				<thead><tr class='report'>
				<th rowspan='2' scope='col'>#</th>
				<th rowspan='2' scope='col'>Meter Serial No</th>
				<th scope='col'>Tenant</th>
				<th scope='col'>Measuring Point</th>
				<th scope='col'>Meter Multiplier</th>
				<th scope='col'>Gateway S/N</th>
				<th scope='col'>Status</th>
				<th scope='col'>Last Reading Date</th>
				</tr></thead><tbody>";  

					$gateway_meter_list = $this->Report_model->meter_by_physical_location_list($physical_location,$site_code); 
					
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
							$measuring_point = $meter_cols['measuring_point'];
							$last_log_update = $meter_cols['last_log_update'];
							$rtu_sn_number = $meter_cols['rtu_sn_number'];
							//$meter_default_name = $meter_cols['meter_default_name'];
							
					echo "<tr>
					<td>$n</td>
					<td>$meter_name</td>
					<td>$customer_name</td>
					<td>$measuring_point</td>
					<td>$meter_multiplier</td>
					<td>$rtu_sn_number</td>
					<td>$meter_status</td>
					<td>$last_log_update</td>
					</tr>";
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
	
	public function meter_list_per_location_report_download()
	{

	if($this->session->userdata('currently_logged_in_as')!=''){

	$this->load->helper('form');

	//METER DETAILS 
	$business_entity = $_GET['business_entity'];
	$report_name_s  = "Meter List";
		
		$spreadsheet = new Spreadsheet();
							
		$spreadsheet->getProperties()->setCreator("$report_name_s")
									->setLastModifiedBy("$report_name_s")
									->setTitle("$report_name_s")
									->setSubject("$report_name_s")
									->setDescription("$report_name_s")
									->setKeywords("$report_name_s")
									->setCategory("$report_name_s");

		$location_gateway_list = $this->Report_model->get_physical_location_meter_list($business_entity); 
		
		$site_info = $this->Report_model->get_selected_site_by_code($business_entity);
		
		$site_code = $site_info['site_code'];
		$site_name = $site_info['site_name'];
		$business_entity = $site_info['business_entity'];
		$device_ip_range = $site_info['device_ip_range'];
		$ip_netmask = $site_info['ip_netmask'];
		$ip_gateway = $site_info['ip_gateway'];
		
		$no_excel_sheet = 0;
		foreach ($location_gateway_list as $gateway_cols): 

				 $physical_location = $gateway_cols['physical_location'];

										/*START EXCEL HEADER and SHEET TITLE*/
										$spreadsheet->createSheet();
																													
										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
													->setCellValue('A1', 'Business Entity');

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
													->setCellValue('A2', 'Site Code');

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
													->setCellValue('A3', 'Site Description');

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
													->setCellValue('A4', 'Physical Location');
																			
										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
										->setCellValue('B1', $business_entity);

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
										->setCellValue('B2', $site_code);
										
										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
										->setCellValue('B3', $site_name);

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
										->setCellValue('B4', $physical_location);

										$spreadsheet->setActiveSheetIndex($no_excel_sheet)
													->setCellValue('A5', '#')
													->setCellValue('B5', 'Meter S/N')
													->setCellValue('C5', 'Tenant')
													->setCellValue('D5', 'Measuring Point')
													->setCellValue('E5', 'Meter Multiplier')
													->setCellValue('F5', 'Gateway S/N')
													->setCellValue('G5', 'Status')
													->setCellValue('H5', 'Last Reading Date');
							
										/*font bold*/
										$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('A1:A5')->getFont()->setBold(true);
										$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('D1:D6')->getFont()->setBold(true);
										$spreadsheet->getActiveSheet($no_excel_sheet)->getStyle('A5:I5')->getFont()->setBold(true);
																		
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
							
										$gateway_meter_list = $this->Report_model->meter_by_physical_location_list($physical_location,$site_code); 
					
											$n = 1;
											$no_excel_data = 6;
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
													$measuring_point = $meter_cols['measuring_point'];
													$last_log_update = $meter_cols['last_log_update'];
													$rtu_sn_number = $meter_cols['rtu_sn_number'];

																	$spreadsheet->setActiveSheetIndex($no_excel_sheet)
																	->setCellValue("A$no_excel_data", $n)
																	->setCellValue("B$no_excel_data", $meter_name)
																	->setCellValue("C$no_excel_data", $customer_name)
																	->setCellValue("D$no_excel_data", $measuring_point)
																	->setCellValue("E$no_excel_data", $meter_multiplier)
																	->setCellValue("F$no_excel_data", $rtu_sn_number)
																	->setCellValue("G$no_excel_data", $meter_status)
																	->setCellValue("H$no_excel_data", $last_log_update);
													
											$no_excel_data++;	
											$n++;
											endforeach;
											
											if($physical_location == ''){
												$_physical_location = 'No Location';
											}else
											{
												$_physical_location = $physical_location;
											}
											
											$sheet_title = preg_replace("/[^A-Za-z0-9\-]/", "", "$_physical_location ");		
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
	
	public function meter_info()
	{
		if($this->session->userdata('currently_logged_in_as')!=''){
		
	
	$meter_id = $this->input->post('meter_id');
	
	if($meter_id!=''){
	
	$data['meter_info']=$this->Report_model->get_selected_meter($meter_id);

	$client_name = $data['meter_info']['customer_name'];
	$measuring_point = $data['meter_info']['measuring_point'];

	$created_by = $data['meter_info']['created_by'];
	$date_created = $data['meter_info']['date_created'];
	$modified_by = $data['meter_info']['modified_by'];
	$date_modified = $data['meter_info']['date_modified'];

	$meter_site_id = $this->input->post('site_id');

	$rtu_sn_number_id = $data['meter_info']['rtu_sn_number_id'];
	$rtu_sn_number = $data['meter_info']['rtu_sn_number'];
	$meter_name = $data['meter_info']['meter_name'];
	$meter_model = $data['meter_info']['meter_model'];
	$meter_model_id = $data['meter_info']['meter_model_id'];
	$meter_config_file = $data['meter_info']['meter_config_file'];
	$meter_type = $data['meter_info']['meter_type'];
	$meter_multiplier = $data['meter_info']['meter_multiplier'];
	$meter_status = $data['meter_info']['meter_status'];
	$meter_role = $data['meter_info']['meter_role'];
	$meter_name_addressable = $data['meter_info']['meter_name_addressable'];
	$meter_default_name = $data['meter_info']['meter_default_name'];
	$physical_location = $data['meter_info']['physical_location'];
	$meter_list_src = $data['meter_info']['meter_list_src'];
	$siteid = $meter_site_id;

	?>
    <div class="col-md-2">
        
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
    
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Information</div>
    
	<?php

		$company_no = $data['meter_info']['company_no'];
		$building = $data['meter_info']['building'];
		$building_description = $data['meter_info']['building_description'];
		$land = $data['meter_info']['land'];
		$land_description = $data['meter_info']['land_description'];
		$meter_site_name = $data['meter_info']['meter_site_name'];

		$rental_object = $data['meter_info']['rental_object'];
		$rental_object_name = $data['meter_info']['rental_object_name'];
		$usage_type = $data['meter_info']['usage_type'];
		$ro_valid_from = $data['meter_info']['ro_valid_from'];

		$ro_valid_to = $data['meter_info']['ro_valid_to'];
		$contract_number = $data['meter_info']['contract_number'];
		$customer_name = $data['meter_info']['customer_name'];
		$meter_characteristic = $data['meter_info']['meter_characteristic'];
	
		$measuring_point = $data['meter_info']['measuring_point'];
		$measuring_point_description = $data['meter_info']['measuring_point_description'];
		$measurement_sequence = $data['meter_info']['measurement_sequence'];
		$measurement_separator = $data['meter_info']['measurement_separator'];

		$meter_reading_date = $data['meter_info']['meter_reading_date'];
		$measurement_separator = $data['meter_info']['measurement_separator'];
		$participation_group = $data['meter_info']['participation_group'];
		
		$creation_date = $data['meter_info']['creation_date'];
		$CREATION_BY = $data['meter_info']['creation_by'];
		$last_change_on = $data['meter_info']['last_change_on'];
		$last_change_by = $data['meter_info']['last_change_by'];
		$business_entity = $data['meter_info']['business_entity'];
	
	?>
	<div class="row">    
    <div class="col-md-12">    
    <div align="right">
	<?php
    echo "<br><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button>"
    ?>
    </div>
    
    <h4>METER DESCRIPTION : <?=$meter_name;?></h4>
    <h6>GATEWAY SERIAL NUMBER: <?=$rtu_sn_number;?></h6>
    <h6>ALTERNATE ADDRESS : <?=$meter_default_name;?></h6>
    <h6>CONFIGURATION FILE : <?=$meter_config_file;?></h6>
    <h6>LOCATION : <?=$physical_location;?></h6>
    <h6>TYPE : <?=$meter_type;?></h6>
    <h6>ROLE : <?=$meter_role;?></h6>
	
    </div>
    </div>
    <div class="row">    
    <div class="col-md-6">
    
    <h6>COMPANY CODE : <?=$company_no;?></h6>
    <h6>BUSINESS ENTITY : <?=$business_entity;?></h6>
    <h6>BUILDING : <?=$building;?></h6>    
    <h6>BUILDING DESCRIPTION : <?=$building_description;?></h6>
    <h6>LAND : <?=$land;?></h6>
    <h6>LAND DESCRIPTION : <?=$land_description;?></h6>
    <h6>RENTAL OBJECT NO : <?=$rental_object;?></h6>
    <h6>RENTAL OBJECT NAME : <?=$rental_object_name;?></h6>
    <h6>USAGE TYPE : <?=$usage_type;?></h6>
    <h6>RO VALID FROM : <?=$ro_valid_from;?></h6>
    <h6>RO VALID TO : <?=$ro_valid_to;?></h6>
    <h6>CONTRACT NUMBER : <?=$contract_number;?></h6>
   
    </div>
    <div class="col-md-6">
     <h6>CONTRACT NAME : <?=$customer_name;?></h6>
    <h6>METER CHARACTERISTIC : <?=$meter_characteristic;?></h6>
    <h6>MEASPONT : <?=$measuring_point;?></h6>
    <h6>MEASUREMENT SEQUENCE : <?=$measurement_sequence;?></h6>
    <h6>MEASUREMENT SEPARATOR : <?=$measurement_separator;?></h6>
    <h6>METER MULTIPLIER : <?=$meter_multiplier;?></h6>
    <h6>METER STATUS : <?=$meter_status;?></h6>
    <h6>PARTICIPATION GROUP : <?=$participation_group;?></h6>
    <h6>CREATION DATE : <?=$creation_date;?></h6>
    <h6>CREATION BY : <?=$CREATION_BY;?></h6>
    <h6>LAST CHANGE ON : <?=$last_change_on;?></h6>
    <h6>LAST CHANGE BY : <?=$last_change_by;?></h6>    
    </div>
    </div>
    
    </div>
    </div>
    <div class="col-md-2">
  
    </div>
    <?php
	}
	else{
	}
	
	
		}
		else{
		}
	}

}