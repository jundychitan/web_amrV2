<script>
/*VIEW OFFLINE METERS*/
function view_offline_meters() {

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/report_action/view_offline_meters",
dataType: 'html',
data: {
'site_id': <?=$site_id;?>,
'rtu_id': <?=$rtu_id;?>
},
success : function(result) { 

var x = document.getElementById("form_float");
x.style.display = "block";

var a = document.getElementById("edit_meter");
a.style.display = "none";

var b = document.getElementById("meter_info");
b.style.display = "none";

var c = document.getElementById("offline_meters");
c.style.display = "block";

$('#offline_meters').html(result);

}
});
}
<!-- Clear Search -->
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "ba_datatables/group_meter_list/$site_id/0/1"; ?>",
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

        xmlhttp.open("GET","<?php echo base_url();?>index.php/ba_action/meter_duplicate?meter_id="+str+"&ids="+ids,true);
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
var meter_seil = $("#meter_seil").val();
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
url: "<?php echo base_url(); ?>" + "index.php/ba_action/meter_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_code'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'meter_seil': meter_seil, 
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
var meter_seil = $("#meter_seil").val();
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
url: "<?php echo base_url(); ?>" + "index.php/ba_action/meter_management_edit_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_code'];?>',
'meter_id': meter_id,
'client_name': client_name, 
'meter_seil': meter_seil, 
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

function delete_meter_info(idX)
{
event.preventDefault();
var meter_id = idX;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/ba_action/delete_meter",
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

/*RESET MESSAGE*/
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
}

function clear_meter_field_form(event)
{
event.preventDefault();
var meter_id = 'NA';
var site_id = <?=$site_id;?>;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/ba_action/clear_meter_action/<?=$site_id;?>",
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
 $('#meter_addressable_id').val('');
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
url: "<?php echo base_url(); ?>" + "index.php/ba_action/clear_meter_action/<?=$site_id;?>",
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
 $('#meter_addressable_id').val('');
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
url: "<?php echo base_url(); ?>" + "index.php/ba_action/select_meter",
dataType: 'html',
data: {
'meter_id': id,
'site_id': <?=$site_id;?>},
success : function(result) {

var x = document.getElementById("form_float");
x.style.display = "block";

var a = document.getElementById("edit_meter");
a.style.display = "block";

var b = document.getElementById("meter_info");
b.style.display = "none";

$('#edit_meter').html(result);
$("#serial_number").focus();

}
});
}

function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {

var b = document.getElementById("meter_info");
b.style.display = "none";

x.style.display = "block";

var a = document.getElementById("edit_meter");
a.style.display = "block";

$("#serial_number").focus();

document.getElementById("serial_number").disabled = false;
document.getElementById("meter_model").disabled = false;
document.getElementById("meter_type").disabled = false;
document.getElementById("meter_addressable").disabled = false;
document.getElementById("meter_addressable_id").disabled = false;
document.getElementById("rtu_sn_number").disabled = false;
document.getElementById("serial_number").readOnly = false;
document.getElementById("meter_model").readOnly = false;
document.getElementById("meter_type").readOnly = false;
document.getElementById("meter_addressable").readOnly = false;
document.getElementById("meter_addressable_id").readOnly = false;


} else {

var b = document.getElementById("meter_info");
b.style.display = "none";

x.style.display = "none";

var a = document.getElementById("edit_meter");
a.style.display = "none";

clear_meter_field_form(event);
$('#item_info').html('');

var c = document.getElementById("offline_meters");
c.style.display = "none";

}
}

