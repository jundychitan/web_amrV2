<nav class="navbar navbar-default navbar-fixed-top">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"> 
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand navbar-brand-style" href="#">
<div align="center">
<img src="<?php echo base_url(); ?>templates/layouts/images/logo-navs.png" alt="" style="width:140px;">
<br />
<!--<b>Primark</b>-->
</div>
</a>

</div>
<div id="navbar" class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="dropdown">
 <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Maintenance<span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/re/site_management">Site Management</a></li>
<li role="separator" class="divider"></li> 
</ul> 
</li>

<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reports<span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/re/meter_logs">Raw Data</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/re/report_kw_demand">KW Demand</a></li>
<li role="separator" class="divider"></li> 
<li><a href="<?php echo base_url(); ?>index.php/re/report_kwh_consumption">KWh Consumption</a></li>
<li role="separator" class="divider"></li> 
<li><a href="<?php echo base_url(); ?>index.php/re/meter_location_report">Location</a></li>
<li role="separator" class="divider"></li> 
<li><a href="<?php echo base_url(); ?>index.php/re/meter_location_sap_report">SAP</a></li>
<li role="separator" class="divider"></li>  
<li><a href="<?php echo base_url(); ?>index.php/re/meter_sap_list" title="Meter List SAP Master List/ Per Physical Location">Meter List</a></li>
<li role="separator" class="divider"></li> 
<li><a href="<?php echo base_url(); ?>index.php/re/meter_abp_report">As Built Plan</a></li>
<li role="separator" class="divider"></li> </ul>
</li>

<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Hi <?=$_SESSION['user_name'];?>!&nbsp;&nbsp;<span style="color:red; font-weight:bold; font-size:18px">|</span>&nbsp;&nbsp;<span class="glyphicon glyphicon-cog"></span>&nbsp;<!--<span class="caret"></span>--></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/re/account_settings">Account Settings</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url()."index.php/Login/logout"; ?>">Logout</a></li>
<li role="separator" class="divider"></li> 
</ul>
</li>

</ul>
</div><!--/.nav-collapse -->
</div>
</nav>
<br />
<br />
<br />
<br /><br />
