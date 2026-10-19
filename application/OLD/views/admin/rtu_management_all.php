<script>
function clear_search() {
//admin_datatables/rtu_list/$site_id
event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "admin_datatables/rtu_list/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();
}

<!-- Search for Duplicate -->
function showResult(){

	ids=$("#add_edit_rtu").val();
	rtu_sn_number=$("#rtu_sn_number").val();
	mac_addr=$("#mac_addr").val();

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
        xmlhttp.open("GET","<?php echo base_url();?>index.php/admin_action/rtu_duplicate?rtu_id="+ids+"&rtu_sn_number="+rtu_sn_number+"&mac_addr="+mac_addr,true);
        xmlhttp.send();

}
 
function add_rtu(event)
{
event.preventDefault();
var site_id = 'NA';
var rtu_id = $("#add_edit_rtu").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var connection_type = $("#connection_type").val();
var phone_no_or_ip_address = $("#phone_no_or_ip_address").val();
var mac_addr = $("#mac_addr").val();
var location_id = $("#location_id").val();
var rtu_physical_location = $("#rtu_physical_location").val();

/*Additional Info JAN 24 2020*/
var idf_number = $("#idf_number").val();
var idf_port = $("#idf_port").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_name'];?>',
'rtu_id': rtu_id,
'connection_type': connection_type, 
'phone_no_or_ip_address': phone_no_or_ip_address, 
'rtu_sn_number': rtu_sn_number, 
'mac_addr': mac_addr,
'location_id': location_id,
'rtu_physical_location': rtu_physical_location,
'idf_number': idf_number,
'idf_port': idf_port
},
success : function(result) { 

result_message = result.split(/\t/);

rtu_id_message = result_message[0];
rtu_sn_number_message = result_message[1];
connection_type_message = result_message[2];
phone_no_or_ip_address_message = result_message[3];
mac_addr_message = result_message[4];
rtu_physical_location_message = result_message[5];
location_id_message = result_message[6];

idf_number_message = result_message[7];
idf_port_message = result_message[8];

message_all = result_message[9];

$('#rtu_id_message').html(rtu_id_message);
$('#connection_type_message').html(connection_type_message);
$('#phone_no_or_ip_address_message').html(phone_no_or_ip_address_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);
$('#mac_addr_message').html(mac_addr_message);
$('#rtu_physical_location_message').html(rtu_physical_location_message);
$('#location_id_message').html(location_id_message);

$('#idf_number_message').html(idf_number_message);
$('#idf_port_message').html(idf_port_message);

$('#message_all').html(message_all).show();

setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

$('#table').DataTable().draw();

}
});
}

function edit_rtu(event)
{
event.preventDefault();
var site_id = 'NA';
var rtu_id = $("#add_edit_rtu").val();
var rtu_sn_number = $("#rtu_sn_number").val();
var connection_type = $("#connection_type").val();
var phone_no_or_ip_address = $("#phone_no_or_ip_address").val();
var mac_addr = $("#mac_addr").val();
var location_id = $("#location_id").val();
var rtu_physical_location = $("#rtu_physical_location").val();

/*Additional Info JAN 24 2020*/
var idf_number = $("#idf_number").val();
var idf_port = $("#idf_port").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_management_edit_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_name'];?>',
'rtu_id': rtu_id,
'connection_type': connection_type, 
'phone_no_or_ip_address': phone_no_or_ip_address, 
'rtu_sn_number': rtu_sn_number, 
'mac_addr': mac_addr,
'location_id': location_id,
'rtu_physical_location': rtu_physical_location,
'idf_number': idf_number,
'idf_port': idf_port
},
success : function(result) { 

result_message = result.split(/\t/);

rtu_id_message = result_message[0];
rtu_sn_number_message = result_message[1];
connection_type_message = result_message[2];
phone_no_or_ip_address_message = result_message[3];
mac_addr_message = result_message[4];
rtu_physical_location_message = result_message[5];
location_id_message = result_message[6];

idf_number_message = result_message[7];
idf_port_message = result_message[8];

message_all = result_message[9];

$('#rtu_id_message').html(rtu_id_message);
$('#connection_type_message').html(connection_type_message);
$('#phone_no_or_ip_address_message').html(phone_no_or_ip_address_message);
$('#rtu_sn_number_message').html(rtu_sn_number_message);
$('#mac_addr_message').html(mac_addr_message);
$('#rtu_physical_location_message').html(rtu_physical_location_message);
$('#location_id_message').html(location_id_message);

$('#idf_number_message').html(idf_number_message);
$('#idf_port_message').html(idf_port_message);

$('#message_all').html(message_all).show();

setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

$('#table').DataTable().draw();

}
});

}
 
