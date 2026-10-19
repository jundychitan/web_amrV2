
<body>
	<div id="canvas-holder" style="width:40%">
		<canvas id="chart-area"></canvas>
	</div>
	<button id="randomizeData">Randomize Data</button>
	<button id="addDataset">Add Dataset</button>
	<button id="removeDataset">Remove Dataset</button>
	<script>
				
	var randomScalingFactor = function() {
			return Math.round(Math.random() * 100);
		};
		<?php
	$arrLabels = array("Red","Yellow","Blue");
	$arrDatasets = array('label' => "Colors",'fillColor' => "rgba(220,220,220,0.2)", 'strokeColor' => "rgba(220,220,220,1)", 'pointColor' => "rgba(220,220,220,1)", 'pointStrokeColor' => "#fff", 'pointHighlightFill' => "#fff", 'pointHighlightStroke' => "rgba(220,220,220,1)", 'data' => array('28', '48', '40'));
	
	$arrReturn = array(array('labels' => $arrLabels, 'datasets' => $arrDatasets));
	
		?>		
	var obj = this.responseText;
			var config = {
			type: 'pie',
			data: {
				 <?php print(json_encode($arrReturn)); ?>
			},
			options: {
				responsive: true
			}
		};

		window.onload = function() {
			var ctx = document.getElementById('chart-area').getContext('2d');
			window.myPie = new Chart(ctx, config);
		};
		
            }
        };

		

	</script>
</body>

</html>
