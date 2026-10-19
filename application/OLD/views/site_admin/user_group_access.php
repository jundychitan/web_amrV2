<script type="text/javascript"> 

function add_user_group(event)
{
event.preventDefault();

var user_id = "<?php echo $user_id; ?>";
var user_group_id = $("#user_group_id").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/user_group_management_add_post",
dataType: 'html',
data: {
'user_id': user_id,
'user_group_id': user_group_id,
},
success : function(result) { 
//alert(result);
result_message = result.split(/\t/);
user_group_id_message =result_message[0];
message_all =result_message[1];
$('#user_group_id_message').html(user_group_id_message);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
show_user_group_table();
//$('#table').DataTable().draw();
}
});
}

function delete_user_group_info(id)
{
var user_group_id = id;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/delete_user_group",
dataType: 'html',
data: {
'user_group_id': user_group_id
},
success : function(result) { 
result_message = result.split(/\t/);
message_all =result_message[0];
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1500); 

$('#location_name').val('');
 $('#user_group_id_message').html('');
show_user_group_table();
}
});
}


function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {
x.style.display = "block";
} else {
x.style.display = "none";
clear_user_field_form(event);
}
}
</script>
 <div id="form_float" style="display:none;">
<div id="form_float_close" onclick="myFunction()">X</div>
<div class="col-md-12">
 <div class="row"> 
<div class="col-md-4"> 
</div>
<div class="col-md-4">
 <div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title_2; ?></div> 
<div class="panel-body">

<div id="edit_form">
<?php echo form_open('admin/user_group_access/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>Select Site</label><div id="sub_group_message"></div>
<select class="form-control" id="user_group_id" name="user_group_id"/>
<option>Please Select Site</option>
<?php foreach ($site_list as $site_list_cols):?>
<option value="<?php echo "$site_list_cols[id],$site_list_cols[company_no],$site_list_cols[site_code]";?>"><?=$site_list_cols['site_name'];?></option>
<?php endforeach;?>
</select>

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
 <div class="btn">

<button type="submit" name="add_edit_user_group" id="add_edit_user_group" class="btn btn-primary" value="" onClick="add_user_group(event)">Save</button>

 
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
<div class="col-md-4"> 
</div>
</div>
</div>
</div>
<div class="col-md-12">
<div class="row">
<div class="col-md-12">
      <div align="left" style=""><h3><?php echo $title; ?></h3></div>
<div id="message_all"></div>
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
<?php
if($user_details['user_list_src']=='SAP'){

}else{?>
[ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ]
<?php
}
	?>
</div>
<div style=" padding:5px; font-size:14px; font-weight:bold;">User Name : <?=$user_details['user_name'];?></div><br />

<div class="table-responsive">
<div id="default_table">
 <table id="table" class="table" cellspacing="0" width="100%">
<thead>
<tr>
 <th>#</th>
 <th>Company</th>
 <th>Business Entity</th>
 <th>Access Expiration</th>
 <th>Action</th>
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
function show_user_group_table() {
 
//var user_id = <!?=$user_id;?>;
var user_id = "<?php echo $user_id; ?>";

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
xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/admin_action/user_group_list/"+user_id,true);
xmlhttp.send();
}
window.onload = show_user_group_table;

</script></div>
</div>
</div>




