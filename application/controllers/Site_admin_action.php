
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	class Site_admin_action extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('site_admin_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	}
	
	//PAGE ACTION SCRIPT HERE
	public function account_settings_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	
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
	//$user_type = 'Officer';
	
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
	
	$this->site_admin_model->user_management_action();
	
	$a = "";
	$b = "";
	$c = "";
	$d = "";
	$e = "<div class='message_style'><div class='message_style_content'><strong>***Saved!***</strong></div></div>";
	
	echo  $a . "\t" . $b . "\t" . $c . "\t" . $d . "\t" . $e . "\n";
	}
	}
	}
	else{
	redirect('Login/invalid');  
	}
   
	}
	
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
	
	public function select_meter()
	{
	
	$meter_id = $this->input->post('meter_id');
	
	if($meter_id!=''){
	
	$data['meter_info']=$this->site_admin_model->get_selected_meter($meter_id);

	$client_name = $data['meter_info']['customer_name'];
	$measuring_point = $data['meter_info']['measuring_point'];

	$created_by = $data['meter_info']['created_by'];
	$date_created = $data['meter_info']['date_created'];
	$modified_by = $data['meter_info']['modified_by'];
	$date_modified = $data['meter_info']['date_modified'];

	$meter_site_id = $this->input->post('site_id');

	$rtu_sn_number_id = $data['meter_info']['rtu_sn_number_id'];
	$rtu_sn_number = $data['meter_info']['rtu_sn_number'];
	$meter_name = $data['meter_info']['meter_name'];
	$meter_model = $data['meter_info']['meter_model'];
	$meter_model_id = $data['meter_info']['meter_model_id'];
	$meter_type = $data['meter_info']['meter_type'];
	$meter_multiplier = $data['meter_info']['meter_multiplier'];
	$meter_status = $data['meter_info']['meter_status'];
	$meter_role = $data['meter_info']['meter_role'];
	$meter_name_addressable = $data['meter_info']['meter_name_addressable'];
	$meter_default_name = $data['meter_info']['meter_default_name'];
	$physical_location = $data['meter_info']['physical_location'];
	$meter_list_src = $data['meter_info']['meter_list_src'];
	$siteid = $meter_site_id;
	
	$meter_model_list = $this->site_admin_model->meter_model_list();
	$rtu_list = $this->site_admin_model->rtu_list($siteid);

	?>
 	<div class="col-md-2">
    </div>    
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Properties</div>
    
    <div class="row">
    
        <div class="col-md-2">
        </div>
    
        <div class="col-md-4">
        <label>Serial Number</label>
        <!--METER NUMBER-->
        <input type="text"  class="form-control" id="serial_number" name="serial_number" onchange="showResult(this.value)" onkeydown="showResult(this.value)" onkeyup="showResult(this.value)" value="<?=$meter_name;?>" tabindex="1" autocomplete="off">
        <div id="serial_number_message"></div>
        </div>
        
        <div class="col-md-4">
        <label>Description</label>
        <input class="form-control" id="client_name" name="client_name" list="client_list" value="<?=$client_name;?>" tabindex="2" autocomplete="off">
        <div id="client_name_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
    
    </div>
    <hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
    
        <div class="col-md-4">
        <label>Configuration File</label> 
        <select class="form-control" id="meter_model" name="meter_model" tabindex="3">
        <option value="">Pls. Select</option>
        <?php foreach ($meter_model_list as $meter_model_cols): ?>
        <option value="<?=$meter_model_cols['id'];?>,<?=$meter_model_cols['meter_model'];?>,<?=$meter_model_cols['config_file'];?>"
        <?php if($meter_model_cols['id'] == $meter_model_id){ echo 'selected'; }else{ } ?>><?=$meter_model_cols['meter_model'];?></option>
        <?php endforeach;  ?>
        </select>
        
        <div id="meter_model_message"></div>
        </div>
            
        <div class="col-md-4">
        <label>Meter Type</label>
        <input type="text"  class="form-control" id="meter_type" name="meter_type" list="meter_type_list" value="<?=$meter_type;?>" tabindex="4" autocomplete="off">
        <div id="meter_type_message"></div>
        </div>
    
        <div class="col-md-2">
        </div>
    
    </div>
    <hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Gateway Serial No.</label>
        <select class="form-control" id="rtu_sn_number" name="rtu_sn_number"  tabindex="5">
        <option value="">Pls. Select</option>
        <option value="0,unassigned">Unassigned</option>
        <?php
        foreach ($rtu_list as $rtu_list_cols):
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
        <label>Physical Location</label>
        <input type="text"class="form-control" id="physical_location" name="physical_location" value="<?=$physical_location;?>" tabindex="6" autocomplete="off">
        <div id="physical_location_message"></div>
        </div>
     
        <div class="col-md-2">
        </div>
    
    </div>
    <hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Meter Role</label>
        <select class="form-control" id="meter_role" name="meter_role" tabindex="7">
        <option value="Client Meter" <?php if($meter_role=='Client Meter'){ echo 'selected'; } else{} ?>>Client Meter</option>
        <option value="Check Meter" <?php if($meter_role=='Check Meter'){ echo 'selected'; } else{} ?>>Check Meter</option>
        <option value="CUSA" <?php if($meter_role=='CUSA'){ echo 'selected'; } else{} ?>>CUSA</option>
        <option value="Spare Meter" <?php if($meter_role=='Spare Meter'){ echo 'selected'; } else{} ?>>Spare Meter</option>
        </select>
        <div id="meter_role_message"></div>
        </div>
        
        <div class="col-md-4">
        <label>Multiplier</label>
        <input type="text"  class="form-control" id="meter_multiplier" name="meter_multiplier" value="<?=$meter_multiplier;?>" tabindex="8" autocomplete="off">
        <div id="meter_multiplier_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
        
    </div>
    <hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
        
        <div class="col-md-4">
        <label>Use Meter ID as Address?</label>
        <select class="form-control" id="meter_addressable" name="meter_addressable" tabindex="9">
        <option value="1" <?php if($meter_name_addressable==1){echo 'selected'; } else{}?>>YES</option>
        <option value="0" <?php if($meter_name_addressable==0){echo 'selected'; } else{}?>>NO</option>
        </select>
        <div id="meter_addressable_message"></div>
        </div>
    
        <div class="col-md-4">
        <label>Alternate Address</label>
        <input type="text"  class="form-control" id="meter_addressable_id" name="meter_addressable_id" value="<?=$meter_default_name;?>" tabindex="10" autocomplete="off">
        <div id="meter_addressable_id_message"></div>
        </div>
        
        <div class="col-md-2">
        </div>
    
    </div>
    <hr class="user-orange-hr">
    <div class="row">
    
        <div class="col-md-2">
        </div>
    
        <div class="col-md-8">
        <label>Status</label>
        <select class="form-control" id="meter_status" name="meter_status" tabindex="11">
        <option value="Active" <?php if($meter_status=='Active' || $meter_status=='ACTIVE'){ echo 'selected'; } else{} ?>>Active</option>
        <option value="Inactive" <?php if($meter_status=='Inactive' || $meter_status=='INACTIVE'){ echo 'selected'; } else{} ?>>Inactive</option>
        </select>
        <div id="meter_status_message"></div>
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
	
	<?php
	$site_id = $meter_site_id;
	echo $edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_meter' class='btn btn-primary' value='$meter_id' onClick='edit_meter_confirm(event)' tabindex='12'>Update</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_field(event)' tabindex='14'>Clear</button></div><div class='btn-group'><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value='' tabindex='15'>Close</button></div></div>";
	?>
	
    </div>
    </div>
    <div class="col-md-2">
    </div>
    
    
    </div>
    <div class="row">
    <div class="col-md-2">
    </div>
    <div class="col-md-8">
    <div id="check_duplicate">
    </div>
    </div>
    <div class="col-md-2">
    </div>
    </div>
    </form>
	</div>
	</div>
  	<div class="col-md-2">
    </div>  
	<?php
	}
	else{
	}
	
	}
	
	public function clear_meter_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_meter' class='btn btn-primary' value='' onClick='add_meter(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_field(event)'>Clear</button></div><div class='btn-group'><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button></div></div>";	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function delete_meter()
	{
	
	$id = $this->input->post('meter_id');
	$this->site_admin_model->delete_meter($id);
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_sub_group' class='btn btn-primary' value='' onClick='add_sub_group(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_sub_group_field(event)'>Clear</button></div><div class='btn-group'><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button></div></div>";
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	}
	
	public function meter_management_add_post()
	{
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
		
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('client_name', 'Customer Name');
	$this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
	$this->form_validation->set_rules('rtu_sn_number', 'RTU SN Number', 'required');
	$this->form_validation->set_rules('meter_model', 'Meter Model', 'required');
	$this->form_validation->set_rules('meter_type', 'Meter Type');
	$this->form_validation->set_rules('meter_multiplier', 'meter_multiplier');
	$this->form_validation->set_rules('meter_status', 'Meter Status', 'required');
	$this->form_validation->set_rules('physical_location', 'Physical Location', 'required');
	
	if ($this->form_validation->run() === FALSE)
	{
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('physical_location')!=''){ $i = "<div style='color:red; display:inline-flex;'>" . form_error('physical_location') . "</div>"; } else{ $i  = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }

	$j = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	
	}
	else
	{
	$f = $this->site_admin_model->meter_management_action();
	//$this->output->enable_profiler(TRUE);
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function meter_management_edit_post()
	{
		
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	
	$this->load->helper('form');
	$this->load->library('form_validation');

	$this->form_validation->set_rules('client_name', 'Customer Name');
	$this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
	$this->form_validation->set_rules('rtu_sn_number', 'RTU SN Number', 'required');
	$this->form_validation->set_rules('meter_model', 'Meter Model', 'required');
	$this->form_validation->set_rules('meter_type', 'Meter Type');
	$this->form_validation->set_rules('meter_multiplier', 'meter_multiplier');
	$this->form_validation->set_rules('physical_location', 'Physical Location', 'required');
	
	if ($this->form_validation->run() === FALSE)
	{
	 
	$meterid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('serial_number')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('serial_number') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_model')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_type')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('meter_type') . "</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_multiplier')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('meter_multiplier') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('meter_status')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('meter_status') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('rtu_sn_number')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('physical_location')!=''){ $i = "<div style='color:red; display:inline-flex;'>" . form_error('physical_location') . "</div>"; } else{ $i = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }

	$j = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $meterid . "\t" .$a . "\t" .$c . "\t" .$d . "\t" .$e . "\t" .$f . "\t" .$g . "\t" .$h . "\t" .$i . "\t" .$j . "\n";
	
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
	
	/*RTU Management*/
	public function select_rtu()
	{
	
	$rtu_id = $this->input->post('rtu_id');
	
	if($rtu_id!=''){
	
	$data['rtu_info']=$this->site_admin_model->get_selected_rtu($rtu_id);

	$rtu_sn_number = $data['rtu_info']['rtu_sn_number'];
	$gateway_description = $data['rtu_info']['gateway_description'];
	
	$rtu_physical_location = $data['rtu_info']['rtu_physical_location'];

	$created_by = $data['rtu_info']['created_by'];
	$modified_by = $data['rtu_info']['modified_by'];
	$date_created = $data['rtu_info']['date_created'];
	$date_modified = $data['rtu_info']['date_modified'];

	?>
    
 <div class="col-md-12">
 <div class="row">
 <div class="col-md-2">
 </div>
 <div class="col-md-8"> 
 <div class="panel panel-default">

 <div class="panel-body"> <div id="message_all"></div>
 <div align="right"></div>

 <div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Gateway Properties</div>

 <?php echo form_open('admin/rtu_management/', 'class="form-control-overflow"', 'name="user_form"'); ?>
 
    <div class="row">
	<div class="col-md-3">
	&nbsp;
	</div>
    <div class="col-md-6">
	<label>Gateway Serial No.</label>
	<input type="text" class="form-control" id="rtu_sn_number" name="rtu_sn_number" value="<?=$rtu_sn_number;?>" readonly="readonly">
	<div id="rtu_sn_number_message"></div>
	</div>
	<div class="col-md-3">
	</div>
	</div>			 
	
    <div class="row">
    <div class="col-md-3">
    &nbsp;
    </div>
    <div class="col-md-6">
    <label>Gateway Description</label>
    <input type="text"class="form-control" id="gateway_description" name="gateway_description" tabindex="1" value="<?=$gateway_description;?>" >
    <div id="gateway_description_message"></div>
     </div>
    <div class="col-md-3">
    &nbsp;
     </div>
    </div>

    <div class="row">
    <div class="col-md-3">
    &nbsp;
    </div>
    <div class="col-md-6">
    <label>Physical Location</label>
    <input type="text"class="form-control" id="rtu_physical_location" name="rtu_physical_location" value="<?=$rtu_physical_location;?>" readonly="readonly">
    <div id="rtu_physical_location_message"></div>
    </div>
    <div class="col-md-3">
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
		//$site_id = $rtu_site_id;
		echo $edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'>
		<button type='submit' name='submit' id='add_edit_rtu' class='btn btn-primary' value='$rtu_id' onClick='edit_rtu(event)' tabindex='2'>Update</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_rtu_field(event)'tabindex='3' >Clear</button></div>
		<div class='btn-group'><button type='button' name='close_btn' id='close_btn' class='btn btn-primary' onclick='myFunction()'>Close</button tabindex='4'></div></div>";
	?>
	</div>
	</div>
	<div class="col-md-2">
	</div>
	</div>
	<hr class="user-orange-hr">
	<div id="item_info">
    <div class="row">	
    <div class="col-md-2">
    &nbsp;
    </div>
    <div class="col-md-4">
    <label>Created By:</label>
    <?=$created_by;?>
    </div> 
    <div class="col-md-4">
    <label>Modified By:</label>
    <?=$modified_by;?>
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
    <label>Date Created:</label>
    <?=$date_created;?>
    </div> 
    <div class="col-md-4">
    <label>Date Modified:</label>
    <?=$date_modified;?>
    </div>
        
    <div class="col-md-2">
    &nbsp;
    </div>
	</div>
    </div>
	
	</div>

	</form>
	</div>
	<?php
	
	}
	else{
	}
	
	}
	
	public function clear_rtu_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='add_edit_rtu' id='add_edit_rtu' class='btn btn-primary' value='' onClick='add_rtu(event)'>Save</button></div><div class='btn-group'><button type='button' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_rtu_field(event)'>Clear</button></div><div class='btn-group'><button type='button' name='close_btn' id='close_btn' class='btn btn-primary' onclick='myFunction()'>Close</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function delete_rtu()
	{
	
	$id = $this->input->post('rtu_id');
	$this->site_admin_model->delete_rtu($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='add_edit_rtu' id='add_edit_rtu' class='btn btn-primary' value='' onClick='add_rtu(event)'>Save</button></div><div class='btn-group'><button type='button' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_rtu_field(event)'>Clear</button></div><div class='btn-group'><button type='button' name='close_btn' id='close_btn' class='btn btn-primary' onclick='myFunction()'>Close</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function rtu_management_add_post()
	{
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');

	$this->form_validation->set_rules('rtu_sn_number', 'RTU Serial Number', 'required');
	$this->form_validation->set_rules('phone_no_or_ip_address', 'SIM No/IP Address', 'required');
	$this->form_validation->set_rules('connection_type', 'Connection Type');
	$this->form_validation->set_rules('mac_addr', 'MAC Address', 'required');
	$this->form_validation->set_rules('rtu_physical_location', 'Physical Location');
	$this->form_validation->set_rules('location_id', 'Site', 'required');
	
	$this->form_validation->set_rules('idf_number', 'IDF Number', 'required');
	$this->form_validation->set_rules('idf_port', 'IDF Port', 'required');
	
	if ($this->form_validation->run() === FALSE)
	{
	 
	$rtuid = '';
	if(form_error('rtu_sn_number')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_sn_number') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }	
	if(form_error('phone_no_or_ip_address')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('phone_no_or_ip_address') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('connection_type')!=''){ $c = "<div style='color:red; display:inline-flex;'>" . form_error('connection_type') . "</div>"; } else{ $c = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('mac_addr')!=''){ $d = "<div style='color:red; display:inline-flex;'>" . form_error('mac_addr') . "</div>"; } else{ $d = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('rtu_physical_location')!=''){ $e = "<div style='color:red; display:inline-flex;'>" . form_error('rtu_physical_location') . "$$$$</div>"; } else{ $e = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('location_id')!=''){ $f = "<div style='color:red; display:inline-flex;'>" . form_error('location_id') . "</div>"; } else{ $f = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }

	if(form_error('idf_number')!=''){ $g = "<div style='color:red; display:inline-flex;'>" . form_error('idf_number') . "</div>"; } else{ $g = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('idf_port')!=''){ $h = "<div style='color:red; display:inline-flex;'>" . form_error('idf_port') . "</div>"; } else{ $h = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }

	$i = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	
	}
	else
	{
	$this->site_admin_model->rtu_management_action();	
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function rtu_management_edit_post()
	{
			
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	
	$this->load->helper('form');

	$this->site_admin_model->rtu_management_action();
	
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	public function rtu_disable_update()
	{

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
	$rtuid = $this->input->post('rtu_id');

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

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');

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
	
	public function rtu_location_disable_update()
	{

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');

	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 0;
	$f = $this->site_admin_model->rtu_location_set_update_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	public function rtu_location_enable_update()
	{

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');

	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 1;
	$f = $this->site_admin_model->rtu_location_set_update_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	/*REMOTE SSH*/
	public function rtu_remote_ssh_disable()
	{

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');

	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 0;
	$f = $this->site_admin_model->rtu_remote_ssh_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	public function rtu_remote_ssh_enable()
	{
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');

	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 1;
	$f = $this->site_admin_model->rtu_remote_ssh_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	/*FORCE LOAD PROFILE*/
	public function rtu_force_load_profile_disable()
	{

	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){

	$this->load->helper('form');
	$this->load->library('form_validation');
	$rtuid = $this->input->post('rtu_id');

	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 0;
	$f = $this->site_admin_model->rtu_force_load_profile_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
	
	public function rtu_force_load_profile_enable()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
		
	$rtuid = $this->input->post('rtu_id');
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	if($rtuid==0 || $rtuid=='' || $rtuid=='NULL'){
	$a = "<div class='message_style'><div class='message_style_content'><strong>***ID Empty***</strong></div></div>";	
	echo  $rtuid . "\t" .$a . "\n";
	}
	else
	{
	$enable_update = 1;
	$f = $this->site_admin_model->rtu_force_load_profile_action($enable_update);
	} 
	}
	else{
	redirect('Login/invalid');  
	}
	
	}
		
	/*Meter Model MAnagement*/
	public function meter_model_management_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');

	$this->form_validation->set_rules('meter_model', 'MEter Model', 'required');
	$this->form_validation->set_rules('config_file', 'Configuration File', 'required');
	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	if ($this->form_validation->run() === FALSE)
	{
	 
	$id = '';
	if(form_error('meter_model')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('meter_model') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	
	if(form_error('config_file')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('config_file') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	 
	$c = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  $id . "\t" .$a . "\t" .$b . "\t" .$c . "\n";
	 
	}
	else
	{
	$f = $this->site_admin_model->meter_model_management_action();
	
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}

	public function clear_meter_model_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='add_edit_meter_model' id='add_edit_meter_model' class='btn btn-primary' value='' onClick='add_meter_model(event)'>Save</button></div><div class='btn-group'><button type='button' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_model_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function select_meter_model()
	{
	
	$id = $this->input->post('meter_model_id');
	
	if($id!=''){
	
	$data['meter_model_info']=$this->site_admin_model->get_selected_meter_model($id);
	$meter_model_id = $data['meter_model_info']['id'];
	$meter_model = $data['meter_model_info']['meter_model'];
	$config_file = $data['meter_model_info']['config_file'];
	
	$created_by = $data['meter_model_info']['created_by'];
	$date_created = $data['meter_model_info']['date_created'];
	$modified_by = $data['meter_model_info']['modified_by'];
	$date_modified = $data['meter_model_info']['date_modified'];
	?>

<div class="row">
<div class="col-md-4">
&nbsp;
 </div>
<div class="col-md-4">
<label>Meter Model</label>
<input type="text"class="form-control" id="meter_model" name="meter_model" value="<?=$meter_model;?>">
<div id="meter_model_message"></div>
 </div>
<div class="col-md-4">
 </div>

</div> 

<div class="row">
<div class="col-md-4">
&nbsp;
</div>
<div class="col-md-4">
<label>Configutaion File</label>
<input type="text"class="form-control" id="config_file" name="config_file" value="<?=$config_file;?>">
<div id="config_file_message"></div>
 </div>
<div class="col-md-4">
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

echo $edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_meter_model' class='btn btn-primary' value='$meter_model_id' onClick='edit_meter_model(event)'>Update</button></div><div class='btn-group'><button type='submit' name='delete_meter_model' id='delete_meter_model' class='btn btn-primary' value='$meter_model_id' onClick='delete_meter_model_info(event)'>Delete</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_model_field(event)'>Clear</button></div></div>";
						?>
					  </div>
		</div>
	<div class="col-md-2">
	</div>
	</div>
	<div id="item_info">
    <div class="row">	
    <div class="col-md-2">
    &nbsp;
    </div>
    <div class="col-md-4">
    <label>Created By:</label>
    <?=$created_by;?>
    </div> 
    <div class="col-md-4">
    <label>Modified By:</label>
    <?=$modified_by;?>
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
    <label>Date Created:</label>
    <?=$date_created;?>
    </div> 
    <div class="col-md-4">
    <label>Date Modified:</label>
    <?=$date_modified;?>
    </div>
        
    <div class="col-md-2">
    &nbsp;
    </div>
	</div>
    </div>	
	</form>
</div>
	
			<?php
	
	}
	else{
	}

	
	}
	
	public function delete_meter_model()
	{
	
	$id = $this->input->post('meter_model_id');
	$this->site_admin_model->delete_meter_model($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='add_edit_meter_model' id='add_edit_meter_model' class='btn btn-primary' value='' onClick='add_meter_model(event)'>Save</button></div><div class='btn-group'><button type='button' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_meter_model_field(event)'>Clear</button></div></div>";
	
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	/*CLIENT MANAGEMENT*/
	public function select_client(){
	
	$client_id = $this->input->post('client_id');
	
	if($client_id!=''){
	//GET USER INFO
	$data['client_info']=$this->site_admin_model->get_selected_client($client_id);
	
	$client_id = $data['client_info']['id'];
	$client_name = $data['client_info']['client_name'];
	$client_account_no = $data['client_info']['client_account_no'];
	
	$created_by = $data['client_info']['created_by'];
	$date_created = $data['client_info']['date_created'];
	$modified_by = $data['client_info']['modified_by'];
	$date_modified = $data['client_info']['date_modified'];
	
	$edit_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_client' class='btn btn-primary' value='$client_id' onClick='edit_client(event)'>Update</button></div><div class='btn-group'><button type='submit' name='delete_client' id='delete_client' class='btn btn-primary' value='$client_id' onClick='delete_client_info(event)'>Delete</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_client_field(event)'>Clear</button></div></div>";
	?>
	<?php echo form_open('admin/client_management/', 'class="form-control-overflow"', 'name="user_form"'); ?>
<div id="message_all"></div>
<div id="test"></div>
<div class="row">
<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-8">
<label>Client Name</label>
<input type="text"class="form-control" id="client_name" name="client_name" value="<?=$client_name;?>" >
<div id="client_name_message"></div>
</div>
<div class="col-md-2">
&nbsp;
</div>
</div><div class="row">
<div class="col-md-2">
&nbsp;
</div>
<div class="col-md-8">
<label>Client Account No.</label>
<input type="text"class="form-control" id="client_account_no" name="client_account_no" value="<?=$client_account_no;?>">
<div id="client_account_no_message"></div>
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
 <?=$edit_button;?>
</div>
</div>
<div class="col-md-2">
</div>

</div>
<hr class="user-orange-hr">
	<div id="item_info">
    <div class="row">	
    <div class="col-md-2">
    &nbsp;
    </div>
    <div class="col-md-4">
    <label>Created By:</label>
    <?=$created_by;?>
    </div> 
    <div class="col-md-4">
    <label>Modified By:</label>
    <?=$modified_by;?>
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
    <label>Date Created:</label>
    <?=$date_created;?>
    </div> 
    <div class="col-md-4">
    <label>Date Modified:</label>
    <?=$date_modified;?>
    </div>
        
    <div class="col-md-2">
    &nbsp;
    </div>
	</div>
    </div>
    <div class="col-md-2">
    &nbsp;
    </div>
    <div class="col-md-4">
    <label>Date Created:</label>
    <?=$date_created;?>
    </div> 
    <div class="col-md-4">
    <label>Date Modified:</label>
    <?=$date_modified;?>
    </div>
        
    <div class="col-md-2">
    &nbsp;
    </div>
	</div>
</form>
<?php
		
	}
	else{
	}
	
	}
	
	public function client_management_add_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('client_name', 'Client Name', 'required');
	$this->form_validation->set_rules('client_account_no', 'Client Account No.', 'required');	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	if ($this->form_validation->run() === FALSE)
	{
	$userid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('client_account_no')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('client_account_no') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  @$userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}
	else
	{
	$a = $this->site_admin_model->client_management_action();
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function client_management_edit_post()
	{
	$this->load->helper('form');
	$this->load->library('form_validation');
	
	$this->form_validation->set_rules('client_name', 'Client Name', 'required');
	$this->form_validation->set_rules('client_account_no', 'Client Account No.', 'required');	
	if($this->session->userdata('currently_logged_in_as')=='Supervisor' || $this->session->userdata('currently_logged_in_as')=='Manager' || $this->session->userdata('currently_logged_in_as')=='Officer'){
	if ($this->form_validation->run() === FALSE)
	{
	$userid = '';
	if(form_error('client_name')!=''){ $a = "<div style='color:red; display:inline-flex;'>" . form_error('client_name') . "</div>"; } else{ $a = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	if(form_error('client_account_no')!=''){ $b = "<div style='color:red; display:inline-flex;'>" . form_error('client_account_no') . "</div>"; } else{ $b = "<div style='color:green; display:inline-flex;'>&#10004;</div>"; }
	$h = "<div class='message_style'><div class='message_style_content'><strong>***Required Field***</strong></div></div>";
	echo  @$userid . "\t" . $a . "\t" . $b . "\t" . $h . "\n";
	}
	else
	{
	$a = $this->site_admin_model->client_management_action();
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function delete_client()
	{
	
	$id = $this->input->post('client_id');
	$this->site_admin_model->delete_client($id);
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_client' class='btn btn-primary' value='' onClick='add_client(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_client_field(event)'>Clear</button></div></div>";
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Deleted***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	public function clear_client_action()
	{
	
	$default_button = "<div class='btn-group btn-group-justified'><div class='btn-group'><button type='submit' name='submit' id='add_edit_client' class='btn btn-primary' value='' onClick='add_client(event)'>Save</button></div><div class='btn-group'><button type='submit' name='clear_field' id='clear_field' class='btn btn-primary' onClick='clear_client_field(event)'>Clear</button></div></div>";
	$a = "<div class='message_style'><div class='message_style_content'><strong>***Cleared***</strong></div></div>";
	echo  $default_button . "\t" . $a . "\n";
	
	}
	
	/*METER DUPLICATE CHECK*/
	public function meter_duplicate()
	{

	$meter_id = $this->input->get('meter_id');
	
	$ids = $this->input->get('ids');
	
	$meter_info = $this->site_admin_model->get_meter_info($meter_id);
	
	$id = $meter_info['id'];
	
	$meter_name = $meter_info['meter_name'];
	$rtu_sn_number = $meter_info['rtu_sn_number'];
	$meter_site_name = $meter_info['meter_site_name'];
	if($meter_id==$meter_name && $id!=$ids){
		echo "<div style='background-color: ed1c24;color: white;font-weight: bold;font-size: 12px;padding: 5px;margin: 5px;border-radius: 6px;'><center>Duplicate Entry</center><br>Meter ID : $meter_name<br>RTU Number : $rtu_sn_number<br>Site Name : $meter_site_name<br></div>";
	}else{
		echo "";
	}

	}

	/*RTU DUPLICATE CHECK*/
	public function rtu_duplicate()
	{

	$rtu_id = $this->input->get('rtu_id');
	$rtu_sn_number = $this->input->get('rtu_sn_number');
	$mac_addr = $this->input->get('mac_addr');

	$info = $this->site_admin_model->check_rtu_duplicate_info($rtu_id,$rtu_sn_number,$mac_addr);

	$id = $info['id'];

	$rtu_sn_number_q = $info['rtu_sn_number'];
	$mac_addr_q = $info['mac_addr'];
	$rtu_site_name_q = $info['rtu_site_name'];

	if($mac_addr==$mac_addr_q){
		echo "<div style='background-color: #ed1c24;color: white;font-weight: bold;font-size: 12px;padding: 5px;margin: 5px;border-radius: 6px;'><center>Duplicate Entry</center><br>RTU Serial No. : $rtu_sn_number_q<br>MAC address : $mac_addr_q<br>Site Name : $rtu_site_name_q<br></div>";
	}else if($rtu_sn_number==$rtu_sn_number_q){
		echo "<div style='background-color: #ed1c24;color: white;font-weight: bold;font-size: 12px;padding: 5px;margin: 5px;border-radius: 6px;'><center>Duplicate Entry</center><br>RTU Serial No. : $rtu_sn_number_q<br>MAC address : $mac_addr_q<br>Site Name : $rtu_site_name_q<br></div>";
	}else{

	}

	}
	
	/*SITE DUPLICATE CHECK*/
	public function site_duplicate()
	{

	$site_id = $_GET['site_id'];
	$site_name = $_GET['site_name'];
	$site_code = $_GET['site_code'];

	$info = $this->site_admin_model->check_site_duplicate_info($site_id,$site_name,$site_code);

	$id = $info['id'];

	$site_name_q = $info['site_name'];
	$site_code_q = $info['site_code'];
	if($site_name_q == ''){
		
	}else{

	echo "<div style='background-color: #ed1c24;color: white;font-weight: bold;font-size: 12px;padding: 5px;margin: 5px;border-radius: 6px;'><center>Duplicate Entry</center><br>Site Name : $site_name_q<br>Business Entity/Code : $site_code_q<br></div>";

	}
	}
	
	public function meter_info()
	{
	
	$meter_id = $this->input->post('meter_id');
	
	if($meter_id!=''){
	
	$data['meter_info']=$this->site_admin_model->get_selected_meter($meter_id);

	$client_name = $data['meter_info']['customer_name'];
	$measuring_point = $data['meter_info']['measuring_point'];

	$created_by = $data['meter_info']['created_by'];
	$date_created = $data['meter_info']['date_created'];
	$modified_by = $data['meter_info']['modified_by'];
	$date_modified = $data['meter_info']['date_modified'];

	$meter_site_id = $this->input->post('site_id');

	$rtu_sn_number_id = $data['meter_info']['rtu_sn_number_id'];
	$rtu_sn_number = $data['meter_info']['rtu_sn_number'];
	$meter_name = $data['meter_info']['meter_name'];
	$meter_model = $data['meter_info']['meter_model'];
	$meter_model_id = $data['meter_info']['meter_model_id'];
	$meter_config_file = $data['meter_info']['meter_config_file'];
	$meter_type = $data['meter_info']['meter_type'];
	$meter_multiplier = $data['meter_info']['meter_multiplier'];
	$meter_status = $data['meter_info']['meter_status'];
	$meter_role = $data['meter_info']['meter_role'];
	$meter_name_addressable = $data['meter_info']['meter_name_addressable'];
	$meter_default_name = $data['meter_info']['meter_default_name'];
	$physical_location = $data['meter_info']['physical_location'];
	$meter_list_src = $data['meter_info']['meter_list_src'];
	$siteid = $meter_site_id;

	?>
    <div class="col-md-2">
        
    </div>
    <div class="col-md-8">
    <div class="panel panel-default" style="overflow:hidden; padding:10px">
    
	<div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Meter Information</div>
    
	<?php

		$company_no = $data['meter_info']['company_no'];
		$building = $data['meter_info']['building'];
		$land = $data['meter_info']['land'];
		$meter_site_name = $data['meter_info']['meter_site_name'];

		$rental_object = $data['meter_info']['rental_object'];
		$rental_object_name = $data['meter_info']['rental_object_name'];
		$usage_type = $data['meter_info']['usage_type'];
		$ro_valid_from = $data['meter_info']['ro_valid_from'];

		$ro_valid_to = $data['meter_info']['ro_valid_to'];
		$contract_number = $data['meter_info']['contract_number'];
		$customer_name = $data['meter_info']['customer_name'];
		$meter_characteristic = $data['meter_info']['meter_characteristic'];
	
		$measuring_point = $data['meter_info']['measuring_point'];
		$measuring_point_description = $data['meter_info']['measuring_point_description'];
		$measurement_sequence = $data['meter_info']['measurement_sequence'];
		$measurement_separator = $data['meter_info']['measurement_separator'];

		$meter_reading_date = $data['meter_info']['meter_reading_date'];
		$measurement_separator = $data['meter_info']['measurement_separator'];
		$participation_group = $data['meter_info']['participation_group'];
		
		$creation_date = $data['meter_info']['creation_date'];
		$CREATION_BY = $data['meter_info']['creation_by'];
		$last_change_on = $data['meter_info']['last_change_on'];
		$last_change_by = $data['meter_info']['last_change_by'];
	
	?>
	<div class="row">    
    <div class="col-md-12">    
    <div align="right">
	<?php
    echo "<br><button type='close' name='close' onclick='myFunction()' class='btn btn-primary' value=''>Close</button>"
    ?>
    </div>
    
    <h4>METER DESCRIPTION : <?=$meter_name;?></h4>
    <h6>GATEWAY SERIAL NUMBER: <?=$rtu_sn_number;?></h6>
    <h6>ALTERNATE ADDRESS : <?=$meter_default_name;?></h6>
    <h6>CONFIGURATION FILE : <?=$meter_config_file;?></h6>
    <h6>LOCATION : <?=$physical_location;?></h6>
    <h6>TYPE : <?=$meter_type;?></h6>
    <h6>ROLE : <?=$meter_role;?></h6>
	
    </div>
    </div>
    <div class="row">    
    <div class="col-md-2">
    </div>
    <div class="col-md-4">
    
    <h6>COMPANY CODE : <?=$company_no;?></h6>
    <h6>BUSINESS ENTITY : <?=$meter_site_name;?></h6>
    <h6>BUILDING : <?=$building;?></h6>
    <h6>LAND : <?=$land;?></h6>
    <h6>RENTAL OBJECT NO : <?=$rental_object;?></h6>
    <h6>RENTAL OBJECT NAME : <?=$rental_object_name;?></h6>
    <h6>USAGE TYPE : <?=$usage_type;?></h6>
    <h6>RO VALID FROM : <?=$ro_valid_from;?></h6>
    <h6>RO VALID TO : <?=$ro_valid_to;?></h6>
    <h6>CONTRACT NUMBER : <?=$contract_number;?></h6>
    <h6>CONTRACT NAME : <?=$customer_name;?></h6>
    </div>
    <div class="col-md-6">
    <h6>METER CHARACTERISTIC : <?=$meter_characteristic;?></h6>
    <h6>MEASPONT : <?=$measuring_point;?></h6>
    <h6>MEASUREMENT SEQUENCE : <?=$measurement_sequence;?></h6>
    <h6>MEASUREMENT SEPARATOR : <?=$measurement_separator;?></h6>
    <h6>METER MULTIPLIER : <?=$meter_multiplier;?></h6>
    <h6>METER STATUS : <?=$meter_status;?></h6>
    <h6>PARTICIPATION GROUP : <?=$participation_group;?></h6>
    <h6>CREATION DATE : <?=$creation_date;?></h6>
    <h6>CREATION BY : <?=$CREATION_BY;?></h6>
    <h6>LAST CHANGE ON : <?=$last_change_on;?></h6>
    <h6>LAST CHANGE BY : <?=$last_change_by;?></h6>    
    </div>
        <div class="col-md-2">
    </div>
    </div>
    
    </div>
    </div>
    <div class="col-md-2">
  
    </div>
    <?php
	}
	else{
	}
	
	}
}