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
<img src="<?php echo base_url(); ?>templates/layouts/images/logo-navs.png" alt="" style="width:50px;">
<br />
<b>Dychitan Electronics Corporation</b>
</div>
 
</a>
</div>
<div id="navbar" class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Maintenance<span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/site_admin/site_management">Site Management</a></li>
</ul>
</li>
<li class="dropdown">
 <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reports<span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/site_admin/meter_logs">Raw Data</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/report_kw_demand">KW Demand</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/report_kwh_consumption">KWh Consumption</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/meter_location_report">Location</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/meter_location_sap_report">SAP</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/meter_sap_list">Meter List [SAP]</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/site_admin/meter_abp_report">As Built Plan</a></li>
<li role="separator" class="divider"></li> 

</ul>
</li>
<li class="dropdown">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
    Hi <?=$_SESSION['user_name'];?>!&nbsp;&nbsp;
    <span style="color:red; font-weight:bold; font-size:18px">|</span>&nbsp;&nbsp;
    <span class="glyphicon glyphicon-cog"></span>&nbsp;
    </a>
    	<ul class="dropdown-menu"> 
        <li><a href="<?php echo base_url(); ?>index.php/site_admin/account_settings">Username and Password</a></li>
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
