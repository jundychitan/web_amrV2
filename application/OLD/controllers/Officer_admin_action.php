<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Officer_action extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('site_admin_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	} 
	
	public function account_settings_post()
	{

if($this->session->userdata('currently_logged_in_as')=='Site'){

$this->load->helper('form');
$this->load->library('form_validation');

$this->form_validation->set_rules('user_real_name', 'Name', 'required');
$this->form_validation->set_rules('user_name', 'Username', 'required');
$this->form_validation->set_rules('old_password', 'Old Password', 'required|trim|min_length[5]|max_length[12]');
$this->form_validation->set_rules('new_password', 'New Password', 'required|trim|min_length[5]|max_length[12]|matches[retype_new_password]');
$this->form_validation->set_rules('retype_new_password', 'Confirm Password', 'required|trim|min_length[5]|max_length[12]');

//VALIDATION FAILED
if ($this->form_validation->run() === FALSE)
{

$a = "<div style='color:red; display:inline-flex;'>" . form_error('user_real_name') . "</div>";
$b = "<div style='color:red; display:inline-flex;'>" . form_error('user_name') . "</div>";
$c = "<div style='color:red; display:inline-flex;'>" . form_error('old_password') . "</div>";
$d = "<div style='color:red; display:inline-flex;'>" . form_error('new_password') . "</div>"; 
$e = "<div style='color:red; display:inline-flex;'>" . form_error('retype_new_password') . "</div>";
$f = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";

echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";

}
//VALIDATION SUCCESS
else{

$username = $this->input->post('user_name');
$user_password = md5($this->input->post('new_password'));
//$user_type = 'Site';

$account_settings = $this->site_admin_model->user_info();
$data['current_account_settings'] = $account_settings;

$user_password_current = $data['current_account_settings']['user_password'];
$user_password_old_from_input = md5($this->input->post('old_password'));

if($user_password_current!=$user_password_old_from_input){
//Current password did not match to old password input
$a = "";
$b = "";
$c = "<div style='color:red;'>Wrong Password</div>";
$d = "";
$e = "";
$f = "<div class='message_style'><div class='message_style_content'><strong>***Wrong Password!***</strong></div></div>";

echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\t" . $f . "\n";

}else{

//$myfile_config_copy = fopen("/projects/config_file/account_settings", "w") or die("Unable to open file!");
//fwrite($myfile_config_copy, "$username,$user_password,$user_type");
//fclose($myfile_config_copy);
$this->site_admin_model->user_management_action();

$a = "";
$b = "";
$c = "";
$d = "";
$e = "<div class='message_style'><div class='message_style_content'><strong>***Saved!***</strong></div></div>";
//$e = "<div class='alert alert-success  alert-dismissable'><a href='#' class='close' data-dismiss='alert' aria-label='close'>×</a><strong>Saved!</strong></div>";
echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\n";
}
}
}
else{
redirect('Login/invalid');  
}
   
}
	
	//SITE MANAGEMENT ACTION 
	public function select_site(){
	
	$site_id = $this->input->post('site_id');
	if($site_id!=''){
	//GET USER INFO
	$data['site_info']=$this->site_admin_model->get_selected_site($site_id);
	
	$site_id = $data['site_info']['id'];
	$site_name = $data['site_info']['site_name'];
	
	$edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_site' class='btn btn-primary' value='$site_id' onClick='edit_site(event)'>Update</button></div><div class='btn-group'><button type='submit' name='delete_site' id='delete_site' class='btn btn-primary' value='$site_id' onClick='delete_site_info(event)'>Delete</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_site_field(event)'>Clear</button></div></div>";
	
	echo  $site_name . "\t" . $edit_button . "\n";
	
	}
	else{
	}
	
	}
	
	public function select_location_meter_list()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	$location_id = $this->input->post('location_id');
	/*QUERY METER LIST BY LOCATION*/
	$meter_list = $this->site_admin_model->get_location_meter_list($location_id);
	?>
    <label>Meter</label><br />
    <input type="text" class="form-control" id="meter" name="meter" list="meter_list" size="20" autocomplete="off"> 
    <datalist id="meter_list">
    <?php
    foreach ($meter_list as $meter_col): 
    $meter_id = $meter_col['meter_id'];
   	$client_name = $meter_col['client_name'];
    ?>
    <option value="<?php echo "$meter_id";?>"><?php echo "$client_name-$meter_id"; ?></option>
    <?php
    endforeach;
    ?>
    </datalist>
  
    <?php
	}
	
	public function site_management_add_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('site_name', 'Site Name', 'required');
	
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if ($this->form_validation->run() === FALSE)
	{
	 
	$userid = '';
	if(form_error('site_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('site_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $userid . "\t" . $a . "\n";
	
	}
	else
	{
	$a = $this->site_admin_model->site_management_action();
	echo  @$userid . "\t" . $a . "\n";
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function site_management_edit_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('site_name', 'Site Name', 'required');
	
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if ($this->form_validation->run() === FALSE)
	{
	 
	$userid = '';
	if(form_error('site_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('site_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $userid . "\t" . $a . "\n";
	
	}
	else
	{
	$this->site_admin_model->site_management_action();
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function delete_site()
	{
	
	$id = $this->input->post('site_id');
	$this->site_admin_model->delete_site($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_site' class='btn btn-primary' value='' onClick='add_site(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_site_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function clear_site_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_site' class='btn btn-primary' value='' onClick='add_site(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_site_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	//SUB GROUP MANAGEMENT ACTION
	public function select_sub_group()
	{
	
	$sub_group_id = $this->input->post('sub_group_id');
	
	if($sub_group_id!=''){
	//GET USER INFO
	$data['sub_group_info']=$this->site_admin_model->get_selected_sub_group($sub_group_id);
	
	$id = $data['sub_group_info']['id'];
	$sub_group_name = $data['sub_group_info']['sub_group_name'];
	
	$edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_sub_group' class='btn btn-primary' value='$id' onClick='edit_sub_group(event)'>Update</button></div><div class='btn-group'><button type='submit' name='delete_sub_group' id='delete_sub_group' class='btn btn-primary' value='$id' onClick='delete_sub_group_info(event)'>Delete</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_sub_group_field(event)'>Clear</button></div></div>";
	
	echo  $sub_group_name . "\t" . $edit_button . "\n";
	
	}
	else{
	}
	
	}
	
	public function sub_group_management_add_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('sub_group_name', 'Sub-Group Name', 'required');
	
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if ($this->form_validation->run() === FALSE)
	{
	 
	$subgroupid = '';
	if(form_error('sub_group_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $subgroupid . "\t" . $a . "\n";
	
	}
	else
	{
	$subgroupid = '';
	$a = $this->site_admin_model->sub_group_management_action();
	echo  @$subgroupid . "\t" . $a . "\n";
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function sub_group_management_edit_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('sub_group_name', 'Sub-Group Name', 'required');
	
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if ($this->form_validation->run() === FALSE)
	{
	 
	$subgroupid = '';
	if(form_error('sub_group_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $subgroupid . "\t" . $a . "\n";
	
	}
	else
	{
	$subgroupid = '';
	$a = $this->site_admin_model->sub_group_management_action();
	echo  @$subgroupid . "\t" . $a . "\n";
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	
	/**/
	public function delete_sub_group()
	{
	
	$id = $this->input->post('sub_group_id');
	$this->site_admin_model->delete_sub_group($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_sub_group' class='btn btn-primary' value='' onClick='add_sub_group(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_sub_group_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function clear_sub_group_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_sub_group' class='btn btn-primary' value='' onClick='add_sub_group(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_sub_group_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	
	public function sub_group_list()
	{
		$siteid = $this->uri->segment(3);
	if (empty($siteid))
	{
	show_404();
	}
	
	$data['sub_group_details'] = $this->site_admin_model->sub_group_list($siteid);
					 $n = 1;
					 foreach ($data['sub_group_details'] as $sub_group_details_cols): 
					 ?>
					 <tr>
					   <td><input type="radio" onClick="selected_sub_group_id('<?php echo $sub_group_details_cols['id'];?>')" name="select_user"></td>
					   <td><?php echo $n; ?></td>
					   <td><?php echo $sub_group_details_cols['sub_group_name']; ?></td>
					   <td align="center"><?php echo $sub_group_details_cols['created_by']; ?></td>
					   <td align="center"><?php echo $sub_group_details_cols['date_created']; ?></td>
					   <td align="center"><?php echo $sub_group_details_cols['modified_by']; ?></td>
					   <td align="center"><?php echo $sub_group_details_cols['date_modified']; ?></td>
					 </tr>
					   <?php 
					   $n++;
					   endforeach; 
	}
	
	public function site_meter_list()
	{
	   $site_id = $this->uri->segment(3);
	
	if (empty($site_id))
	{
	show_404();
	}
	
	$data['site_sub_group_details'] = $this->site_admin_model->site_meter_group_list($site_id);
					 $n = 1;
					 foreach ($data['site_sub_group_details'] as $sub_group_details_cols): 
					 $sub_group_id = $sub_group_details_cols['id'];
	 $sub_group_name = $sub_group_details_cols['sub_group_name'];
	 ?>
					 
				  <a data-toggle="collapse" href="#<?=$sub_group_id;?>" style="padding:20px; font-size:14px;"><div id="site_<?=$sub_group_id;?>" class="glyphicon glyphicon-folder-close" style="padding:10px; font-size:20px;" onclick="change_class(this.id);">&nbsp;&nbsp;<?=$sub_group_name;?></div></a>
				  <br />
				  <?php
		$data['site_sub_group_meter_list'] = $this->site_admin_model->site_sub_group_meter_list($site_id,$sub_group_id);
	$total_meter = count($data['site_sub_group_meter_list']);
	if($total_meter == 0){
	//echo 'Empty';
	}
	else{
	  ?>
				 <div id="<?=$sub_group_id;?>" class="panel-collapse collapse meter_list_style">
	<?php 
									
	 $m = 1;
									 foreach ($data['site_sub_group_meter_list'] as $site_sub_group_meter_list_cols): 
	 $meter_id= $site_sub_group_meter_list_cols['id'];
	 $meter_name= $site_sub_group_meter_list_cols['meter_name'];
								?>
				 <div style="color:#000; margin-left:-1px;">&macr;&macr;<label><input type="radio" onClick="selected_meter('<?php echo $meter_id;?>')" name="select_id">&nbsp;<?=$meter_name;?></label></div>
								<?php 
	$m++;
	endforeach; 
		?>
	
						  </div>
						  <?php } ?>
						</div>
	
					   <?php 
					   $n++;
					   endforeach; 
	}
	
	public function load_subgroup_meter_get()
	{
	$site_id = $this->uri->segment(3);
	
	$site_id = $this->input->post('site_select');
	$data['site_sub_group_details'] = $this->site_admin_model->site_meter_group_list($site_id);
	$n = 1;
	
	foreach ($data['site_sub_group_details'] as $sub_group_details_cols): 
	$sub_group_id = $sub_group_details_cols['id'];
	$sub_group_name = $sub_group_details_cols['sub_group_name'];
	 ?>
    <a data-toggle="collapse" href="#<?=$sub_group_id;?>" style="padding:20px; font-size:14px;"><div id="site_<?=$sub_group_id;?>" class="glyphicon glyphicon-folder-close" style="padding:10px; font-size:20px;" onclick="change_class(this.id);">&nbsp;&nbsp;<?=$sub_group_name;?></div></a>
	<br />
	 <?php
	$data['site_sub_group_meter_list'] = $this->site_admin_model->site_sub_group_meter_list($site_id,$sub_group_id);
	$total_meter = count($data['site_sub_group_meter_list']);
	if($total_meter == 0){
	/*echo 'Empty';*/
	}
	else{
	?>
	<div id="<?=$sub_group_id;?>" class="panel-collapse collapse meter_list_style">
	<table border="0" cellspacing="0" cellspacing="2">
    <tr><thead><th  width="70%" style="text-align:center !important;">Customer</th><th align="center" width="30%" style="text-align:center" >Meter ID</th></thead></tr>
	<?php 
	 $m = 1;
	 foreach ($data['site_sub_group_meter_list'] as $site_sub_group_meter_list_cols): 
	 $meter_id= $site_sub_group_meter_list_cols['id'];
	 $meter_name= $site_sub_group_meter_list_cols['meter_name'];
	 $client_name = $site_sub_group_meter_list_cols['client_name'];
	 $meter_model = $site_sub_group_meter_list_cols['meter_model'];
	 $meter_type = $site_sub_group_meter_list_cols['meter_type'];
	 ?>
    <tr><td>
	<div style="color:#000; margin-left:-1px;">&macr;&macr;
    <label>
    <input type="checkbox" name="meter_list" value="<?php echo "$meter_name"."_$sub_group_id"; ?>">&nbsp;<?php echo "$client_name";?></label>
    </td>
    <td><label><?php echo "$meter_name";?></label></td></tr>
    </div> 
	<?php 
	 $m++;
	 endforeach; 
	?> 
	</div>
		 </table>				  <?php } ?>
						</div>
	
					   <?php 
					   $n++;
					   endforeach; 
					   ?>
                      
                       </div>
                       <?php
	}
	
	public function select_meter()
	{
	
	$meter_id = $this->input->post('meter_id');
	
	if($meter_id!=''){
	
	$data['meter_info']=$this->site_admin_model->get_selected_meter($meter_id);
	
	$client_name = $data['meter_info']['client_name'];
	 
	$meter_site_id = $data['meter_info']['meter_site_id'];
	$data['sub_group_details'] = $this->site_admin_model->sub_group_list($meter_site_id);
	$meter_site_sub_group_id = $data['meter_info']['meter_site_sub_group_id'];
	$meter_site_sub_group_name = $data['meter_info']['meter_site_sub_group_name'];
	$rtu_sn_number_id = $data['meter_info']['rtu_sn_number_id'];
	$rtu_sn_number = $data['meter_info']['rtu_sn_number'];
	$meter_name = $data['meter_info']['meter_name'];
	$meter_model = $data['meter_info']['meter_model'];
	$meter_model_id = $data['meter_info']['meter_model_id'];
	$meter_type = $data['meter_info']['meter_type'];
	$meter_multiplier = $data['meter_info']['meter_multiplier'];
	$meter_status = $data['meter_info']['meter_status'];
	$meter_name_addressable = $data['meter_info']['meter_name_addressable'];
	$siteid = $meter_site_id;
	
	$meter_model_list = $this->site_admin_model->meter_model_list();
	$rtu_list = $this->site_admin_model->rtu_list($siteid);
	?>
	<div class="row">
	<div class="col-md-2">
	&nbsp;
		</div>
	
	<div class="col-md-4">
	<label>Customer Name</label>
	<input type="text"  class="form-control" id="client_name" name="client_name" list="client_name_list" value="<?=$client_name;?>">
	<div id="client_name_message"></div>
		</div>
	<div class="col-md-4">
	<label>Sub-Group Name</label>
	  
	<select class="form-control" id="sub_group" name="sub_group">
	
	<?php foreach ($data['sub_group_details'] as $sub_group_details_cols):  ?>
	<option value="<?=$sub_group_details_cols['id'];?>,<?=$sub_group_details_cols['sub_group_name'];?>" 
	   <?php if($sub_group_details_cols['id']==$meter_site_sub_group_id){ echo 'selected'; } else{} ?>>
	   <?=$sub_group_details_cols['sub_group_name'];?>
	</option>
	<?php endforeach;  ?>
	
	</select>
	
	<div id="sub_group_message"></div>
		</div>
	<div class="col-md-2">
	&nbsp;
		</div>
	
	</div>
	
	<div class="row">
	
	<div class="col-md-2">
	&nbsp;
		</div>
	<div class="col-md-4">
	<label>RTU Serial No.</label>
	   <select class="form-control" id="rtu_sn_number" name="rtu_sn_number">
	<option value="">Pls. Select</option>
	   <?php foreach ($rtu_list as $rtu_list_cols): ?>
	<?php
	   if($rtu_sn_number_id == $rtu_list_cols['id']){
	   $selected = " selected";
	   }
	   else{
	   $selected = " ";
	   }
	   ?>
	<option value="<?=$rtu_list_cols['id'];?>,<?=$rtu_list_cols['rtu_sn_number'];?>" <?=$selected;?>><?=$rtu_list_cols['rtu_sn_number'];?></option>
	<?php endforeach;  ?>
	</select>
		
		
		
		<div id="rtu_sn_number_message"></div>
		</div>
	<div class="col-md-4">
	<label>Meter ID</label>
		<!--METER NUMBER-->
	<input type="text"  class="form-control" id="serial_number" name="serial_number" list="serial_number_list" value="<?=$meter_name;?>">
	<div id="serial_number_message"></div>
		</div>
	
	<div class="col-md-2">
	&nbsp;
		</div>
	
	</div>
	<div class="row">
	
	<div class="col-md-2">
	&nbsp;
		</div>
	
	<div class="col-md-4">
<label>Meter Model</label>
<select class="form-control" id="meter_model" name="meter_model">
<option value="">Pls. Select</option>
<?php foreach ($meter_model_list as $meter_model_cols): ?>
<option value="<?=$meter_model_cols['id'];?>,<?=$meter_model_cols['meter_model'];?>,<?=$meter_model_cols['config_file'];?>"
<?php
if($meter_model_cols['id'] == $meter_model_id){
	echo 'selected';
}else{
}
?>
><?=$meter_model_cols['meter_model'];?></option>
<?php endforeach;  ?>
</select>

<div id="meter_model_message"></div>
</div>
	<div class="col-md-4">
	<label>Meter Type</label>
	<input type="text"  class="form-control" id="meter_type" name="meter_type" list="meter_type_list" value="<?=$meter_type;?>">
	<div id="meter_type_message"></div>
		</div>
	<div class="col-md-2">
	&nbsp;
		</div>
	
	</div>
	
	<div class="row">
	
	<div class="col-md-2">
	&nbsp;
		</div>
	
	 <div class="col-md-8">
	<label>Meter Multiplier</label>
	<input type="text"  class="form-control" id="meter_multiplier" name="meter_multiplier" value="<?=$meter_multiplier;?>">
	<div id="meter_multiplier_message"></div>
		</div>
	
	<div class="col-md-2">
	&nbsp;
		</div>
	
	</div>
	<div class="row">
	
	<div class="col-md-2">
	&nbsp;
		</div>
	<div class="col-md-4">
<label>Addressable Meter ID?</label>
<select class="form-control" id="meter_addressable" name="meter_addressable" >
<option value="1" <?php if($meter_name_addressable==1){echo 'selected'; } else{}?>>YES</option>
<option value="0" <?php if($meter_name_addressable==0){echo 'selected'; } else{}?>>NO</option>
</select>
<div id="meter_addressable_message"></div>
</div>
	<div class="col-md-4">
	<label>Status</label>
		 <select class="form-control" id="meter_status" name="meter_status" >
		<option value="Active" <?php if($meter_status=='Active'){ echo 'selected'; } else{} ?>>Active</option>
		<option value="Inactive" <?php if($meter_status=='Inactive'){ echo 'selected'; } else{} ?>>Inactive</option>
		</select>
	<div id="meter_status_message"></div>
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
	
	<?php
						$site_id = $meter_site_id;
						echo $edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_meter' class='btn btn-primary' value='$meter_id' onClick='edit_meter(event)'>Update</button></div><div class='btn-group'><button type='submit' name='delete_meter' id='delete_meter' class='btn btn-primary' value='$meter_id' onClick='delete_meter_info(event)'>Delete</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_field(event)'>Clear</button></div></div>";
						?>
					  </div>
		</div>
	<div class="col-md-2">
	</div>
	</div>
	</form>
</div>
	
			<?php
	
	}
	else{
	}
	
	}
	
	public function clear_meter_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_meter' class='btn btn-primary' value='' onClick='add_meter(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function delete_meter()
	{
	
	$id = $this->input->post('meter_id');
	$this->site_admin_model->delete_meter($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_sub_group' class='btn btn-primary' value='' onClick='add_sub_group(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_sub_group_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function meter_management_add_post()
	{
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('client_name', 'Customer Name', 'required');
	$this->form_validation->set_rules('sub_group', 'Sub-Group Name', 'required');
	$this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
	$this->form_validation->set_rules('rtu_sn_number', 'RTU SN Number', 'required');
	$this->form_validation->set_rules('meter_model', 'Meter Model', 'required');
	$this->form_validation->set_rules('meter_type', 'Meter Type');
	$this->form_validation->set_rules('meter_multiplier', 'meter_multiplier');
	$this->form_validation->set_rules('meter_status', 'Meter Status', 'required');
	
	if ($this->form_validation->run() === FALSE)
	{
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('sub_group')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	
	}
	else
	{
	$f = $this->site_admin_model->meter_management_action();
	
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management_edit_post()
	{
	if($this->session->userdata('currently_logged_in_as')=='Site'){	
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('client_name', 'Customer Name', 'required');
	$this->form_validation->set_rules('sub_group', 'Sub-Group Name', 'required');
	$this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
	$this->form_validation->set_rules('rtu_sn_number', 'RTU SN Number', 'required');
	$this->form_validation->set_rules('meter_model', 'Meter Model', 'required');
	$this->form_validation->set_rules('meter_type', 'Meter Type');
	$this->form_validation->set_rules('meter_multiplier', 'meter_multiplier');
	$this->form_validation->set_rules('meter_status', 'Meter Status', 'required');
	

	if ($this->form_validation->run() === FALSE)
	{
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('sub_group')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('sub_group') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$i = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$b . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\n";
	
	}
	else
	{
	$this->site_admin_model->meter_management_action();
	
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	/*REPORTING*/
	public function select_report_type_get()
	{
	
	$this->load->helper('form');
	$this->load->library('form_validation');
	//echo $report_type;
	$this->form_validation->set_rules('client_name', 'Customer Name', 'required');
	$data['report_type_list'] = $this->site_admin_model->report_type_list();
	
	echo "<select class='form-control' id='report_template' name='report_template'>";
	foreach ($data['report_type_list'] as $report_type_list_col): 
	$report_id = $report_type_list_col['id'];
	$report_name = $report_type_list_col['report_name'];
	?>
						<option value=<?php echo "$report_id";?>><?php echo "$report_name"; ?></option>
							<?php
	endforeach;
	echo "</select>";
	
	}
	
	/*RTU Management*/
	public function rtu_disable_update()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 0;
	$f = $this->site_admin_model->rtu_set_update_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	public function rtu_enable_update()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');
	if($this->session->userdata('currently_logged_in_as')=='Site'){
	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 1;
	$f = $this->site_admin_model->rtu_set_update_action($enable_update);
	} 
	} 
	else{
	redirect('Login/invalid');  
	}
	
	}	
	
	/*CHART*/
	public function load_meter_get()
	{
	$site_id = $this->input->post('site_select');
	$meter_role = $this->input->post('meter_role');
	?>
    <a data-toggle="collapse" href="#<?=$site_id;?>" style="padding:20px; font-size:14px;"><div id="site_<?=$site_id;?>" class="glyphicon glyphicon-folder-close" style="padding:10px; font-size:20px;" onclick="change_class(this.id);">&nbsp;&nbsp;Meter List</div></a>
	<br />
	<?php
	$data['site_sub_group_meter_list'] = $this->site_admin_model->site_sub_group_meter_list($site_id,$meter_role);
	$total_meter = count($data['site_sub_group_meter_list']);
	if($total_meter == 0){
	echo "";
	}
	else{
	?>
	<div id="<?=$site_id;?>" class="panel-collapse collapse meter_list_style">
    
	<table border="0" cellspacing="0" cellspacing="2">
    <tr><thead><th  width="70%" style="text-align:center !important;">Customer</th><th align="center" width="30%" style="text-align:center" >Meter ID</th></thead></tr>
	<?php 
	 $m = 1;
	 foreach ($data['site_sub_group_meter_list'] as $site_sub_group_meter_list_cols): 
	 $meter_id= $site_sub_group_meter_list_cols['id'];
	 $meter_name= $site_sub_group_meter_list_cols['meter_name'];
	 $client_name = $site_sub_group_meter_list_cols['client_name'];
	 $other_description = $site_sub_group_meter_list_cols['other_description'];
	 $meter_model = $site_sub_group_meter_list_cols['meter_model'];
	 $meter_type = $site_sub_group_meter_list_cols['meter_type'];
	 ?>
    <tr><td>
	<div style="color:#000; margin-left:-1px;">&macr;&macr;
    <label>
    <input type="checkbox" name="meter_list" class="check_box" value="<?php echo "$meter_name"; ?>">&nbsp;<?php echo "$client_name $other_description";?></label>
    </td>
    <td><label><?php echo "$meter_name";?></label></td></tr>
    </div>
	<?php 
	 $m++;
	 endforeach; 
	?> 
	</div>
		 </table>				  <?php } ?>
						</div>
	
	
                     
                       </div>
                       <?php
	}
}