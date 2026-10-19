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
<img src="<?php echo base_url(); ?>templates/layouts/images/logo.png" alt="" width="55%">
<br />
<!--<b>Primark</b>-->
</div>
</a> 

</div>
<div id="navbar" class="navbar-collapse collapse">
<ul class="nav navbar-nav">

<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reports<span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="<?php echo base_url(); ?>index.php/client/meter_logs">Meter Logs</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/client/meter_hourly_report">Hourly</a></li>
<li role="separator" class="divider"></li> 

<li><a href="<?php echo base_url(); ?>index.php/client/meter_daily_report">Daily</a></li>
<li role="separator" class="divider"></li> 

<!--<li><a href="<?php// echo base_url(); ?>index.php/client/report_with_chart">Report with Chart</a></li>
<li role="separator" class="divider"></li> -->
</ul>
</li>
<li><a href="<?php echo base_url()."index.php/Login/logout"; ?>">Logout</a></li>
</ul>
</div><!--/.nav-collapse -->
</div>
</nav>
<br /><br /><br /><br /><br />