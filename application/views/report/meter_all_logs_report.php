<script type="text/javascript"> 
/* Ajax post */
$(document).ready(function() {
$("#viewalllogs").click(function(event) {
event.preventDefault();

var interval_type = 'meter_logs_post';
var date_log = $("#date_log").val();
var meter = $("#meter").val();
var location_id = $("#location_id").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/Report_action/"+interval_type,
dataType: 'html',
data: {'date_log': date_log, 'meter_name': meter, 'location_id': location_id },
success : function(result) {
report_result = result;
$('#report_result').html(report_result);
},
beforeSend:function()
{
 $("#report_result").html("<b align='center'>* * * Loading * * *</b>")
}
});
});
});

function selected_location()
{
var location_id = $("#location_id").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/Report_action/select_location_meter_list",
dataType: 'html',
data: {'location_id': location_id },
success : function(result) { 

$('#display_meter_list').html(result);

},
beforeSend:function()
{
 $("#display_meter_list").html("<label>Meter</label><br /><input type='text'class='form-control' id='meter' name='meter' list='meter_list' size='20' placeholder='***Loading***'>")
}
});
}

$(function() { 
  $('input[name="date_log"]').daterangepicker({
    timePicker: true, 
	"timePicker24Hour": true,
	"autoApply": true,
	"opens": "center",
    "drops": "down",
    //startDate: moment().startOf('hour'),
    //endDate: moment().startOf('hour').add(336, 'hour'),
    locale: {
      format: 'YYYY-MM-DD HH:mm:ss'
    }
  });
});
</script>
<div class="col-md-12">
    <div class="row">
    <div class="col-md-12"> 
      <div align="left" style=""><h3><?php echo $title; ?></h3></div>
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
                <?=$back_prev_page;?> 
                </div>
            </div>
            <div class="row">
            <div class="col-md-2"> 
                    
                </div>
                <div class="col-md-2"> 
                
                <div class="form-group">
                            <label>Select Site</label><div id="sub_group_message"></div>
                            <select class="form-control" id="location_id" name="location_id" onchange="selected_location()"/>
                            <option>Please Select Site</option>
                            <?php foreach ($site_list as $site_list_cols):?>
                            <option value="<?php echo "$site_list_cols[site_code]";?>"><?=$site_list_cols['site_code'];?></option>
                            <?php endforeach;?>
                            </select>
                            </div>
                </div>
                                                
                <div class="col-md-2">
                            <div class="form-group">
                            <div id="display_meter_list">
                            <label>Meter</label><br />
                            <input type="text"class="form-control" id="meter" name="meter" list="meter_list" size="20" placeholder=""  autocomplete="off">
                            </div>
                            </div>
                </div>
                 
            
                <div class="col-md-4"> 
                      <div class="form-group">
                            <label>Date</label>
                            <input type="text" class="form-control" placeholder="Date Range" name="date_log" id="date_log">
                            </div>      
                </div>
                 <div class="col-md-2"> 
                 <div class="form-group">
                  <label>&nbsp;</label><br />
                 <button class="btn btn-default" type="button" name="viewalllogs" id="viewalllogs" >Submit</button>
                 </div>
                 </div>
                 
                 
            
            </div>    
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
        <div class="row">
       <!-- <div class="col-md-2"></div><div class="col-md-8">-->
       
        <div id="report_result"></div>
    
        <!--</div><div class="col-md-2"></div>-->
    
        </div>
        
        </div>   
        </div>


</div>
