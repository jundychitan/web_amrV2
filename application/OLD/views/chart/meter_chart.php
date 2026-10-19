<script>

function show_meter_status_chart() {

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
		var obj = this.responseText;
//alert(obj);
		var config = {
			type: 'pie',
			data: { obj },
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

        xmlhttp.open("GET","<?php echo base_url(); ?>" + "index.php/chart/meter_chart_data_1/",true);
        xmlhttp.send();
    }
	window.onload = show_meter_status_chart;


/*
	show_meter_status_chart(); // This will run on page load
	setInterval(function(){
	   show_meter_status_chart() // this will run after every 5 seconds
	}, 1000);*/

</script>
</head>
<body>

<div id="canvas-holder" style="width:40%">
		<canvas id="chart-area"></canvas>
	</div>
    
    <div id="sss"></div>
</body>
</html>                          