function view_meter_info(id)
{
var meter_id = id;

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/report_action/meter_info",
dataType: 'html',
data: {
'meter_id': id,
'site_id': <?=$site_id;?>},
success : function(result) { 

var x = document.getElementById("form_float");
x.style.display = "block";

var a = document.getElementById("edit_meter");
a.style.display = "none";

var b = document.getElementById("meter_info");
b.style.display = "block";

$('#meter_info').html(result);
//$("#serial_number").focus();
var c = document.getElementById("offline_meters");
c.style.display = "none";

}
});
}
</script> 
<div id="message_all"></div> 
<div id="form_float" style="display:none;">
<div id="edit_meter" style="display:none;">
    <div class="col-md-2">
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Properties</div>
    
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Serial Number</label>
        <!--METER NUMBER-->
        <input type="text"  class="form-control" id="serial_number" name="serial_number" onchange="showResult(this.value)" onkeydown="showResult(this.value)" onkeyup="showResult(this.value)" tabindex="1" autocomplete="off">
        <div id="serial_number_message"></div>
        </div>
        
        <div class="col-md-4">
        <label>Description</label>
        <input class="form-control" id="client_name" name="client_name" list="client_list" tabindex="2" autocomplete="off">
        <div id="client_name_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
    
    
    </div>
	<hr class="user-orange-hr">
	
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-8">
        <label>SEIL</label>
        <input class="form-control" id="meter_seil" name="meter_seil" tabindex="2" autocomplete="off">
        <div id="meter_seil_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
		
    </div>
	
	<hr class="user-orange-hr">
	    <div class="row">
    
        <div class="col-md-2">
        </div>
    
        <div class="col-md-4">
        <label>Configuration File</label> 
        <select class="form-control" id="meter_model" name="meter_model" tabindex="3">
        <option value="">Pls. Select</option>
        <?php foreach ($meter_model_list as $meter_model_cols): ?>
        <option value="<?=$meter_model_cols['id'];?>,<?=$meter_model_cols['meter_model'];?>,<?=$meter_model_cols['config_file'];?>"><?=$meter_model_cols['meter_model'];?></option>
        <?php endforeach;  ?>
        </select>
        
        <div id="meter_model_message"></div>
        </div>
            
        <div class="col-md-4">
        <label>Meter Type</label>
        <input type="text"  class="form-control" id="meter_type" name="meter_type" list="meter_type_list" tabindex="4" autocomplete="off">
        <div id="meter_type_message"></div>
        </div>
    
        <div class="col-md-2">
        </div>
    
    </div>
	<hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Gateway Serial No.</label>
        <select class="form-control" id="rtu_sn_number" name="rtu_sn_number" tabindex="5">
        <option value="0,unassigned">Unassigned</option>
        <?php foreach ($rtu_list as $rtu_list_cols): ?>
        <option value="<?=$rtu_list_cols['id'];?>,<?=$rtu_list_cols['rtu_sn_number'];?>"><?=$rtu_list_cols['rtu_sn_number'];?></option>
        <?php endforeach;  ?>
        </select>
        <div id="rtu_sn_number_message"></div> 
        </div>
        
        <div class="col-md-4">
        <label>Physical Location</label>
        <input type="text"class="form-control" id="physical_location" name="physical_location" tabindex="6" autocomplete="off">
        <div id="physical_location_message"></div>
        </div>
     
        <div class="col-md-2">
        </div>
    
    </div>
	<hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Meter Role</label>
        <select class="form-control" id="meter_role" name="meter_role" tabindex="7">
        <option value="Client Meter" selected="selected">Client Meter</option>
        <option value="Check Meter">Check Meter</option>
        <option value="CUSA">CUSA</option>
        <option value="Spare Meter">Spare Meter</option>
        </select>
        <div id="meter_role_message"></div>
        </div>
        
        <div class="col-md-4">
        <label>Multiplier</label>
        <input type="text"  class="form-control" id="meter_multiplier" name="meter_multiplier" value="1" tabindex="8" autocomplete="off">
        <div id="meter_multiplier_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
        
    </div>
	<hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
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
        <input type="text"  class="form-control" id="meter_addressable_id" name="meter_addressable_id" value="1" tabindex="10" autocomplete="off">
        <div id="meter_addressable_id_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
    
    </div>
	<hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
    
        <div class="col-md-8">
        <label>Status</label>
        <select class="form-control" id="meter_status" name="meter_status" tabindex="11">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
        </select>
        <div id="meter_status_message"></div>
        </div>
        
        <div class="col-md-2">
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
	<hr class="user-orange-hr">
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
    <div class="col-md-2">
    </div>
<!--
    <div class="col-md-4">
    SAP METER INFORMATION
    </div>
-->
</div> 	
</div>
<div id="meter_info" style="display:none;">
</div>
<div id="offline_meters" style="display:none;">
</div>
</div>
<div class="col-md-12">
<div class="row">
<div class="col-md-12"> 

      <div align="left" style=""><h3><?php echo $title; ?></h3></div>

<div class="row">

<div class="col-md-6">
<div style=" padding:5px; font-size:14px;">
  <h5>Business Entity : <?=$site_details['business_entity'];?></h5>
  <h5>Site Code : <?=$site_details['site_code'];?></h5>
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
<?=$back_prev_page;?> 
[ <a href="#" onclick="myFunction()" class="">&plus;Add</a> ] 
[ <a href="<?php echo base_url(); ?>index.php/ba/rtu_management/<?=$site_id;?>" class="">Gateway List</a> ]
[ <a href="<?php echo base_url(); ?>index.php/ba/meter_management_unassigned/<?=$site_id;?>" class="">Unassigned Meters</a> ]
[ <a href="#" onclick="view_offline_meters()" class="">Offline Meters</a> ]
</div>
</div>
</div>
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<th>#</th>
<th >Measuring Point</th>
<th title="Measuring Point Description">Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway</th>
<!--
<th >Meter ID as Address?</th>
<th >Model</th>
<th >Multiplier</th>-->
<th >Role</th>
<th >Status</th>
<th title="Last Reading Date">Last Reading Date</th>
<th title="CREATION_DATE From SAP">Creation Date</th>
<th title="CREATION_BY From SAP">Creation By</th>
<th title="LAST_CHANGE_ON From SAP">Last Change On</th>
<th title="LAST_CHANGE_BY From SAP">Last Change By</th>
<th>View</th>
<th>Edit</th>
<th>Delete</th>
</thead>
<tbody>
</tbody>

<tfoot> 
<tr> 
<th>#</th>
<th >Measuring Point</th>
<th title="Measuring Point Description">Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway</th>
<!--
<th >Meter ID as Address?</th>
<th >Model</th>
<th >Multiplier</th>-->
<th >Role</th>
<th >Status</th>
<th title="Last Reading Date">Last Reading Date</th>
<th title="CREATION_DATE From SAP">Creation Date</th>
<th title="CREATION_BY From SAP">Creation By</th>
<th title="LAST_CHANGE_ON From SAP">Last Change On</th>
<th title="LAST_CHANGE_BY From SAP">Last Change By</th>
<th>View</th>
<th>Edit</th>
<th>Delete</th>
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
"url": "<?php echo site_url("ba_datatables/group_meter_list/$site_id/$rtu_id")?>",
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

function doconfirm(id)
{
	job=confirm("Are you sure to Delete this Meter?");
		if(job!=true)
		{
			return false;
		}else{
			delete_meter_info(id);
		}
}

function edit_meter_confirm()
{
	job=confirm("Are you sure you want to Update this Meter?");
		if(job!=true)
		{
			return false;
		}else{
			edit_meter(event);
		}
}
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