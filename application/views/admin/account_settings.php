<script type="text/javascript">
// Ajax post
$(document).ready(function() {
$("#accountsettings").click(function(event) {
event.preventDefault();
var user_real_name = $("#user_real_name").val();
var user_name = $("#user_name").val();
var old_password = $("#old_password").val();
var new_password = $("#new_password").val();
var retype_new_password = $("#retype_new_password").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/your_account_settings_post",
dataType: 'html',
data: {
	'user_real_name': user_real_name,
	'user_name': user_name,
 	'old_password': old_password, 
	'new_password': new_password, 
	'retype_new_password': retype_new_password 
	},
success : function(result) { 
result_message = result.split(/\t/);
user_real_name_message =result_message[0];
user_name_message =result_message[1];
old_password_message =result_message[2];
new_password_message =result_message[3];
retype_new_password_message =result_message[4];
message_all =result_message[5];

$('#message_all').html(message_all);
$('#user_real_name_message').html(user_real_name_message); 
$('#user_name_message').html(user_name_message);
$('#old_password_message').html(old_password_message);
$('#new_password_message').html(new_password_message);
$('#retype_new_password_message').html(retype_new_password_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},3000); 

}
});
});
});
</script>

<div class="" style="overflow:hidden !important">
<div class="row">
<div class="col-md-3">

</div>
<div class="col-md-6">
<div class="panel panel-default">
<div class="panel-heading blue-panel"><?=$title;?></div>
<div class="panel-body">

<?php echo $this->session->flashdata('msg_success'); ?>
 <?php echo form_open('admin/your_account_settings_post/', 'class="form-control-overflow"'); ?>
<div id="message_all"></div>
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>NAME</label>
<input type="text"class="form-control" id="user_real_name" name="user_real_name" autocomplete="off" value="<?=$user_info['user_real_name'];?>">
<div id="user_real_name_message" class="input_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>

<hr class="user-orange-hr">

<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>USERNAME</label>
<input type="text"class="form-control" id="user_name" name="user_name" autocomplete="off" value="<?=$user_info['user_name'];?>">
<div id="user_name_message" class="input_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>

<hr class="user-orange-hr">
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>OLD PASSWORD</label>
<input type="password"class="form-control" id="old_password" name="old_password">

<div id="old_password_message" class="input_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>

<hr class="user-orange-hr">
<div class="row">

<div class="col-md-2">
&nbsp;
</div>

<div class="col-md-8">
<label>NEW PASSWORD</label>
<input type="password"class="form-control" id="new_password" name="new_password">

<div id="new_password_message" class="input_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>

<hr class="user-orange-hr">
<div class="row">

<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-8">
<label>CONFIRM PASSWORD</label>
<input type="password"class="form-control" id="retype_new_password" name="retype_new_password">
<div id="retype_new_password_message" class="input_message"></div>
</div>

<div class="col-md-2">
&nbsp;
</div>

</div>
<hr class="user-orange-hr">
<div class="row">
<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-8">
<button type="submit" name="submit" id="accountsettings" class="btn btn-primary">Submit</button>
</div>
<div class="col-md-2">
&nbsp;
</div>
</div>

</form>
</div>
</div>
</div>

</div>
</div>
</div>
</div>