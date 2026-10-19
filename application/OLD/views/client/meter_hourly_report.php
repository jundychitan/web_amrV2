<script type="text/javascript"> 
// Ajax post
$(document).ready(function() {
$("#viewhourlyreport").click(function(event) {
event.preventDefault();

var date_start = $("#date_start").val();
var date_end = $("#date_end").val();
var meter = $("#meter").val();
 
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action_log_report/meter_hourly_report_post",
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
<div class="panel-heading blue-panel"><?php echo $title; ?></div>
<div class="panel-body panel-body-custom">
<div align="left" style="font-size:14px; font-weight:bold; color:#e61111 !important; position:absolute;">
Signed in as <?= $_SESSION['real_name'];?>
</div>
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
</div>

<div class="col-md-3"> 

<div id="mobile_view_option">
<div class="row">
<div class="col-md-12">
<div class="form-group">
<label>Date Start</label><br />
<input type="text"class="form-control" id="date_start" name="date_start">
</div>

</div>
</div>
<div class="row">
<div class="col-md-12">
<div class="form-group">
<label>Date End</label><br />
<input type="text"class="form-control" id="date_end" name="date_end">
</div>
 </div>
</div>

<div class="row">
<div class="col-md-12">
<div class="form-group">
<div id="display_meter_list">

<label>Meter</label><br />
<input type="text" class="form-control" id="meter" name="meter" list="meter_list" size="20" autocomplete="off"> 
<datalist id="meter_list">
<?php
foreach ($meter_list as $meter_col): 
$meter_id = $meter_col['meter_name'];
//$client_name = $meter_col['client_name'];
?>
<option value="<?php echo "$meter_id";?>"><?php echo "$meter_id"; ?></option>
<?php
endforeach;
?>
</datalist>
 
 
</div>
</div>
</div>
</div>

<div class="row">
<div class="col-md-12">
<div class="form-group">
<button type="submit" name="submit" id="viewhourlyreport" class="btn btn-primary" style="margin-top:29px;">Submit</button>
</div>
</div>

</div>
</div>

</div>

<div class="col-md-9">
<div id="report_result">
</div> 
<div id="mobile_view">
<a href="javascript:hideshow(document.getElementById('mobile_view_option'))" style="background-color:rgba(123, 66, 9, 0.89);; width:50px; height:50px; border-top-right-radius:50px; bottom:0; left: 0;position:fixed; z-index:9999;"></a>
</div>
<script type="text/javascript">
function hideshow(which){
if (!document.getElementById)
return
if (which.style.display=="none")
which.style.display="block"
else
which.style.display="none"
}
</script>
</div>
</div>
</div>
</div>
</div>
</div>