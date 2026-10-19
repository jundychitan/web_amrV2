
<div class="col-md-12">
<div class="row">
<div class="col-md-12">

<div align="left" style="height: 45px; padding: 9px; font-size: 20px; font-weight: bold !important;"><?php echo $title; ?></div>

<div class="row">
<div class="col-md-6">

</div>

<div class="col-md-6">

<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<!--[ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ]-->
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
<th >Company Code</th>
<th >Business Entity</th>
<th title="Site Group">Site Code</th>
<th >Site Description</th>
<th title="MALL/CPG">Type</th>
<th title="Assigned Cut off Per Business Entity">Cut Off</th> 
<th >Valid From</th>
<th >Valid To</th>
<th >Status</th>
<th >Gateway List</th>
<th >Meter List</th> </tr>
</thead>
<tbody>
</tbody>

<tfoot>
<tr>
<th>#</th>
<th >Company Code</th>
<th >Business Entity</th>
<th title="Site Group">Site Code</th>
<th >Site Description</th>
<th title="MALL/CPG">Type</th>
<th title="Assigned Cut off Per Business Entity">Cut Off</th> 
<th >Valid From</th>
<th >Valid To</th>
<th >Status</th>
<th >Gateway List</th>
<th >Meter List</th> </tr>
</tfoot>
</table>
<script type="text/javascript">
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "ba_datatables/site_list/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

}
var table;

$(document).ready(function() {

<?php /*datatables*/ ?> 
table = $('#table').DataTable({ 
"scrollY":        350,
"scrollCollapse": true,
// "jQueryUI":       true,
"processing": false,<?php /*Feature control the processing indicator.*/ ?>
"serverSide": true,<?php /*Feature control DataTables' server-side processing mode.*/ ?>
"order": [],<?php /*Initial no order.*/ ?>

 <?php /* Load data for the table's content from an Ajax source*/ ?>
"ajax": {
"url": "<?php echo site_url('ba_datatables/site_list/0')?>",
"type": "POST"
}, 

 <?php /*Set column definition initialisation properties.*/ ?>
"columnDefs": [
{ 
"targets": [ 0 ],<?php /*first column / numbering column*/ ?>
"orderable": false,<?php /*set not orderable*/ ?>
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