function delete_rtu_info(event) 
{
event.preventDefault();
 var rtu_id = $("#delete_rtu").val();
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/delete_rtu",
dataType: 'html',
data: {
'rtu_id': rtu_id
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

 $('#phone_no_or_ip_address').val('');
 $("#connection_type option[value='1']").attr('selected', 'selected');
 $('#rtu_sn_number').val('');
 $('#mac_addr').val('');
 $('#location_id').val('');
 $('#rtu_physical_location').val('');
 $('#update_rtu').val('');

 $('#idf_number').val('');
 $('#idf_port').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#rtu_id_message').html('');
$('#rtu_sn_number_message').html('');
$('#connection_type_message').html('');
$('#phone_no_or_ip_address_message').html('');
$('#rtu_sn_number_message').html('');
$('#mac_addr_message').html('');
$('#location_id_message').html('');
$('#rtu_physical_location_message').html('');

$('#idf_number_message').html('');
$('#idf_port_message').html('');

$('#table').DataTable().draw();
clear_rtu_field_form(event);

}
 
});
}

function clear_rtu_field_form(event)
{
event.preventDefault();
 var rtu_id = 'NA';
var site_id = 'NA';
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_rtu_action/'NA'",
dataType: 'html',
data: {
'rtu_id': rtu_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);

 $('#phone_no_or_ip_address').val('');
 $("#connection_type option[value='1']").attr('selected', 'selected');
 $('#rtu_sn_number').val('');
 $('#mac_addr').val('');
 $('#location_id').val('');
 $('#rtu_physical_location').val('');
 $('#update_rtu').val('');

 $('#idf_number').val('');
 $('#idf_port').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#rtu_id_message').html('');
$('#rtu_sn_number_message').html('');
$('#connection_type_message').html('');
$('#phone_no_or_ip_address_message').html('');
$('#rtu_sn_number_message').html('');
$('#mac_addr_message').html('');
$('#location_id_message').html('');
$('#rtu_physical_location_message').html('');

$('#idf_number_message').html('');
$('#idf_port_message').html('');

}
});
}

function clear_rtu_field(event)
{
event.preventDefault();
 var rtu_id = 'NA';
var site_id = 'NA';
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_rtu_action/'NA'",
dataType: 'html',
data: {
'rtu_id': rtu_id
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

 $('#phone_no_or_ip_address').val('');
 $("#connection_type option[value='1']").attr('selected', 'selected');
 $('#rtu_sn_number').val('');
 $('#mac_addr').val('');
 $('#location_id').val('');
 $('#rtu_physical_location').val('');
 $('#update_rtu').val('');

 $('#idf_number').val('');
 $('#idf_port').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#rtu_id_message').html('');
$('#rtu_sn_number_message').html('');
$('#connection_type_message').html('');
$('#phone_no_or_ip_address_message').html('');
$('#rtu_sn_number_message').html('');
$('#mac_addr_message').html('');
$('#location_id_message').html('');
$('#rtu_physical_location_message').html('');

$('#idf_number_message').html('');
$('#idf_port_message').html('');

}
});
}

