<script type="text/javascript">
function change_class(id){
//alert(id);
var NAME = document.getElementById(id);
var currentClass = NAME.className;
if (currentClass == "glyphicon glyphicon-folder-close") { // Check the current class name
NAME.className = "glyphicon glyphicon-folder-open"; // Set other class name
} else {
NAME.className = "glyphicon glyphicon-folder-close";// Otherwise, use `second_name`
}
} 

function load_subgroup_meter() {
var site_select = $("#site_select").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/load_subgroup_meter_get",
dataType: 'html',
data: {
'site_select': site_select
},
success : function(result) { 
$('#load_sub_group_meter_list').html(result);
}
});
}
</script>

<script type="text/javascript">
$(document).ready(function(){
//.click
//.mouseleave
 $("#create_report_btn").click(function(){
event.preventDefault();
var create_report_btn = $("#create_report_btn").val();
var interval_type = $('input[name=interval_type]:checked').val(); 
var date_start = $("#date_start").val();
var date_end = $("#date_end").val();

var chart_type = $('input[name=chart_type]:checked').val(); 
var meter_list = [];

$.each($("input[name='meter_list']:checked"), function(){
meter_list.push($(this).val());
});

var meter_list_checked = meter_list.join(",");
var report_template = $("#report_template").val();
var site_select = $("#site_select").val();

srch1=$("#srch1").val();
srch2=$("#srch2").val();
srchtype=$("#srchtype").val();
events=$("#events").val();

 $.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action_chart_report/"+interval_type,
data: {
'chart_type': chart_type,
'meter_list_checked':meter_list_checked,
'date_start':date_start,
'date_end':date_end,
'site_select':site_select
},
success: function(html){
result_message = html.split(/\t/);
error_message =result_message[0];

if(error_message=='TRUE')
{
//alert('error');
interval_type_message =result_message[1];
chart_type_message =result_message[2];
meter_list_checked_message =result_message[3];
date_start_message =result_message[4];
date_end_message =result_message[5];
site_select_message =result_message[6];

$('#message_all').html(result_message[7]).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1000); 

 $('#interval_type_message').html(interval_type_message);
$('#chart_type_message').html(chart_type_message);
$('#meter_list_checked_message').html(meter_list_checked_message);
$('#date_start_message').html(date_start_message);
$('#date_end_message').html(date_end_message);
$('#sub_group_message').html(site_select_message);

$("#view_result").html('');
}
else
{

$('#interval_type_message').html('');
$('#chart_type_message').html('');
$('#meter_list_checked_message').html('');
$('#date_start_message').html('');
$('#date_end_message').html('');
$('#sub_group_message').html('');

$("#view_result").html(result_message[7]);

}


},
beforeSend:function()
{
 $("#view_result").html("<b>* * * Loading * * *</b>")
}
});
 return false;
});
});
</script>

<script type="text/javascript">
$(document).ready(function() {
try{
initialize();
}catch (e){
alert(e);
}
} );

function initialize(){
try{
$('#date_start').val($.datepicker.formatDate('yy-mm-dd', new Date())+' 00:00:00');
$('#date_end').val($.datepicker.formatDate('yy-mm-dd', new Date())+' 23:59:59');

}catch(e){
alert(e);
}
}

$(function () {

var startDateTextBox = $('#date_start');
var endDateTextBox = $('#date_end');

$('#date_start').datetimepicker({format: 'YYYY-MM-DD HH:mm:ss',});
$('#date_end').datetimepicker({
format: 'YYYY-MM-DD HH:mm:ss',
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
<div class="panel-heading blue-panel"><?php echo $title_2; ?></div>
<div class="panel-body">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
</div>
<div id="edit_form">
<?php echo form_open('admin/report_with_chart/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div id="message_all"></div>
<div id="test"></div>
<div class="col-md-2">

<div class="panel panel-default">
<div class="panel-heading">Interval</div>
<div class="panel-body"><div id="interval_type_message"></div>
<label><input type='radio' name='interval_type' id='interval_type_2' value='generate_chart_report_hourly_logs_post'>&nbsp;&nbsp;Hourly</label><br />
<label><input type='radio' name='interval_type' id='interval_type_1' value='generate_chart_report_daily_logs_post'>&nbsp;&nbsp;Daily</label><br />
<label><input type='radio' name='interval_type' id='interval_type_1' value='generate_chart_report_monthly_logs_post' checked="checked">&nbsp;&nbsp;Monthly</label><br />
 </div>
</div>
</div> 
<div class="col-md-2">
<div class="panel panel-default">
<div class="panel-heading">Select Date</div>
<div class="panel-body">
<label>Date Start</label><div id="date_start_message"></div>
<input type="text"class="form-control" id="date_start" name="date_start">

<label>End Start</label><div id="date_end_message"></div>
<input type="text"class="form-control" id="date_end" name="date_end">
</div>
</div>
<div class="panel panel-default">
<div class="panel-heading">Chart</div>
<div class="panel-body">
<div id="chart_type_message"></div>
<label>
<input type="radio" name="chart_type" value="bar_graph" id="chart_type_0" checked="checked"/>
Bar Graph</label>
<br />
<label>
<input type="radio" name="chart_type" value="line_graph" id="chart_type_2" />
Line Graph</label>
<br />
</div>
</div>
</div>
<div class="col-md-8">
<div class="panel panel-default">
<div class="panel-heading">Site</div>
<div class="panel-body" style="overflow:hidden;">
<div class="row">
<div class="col-md-4">
 <label>Select Site</label><div id="sub_group_message"></div>
 <select class="form-control" id="site_select" name="site_select" onchange="load_subgroup_meter()"/>
 <option>Please Select Site</option>
 <?php foreach ($site_list as $site_list_cols):?>
 <option value="<?php echo "$site_list_cols[meter_site_id]";?>"><?=$site_list_cols['group_to_access'];?></option>
 <?php endforeach;?>
 </select>
</div>
<div class="col-md-4">
</div>
<div class="col-md-4">
</div>
</div>
<div class="row">
<div class="col-md-12"><div id="load_sub_group_meter_list"></div>
<div id="meter_list_checked_message"></div>
</div>
</div>
</div>
</div>
<button type="button" name="create_report_btn" id="create_report_btn" class="btn btn-primary" value="" >Create Report</button>
<div id="view_result" style="display:inline-block; font-size:14px; padding:7px; margin-left:10px; font-weight:bold;">
</div>
</div>
<div class="row">
<div class="col-md-12">
</div>
</div>
<br /><br /><br /><br />
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
