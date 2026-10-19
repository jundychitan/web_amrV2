
<div class="col-md-12">
<div id="rtu_message"></div>
<div class="row">
<div class="col-md-12"> 
<div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title_1; ?></div>
<div class="panel-body" style="height:590px !important;">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> [ <a href="<?php echo base_url(); ?>index.php/client/site_management" class="">Site List</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/client/site_sub_group/<?=$site_id;?>" class="">Sub-group List</a> ] [ 
<a href="<?php echo base_url(); ?>index.php/client/meter_management/<?=$site_id;?>" class="">Meter List</a> ]</div>
<div style=" padding:5px; font-size:14px; font-weight:bold;">Site Name : <?=$site_details['site_name'];?></div><br />
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
 <div id="default_table">
 <table id="table" class="table table-striped" cellspacing="0" width="100%">
 <thead>
 <th>#</th>
 <th >RTU Serial No.</th>
 <th >Connection Type</th>
 <th >Phone No./IP Address</th>
<th >MAC Address</th>
<th >Server IP Address</th>
<th >Update</th>
 <th >Created By</th>
 <th >Date Created</th>
 <th >Modified By</th>
 <th >Date Modified</th>
 <th >Last Update</th>
 <th >Meter List</th> 
 </thead>
 <tbody>
 </tbody>
 <tfoot>
 <th>#</th>
 <th >RTU Serial No.</th>
 <th >Connection Type</th>
 <th >Phone No./IP Address</th>
<th >MAC Address</th>
<th >Server IP Address</th>
<th >Update</th>
 <th >Created By</th>
 <th >Date Created</th>
 <th >Modified By</th>
 <th >Date Modified</th>
 <th >Last Update</th>
 <th >Meter List</th>
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
 "url": "<?php echo site_url("client_datatables/rtu_list/$site_id")?>",
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
</div>
</div>




