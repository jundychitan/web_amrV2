<script>
function add_meter(event)
{
event.preventDefault();
var site_id = <?=$site_id;?>;
var meter_id = $("#add_edit_meter").val();
var serial_number = $("#serial_number").val();
var client_name = $("#client_name").val();
var sub_group = $("#sub_group").val();
var meter_model = $("#meter_model").val();
var meter_type = $("#meter_type").val();
var meter_status = $("#meter_status").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var meter_multiplier = $("#meter_multiplier").val(); 
var meter_addressable = $("#meter_addressable").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/meter_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_name'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'sub_group': sub_group, 
'serial_number': serial_number, 
'meter_model': meter_model,
'meter_type': meter_type,
'meter_status': meter_status, 
'rtu_sn_number': rtu_sn_number,
'meter_multiplier': meter_multiplier,
'meter_addressable': meter_addressable
},
success : function(result) { 
result_message = result.split(/\t/);
meter_id_message = result_message[0];
client_name_message = result_message[1];
sub_group_message = result_message[2];
serial_number_message = result_message[3];
meter_model_message = result_message[4];
meter_type_message = result_message[5];
meter_status_message = result_message[6];
meter_multiplier_message = result_message[7];
rtu_sn_number_message = result_message[8];
message_all = result_message[9];

$('#meter_id_message').html(meter_id_message);

$('#client_name_message').html(client_name_message);
$('#sub_group_message').html(sub_group_message);
$('#serial_number_message').html(serial_number_message);
$('#meter_model_message').html(meter_model_message);
$('#meter_type_message').html(meter_type_message);
$('#meter_status_message').html(meter_status_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);

$('#meter_multiplier_message').html(meter_multiplier_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

/*
setTimeout(function() {
$('#form_float').fadeOut('slow');
},1600); 
*/

$('#table').DataTable().draw();
//clear_meter_field_form(event);
}
});
}

function edit_meter(event)
{
event.preventDefault();

var site_id = <?=$site_id;?>;
var meter_id = $("#add_edit_meter").val();
var serial_number = $("#serial_number").val();
var client_name = $("#client_name").val();
var sub_group = $("#sub_group").val();
var meter_model = $("#meter_model").val();
var meter_type = $("#meter_type").val();
var meter_status = $("#meter_status").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var meter_multiplier = $("#meter_multiplier").val();
var meter_addressable = $("#meter_addressable").val();
 
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/meter_management_edit_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_name'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'sub_group': sub_group, 
'serial_number': serial_number, 
'meter_model': meter_model,
'meter_type': meter_type,
'meter_status': meter_status, 
'rtu_sn_number': rtu_sn_number,
'meter_multiplier': meter_multiplier,
'meter_addressable': meter_addressable
},
success : function(result) { 

result_message = result.split(/\t/);
meter_id_message = result_message[0];
client_name_message = result_message[1];
sub_group_message = result_message[2];
serial_number_message = result_message[3];
meter_model_message = result_message[4];
meter_type_message = result_message[5];
meter_status_message = result_message[6];meter_multiplier_message = result_message[7];
rtu_sn_number_message = result_message[8];
message_all = result_message[9];
$('#meter_id_message').html(meter_id_message);

$('#client_name_message').html(client_name_message);
$('#sub_group_message').html(sub_group_message);
$('#serial_number_message').html(serial_number_message);
$('#meter_model_message').html(meter_model_message);
$('#meter_type_message').html(meter_type_message);
$('#meter_status_message').html(meter_status_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);

$('#meter_multiplier_message').html(meter_multiplier_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

/*
setTimeout(function() {
$('#form_float').fadeOut('slow');
},1600); 
*/
$('#table').DataTable().draw();
//clear_meter_field_form(event);


}
});
}

