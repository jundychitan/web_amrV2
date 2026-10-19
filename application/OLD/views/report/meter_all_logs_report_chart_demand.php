<script type="text/javascript"> 
/* Ajax post */
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
 $("#display_meter_list").html("<label>Meter</label><br /><input type='text'class='form-control' id='meter' name='meter' list='meter_list' size='20' placeholder='***Please Wait!***' tabindex='2'>")
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
          <div align="left" style=""><h3><?php echo $title; ?></h3>
          </div>
          <div class="row">
                <div class="col-md-6">
                </div>
                <div class="col-md-6">
                    <div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
                    <?=$back_prev_page;?>
                    </div>
                </div> 
          </div>
          
                <div class="row">
                    <div class="col-md-2"> 
                    
                    <div class="form-group">
                    <label>Select Site</label><div id="sub_group_message"></div>
                    <select class="form-control" id="location_id" name="location_id" onchange="selected_location()" tabindex="1"/>
                    <option>Please Select Site</option>
                    <?php foreach ($site_list as $site_list_cols):?>
                    <option value="<?php echo "$site_list_cols[business_entity]";?>"><?=$site_list_cols['business_entity'];?></option>
                    <?php endforeach;?>
                    </select>
                    </div>
                    </div>
                                                    
                    <div class="col-md-2">
                    <div class="form-group">
                    <div id="display_meter_list">
                    <label>Meter</label><br />
                    <input type="text"class="form-control" id="meter" name="meter" list="meter_list" size="20" placeholder=""  autocomplete="off" tabindex="2">
                    </div>
                    </div>
                    </div>
                    
                    <div class="col-md-2">
                    <div class="form-group">
                    <label>Interval</label>
                    
                    <div id="display_interval_type">
                    <select class="form-control" id="interval_type" name="interval_type" tabindex="3"/>
                    <option value="meter_fifteen_report_post">15 Minute</option>
                    <option value="meter_hourly_demand_report_post">Hourly</option>
                    </select>
                    </div>
                    </div>
                    </div>
                    
                    <div class="col-md-3"> 
                    <div class="form-group">
                    <label>Date</label>
                    <input type="text" class="form-control" placeholder="Date Range" name="date_log" id="date_log" tabindex="4">
                    </div>      
                    </div>
                    
                    <div class="col-md-2">
                    <div class="form-group">
                    <label>Chart Type</label>
                    <select class="form-control" id="chart_type" name="chart_type" tabindex="5"/>
                    <option value="column" selected>Column</option>
                    <option value="spline">Spline</option>
                    </select>
                    </div>
                    </div>
                    
                     <div class="col-md-1"> 
                     <div class="form-group">
                     <label>&nbsp;</label><br />
                     <button class="btn btn-default" type="button" name="viewalllogs" id="viewalllogs" tabindex="6">Submit</button>
                     </div>
                     </div>
                </div>          

        <div class="row">
        
        <div class="col-md-2"></div>
        
        <div class="col-md-8">
        <div align="center"><div id="report_result_loading"></div></div>
        <div id="loadchart"></div>
        </div>
        
        <div class="col-md-2"></div>
        
        </div>
        
        <div class="row">

        <div id="report_result"></div>

        </div>

 
        </div>

		</body>
		<script type="text/javascript" charset="utf-8"> 		
		$(function(){
			$('#viewalllogs').on('click',function(){	
				load_table();
				load_chart();
			})
		});

		function load_table(){
		event.preventDefault();
		
		var interval_type = $('#interval_type').val();
		var date_log = $("#date_log").val();
		var meter = $("#meter").val();
		var report_type = 'kw_demand';
		
		jQuery.ajax({
		type: "POST",
		url: "<?php echo base_url(); ?>" + "index.php/report_action/"+interval_type,
		dataType: 'html',
		data: {'date_log': date_log,'meter_name': meter },
		success : function(result) {
		report_result = result;
		$("#report_result_loading").html("")
		$('#report_result').html(report_result);
		},
		beforeSend:function()
		{
		 $("#report_result_loading").html("<b align='center'>* * * Loading * * *</b>")
		}
		});
		}
	
		function load_chart(){
			
			var location = $('#meter').val();
			var date_time = $('#date_log').val();
			var interval_type = $('#interval_type').val();
			var chart_type = $("#chart_type").val();
			var chart_title = location + '';
			
			if(interval_type == 'meter_fifteen_report_post'){
			var report_type = "15 minute Block<br>KW Demand";
			}
			else{
			var report_type = "1 hr Block<br>KW Demand";	
			}

			var options = {
				chart: {
					renderTo: 'loadchart',
					type: chart_type,
					 minWidth: 500,
					 maxWidth: 600,
					zoomType: 'xy'
					
				},
				title: {
					text: chart_title,
					x: -20 //center
				},
				subtitle: {
					text: '',
					x: -20
				},
				plotOptions:{
					series: {
						lineWidth:1,
						marker:{radius: 2}
					}
				},
				xAxis: {
					type: 'datetime',
					useUTC: false
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return this.value +' KW';
						},
						style: {
							color: '#000'
						}
					},					
					title: {
						text: report_type,
						style: {
							color: '#000'
						}						
					}
				}
				],
				legend: {
					enabled: false
				},/*
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'top',
					x: -10,
					y: 100,
					borderWidth: 0
				},*/
				series: [					
					{name: report_type,yAxis: 0, color: '#ffa500'}, 
				] 
			}
			// Load data asynchronously using jQuery. On success, add the data
			// to the options and initiate the chart.
			// This data is obtained by exporting a GA custom report to TSV.
			// http://api.jquery.com/jQuery.get/	
				
			jQuery.post("<?php echo base_url(); ?>" + "index.php/Report_chart_action/"+interval_type,{meter_name: location, date: date_time}, function(tsv) {
				var lines = [];
				kwh = [];
				//rain_count =[];			
				try {
					// split the data return into lines and parse them
					tsv = tsv.split(/\n/g);
					jQuery.each(tsv, function(i, line) {
						line = line.split(/\t/);
						date = Date.parse(line[0]);
						kwh.push([date, parseFloat(line[1].replace(',', ''))]);
						charttitle = line[2];
					});
				} catch (e) {  }
				options.series[0].data = kwh;
				Highcharts.setOptions({
					global : {
						useUTC : false
					}
				});				
				chart = new Highcharts.Chart(options);
				chart.setTitle({ text: charttitle });
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

		.daterangepicker{
			z-index:99999 !important;
		}
</style>