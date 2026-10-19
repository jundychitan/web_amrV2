<div id="form_float" style="display:none;">
<div id="meter_info" style="display:none;">
</div>
<div id="offline_meters" style="display:none;">
</div>
</div>
<div class="col-md-12">
<div class="row">
<div class="col-md-12"> 
<div align="left" style="height: 45px; padding: 9px; font-size: 20px; font-weight: bold !important;"><?php echo $title; ?></div>
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
[ <a href="<?php echo base_url(); ?>index.php/re/rtu_management/<?=$site_id;?>" class="">Gateway List</a> ]
[ <a href="#" onclick="view_offline_meters()" class="">Offline Meters</a> ]
</div>
</div>
</div>
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<!--<th>&nbsp;</th>-->
<th>#</th>
<th title="Measuring Point Description">Measuring Point</th>
<th>Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway</th>
<th >Meter Role</th>
<th >Meter Status</th>
<th title="Last Reading Date">Last Reading Date</th>
<th title="CREATION_DATE From SAP">Creation Date</th>
<th title="CREATION_BY From SAP">Creation By</th>
<th title="LAST_CHANGE_ON From SAP">Last Change On</th>
<th title="LAST_CHANGE_BY From SAP">Last Change By</th>
<th >View</th>
</thead>
<tbody>
</tbody>

<tfoot> 
<tr> 
<th>#</th>
<th title="Measuring Point Description">Measuring Point</th>
<th>Meter ID</th>
<th title="Tenant/Utilities">Description</th>
<th >Gateway</th>
<th >Meter Status</th>
<th title="Last Reading Date">Last Reading Date</th>
<th title="CREATION_DATE From SAP">Creation Date</th>
<th title="CREATION_BY From SAP">Creation By</th>
<th title="LAST_CHANGE_ON From SAP">Last Change On</th>
<th title="LAST_CHANGE_BY From SAP">Last Change By</th>
<th >View</th>
</tr>
</tfoot>
</table>
</div>
</div>
</div>
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
"url": "<?php echo site_url("re_datatables/group_meter_list/$site_id/$rtu_id")?>",
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

<!-- Clear Search -->
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "re_datatables/group_meter_list/$site_id/$site_id/$rtu_id"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

}

function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {

var b = document.getElementById("meter_info");
b.style.display = "none";

x.style.display = "block";

} else {

var b = document.getElementById("meter_info");
b.style.display = "none";

x.style.display = "none";
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

var b = document.getElementById("meter_info");
b.style.display = "block";

$('#meter_info').html(result);

var c = document.getElementById("offline_meters");
c.style.display = "none";

}
});
}

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

var b = document.getElementById("meter_info");
b.style.display = "none";

var c = document.getElementById("offline_meters");
c.style.display = "block";

$('#offline_meters').html(result);

}
});
}
</script>
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