function delete_meter_info(event)
{
event.preventDefault();
var meter_id = $("#delete_meter").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/delete_meter",
dataType: 'html',
data: {
'meter_id': meter_id
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
 $('#client_name').val('');
 $("#sub_group option[value='1']").attr('selected', 'selected');
 $('#serial_number').val('');
 $('#rtu_sn_number').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
 $("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);

/*RESET MESSAGE*/
$('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#sub_group_message').html('');
$('#meter_model_message').html('');
$('#rtu_sn_number_message').html('');

$('#meter_type_message').html('');
$('#meter_status_message').html('');
clear_meter_field_form(event);
$('#table').DataTable().draw();

}
});
}
function clear_meter_field_form(event)
{
event.preventDefault();
var meter_id = 'NA';
var site_id = <?=$site_id;?>;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/clear_meter_action/<?=$site_id;?>",
dataType: 'html',
data: {
'meter_id': meter_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);

 $('#client_name').val('');
 $("#sub_group option[value='1']").attr('selected', 'selected');
 $('#serial_number').val('');
 $('#rtu_sn_number').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
$("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);


 $('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#sub_group_message').html('');
$('#meter_model_message').html('');
$('#rtu_sn_number_message').html('');
$('#meter_type_message').html('');
$('#meter_status_message').html('');
}
});
}

function clear_meter_field(event)
{
event.preventDefault();
var meter_id = 'NA';
var site_id = <?=$site_id;?>;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/clear_meter_action/<?=$site_id;?>",
dataType: 'html',
data: {
'meter_id': meter_id
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

 $('#client_name').val('');
 $("#sub_group option[value='1']").attr('selected', 'selected');
 $('#serial_number').val('');
$('#rtu_sn_number').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
$("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);


$('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#sub_group_message').html('');
$('#rtu_sn_number_message').html('');
$('#meter_model_message').html('');
$('#meter_type_message').html('');
$('#meter_status_message').html('');
}
});
}

function selected_meter(id)
{
var meter_id = id;

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/select_meter",
dataType: 'html',
data: {'meter_id': id },
success : function(result) { 

var x = document.getElementById("form_float");
x.style.display = "block";

$('#edit_meter').html(result);


}
});
}

