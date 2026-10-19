<script>
<!-- Clear Search -->
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "site_admin_datatables/group_meter_list/$site_id/0/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

}

<!-- Search for Duplicate -->
function showResult(str) {

	ids=$("#add_edit_meter").val();
	
    if (str == "") {
        document.getElementById("check_duplicate").innerHTML = "";
		$("#check_duplicate").fadeOut("fast");

        return;
    } else { 

		$("#check_duplicate").fadeIn("slow");

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("check_duplicate").innerHTML = xmlhttp.responseText;
            }
        };

        xmlhttp.open("GET","<?php echo base_url();?>index.php/site_admin_action/meter_duplicate?meter_id="+str+"&ids="+ids,true);
        xmlhttp.send();

    }
}

function add_meter(event)
{
event.preventDefault();
var site_id = <?=$site_id;?>;
var meter_id = $("#add_edit_meter").val();
var serial_number = $("#serial_number").val();
var client_name = $("#client_name").val();
var other_description = $("#other_description").val();
var meter_model = $("#meter_model").val();
var meter_type = $("#meter_type").val();
var meter_status = $("#meter_status").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var meter_multiplier = $("#meter_multiplier").val();
var meter_addressable = $("#meter_addressable").val();
var meter_addressable_id = $("#meter_addressable_id").val();
var meter_role = $("#meter_role").val();
var physical_location = $("#physical_location").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/meter_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_code'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'other_description': other_description, 
'serial_number': serial_number, 
'meter_model': meter_model,
'meter_type': meter_type,
'meter_status': meter_status, 
'rtu_sn_number': rtu_sn_number,
'meter_multiplier': meter_multiplier,
'meter_addressable': meter_addressable,
'meter_addressable_id': meter_addressable_id,
'meter_role': meter_role,
'physical_location': physical_location
},
success : function(result) { 
result_message = result.split(/\t/);
meter_id_message = result_message[0];
client_name_message = result_message[1];
serial_number_message = result_message[2];
meter_model_message = result_message[3];
meter_type_message = result_message[4];
meter_status_message = result_message[5];
meter_multiplier_message = result_message[6];
rtu_sn_number_message = result_message[7];
physical_location_message = result_message[8];

message_all = result_message[9];

$('#meter_id_message').html(meter_id_message);

$('#client_name_message').html(client_name_message);
$('#serial_number_message').html(serial_number_message);
$('#meter_model_message').html(meter_model_message);
$('#meter_type_message').html(meter_type_message);
$('#meter_status_message').html(meter_status_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);
$('#physical_location_message').html(physical_location_message);

$('#meter_multiplier_message').html(meter_multiplier_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

//$('#table').DataTable().draw();

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
var other_description = $("#other_description").val();
var meter_model = $("#meter_model").val();
var meter_type = $("#meter_type").val();
var meter_status = $("#meter_status").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var meter_multiplier = $("#meter_multiplier").val();
var meter_addressable = $("#meter_addressable").val();
var meter_addressable_id = $("#meter_addressable_id").val();
var meter_role = $("#meter_role").val();
var physical_location = $("#physical_location").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/meter_management_edit_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_code'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'other_description': other_description, 
'serial_number': serial_number, 
'meter_model': meter_model,
'meter_type': meter_type,
'meter_status': meter_status, 
'rtu_sn_number': rtu_sn_number,
'meter_multiplier': meter_multiplier,
'meter_addressable': meter_addressable,
'meter_addressable_id': meter_addressable_id,
'meter_role': meter_role,
'physical_location': physical_location
},
success : function(result) { 
result_message = result.split(/\t/);
meter_id_message = result_message[0];
client_name_message = result_message[1];
serial_number_message = result_message[2];
meter_model_message = result_message[3];
meter_type_message = result_message[4];
meter_status_message = result_message[5];
meter_multiplier_message = result_message[6];
rtu_sn_number_message = result_message[7];
physical_location_message = result_message[8];

message_all = result_message[9];

$('#meter_id_message').html(meter_id_message);

$('#client_name_message').html(client_name_message);
$('#serial_number_message').html(serial_number_message);
$('#meter_model_message').html(meter_model_message);
$('#meter_type_message').html(meter_type_message);
$('#meter_status_message').html(meter_status_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);
$('#physical_location_message').html(physical_location_message);

$('#meter_multiplier_message').html(meter_multiplier_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

//$('#table').DataTable().draw();

}
});

}
/*
function delete_meter_info(event)
{
event.preventDefault();
var meter_id = $("#delete_meter").val();
jQuery.ajax({
type: "POST",
url: "<!?php echo base_url(); ?>" + "index.php/site_admin_action/delete_meter",
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
 $('#serial_number').val('');
 $('#rtu_sn_number').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $('#physical_location').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
 $("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);
*/
/*RESET MESSAGE
$('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#meter_model_message').html('');
$('#rtu_sn_number_message').html('');
$('#physical_location_message').html('');
$('#meter_type_message').html('');
$('#meter_status_message').html('');

clear_meter_field_form(event);
//$('#table').DataTable().draw();
setTimeout(function() {
$('#form_float').fadeOut('slow');
},1800); 

}
});
}*/

