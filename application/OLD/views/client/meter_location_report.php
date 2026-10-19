<script type="text/javascript"> 
// Ajax post
$(document).ready(function() {
$("#viewlocationreport").click(function(event) {
event.preventDefault();

var date_start = $("#date_start").val();
var date_end = $("#date_end").val();
var meter = $("#meter").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action_log_report/meter_location_report_post",
dataType: 'html',
data: {'date_start': date_start,'date_end': date_end, 'meter_name': meter },
success : function(result) { 
result_message = result.split(/\t/);
//alert(result);
report_result =result_message[0];
$('#report_result').html(report_result);
},
beforeSend:function()
{
$("#report_result").html("<div align='center'><b>* * * Loading * * *</b></div>")
}
});
});
});

$(document).ready(function() {
try{
initialize();
}catch (e){
alert(e);
}
} );

function initialize(){
try{
$('#date_start').val($.datepicker.formatDate('yy-mm-dd', new Date()));
$('#date_end').val($.datepicker.formatDate('yy-mm-dd', new Date()));

}catch(e){
alert(e);
}
}

$(function () {

var startDateTextBox = $('#date_start');
var endDateTextBox = $('#date_end');

$('#date_start').datetimepicker({format: 'YYYY-MM-DD',});
$('#date_end').datetimepicker({
format: 'YYYY-MM-DD',
useCurrent: false //Important! See issue #1075
});
$("#date_start").on("dp.change", function (e) {
$('#date_end').data("DateTimePicker").minDate(e.date);
});
$("#date_end").on("dp.change", function (e) {
$('#date_start').data("DateTimePicker").maxDate(e.date);
});
});
</script>
 <div class="col-md-12">
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title; ?></div>
<div class="panel-body panel-body-custom">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
</div>
<div align="center">
<form action="" method="get" class="form-inline" style="margin-top:10px;">
<div class="form-group">
<label>Date Start</label><br />
<input type="text"class="form-control" id="date_start" name="date_start">
</div>

<div class="form-group">
<label>Date End</label><br />
<input type="text"class="form-control" id="date_end" name="date_end">
</div>
 
<div class="form-group">
<label>Location</label><br />
<input type="text"class="form-control" id="meter" name="meter" list="meter_list" size="35"  autocomplete="off">
<datalist id="meter_list">
<?php
//var_dump($meter_name);
 foreach ($meter_name as $meter_col): 
$meter_group = $meter_col['location'];
?>
<option value="<?php echo "$meter_group";?>"><?php echo "$meter_group"; ?></option>
<?php
endforeach;
?>
</datalist>
</div>

<div class="form-group">
<button type="submit" name="submit" id="viewlocationreport" class="btn btn-primary" style="margin-top:29px;">Submit</button>
</div>
</form>

</div>

<div id="report_result">

</div> 

</div>
</div>
</div>
</div>
</div>