function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {
x.style.display = "block";
} else {
x.style.display = "none";
clear_meter_field_form(event);
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
  <div class="panel-body"> <div id="message_all"></div>
  <div align="right"></div>
  <div id="edit_form">
<?php echo form_open('client/site_meter/', 'class="form-control-overflow"', 'name="user_form"'); ?>

<div id="test"></div>
<!-- EDIT -->
<div id="edit_meter">
<div class="row">

<div class="col-md-2">
&nbsp;
 </div>

<div class="col-md-4">
<label>Customer Name</label>
<input type="text"  class="form-control" id="client_name" name="client_name" list="client_name_list">
<div id="client_name_message"></div>
</div>
<div class="col-md-4">
<label>Sub-Group Name</label>
<select class="form-control" id="sub_group" name="sub_group"/>
<?php foreach ($sub_group_details as $sub_group_details_cols): ?>
<option value="<?=$sub_group_details_cols['id'];?>,<?=$sub_group_details_cols['sub_group_name'];?>"><?=$sub_group_details_cols['sub_group_name'];?></option>
<?php endforeach;  ?>
</select>

<div id="sub_group_message"></div>
</div>
<div class="col-md-2">
&nbsp;
</div>

</div>

<div class="row">

<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-4">
<label>RTU Serial No.</label>
<select class="form-control" id="rtu_sn_number" name="rtu_sn_number">
<option value="">Pls. Select</option>
<?php foreach ($rtu_list as $rtu_list_cols): ?>
<option value="<?=$rtu_list_cols['id'];?>,<?=$rtu_list_cols['rtu_sn_number'];?>"><?=$rtu_list_cols['rtu_sn_number'];?></option>
<?php endforeach;  ?>
</select>



<div id="rtu_sn_number_message"></div>
</div>
<div class="col-md-4">
<label>Meter ID</label>
<!--METER NUMBER-->
<input type="text"  class="form-control" id="serial_number" name="serial_number" list="serial_number_list">
<div id="serial_number_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-4">
<label>Meter Model</label> 
<select class="form-control" id="meter_model" name="meter_model">
<option value="">Pls. Select</option>
<?php foreach ($meter_model_list as $meter_model_cols): ?>
<option value="<?=$meter_model_cols['id'];?>,<?=$meter_model_cols['meter_model'];?>,<?=$meter_model_cols['config_file'];?>"><?=$meter_model_cols['meter_model'];?></option>
<?php endforeach;  ?>
</select>

<div id="meter_model_message"></div>
</div>
<div class="col-md-4">
<label>Meter Type</label>
<input type="text"  class="form-control" id="meter_type" name="meter_type" list="meter_type_list">
<div id="meter_type_message"></div>
</div>
<div class="col-md-2">
&nbsp;
</div>

</div>

<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>Meter Multiplier</label>
<input type="text"  class="form-control" id="meter_multiplier" name="meter_multiplier" value="1">
<div id="meter_multiplier_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div><div class="row">

<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-4">
<label>Addressable Meter ID?</label>
<select class="form-control" id="meter_addressable" name="meter_addressable" >
<option value="1" selected="selected">YES</option>
<option value="0">NO</option>
</select>
<div id="meter_addressable_message"></div>
</div>
<div class="col-md-4">
<label>Status</label>
<select class="form-control" id="meter_status" name="meter_status" >
<option value="Active">Active</option>
<option value="Inactive">Inactive</option>
</select>
<div id="meter_status_message"></div>
</div>



<div class="col-md-2">
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
 <button type="submit" name="add_edit_meter" id="add_edit_meter" class="btn btn-primary" value="" onClick="add_meter(event)">Save</button>
 </div>
 <div class="btn-group">
 <button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_meter_field(event)">Clear</button>
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

<div class="panel panel-default">
  <div class="panel-heading blue-panel"><?php echo $title_1; ?></div>
  <div class="panel-body" style="height:590px !important;">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> [ <a href="#" onclick="myFunction()" class="">&plus;Add</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/client/site_management" class="">Site List</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/client/site_sub_group/<?=$site_id;?>" class="">Sub-group List</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/client/rtu_management/<?=$site_id;?>" class="">RTU List</a> ]
</div>
  <div style=" padding:5px; font-size:14px; font-weight:bold;">
  Site Name : <?=$site_details['site_name'];?><br />
<?php 
  if($rtu_id!=''){
  ?>
  RTU Serial No : <?=$rtu_info['rtu_sn_number'];?><br />
  <?php
  }else{
  }
  ?>
  
  
  
  </div><br />
 
  <div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<th>&nbsp;</th>
<th>#</th>
<th >Customer </th>
<th >Meter ID</th>
<th >Sub Group</th>
<th >RTU Serial No.</th>
<th >Addressable Meter ID</th>
<th >Meter Status</th>
<th >Meter Model</th>
<th >Meter Type</th>
<th >Meter Multiplier</th>
<th >Date Created</th>
<th >Last Update</th>
</thead>
<tbody>
</tbody>

<tfoot> 
<tr>
<th>&nbsp;</th>
<th>#</th>
<th >Customer </th>
<th >Meter ID</th>
<th >Sub Group</th>
<th >RTU Serial No.</th>
<th >Addressable Meter ID</th>
<th >Meter Status</th>
<th >Meter Model</th>
<th >Meter Type</th>
<th >Meter Multiplier</th>
<th >Date Created</th>
<th >Last Update</th>
</tr>
</tfoot>
</table>
<script type="text/javascript">
var table;
$(document).ready(function() {
/*datatables*/
table = $('#table').DataTable({ 
"scrollY":        350,
"scrollCollapse": true,
// "jQueryUI":       true,
"processing": false, /*Feature control the processing indicator.*/
"serverSide": true, /*Feature control DataTables' server-side processing mode.*/
"order": [], /*Initial no order.*/
/* Load data for the table's content from an Ajax source */ 
"ajax": {
"url": "<?php echo site_url("client_datatables/group_meter_list/$site_id/$rtu_id")?>",
"type": "POST"
},
/*Set column definition initialisation properties.*/
"columnDefs": [
{ 
"targets": [ 0 ], /*first column / numbering column*/
"orderable": false, /*set not orderable*/
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
</div>
</div>




