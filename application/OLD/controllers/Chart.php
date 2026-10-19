<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Chart extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	//$this->load->model('admin_chart_model');
	//$this->load->helper('url_helper');
	//$this->load->library('session');
	//$this->load->helper('html');
	//$this->load->helper('date');
	//$this->load->library('user_agent');
	}
	
	/*PAGE LOAD HERE*/
	
	public function meter_chart()
	{

	$this->load->view('templates/header_chart');
	//$this->load->view('admin/navigation-admin');
	$this->load->view('chart/charttest');
	//$this->load->view('templates/footer');
	
	}

	public function meter_chart_data()
	{
/*
		$dataPoints = array( 
			array("label"=>"Active w/ Readings", "y"=>64.02),
			array("label"=>"Active w/out Readings", "y"=>12.55),
			array("label"=>"Inactive", "y"=>8.47)
		);
		
		echo json_encode($dataPoints, JSON_NUMERIC_CHECK);
*/
	$arrLabels = array("January","February","March","April","May","June","July");
	$arrDatasets = array('label' => "My First dataset",'fillColor' => "rgba(220,220,220,0.2)", 'strokeColor' => "rgba(220,220,220,1)", 'pointColor' => "rgba(220,220,220,1)", 'pointStrokeColor' => "#fff", 'pointHighlightFill' => "#fff", 'pointHighlightStroke' => "rgba(220,220,220,1)", 'data' => array('28', '48', '40', '19', '86', '27', '90'));
	
	$arrReturn = array(array('labels' => $arrLabels, 'datasets' => $arrDatasets));
	
	print (json_encode($arrReturn));
	} 
		
	public function meter_chart_data_1()
	{

	$arrLabels = array("Red","Yellow","Blue");
	$arrDatasets = array('label' => "Colors",'fillColor' => "rgba(220,220,220,0.2)", 'strokeColor' => "rgba(220,220,220,1)", 'pointColor' => "rgba(220,220,220,1)", 'pointStrokeColor' => "#fff", 'pointHighlightFill' => "#fff", 'pointHighlightStroke' => "rgba(220,220,220,1)", 'data' => array('28', '48', '40'));
	
	$arrReturn = array(array('labels' => $arrLabels, 'datasets' => $arrDatasets));
	
	print (json_encode($arrReturn));
	} 
		
}