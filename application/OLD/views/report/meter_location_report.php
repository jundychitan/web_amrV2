<script type="text/javascript"> 
// Ajax post

function showResult() {
event.preventDefault();	
var date_log = $("#date_log").val();
//var date_end = $("#date_end").val();
var meter = $("#meter").val();
var meter_role = $("#meter_role").val();

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("report_result").innerHTML = xmlhttp.responseText;
            }else{
				document.getElementById("report_result").innerHTML = "<div align='center'><b>* * * Loading * * *</b></div>";
			}
        };
        xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/Report_action/meter_location_report_post?meter_name="+meter+"&meter_role="+meter_role+"&date_log="+date_log,true);
        xmlhttp.send();

}

$(function() { 
  $('input[name="date_log"]').daterangepicker({
   // timePicker: true, 
	//"timePicker24Hour": true,
	"autoApply": true,
	"opens": "center",
    "drops": "down",
    //startDate: moment().startOf('hour'),
    //endDate: moment().startOf('hour').add(336, 'hour'),
    locale: {
      format: 'YYYY-MM-DD'
    }
  });
});
</script>
<style>
table {
  text-align: left;
  position: relative;
  border-collapse: collapse; 
}

tr.report th {
  color: white;
  padding: 0.1rem;
  background: #428bca;
  position: sticky;
  top: 0;
  color: #FFF;
}
</style>

<div class="col-md-12">
<div class="row">
<div class="col-md-12">

      <div align="left" style=""><h3><?php echo $title; ?></h3></div>
<!--<div class="panel-body panel-body-custom">-->

<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<?=$back_prev_page;?> 
<!--
[ <a href="<!?php echo base_url(); ?>index.php/admin/meter_logs" class="">Interval Report</a> ]
[ <a href="<!?php echo base_url(); ?>index.php/admin/report_demand_consumption" class="">Demand/Consumption</a> ]
[ <a href="<!?php echo base_url(); ?>index.php/admin/meter_location_report" class="">Location Report</a> ]
[ <a href="<!?php echo base_url(); ?>index.php/admin/meter_location_sap_report" class="">SAP</a> ]
-->
</div>


    <div class="row">
         <div class="col-md-2"> 
         </div>
        <div class="col-md-2"> 
            <div class="form-group">
            <label>Location</label><br />
            <input type="text"class="form-control" id="meter" name="meter" list="meter_list" size=""  autocomplete="off">
            <datalist id="meter_list">
            <?php
            foreach ($meter_name as $meter_col): 
            $meter_group = $meter_col['business_entity'];
            ?>
            <option value="<?php echo "$meter_group";?>"><?php echo "$meter_group"; ?></option>
            <?php
            endforeach;
            ?>
            </datalist>
            </div>
        </div>
                                        
        <div class="col-md-2">
                <label>Meter Role</label>
                <select class="form-control" id="meter_role" name="meter_role" >
                <option value="Client Meter" selected="selected">Client Meter</option>
                <option value="CUSA">CUSA</option>
                <option value="Check Meter">Check Meter</option>
                <option value="Spare Meter">Spare Meter</option>
                </select>
                <div id="meter_role_message"></div>
        </div>
    
        <div class="col-md-4"> 
                    <label>Date</label>
                    <div class="input-group">
                    <input type="text" class="form-control" placeholder="Date Range" name="date_log" id="date_log">
                    <span class="input-group-btn">
                    <button class="btn btn-default" type="button" name="create_report_btn" id="create_report_btn" onclick="showResult();">Submit</button>
                    </span>
                    </div>
        </div>
         <div class="col-md-2"> 
         </div>
    
    </div>  

	<div class="row">
    <div class="col-md-12">
    <div class="container">
    <div id="report_result"></div>
    </div>
    </div>
    </div>

</div>
</div>
</div>
