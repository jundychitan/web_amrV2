<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	class Admin_client_action extends CI_Controller {
	
	public function __construct()
	{
	parent::__construct();
	$this->load->model('admin_client_model');
	$this->load->helper('url_helper');
	$this->load->library('session');
	$this->load->helper('html');
	$this->load->helper('date');
	}
	
	
	/*CLIENT MANAGEMENT*/
	public function select_client(){
	
	$client_id = $this->input->post('client_id');
	
	if($client_id!=''){
	//GET USER INFO
	$data['client_info']=$this->admin_client_model->get_selected_client($client_id);
	
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
    </div></form>
<?php
	
	
	//echo  $client_name . "\t" . $client_account_no . "\t" . $edit_button . "\n";
	
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
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
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
	$a = $this->admin_client_model->client_management_action();
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
	if($this->session->userdata('currently_logged_in_as')=='Admin'){
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
	$a = $this->admin_client_model->client_management_action();
	}
	
	}
	else{
	redirect('Login/invalid');  
	}
	}
	
	public function delete_client()
	{
	
	$id = $this->input->post('client_id');
	$this->admin_client_model->delete_client($id);
	
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
	
}