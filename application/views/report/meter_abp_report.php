<script type="text/javascript"> 
// Ajax post

function showResult() {
event.preventDefault();	

var meter = $("#meter").val();

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
        xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/Report_action/meter_abp_report_post?business_entity="+meter,true);
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
         <div class="col-md-4"> 
    </div>

                    <div class="col-md-4"> 
                    <label>Site</label>
                    <div class="input-group">
                    <input type="text"class="form-control" id="meter" name="meter" list="meter_list" size=""  autocomplete="off">
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
                    <span class="input-group-btn">
                    <button class="btn btn-default" type="button" name="create_report_btn" id="create_report_btn" onclick="showResult();">Submit</button>
                    </span>
                    </div>
                    </div>         
         


        </div>         
         
        <div class="col-md-4"> 
           
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
