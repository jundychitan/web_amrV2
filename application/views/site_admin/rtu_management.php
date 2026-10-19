<script>
/*VIEW OFFLINE GATEWAY*/
function view_offline_gateway() {

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/report_action/view_offline_gateway",
dataType: 'html',
data: {
'site_id': <?=$site_id;?> 
},
success : function(result) { 

var x = document.getElementById("form_float");
x.style.display = "block";

var a = document.getElementById("edit_rtu");
a.style.display = "none";

var c = document.getElementById("offline_gateway");
c.style.display = "block";

$('#offline_gateway').html(result);

}
});
}
<?php $rtu_id = 0; ?>
function clear_search() {
//admin_datatables/rtu_list/$site_id
event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "site_admin_datatables/rtu_list_per_site/$site_id/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();
}

<!-- Search for Duplicate -->
function showResult() {

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
        xmlhttp.open("GET","<?php echo base_url();?>index.php/site_admin_action/rtu_duplicate?rtu_id="+ids+"&rtu_sn_number="+rtu_sn_number+"&mac_addr="+mac_addr,true);
        xmlhttp.send();

}

function edit_rtu(event)
{
event.preventDefault();
var site_id = <?=$site_id;?>;
var rtu_id = $("#add_edit_rtu").val();
var gateway_description = $("#gateway_description").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/rtu_management_edit_post",
dataType: 'html',
data: {
'site_id': site_id,
'site_name': '<?=$site_details['site_name'];?>',
'rtu_id': rtu_id,
'gateway_description': gateway_description
},
success : function(result) { 

result_message = result.split(/\t/);

rtu_id_message = result_message[0];
gateway_description_message = result_message[1];

message_all = result_message[2];

$('#rtu_id_message').html(rtu_id_message);
$('#gateway_description_message').html(gateway_description_message);
$('#message_all').html(message_all).show();

setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 

$('#table').DataTable().draw();

}
});

}

function clear_rtu_field_form(event)
{
event.preventDefault();

 	var rtu_id = 'NA';
	var site_id = <?=$site_id;?>;

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/clear_rtu_action/<?=$site_id;?>",
dataType: 'html',
data: {
'rtu_id': rtu_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);

$('#gateway_description').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#rtu_id_message').html('');
$('#gateway_description_message').html('');
}
});
}

function clear_rtu_field(event)
{
event.preventDefault();
 var rtu_id = 'NA';
var site_id = <?=$site_id;?>;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/clear_rtu_action/<?=$site_id;?>",
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

$('#gateway_description').val('');

$('#edit_button').html(edit_button);
//RESET MESSAGE
$('#rtu_id_message').html('');
$('#gateway_description_message').html('');

}
});
}

function selected_rtu(id)
{
var rtu_id = id;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/select_rtu",
dataType: 'html',
data: {'rtu_id': id },
success : function(result) { 
var x = document.getElementById("form_float");
x.style.display = "block";

var c = document.getElementById("offline_gateway");
c.style.display = "none";

var d = document.getElementById("edit_rtu");
d.style.display = "block";

$('#edit_rtu').html(result);
$("#gateway_description").focus();
}
});
}

function selected_rtu_disable_update(id)
{
var rtu_id = id;
 jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/rtu_disable_update",
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
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/rtu_enable_update",
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
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/rtu_location_disable_update",
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
url: "<?php echo base_url(); ?>" + "index.php/site_admin_action/rtu_location_enable_update",
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
$("#gateway_description").focus();
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

<div id="edit_rtu" style="display:none;">

</div>

<div id="offline_gateway" style="display:none;">
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
</div>
</div>
<div class="col-md-6">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
[ <a href="<?php echo base_url(); ?>index.php/site_admin/meter_management/<?=$site_id;?>" class="">Meter List</a> ] 
[ <a href="#" onclick="view_offline_gateway()" class="">Offline Gateway</a> ]
</div>
</div>
</div>
<br />
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<th></th>
<th>#</th>
<th >Serial #</th>
<th >IP Address</th>
<th >MAC Address</th>
<th >Physical Location</th>
<th >IDF Name</th>
<th >Switch</th>
<th >Port</th>
<th >Description</th>
<th >Firmware Ver.</th>
<th >Status</th>
<th >Update CSV</th>
<!--<th >Update Location</th>-->
<th >Meter</th>
</thead>
<tbody>
</tbody>
<tfoot>
<!--<th>&nbsp;</th>-->
<th></th>
<th>#</th>
<th >Serial #</th>
<th >IP Address</th>
<th >MAC Address</th>
<th >Physical Location</th>
<th >IDF Name</th>
<th >Switch</th>
<th >Port</th>
<th >Description</th>
<th >Firmware Ver.</th>
<th >Status</th>
<th >Update CSV</th>
<!--<th >Update Location</th>-->
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
"url": "<?php echo site_url("site_admin_datatables/rtu_list_per_site/$site_id/0")?>",
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
/*Append Clear Last Seach to seach box*/
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