function clear_meter_field_form(event)
{
event.preventDefault();
var meter_id = 'NA';
var site_id = <?=$site_id;?>;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/clear_meter_action/<?=$site_id;?>",
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
 $('#serial_number').val('');
 $('#physical_location').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
 $("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);

$('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#meter_model_message').html('');
$('#rtu_sn_number_message').html('');
$('#physical_location_message').html('');
$('#meter_type_message').html('');
$('#meter_status_message').html('');

$("#serial_number").focus();

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
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/clear_meter_action/<?=$site_id;?>",
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
 $('#serial_number').val('');
 $('#physical_location').val('');
 $('#meter_model').val('');
 $('#meter_type').val('');
 $("#meter_status option[value='Active']").attr('selected', 'selected');
 $("#meter_addressable option[value='1']").attr('selected', 'selected');
 $('#edit_button').html(edit_button);


$('#meter_id_message').html('');
$('#serial_number_message').html('');
$('#client_name_message').html('');
$('#rtu_sn_number_message').html('');
$('#physical_location_message').html('');
$('#meter_model_message').html('');
$('#meter_type_message').html('');
$('#meter_status_message').html('');

$("#serial_number").focus();

}
});
}

function selected_meter(id)
{
var meter_id = id;

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/select_meter",
dataType: 'html',
data: {
'meter_id': id,
'site_id': <?=$site_id;?>},
success : function(result) { 

var x = document.getElementById("form_float");
x.style.display = "block";

$('#edit_meter').html(result);
$("#serial_number").focus();

}
});
}

function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {
x.style.display = "block";
$("#serial_number").focus();

} else {
x.style.display = "none";
clear_meter_field_form(event);
$('#item_info').html('');
}
}
</script> 
  <div id="form_float" style="display:none;">
    <div class="col-md-2">
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
    <div id="message_all"></div>
    <div id="edit_form">
									
	<div id="edit_meter">
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Properties</div>
    <div class="row">
    <div class="col-md-4">
    <label>Measuring Point Description</label>
    <!--METER NUMBER-->
    <br />
    <center><b>Not Applicabble</b></center>
    <!--<input type="text"  class="form-control" id="serial_number_sap" name="serial_number_sap" onchange="showResult(this.value)" onkeydown="showResult(this.value)" onkeyup="showResult(this.value)" >-->
    <div id="serial_number_sap_message"></div>
    </div>
    
    <div class="col-md-4">
    <label>Serial Number</label>
    <!--METER NUMBER-->
    <input type="text"  class="form-control" id="serial_number" name="serial_number" onchange="showResult(this.value)" onkeydown="showResult(this.value)" onkeyup="showResult(this.value)" tabindex="1">
    <div id="serial_number_message"></div>
    </div>
    <div class="col-md-4">
    <label>Multiplier</label>
    <input type="text"  class="form-control" id="meter_multiplier" name="meter_multiplier" value="1" tabindex="2">
    <div id="meter_multiplier_message"></div>
    </div>
    
    </div>

	<hr class="user-orange-hr">
    
    <div class="row">

    <div class="col-md-4">
    <label>Description</label>
    <input class="form-control" id="client_name" name="client_name" list="client_list" tabindex="3">
    <div id="client_name_message"></div>
    </div>
    
	<div class="col-md-4">
    <label>Gateway Serial No.</label>
    <select class="form-control" id="rtu_sn_number" name="rtu_sn_number" tabindex="4">
    <option value="0">Unassign</option>
    <?php foreach ($rtu_list as $rtu_list_cols): ?>
    <option value="<?=$rtu_list_cols['id'];?>,<?=$rtu_list_cols['rtu_sn_number'];?>"><?=$rtu_list_cols['rtu_sn_number'];?></option>
    <?php endforeach;  ?>
    </select>
    <div id="rtu_sn_number_message"></div> 
    </div>
    
	<div class="col-md-4">
    <label>Physical Location</label>
    <input type="text"class="form-control" id="physical_location" name="physical_location" tabindex="5">
    <div id="physical_location_message"></div>
    </div>
        
    </div>

    <hr class="user-orange-hr">

    <div class="row">

    <div class="col-md-4">
    <label>Configuration File</label> 
    <select class="form-control" id="meter_model" name="meter_model" tabindex="6">
    <option value="">Pls. Select</option>
    <?php foreach ($meter_model_list as $meter_model_cols): ?>
    <option value="<?=$meter_model_cols['id'];?>,<?=$meter_model_cols['meter_model'];?>,<?=$meter_model_cols['config_file'];?>"><?=$meter_model_cols['meter_model'];?></option>
    <?php endforeach;  ?>
    </select>
    
    <div id="meter_model_message"></div>
    </div>
        
    <div class="col-md-4">
    <label>Meter Type</label>
    <input type="text"  class="form-control" id="meter_type" name="meter_type" list="meter_type_list" tabindex="7">
    <div id="meter_type_message"></div>
    </div>
    
    <div class="col-md-4">
    <label>Meter Role</label>
    <select class="form-control" id="meter_role" name="meter_role" tabindex="8">
    <option value="Client Meter" selected="selected">Client Meter</option>
    <option value="Check Meter">Check Meter</option>
    <option value="CUSA">CUSA</option>
    <option value="Spare Meter">Spare Meter</option>
    </select>
    <div id="meter_role_message"></div>
    </div>
    
    </div>

    <hr class="user-orange-hr">

	<div class="row">

    <div class="col-md-4">
    <label>Use Meter ID as Address?</label>
    <select class="form-control" id="meter_addressable" name="meter_addressable" tabindex="9">
    <option value="1" selected="selected">YES</option>
    <option value="0">NO</option>
    </select>
    <div id="meter_addressable_message"></div>
    </div>

    <div class="col-md-4">
    <label>Alternate Address</label>
    <input type="text"  class="form-control" id="meter_addressable_id" name="meter_addressable_id" value="1" tabindex="10">
    <div id="meter_addressable_id_message"></div>
    </div>
    
    <div class="col-md-4">
    <label>Status</label>
    <select class="form-control" id="meter_status" name="meter_status" tabindex="11">
    <option value="Active">Active</option>
    <option value="Inactive">Inactive</option>
    </select>
    <div id="meter_status_message"></div>
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
     <button type="submit" name="add_edit_meter" id="add_edit_meter" class="btn btn-primary" value="" onClick="add_meter(event)" tabindex="12">Save</button>
     </div>
     <div class="btn-group">
     <button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_meter_field(event)" tabindex="13">Clear</button>
     </div>
     <div class="btn-group">
     <button type="close" name="close" onclick="myFunction()" class="btn btn-primary" value="" tabindex="14">Close</button>	
     </div> 
     </div>
      </div>
    </div>
    <div class="col-md-2">
    </div>
    
    
    </div>

    <div class="row">
    <div class="col-md-2">
    </div>
    <div class="col-md-8">
    
    <div id="check_duplicate">
    </div>
    
    </div><div class="col-md-2">
    </div>
    
    </div>
    </form>
    </div>                                    

    </div>
    </div>    
                                    
    <div class="col-md-2">
    </div>
	
    </div>
    </div>  

