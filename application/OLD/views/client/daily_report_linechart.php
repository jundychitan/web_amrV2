<?php

$cols = str_replace("`","'",$monthly_report_request_data['chart_meter_ykeys']);
$column_req_label = str_replace("`","'",$monthly_report_request_data['chart_meter_label']);
$year_month = $monthly_report_request_data['year_month'];

$from_year_month_day = date('Y-m-d', strtotime($monthly_report_request_data['from_year_month_day']));
$to_year_month_day = date('Y-m-d', strtotime($monthly_report_request_data['to_year_month_day']));
/*
$chart_data = '';

foreach ($chart_data_result as $row):

$chart_data .= "{ 'year_month':'".$row["year_month"]."', $row[column_req_data]}, ";

endforeach; 
*/ 
$chart_data = substr($chart_data, 0, -2);

			@$year_month_arr = explode(",", $year_month);
			foreach ($year_month_arr as $year_month_row):
			$month_year = date('m/d/y', strtotime($year_month_row));
			@$month_year_td .= "<th align='center'>$month_year</th>";
			endforeach; 
			$month_year_td .= "";

?><!DOCTYPE html>
<html>
 <head>
  <title><?=$title;?></title> 
  <link href="<?php echo base_url(); ?>templates/layouts/vendors/morris/morris.css" rel="stylesheet">
  <script src="<?php echo base_url(); ?>templates/layouts/vendors/raphael-min.js"></script>
  <script src="<?php echo base_url(); ?>templates/layouts/vendors/morris/morris.min.js"></script>
 </head>
     <body style="background-color:#fff !important;">
     	<div class="col-md-12">
	  		<div class="row">
				<div class="col-md-12">

  <br />
  <div class="container" style="width: auto">
    
   <h1 align="center"><?=$title;?></h1> 
   <h4 align="left">Location : <?=$site_info['site_name'];?></h4>
   <h4 align="left">Report Duration : <?php echo "$from_year_month_day - $to_year_month_day" ?></h4>  
   <br />
   <div id="chart" style="background-color:#FFF !important;"></div>
  </div>


		<br>
        <div style="background-color:#FFF; padding:5px; margin:14px;">
		<table class="table table-striped " cellspacing="0" width="90%" border="2" bordercolor="#3399FF" background="">
        <thead>
        <tr> 
        <th>#</th>
        <th>Customer</th>
        <th>Meter ID</th>
		<?php echo $month_year_td; ?>
        </tr>
        </thead>
        <?php
		echo "$meter_list";
		?>
        </table>
        </div>
        </div>
        </div>
        </div>
<!--<script>
		Morris.Bar({
		 element : 'chart',
		 data:[<?php// echo $chart_data; ?>],
		 xkey:'year_month',
		 ykeys:[<?//=$cols;?>],
		 labels:[<?//=$cols;?>],
		 hideHover:'auto',
		 stacked:true
		});
</script>-->
<?php $color_labels = rtrim($color_label,","); ?>
<script>

$(document).ready(function(){
 
 var data = [<?php echo $chart_data; ?>}],
    config = {
      data: data,
      xkey: 'year_month',
      ykeys: [<?=$cols;?>],
      labels: [<?=$cols;?>],
      fillOpacity: 0.3,
      hideHover: 'auto',
      behaveLikeLine: true,
      resize: true,
      pointFillColors:[<?=$color_labels;?>],
      pointStrokeColors: ['#FFF'],
      lineColors:[<?=$color_labels;?>]
  };

//config.element = 'line-chart';
//Morris.Line(config);
//config.element = 'bar-chart';
//Morris.Bar(config);
config.element = 'chart';
config.stacked = true;
Morris.Line(config);


});

</script>
 </body>
</html>