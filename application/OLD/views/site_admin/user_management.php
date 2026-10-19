	<script type="text/javascript"> 
	/* Ajax post */
	function add_user( event )
	{
	event.preventDefault();
	var user_id = $("#add_edit_user").val();
	var user_real_name = $("#user_real_name").val();
	var user_name = $("#user_name").val();
	var user_type = $("#user_type").val();
	var user_password = $("#user_password").val();
	var user_password_confirm = $("#user_password_confirm").val();
	
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/user_management_add_post",
	dataType: 'html',
	data: {
	'user_id': user_id,
	'user_real_name': user_real_name, 
	'user_name': user_name,
	'user_type': user_type,
	'user_password': user_password,
	'user_password_confirm': user_password_confirm,
	},
	success : function(result) { 
	//alert(result);
	result_message = result.split(/\t/);
	user_id_message =result_message[0];
	user_real_name_message =result_message[1];
	user_name_message =result_message[2];
	user_type_message =result_message[3];
	user_password_message =result_message[4];
	user_password_confirm_message =result_message[5];
	message_all =result_message[6];
	
	$('#user_id_message').html(user_id_message);
	$('#user_real_name_message').html(user_real_name_message);
	$('#user_name_message').html(user_name_message);
	$('#user_type_message').html(user_type_message);
	$('#user_password_message').html(user_password_message);
	$('#user_password_confirm_message').html(user_password_confirm_message);
	
	
	$('#message_all').html(message_all).show();
	setTimeout(function() {
	$('#message_all').fadeOut('slow');
	},1800); 
	
	$('#table').DataTable().draw();
	
	}
	});
	}
	
	
	function edit_user(event)
	{
	event.preventDefault();
	
	var user_id = $("#add_edit_user").val();
	var user_real_name = $("#user_real_name").val();
	var middle_name = $("#middle_name").val();
	var last_name = $("#last_name").val();
	var user_name = $("#user_name").val();
	var user_type = $("#user_type").val();
	var user_password = $("#user_password").val();
	var user_password_confirm = $("#user_password_confirm").val();
	var session_duration = $("#session_duration").val();
	var session_duration_uom = $("#session_duration_uom").val();
	
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/user_management_edit_post",
	dataType: 'html',
	data: {
	'user_id': user_id,
	'user_real_name': user_real_name, 
	'middle_name': middle_name, 
	'last_name': last_name , 
	'user_name': user_name,
	'user_type': user_type,
	'user_password': user_password,
	'user_password_confirm': user_password_confirm,
	'session_duration': session_duration,
	'session_duration_uom': session_duration_uom
	},
	success : function(result) { 
	//alert(result);
	result_message = result.split(/\t/);
	user_id_message =result_message[0];
	user_real_name_message =result_message[1];
	user_name_message =result_message[2];
	user_type_message =result_message[3];
	user_password_message =result_message[4];
	user_password_confirm_message =result_message[5];
	message_all =result_message[6];
	
	$('#user_id_message').html(user_id_message);
	$('#user_real_name_message').html(user_real_name_message);
	$('#user_name_message').html(user_name_message);
	$('#user_type_message').html(user_type_message);
	$('#user_password_message').html(user_password_message);
	$('#user_password_confirm_message').html(user_password_confirm_message);
	
	$('#message_all').html(message_all).show();
	setTimeout(function() {
	$('#message_all').fadeOut('slow');
	},1800); 
	$('#table').DataTable().draw();

	}
	});
	}
	
	function delete_user_info(event)
	{
	event.preventDefault();
	var user_id = $("#delete_user").val();
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/delete_user",
	dataType: 'html',
	data: {
	'user_id': user_id
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
	
	$('#table').DataTable().draw();
	
	 $('#user_real_name').val('');
	 $('#user_name').val('');
	 $('#user_password').val('');
	 $('#user_password_confirm').val('');
	
	$('#user_id_message').html('');
	$('#user_real_name_message').html('');
	$('#user_name_message').html('');
	$('#user_type_message').html('');
	$('#user_password_message').html('');
	$('#user_password_confirm_message').html('');
	
	}
	});
	
	}
	
	function clear_user_field_form(event)
	{
	event.preventDefault();
	var user_id = 'NA';
	
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_action",
	dataType: 'html',
	data: {
	'user_id': user_id
	},
	success : function(result) { 
	result_message = result.split(/\t/);
	edit_button =result_message[0];
	message_all =result_message[1]; 	
	$('#edit_button').html(edit_button);
	$('#user_real_name').val('');
	$('#user_name').val('');
	$('#user_password').val('');
	$('#user_password_confirm').val('');
	$('#session_duration').val('');
	 
	$('#user_id_message').html('');
	$('#user_real_name_message').html('');
	$('#user_name_message').html('');
	$('#user_type_message').html('');
	$('#user_password_message').html('');
	$('#user_password_confirm_message').html('');

	}
	});
	
	}
	
	function clear_user_field(event)
	{
	event.preventDefault();
	var user_id = 'NA';
	
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_action",
	dataType: 'html',
	data: {
	'user_id': user_id
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
	
	 
	 $('#user_real_name').val('');
	 $('#user_name').val('');
	 $('#user_password').val('');
	 $('#user_password_confirm').val('');
	 
	$('#user_id_message').html('');
	$('#user_real_name_message').html('');
	$('#user_name_message').html('');
	$('#user_type_message').html('');
	$('#user_password_message').html('');
	$('#user_password_confirm_message').html('');
	
	}
	});
	
	}
	function selected_id(id)
	{
	var user_id = id;
	jQuery.ajax({
	type: "POST",
	url: "<?php echo base_url(); ?>" + "index.php/admin_action/select_user",
	dataType: 'html',
	data: {'user_id': id },
	success : function(result) { 
	result_col = result.split(/\t/);
	var x = document.getElementById("form_float");

	x.style.display = "block";
	$('#edit_form').html(result);
	 
	}
	});}
	
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
<div class="col-md-2"> 
</div>
<div class="col-md-8">
<div class="panel panel-default">
<div class="panel-heading blue-panel"><?php echo $title_2; ?></div>
<div class="panel-body"></div>
<div id="edit_form">
<?php echo form_open('admin/user_management/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div class="row">

<div class="col-md-2">

</div>

<div class="col-md-8">
<label>Name</label>
<input type="text"class="form-control" id="user_real_name" name="user_real_name" list="user_real_name_list">
<div id="user_real_name_message"></div>
</div>

<div class="col-md-2">
</div>

</div>

<hr class="user-orange-hr">
<div class="row">
<div class="col-md-2">
</div>
<div class="col-md-6">
<label>Username</label>
<input type="text"class="form-control" id="user_name" name="user_name" />
<div id="user_name_message"></div>
</div>

<div class="col-md-2">
<label>User Role</label>
<div id="usertype">
<select name="user_type"id="user_type" class="form-control">
<option value="Admin" selected="selected">Admin</option>
<option value="Site">Site Admin</option>
<option value="Client">Client</option>
</select>
</div>
<div id="user_type_message"></div>
</div>
<div class="col-md-2">
</div>

</div>

<hr class="user-orange-hr">
<div class="row">
<div class="col-md-2">
</div>
<div class="col-md-4">
<label>Password</label>
<input type="password"class="form-control" id="user_password" name="user_password" value=""/>
<div id="user_password_message"></div>
</div>

<div class="col-md-4">
<label>Confirm Password</label>
<input type="password"class="form-control" id="user_password_confirm" name="user_password_confirm" value=""/>
<div id="user_password_confirm_message"></div>
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
 <button type="submit" name="add_edit_user" id="add_edit_user" class="btn btn-primary" value="" onClick="add_user(event)">Save</button>
 </div>
 <div class="btn-group">
 <button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_user_field(event)">Clear</button>
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
<div class="col-md-2"> 
</div>
 </div>
 </div>
</div>

<div class="col-md-12">
<div class="row">
<div class="col-md-12">
      <div align="left" style=""><h3><?php echo $title; ?></h3></div>

<div id="message_all"></div>
<div id="test"></div>
<div class="row">
<div class="col-md-6">

</div>

<div class="col-md-6">
<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
[ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ]
</div>
</div>

</div>

<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<th>&nbsp;</th>
<th>#</th>
<th >Name</th>
<th >Username</th>
<th >User Role</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >User Access Group</th>
</tr>
</thead>
<tbody>
</tbody>

<tfoot>
<tr>
<th>&nbsp;</th>
<th>#</th>
<th >Name</th>
<th >Username</th>
<th >User Role</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >User Access Group</th>
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
"processing": true, //Feature control the processing indicator.
"serverSide": true, //Feature control DataTables' server-side processing mode.
"order": [], //Initial no order.
// Load data for the table's content from an Ajax source
"ajax": {
"url": "<?php echo site_url('admin_datatables/user_list')?>",
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




