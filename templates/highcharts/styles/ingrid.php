<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
 
<title>AWS Table Data</title>

<!--Ingrid-->
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.ingrid.js"></script>
<link rel="stylesheet" href="css/ingrid.css" type="text/css" media="screen" />

<!--Date Picker-->
<!-- required plugins -->
<script type="text/javascript" src="scripts/date.js"></script>
<!--[if lt IE 7]><script type="text/javascript" src="scripts/jquery.bgiframe.min.js"></script><![endif]-->
<!-- jquery.datePicker.js -->
<script type="text/javascript" src="scripts/jquery.datePicker.js"></script>
<!-- datePicker required styles -->
<link rel="stylesheet" type="text/css" media="screen" href="styles/datePicker.css">
<!-- page specific styles --> 
<link rel="stylesheet" type="text/css" media="screen" href="styles/demo.css"> 

<!-- initialize -->
<script type="text/javascript">
$(document).ready(function(){

}
);

	$(function() {	
		$('.message-button').on('click',function(){
			
			update_table();		
			alert('update table');
		})
	});

	function table_update() {
		//var location = $("#location option:selected").text().trim();
		//var date_time = $('#date').val();
		//var url = 'list.php?location='+location+'&date='+date_time; 	
		alert('jun');
		
		/*
		$("#table1").ingrid({ 
			//url: 'list.php?location=magallanes&date=2013-10-23',
			url: url,
			height: 350,
			initialLoad: true,
			colWidths: [150,150,300,200,200,200],		// width of each column
			rowClasses: ['grid-row-style1','grid-row-style2'],
			resizableCols: true,
			paging: false,
			sorting: false			
		});
		*/
	};


$(function(){
	$('.date-pick').datePicker(
		{startDate:'2013-01-01',showYearNavigation:false,clickInput:true}
	).val(new Date().asString()).trigger('change');
});	

</script> 
</head>
<body>
<div id="container1">				
		<fieldset>
			<legend>Select Date and Location</legend>                   														                            															
			<?php include ('list2.php'); ?>
			<input name="date" id="date" class="date-pick" />
			<input class="message-button" type="submit" value="Submit" />
			
		</fieldset>
		
</div>
<br></br>
<table id="table1">
<thead>
    <tr>       
      <th>Date-Time</th>
      <th>Rain Level</th>
      <th>Wind Speed</th>
      <th>Wind Direction</th>
	  <th>Temperature</th>
	  <th>Humidity</th>
    </tr>
  </thead>
<tbody>
<tr><td>static col 1</td><td>static col 2</td><td>static col 3</td><td>static col 4</td><td>static col 4</td><td>static col 4</td></tr>
<tr><td>static col 1</td><td>static col 2</td><td>static col 3</td><td>static col 4</td><td>static col 4</td><td>static col 4</td></tr>
<tr><td>static col 1</td><td>static col 2</td><td>static col 3</td><td>static col 4</td><td>static col 4</td><td>static col 4</td></tr>
</tbody>
</table>	
</body>
</html>