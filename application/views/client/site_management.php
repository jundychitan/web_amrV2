<div class="col-md-12">
<div class="row">
<div class="col-md-12">

<div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title_1; ?></div>
<div class="panel-body">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
</div>
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<th>#</th>
<th >Site Name</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Sub-Group</th>
<th >RTU List</th>
<th >Meter List</th>
</tr>
</thead>
<tbody>
</tbody>

<tfoot>
<tr>
 
<th>#</th>
<th >Site Name</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Sub-Group</th>
<th >RTU List</th>
<th >Meter List</th>
</tr>
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
"url": "<?php echo site_url('client_datatables/site_list')?>",
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