function selected_rtu(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/select_rtu",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
var x = document.getElementById("form_float");
x.style.display = "block";
$('#edit_rtu').html(result);
$("#rtu_sn_number").focus();
}
 });
}

function selected_rtu_disable_update(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_disable_update",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);
message_all = result_message[1];
$('#rtu_message').html(message_all).show(); 

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}

function selected_rtu_enable_update(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_enable_update",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);

message_all = result_message[1];
$('#rtu_message').html(message_all).show();

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 
}
});
}

function selected_rtu_location_disable_update(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_location_disable_update",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);
message_all = result_message[1];
$('#rtu_message').html(message_all).show(); 

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}

function selected_rtu_location_enable_update(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_location_enable_update",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);

message_all = result_message[1];
$('#rtu_message').html(message_all).show();

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
});
}

function rtu_remote_ssh_disable(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_remote_ssh_disable",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);
message_all = result_message[1];
$('#rtu_message').html(message_all).show(); 

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}

function rtu_remote_ssh_enable(id)
{
var rtu_id = id;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_remote_ssh_enable",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);

message_all = result_message[1];
$('#rtu_message').html(message_all).show();

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}

/*FORCE LOAD PROFILE*/
function rtu_force_load_profile_disable(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_force_load_profile_disable",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);
message_all = result_message[1];
$('#rtu_message').html(message_all).show(); 

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}
 
function rtu_force_load_profile_enable(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/rtu_force_load_profile_enable",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
result_message = result.split(/\t/);

message_all = result_message[1];
$('#rtu_message').html(message_all).show();

$('#table').DataTable().draw();

setTimeout(function() {
$('#rtu_message').fadeOut('slow');
},1800); 

}
 });
}

function myFunction() {
 var x = document.getElementById("form_float");
 if (x.style.display === "none") {
x.style.display = "block";
$("#rtu_sn_number").focus();
 } else {
x.style.display = "none";
clear_rtu_field_form(event);
$('#item_info').html(''); 
$('#check_duplicate').html(''); 
}
}
</script> 
<div id="rtu_message"></div>
<div id="form_float" style="display:none;">
<div class="col-md-12">
<div class="row">
 <div class="col-md-2">
 </div>
 <div class="col-md-8"> 
 <div class="panel panel-default">

<div class="panel-body"> <div id="message_all"></div>
<div align="right"></div>
<div id="edit_form">
<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;"><?php echo $title_2; ?></div>

