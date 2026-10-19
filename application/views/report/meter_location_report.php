<script type="text/javascript"> 
// Ajax post

function showResult() {
event.preventDefault();	
var date_log = $("#date_log").val();
var meter = $("#meter").val();
var meter_role = $("#meter_role").val();

var sap_info = $("#sap_info").val();
var building_list = $("#building_list").val();

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
        xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/Report_action/meter_location_report_post?meter_name="+meter+"&meter_role="+meter_role+"&date_log="+date_log+"&sap_info="+sap_info+"&building_list="+building_list,true);
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

function get_building_list()
{
var location_id = $("#meter").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/Report_action/get_building_list",
dataType: 'html',
data: {'location_id': location_id },
success : function(result) { 

$('#display_building_list').html(result);

},
beforeSend:function()
{
 $("#display_building_list").html("Generating List of Building...")
}
});
}
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
</div>

    <div class="row">
         <div class="col-md-2"> 
         </div>
        <div class="col-md-2"> 
            <div class="form-group">
            <label>Site</label><br />
            <input type="text"class="form-control" id="meter" name="meter" list="meter_list" size=""  autocomplete="off" onchange="get_building_list()">
            <datalist id="meter_list">
            <?php
            foreach ($meter_name as $meter_col): 
            $meter_group = $meter_col['site_code'];
            ?>
            <option value="<?php echo "$meter_group";?>"><?php echo "$meter_group"; ?></option>
            <?php
            endforeach;
            ?>
            </datalist>
            </div>
        </div>
                                        
        <div class="col-md-2">

                    <label>Building</label>
                      <!--Generate Based on Meter List Building Code from SAP--> 
                       <div id="display_building_list">
                       <center><b>Please select Location</b></center>
                       </div>        </div>
    
        <div class="col-md-4"> 
                    <label>Date</label>
                    <input type="text" class="form-control" placeholder="Date Range" name="date_log" id="date_log">
        </div>
         <div class="col-md-2"> 
         </div>
    
    </div>
    
    <!-- FILTER by Include only with meas point and by building(default All)-->
    <div class="row">
         <div class="col-md-2"> 
         </div>
        <div class="col-md-4"> 
            <div class="form-group">
            <label title="this will be filter by valid information like meter has measuring point, Valid RO_VALID_TO, with contract number and with tenant name, meter will not be included if any of these are Invalid">Include only meters with Valid SAP Information?</label>
            <select class="form-control" id="sap_info" name="sap_info" >
                <option value="No" selected="selected">No</option>
                <option value="Yes">Yes</option>
                </select>
            </div>
        </div>
                                        
        <div class="col-md-4"> 
        <div class="form-group">
                <label>Meter Role</label>
                <select class="form-control" id="meter_role" name="meter_role">
                <option value="Client Meter" selected="selected">Client Meter</option>
                <option value="CUSA">CUSA</option>
                <option value="Check Meter">Check Meter</option>
                <option value="Spare Meter">Spare Meter</option>
                </select>
                <div id="meter_role_message"></div>
                </div>
                    </div>
        </div>
        
		 <div class="row">
         <div class="col-md-2"> 
         </div>
        
        <div class="col-md-8"> 
        <br />
     	&nbsp;<button class="btn btn-default" type="button" name="create_report_btn" id="create_report_btn" onclick="showResult();">Submit</button>
        </div>
        <div class="col-md-2"> 
         </div>
        </div>
    
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
