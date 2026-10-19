<script type="text/javascript">   

function add_sub_group(event)
{
event.preventDefault();

var site_id = <?=$site_id;?>;
var sub_group_id = $("#add_edit_sub_group").val();
var sub_group_name = $("#sub_group_name").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/sub_group_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'sub_group_id': sub_group_id,
'sub_group_name': sub_group_name, 
},
success : function(result) { 
//alert(result);
result_message = result.split(/\t/);
sub_group_id_message =result_message[0];
sub_group_name_message =result_message[1];
message_all =result_message[2];
$('#sub_group_id_message').html(sub_group_id_message);
$('#sub_group_name_message').html(sub_group_name_message);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
show_sub_group_table();
//$('#table').DataTable().draw();
}
});
}

function edit_sub_group(event)
{
event.preventDefault();
var site_id = <?=$site_id;?>;
var sub_group_id = $("#add_edit_sub_group").val();
var sub_group_name = $("#sub_group_name").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/sub_group_management_add_post",
dataType: 'html',
data: {
'site_id': site_id,
'sub_group_id': sub_group_id,
'sub_group_name': sub_group_name, 
},
success : function(result) { 
//alert(sub_group_id);
result_message = result.split(/\t/);
sub_group_id_message =result_message[0];
sub_group_name_message =result_message[1];
message_all =result_message[2];
$('#sub_group_id_message').html(sub_group_id_message);
$('#sub_group_name_message').html(sub_group_name_message);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
show_sub_group_table();
//$('#table').DataTable().draw();
}
});
}

function delete_sub_group_info(event)
{
event.preventDefault();
var sub_group_id = $("#delete_sub_group").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/delete_sub_group",
dataType: 'html',
data: {
'sub_group_id': sub_group_id
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
//$('#table').DataTable().draw();

$('#sub_group_name').val('');
 $('#sub_group_id_message').html('');

}
});
}

function clear_sub_group_field(event)
{
event.preventDefault();
var sub_group_id = 'NA';
var site_id = <?=$site_id;?>;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/clear_sub_group_action/<?=$site_id;?>",
dataType: 'html',
data: {
'sub_group_id': sub_group_id
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
 $('#sub_group_name').val('');
 $('#sub_group_id_message').html('');
$('#sub_group_name_message').html('');
}
});
}

function selected_sub_group_id(id)
{
var sub_group_id = id;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/client_action/select_sub_group",
dataType: 'html',
data: {'sub_group_id': id },
success : function(result) { 
//alert(result);
result_col = result.split(/\t/);
sub_group_name =result_col[0];
edit_button =result_col[1];
 $('#sub_group_name').val(sub_group_name);
 $('#add_edit_sub_group').val(sub_group_id);
 $('#edit_button').html(edit_button);
 $('#sub_group_name_message').html('');
}
});
}

</script>
  <div class="col-md-12">
  <div class="row">

<div class="col-md-4">

<div class="panel panel-default">
  <div class="panel-heading blue-panel"><?php echo $title_2; ?></div>
  <div class="panel-body">
  <div id="edit_form">
<?php echo form_open('admin/site_sub_group/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div id="message_all"></div>
<div id="test"></div>
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>Sub-Group Name</label>
<input type="text"  class="form-control" id="sub_group_name" name="sub_group_name" list="sub_group_name_list">
<div id="sub_group_name_message"></div>
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
 <button type="submit" name="add_edit_sub_group" id="add_edit_sub_group" class="btn btn-primary" value="" onClick="add_sub_group(event)">Save</button>
 </div>
 <div class="btn-group">
 <button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_sub_group_field(event)">Clear</button>
 </div>
 </div>
  </div>
</div>
<div class="col-md-2">
</div>

</div>
</form>
</div>
  </div>
</div>

  </div>
  
<div class="col-md-8">

<div class="panel panel-default">
  <div class="panel-heading blue-panel"><?php echo $title_1; ?></div>
  <div class="panel-body">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
[ <a href="<?php echo base_url(); ?>index.php/client/site_management">Site List</a> ][ 
<a href="<?php echo base_url(); ?>index.php/client/meter_management/<?=$site_id;?>">Meter List</a>
 ][ <a href="<?php echo base_url(); ?>index.php/client/rtu_management/<?=$site_id;?>">RTU List</a> ]
</div>
  <div style=" padding:5px; font-size:14px; font-weight:bold;">Site Name : <?=$site_details['site_name'];?></div><br />
  <div class="table-responsive">
<div id="default_table">
 <table id="table" class="table" cellspacing="0" width="100%">
<thead>
<tr>
   <th></th>
   <th>#</th>
   <th>Sub-Group Name</th>
   <th>Created by</th>
   <th>Date Created</th>
   <th>Modified by</th>
   <th>Date Modified</th>
</tr>
</thead>
<tbody id="txtHint">
</tbody>
</table>
</div>
  </div>
</div>
</div>
<script>
function show_sub_group_table() {
 
var site_id = <?=$site_id;?>;

if (window.XMLHttpRequest) {
// code for IE7+, Firefox, Chrome, Opera, Safari
xmlhttp = new XMLHttpRequest();
} else {
// code for IE6, IE5
xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
}
xmlhttp.onreadystatechange = function() {
if (this.readyState == 4 && this.status == 200) {
document.getElementById("txtHint").innerHTML = this.responseText;
}
};
xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/client_action/sub_group_list/"+site_id,true);
xmlhttp.send();
}
window.onload = show_sub_group_table;

</script></div>
  </div>
  </div>
</div>
</div>




