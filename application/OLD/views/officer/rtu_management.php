<div class="col-md-12">
<div id="rtu_message"></div>
<div class="row">
<div class="col-md-12"> 
<div align="left" style="height: 45px; padding: 9px; font-size: 20px; font-weight: bold !important;"><?php echo $title; ?></div>
<div class="row">
<div class="col-md-6">
<div style=" padding:5px; font-size:14px;"> 
<h5>Business Entity : <?=$site_details['site_code'];?></h5>
<h5>Site Name : <?=$site_details['site_name'];?></h5>
</div>
</div>
<div class="col-md-6">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;"><?=$back_prev_page;?>
</div>
</div>
</div>
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<th>#</th>
<th >Description</th>
<th >Serial #</th>
<th >Connection Type</th>
<th >Phone No./IP Address</th>
<th >MAC Address</th>
<th >Created By</th>
<th >Date Created</th>
<th >Modified By</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Firmware Ver.</th>
<th >Meter List</th>
</thead>
<tbody>
</tbody>
<tfoot>
<th>#</th>
<th >Description</th>
<th >Serial #</th>
<th >Connection Type</th>
<th >Phone No./IP Address</th>
<th >MAC Address</th>
<th >Created By</th>
<th >Date Created</th>
<th >Modified By</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Firmware Ver.</th>
<th >Meter List</th>
</tfoot>
</table>
</div>
</div>
</div>

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
 "url": "<?php echo site_url("officer_datatables/rtu_list/$site_id")?>",
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

<!-- Clear Search -->
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "officer_datatables/rtu_list/$site_id"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

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