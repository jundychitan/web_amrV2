<div class="page-content container">
<div class="row">
<div class="col-md-4 col-md-offset-4">
<div class="login-wrapper">
<div class="box">
<div class="content-wrap">
<img src="<?php echo base_url(); ?>templates/layouts/images/logo-navs.png" alt="" style="width:150px">
<br>
<br>
<?php
echo form_open('Login/login_action');  
echo validation_errors();  
?>
<div class="input-group input-group-md">
  <span class="input-group-addon" id="sizing-addon1"><div class="glyphicon glyphicon-user" ></div></span>
  <input type="text" name="user_name" id="user_name"class="form-control" placeholder="Username" aria-label="Username" aria-describedby="sizing-addon1">
</div>
<br />
<div class="input-group input-group-md">
  <span class="input-group-addon" id="sizing-addon1"><div class="glyphicon glyphicon-lock" ></div></span>
  <input type="password" name="user_password" id="user_password"class="form-control" placeholder="Password" aria-label="Password" aria-describedby="sizing-addon1">
</div>
<br />
<input class="btn btn-primary btn-block" type="submit" name="login_submit" id="login_submit" value="Login">
</form> 
</div>
</div>
</div>
</div>
</div>
</div>