<div class="col-md-12">
<div class="row">
<div class="col-md-12"> 

      <div align="left" style=""><h3><?php echo $title; ?></h3></div>

<div class="row">

<div class="col-md-6">
<div style=" padding:5px; font-size:14px;">
  <h5>Business Entity : <?=$site_details['site_code'];?></h5>
  <h5>Site Name : <?=$site_details['site_name'];?></h5>
<?php 
  if($rtu_id!=''){
  ?>
  <h5>Gateway IP Address/Phone No. : <?=$rtu_info['phone_no_or_ip_address'];?></h5>
  <h5>Gateway Serial No : <?=$rtu_info['rtu_sn_number'];?></h5>
  <h5>MAC Address : <?=$rtu_info['mac_addr'];?></h5>
  <?php
  }else{
  } 
  ?>
</div>
</div>
<div class="col-md-6">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> [ <a href="#" onclick="myFunction()" class="">&plus;Add</a> ] 
[ <a href="<?php echo base_url(); ?>index.php/site_admin/rtu_management/<?=$site_id;?>" class="">Gateway List</a> ]
</div>
</div>
</div>
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<th>&nbsp;</th>
<th>#</th>
<th >Measuring Point</th>
<th title="Measuring Point Description">Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway Serial No.</th>
<th >Meter ID as Address?</th>
<th >Model</th>
<th >Type</th>
<th >Multiplier</th>
<th >Role</th>
<th >Status</th>
<th >Last Update</th>
</thead>
<tbody>
</tbody>

<tfoot> 
<tr> 
<th>&nbsp;</th>
<th>#</th>
<th >Measuring Point</th>
<th title="Measuring Point Description">Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway Serial No.</th>
<th >Meter ID as Address?</th>
<th >Model</th>
<th >Type</th>
<th >Multiplier</th>
<th >Role</th>
<th >Status</th>
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
"url": "<?php echo site_url("site_admin_datatables/group_meter_list/$site_id/$rtu_id")?>",
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
/*Append Clear Last Seach to seach box*/
$('<div class="clear_search" onClick="clear_search(event)">Clear Last Search</div>').appendTo('#table_filter');
});
</script>
</div>
</div>
</div>
</div>
</div>
<style>
.clear_search{
	position: inherit;
    display: inline;
    background-color: #0081ce;
    padding: 4px;
    border-radius: 12px;
    font-weight: bold;
    color: #FFF;
    cursor: pointer;
}
</style>