<!DOCTYPE html>
<html>
 <head>
  <title><?=$title;?></title> 
  <link href="<?php echo base_url(); ?>templates/layouts/vendors/morris/morris.css" rel="stylesheet">
  <script src="<?php echo base_url(); ?>templates/layouts/vendors/raphael-min.js"></script>
  <script src="<?php echo base_url(); ?>templates/layouts/vendors/morris/morris.min.js"></script>
 </head>
     <body>
  <br />
  <div class="container" style="width:900px;">
   <h2 align="center"><?=$location;?></h2>
   <h3 align="center">Monthly Report</h3>   
   <br /><br />
   <div id="chart" style="background-color:#FFF !important;"></div>
  </div>
 </body>
</html>
<?php 
$chart_data = '';
foreach ($chart_data_result as $row):
 $data[] = array(
  'label'  => $row["meter_id"],
  'value'  => $row["wh_total_consumption"]
 ); 
endforeach;
$data = json_encode($data);
?>
<script>

$(document).ready(function(){
 var donut_chart = Morris.Donut({
     element: 'chart',
     data: <?php echo $data; ?>
    });
});
</script>