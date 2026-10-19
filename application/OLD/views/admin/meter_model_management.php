<script>
function add_meter_model(event)
{
event.preventDefault();

var meter_model_id = $("#add_edit_meter_model").val();
var meter_model = $("#meter_model").val();
var config_file = $("#config_file").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/meter_model_management_post",
dataType: 'html',
data: {
'meter_model_id': meter_model_id,
'meter_model': meter_model, 
'config_file': config_file,
},
success : function(result) { 

result_message = result.split(/\t/);
meter_model_id_message = result_message[0];
meter_model_message = result_message[1];
config_file_message = result_message[2];
message_all = result_message[3];

$('#meter_model_id_message').html(meter_model_id_message);
$('#meter_model_message').html(meter_model_message);
$('#message_all').html(message_all).show();

setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
setTimeout(function() {
$('#form_float').fadeOut('slow');
},1600); 
clear_meter_model_field_form(event);
$('#table').DataTable().draw();
}

});
}

/*EDIT*/
function edit_meter_model(event)
{
event.preventDefault();

var meter_model_id = $("#add_edit_meter_model").val();
var meter_model = $("#meter_model").val();
var config_file = $("#config_file").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/meter_model_management_post",
dataType: 'html',
data: {
'meter_model_id': meter_model_id,
'meter_model': meter_model, 
'config_file': config_file,
},
success : function(result) { 

result_message = result.split(/\t/);
meter_model_id_message = result_message[0];
meter_model_message = result_message[1];
config_file_message = result_message[2];
message_all = result_message[3];

$('#meter_model_id_message').html(meter_model_id_message);
$('#meter_model_message').html(meter_model_message);
$('#config_file_message').html(config_file_message);
$('#message_all').html(message_all).show();

setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
setTimeout(function() {
$('#form_float').fadeOut('slow');
},1600); 
clear_meter_model_field_form(event);
$('#table').DataTable().draw();
}
 });
}
 
function delete_meter_model_info(event)
{
event.preventDefault();
var meter_model_id = $("#delete_meter_model").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/delete_meter_model",
dataType: 'html',
data: {
'meter_model_id': meter_model_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1500); 
$('#meter_model').val('');
$('#config_file').val('');
$('#meter_model_server_ip').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#meter_model_id_message').html('');
$('#meter_model_message').html('');
$('#config_file_message').html('');

clear_meter_model_field_form(event);
$('#table').DataTable().draw();
}

});
}

function clear_meter_model_field_form(event)
{
event.preventDefault();
var meter_model_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_meter_model_action/",
dataType: 'html',
data: {
'meter_model_id': meter_model_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);

$('#meter_model').val('');
$('#edit_button').html(edit_button);
$('#config_file').val('');

//RESET MESSAGE
$('#meter_model_id_message').html('');
$('#meter_model_message').html('');
$('#config_file_message').html('');
}

});
}
function clear_meter_model_field(event)
{
event.preventDefault();
var meter_model_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_meter_model_action/",
dataType: 'html',
data: {
'meter_model_id': meter_model_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},700); 

$('#meter_model').val('');
$('#edit_button').html(edit_button);
$('#config_file').val('');
 
//RESET MESSAGE
$('#meter_model_id_message').html('');
$('#meter_model_message').html('');
$('#config_file_message').html('');
}
 });
}
function selected_meter_model(id)
{
var meter_model_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/select_meter_model",
dataType: 'html',
data: {'meter_model_id': id },
success : function(result) { 
var x = document.getElementById("form_float");
x.style.display = "block";
$('#edit_meter_model').html(result);
}
 });
}
function myFunction() {
 var x = document.getElementById("form_float");
 if (x.style.display === "none") {
x.style.display = "block";
 } else {
x.style.display = "none";
clear_meter_model_field_form(event);
$('#item_info').html(''); 
}
}
</script> 
<div id="form_float" style="display:none;">
<div id="form_float_close" onclick="myFunction()">X</div>
<div class="col-md-12">
<div class="row">
 <div class="col-md-2">
 </div>
 <div class="col-md-8"> 
 <div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title_2; ?></div>
<div class="panel-body"> 

<div id="message_all"></div>
<div align="right"></div>
<div id="edit_form">
<?php echo form_open('admin/meter_model_management/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div id="test"></div>
<!-- EDIT -->
<div id="edit_meter_model">
<div class="row">
<div class="col-md-4">
&nbsp;
 </div>
<div class="col-md-4">
<label>Meter Model</label>
<input type="text"class="form-control" id="meter_model" name="meter_model" list="meter_model_list">
<div id="meter_model_message"></div>
 </div>
<div class="col-md-4">
 </div>

</div> 

<div class="row">
<div class="col-md-4">
&nbsp;
</div>
<div class="col-md-4">
<label>Configutaion File</label>
<input type="text"class="form-control" id="config_file" name="config_file" >
<div id="config_file_message"></div>
 </div>
<div class="col-md-4">
&nbsp;
 </div>
</div>


<hr class="user-orange-hr">
<div class="row">
<div class="col-md-2">
</div>
<div class="col-md-8">
<div id="edit_button">
 <div class="btn-group btn-group-justified">
<div class="btn-group">
<button type="submit" name="add_edit_meter_model" id="add_edit_meter_model" class="btn btn-primary" value="" onClick="add_meter_model(event)">Save</button>
</div>
<div class="btn-group">
<button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_meter_model_field(event)">Clear</button>
</div>
</div>
</div>
 </div>
<div class="col-md-2">
</div>
</div>
</form>
</div>
<!-- EDIT -->
</div>
</div>
 </div>
 </div>
 <div class="col-md-2">
 </div>
</div>
</div>
 
</div>
 
<div class="col-md-12">
<div class="row">

<div class="col-md-12"> 
      <div align="left" style=""><h3><?php echo $title; ?></h3></div>

<div class="row">
<div class="col-md-6"> 

</div>
<div class="col-md-6"> 
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> [ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ] 
</div>
</div>
</div>
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<th>&nbsp;</th>
<th>#</th>
<th >Model Name</th>
<th >Configuration File</th>
<th >Created By</th>
<th >Date Created</th>
<th >Modified By</th>
<th >Date Modified</th>

</thead>
<tbody>
</tbody>
<tfoot>
<th>&nbsp;</th>
<th>#</th>
<th >Model Name</th>
<th >Configuration File</th>
<th >Created By</th>
<th >Date Created</th>
<th >Modified By</th>
<th >Date Modified</th>
</tfoot>
 </table>
<script type="text/javascript">
var table;
$(document).ready(function() {
//datatables
table = $('#table').DataTable({ 
"scrollY":        350,
"scrollCollapse": true,
// "jQueryUI":       true,
"processing": false, //Feature control the processing indicator.
"serverSide": true, //Feature control DataTables' server-side processing mode.
"order": [], //Initial no order.
// Load data for the table's content from an Ajax source
"ajax": {
"url": "<?php echo site_url("admin_datatables/meter_model_list")?>",
"type": "POST"
},
//Set column definition initialisation properties.
"columnDefs": [
{ 
"targets": [ 0 ], //first column / numbering column
"orderable": false, //set not orderable
},
],
 });
});
</script>
</div>
</div>

</div>
</div>
</div>
</div>
 </div>


