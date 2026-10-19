<script type="text/javascript"> 
// Ajax post 
function showResult() {
event.preventDefault();	
var business_entity = $("#meter").val();
var list_type = $("#list_type").val();
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
        xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/Report_action/"+list_type+"?business_entity="+business_entity,true);
        xmlhttp.send();
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
         </div>
        <div class="col-md-2"> 
         
			<div class="form-group">
        	<label>Site</label> 
            <input type="text" class="form-control" id="meter" name="meter" list="meter_list" size="" autocomplete="off">
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
                    <div class="form-group">
                    <label>List Type</label>
                    <select class="form-control" id="list_type" name="list_type"/>
                    <option value="meter_location_sap_list_report_post">SAP</option>
                    <option value="meter_list_per_location_report_post" selected>Location</option>
                    </select>
                    </div>
        </div>
       
         <div class="col-md-2"> 
          <div class="form-group">
             <label>&nbsp;</label><br />
            <button class="btn btn-default" type="button" name="create_report_btn" id="create_report_btn" onclick="showResult();">Submit</button>
             </div>
         </div>
    </div>  

	<div class="row">
    <div class="col-md-12">
    <div class="container2">
    <div id="report_result"></div>
    </div>
    </div>
    </div>

</div>
</div>
</div>