<?php echo form_open('admin/rtu_management/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div id="test"></div>
<!-- EDIT -->
<div id="edit_rtu">
<div class="row">
<div class="col-md-3">
&nbsp;
 </div>
<div class="col-md-6">
<label>Gateway Serial No.</label>
<input type="text"class="form-control" id="rtu_sn_number" name="rtu_sn_number" onchange="showResult()" onkeydown="showResult()" onkeyup="showResult()" autofocus="autofocus" tabindex="1">
<div id="rtu_sn_number_message"></div>
 </div>
<div class="col-md-3">
</div>
</div> 

<div class="row">
<div class="col-md-3">
&nbsp;
</div>
<div class="col-md-6">
<label>IP Address</label>
<input type="text"class="form-control" id="phone_no_or_ip_address" name="phone_no_or_ip_address" tabindex="2">
<div id="phone_no_or_ip_address_message"></div>
 </div>
<div class="col-md-3">
&nbsp;
 </div>
</div>

<div class="row">
<div class="col-md-3">
&nbsp;
</div>
<div class="col-md-6">
<label>MAC Address</label>
<input type="text"class="form-control" id="mac_addr" name="mac_addr" onchange="showResult()" onkeydown="showResult()" onkeyup="showResult()" tabindex="3" />
<div id="mac_addr_message"></div>
 </div>
<div class="col-md-3">
&nbsp;
 </div>
</div>

<div class="row">
<div class="col-md-3">
&nbsp;
</div>
<div class="col-md-3">
<label>IDF Number</label>
<input type="text"class="form-control" id="idf_number" name="idf_number"  tabindex="4">
<div id="idf_number_message"></div>
</div>
 
<div class="col-md-3">
<label>IDF Port </label>
<input type="text"class="form-control" id="idf_port" name="idf_port"  tabindex="5">
<div id="idf_port_message"></div>
</div> 
 
<div class="col-md-3">
&nbsp;
 </div>
</div>

<div class="row">
<div class="col-md-3">
&nbsp;
</div>
<div class="col-md-6">
<label>Physical Location</label>
<input type="text"class="form-control" id="rtu_physical_location" name="rtu_physical_location" tabindex="6">
<div id="rtu_physical_location_message"></div>
 </div>
<div class="col-md-3">
&nbsp;
 </div>
</div><div class="row">
<div class="col-md-3">
&nbsp;
</div>

<div class="col-md-3">
<label>Connection Type</label>
<select class="form-control" id="connection_type" name="connection_type" tabindex="7">
<option value="LAN">Ethernet </option>
<option value="3G">3G</option>
</select>
<div id="connection_type_message"></div>
</div> 

<div class="col-md-3">
<label>Site</label>
<select class="form-control" id="location_id" name="location_id"  tabindex="8"/>
<option value="">Please Select Site</option>
<?php foreach ($site_list as $site_list_cols):?>
<option value="<?php echo "$site_list_cols[meter_site_id],$site_list_cols[business_entity]";?>" <?php if($site_id==$site_list_cols['meter_site_id']){ echo 'selected'; } else{} ?>><?=$site_list_cols['business_entity'];?></option>
<?php endforeach;?>
</select>
<div id="location_id_message"></div>
</div>

<div class="col-md-3">
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
<button type="submit" name="add_edit_rtu" id="add_edit_rtu" class="btn btn-primary" value="" onClick="add_rtu(event)" tabindex="9">Save</button>
</div>
<div class="btn-group">
<button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_rtu_field(event)"  tabindex="10">Clear</button>
</div>
<div class="btn-group">
<button type="button" name="close_btn" id="close_btn" class="btn btn-primary" onclick="myFunction()"  tabindex="11">Close</button>
</div>
</div>
</div>
 </div>
<div class="col-md-2">
</div>
</div>
</form>
</div>

<div id="check_duplicate"></div>

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
<?=$back_prev_page;?> [ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/admin/site_management" class="">Site List</a> ] <!--[ 
<a href="<?php// echo base_url(); ?>index.php/admin/site_sub_group/<?//=//$site_id;?>" class="">Sub-group List</a> ]-->
</div>
</div>
</div>
<br />

<div class="table-responsive">
 <div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<th>&nbsp;</th>
<th>#</th>
<th >Description</th>
<th >Serial #</th>
<th >MAC Address</th>
<th >Site</th>
<th >Connection Type</th>
<th >SIM #/IP Address</th>
<th >Firmware Ver.</th>
<th >Last Update</th>
<th >Update CSV</th>
<th >Update Location</th>
<th >Remote SSH</th>
<th>Force LP</th>
<th >Meter</th>
</thead>
<tbody>
</tbody>
<tfoot>
<th>&nbsp;</th>
<th>#</th>
<th >Description</th>
<th >Serial #</th>
<th >MAC Address</th>
<th >Site</th>
<th >Connection Type</th>
<th >SIM #/IP Address</th>
<th >Firmware Ver.</th>
<th >Last Update</th>
<th >Update CSV</th>
<th >Update Location</th>
<th >Remote SSH</th>
<th >Force LP</th>
<th >Meter</th>
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
"url": "<?php echo site_url("admin_datatables/rtu_list/$site_id")?>",
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
$('<div class="clear_search" onClick="clear_search(event)">Clear Last Search</div>').appendTo('#table_filter');
});
</script>
</div>
</div>

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


