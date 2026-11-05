<?php

	if($_POST['location']=='103001'){
		include('connection_robinsons.php');
	}
	
	else{
		include('connection.php');	
	}
	
    $save_to_meter_data= mysqli_real_escape_string($conn,$_POST['save_to_meter_data']);
    $meter_id= mysqli_real_escape_string($conn,$_POST['meter_id']);
    
	$location= mysqli_real_escape_string($conn,$_POST['location']);
	
	$_datetime= mysqli_real_escape_string($conn,$_POST['datetime']);
	$datetime = str_replace('%20',' ',$_datetime);
	$vrms_a= mysqli_real_escape_string($conn,$_POST['vrms_a']);
    $vrms_b= mysqli_real_escape_string($conn,$_POST['vrms_b']);
    $vrms_c= mysqli_real_escape_string($conn,$_POST['vrms_c']);

    $irms_a= mysqli_real_escape_string($conn,$_POST['irms_a']);
    $irms_b= mysqli_real_escape_string($conn,$_POST['irms_b']);
    $irms_c= mysqli_real_escape_string($conn,$_POST['irms_c']);

    $freq= mysqli_real_escape_string($conn,$_POST['freq']);
    $pf= mysqli_real_escape_string($conn,$_POST['pf']);
    $watt= mysqli_real_escape_string($conn,$_POST['watt']);

    $va= mysqli_real_escape_string($conn,$_POST['va']);
    $var= mysqli_real_escape_string($conn,$_POST['var']);
	
    $wh_del= mysqli_real_escape_string($conn,$_POST['wh_del']);
    $wh_rec= mysqli_real_escape_string($conn,$_POST['wh_rec']);
    $wh_net= mysqli_real_escape_string($conn,$_POST['wh_net']);
    $wh_total= mysqli_real_escape_string($conn,$_POST['wh_total']);

    $varh_neg= mysqli_real_escape_string($conn,$_POST['varh_neg']);
    $varh_pos= mysqli_real_escape_string($conn,$_POST['varh_pos']);
    $varh_net= mysqli_real_escape_string($conn,$_POST['varh_net']);
    $varh_total= mysqli_real_escape_string($conn,$_POST['varh_total']);
    $vah_total= mysqli_real_escape_string($conn,$_POST['vah_total']);

    @$max_rec_kw_dmd= mysqli_real_escape_string($conn,$_POST['max_rec_kw_dmd'])+0;
    @$max_rec_kw_dmd_time= mysqli_real_escape_string($conn,$_POST['max_rec_kw_dmd_time']);
    @$max_del_kw_dmd= mysqli_real_escape_string($conn,$_POST['max_del_kw_dmd'])+0;
    @$max_del_kw_dmd_time= mysqli_real_escape_string($conn,$_POST['max_del_kw_dmd_time']);

    @$max_pos_kvar_dmd= mysqli_real_escape_string($conn,$_POST['max_pos_kvar_dmd'])+0;
    @$max_pos_kvar_dmd_time= mysqli_real_escape_string($conn,$_POST['max_pos_kvar_dmd_time']);
    @$max_neg_kvar_dmd= mysqli_real_escape_string($conn,$_POST['max_neg_kvar_dmd'])+0;
    @$max_neg_kvar_dmd_time= mysqli_real_escape_string($conn,$_POST['max_neg_kvar_dmd_time']);

	/*ADDITIONAL FOR SFELAPCO*/
    @$v_ph_angle_a= mysqli_real_escape_string($conn,$_POST['v_ph_angle_a'])+0;
    @$v_ph_angle_b= mysqli_real_escape_string($conn,$_POST['v_ph_angle_b'])+0;
    @$v_ph_angle_c= mysqli_real_escape_string($conn,$_POST['v_ph_angle_c'])+0;

    @$i_ph_angle_a= mysqli_real_escape_string($conn,$_POST['i_ph_angle_a'])+0;
    @$i_ph_angle_b= mysqli_real_escape_string($conn,$_POST['i_ph_angle_b'])+0;
    @$i_ph_angle_c= mysqli_real_escape_string($conn,$_POST['i_ph_angle_c'])+0;

	@$relay_status= mysqli_real_escape_string($conn,$_POST['relay_status'])+0;

	@$mac_addr= mysqli_real_escape_string($conn,$_POST['mac_address']);
	@$soft_rev= mysqli_real_escape_string($conn,$_POST['soft_rev']);

	$sql_check_meter_id = "SELECT meter_id FROM live_meter_data WHERE meter_id = $meter_id";
	$result_check_meter_id = mysqli_query($conn, $sql_check_meter_id);							
	
	if($save_to_meter_data ==1){

		$sql = "INSERT IGNORE INTO meter_data 
                            (location,
							meter_id,
							datetime,
                            vrms_a,
							vrms_b,
							vrms_c,
                            irms_a,
							irms_b,
							irms_c,
                            freq,
							pf,
							watt,
							va,
							var,
                            wh_del,
							wh_rec,
							wh_net,
							wh_total,
                            varh_neg,
							varh_pos,
							varh_net,
							varh_total,
							vah_total,
							max_rec_kw_dmd,
							max_rec_kw_dmd_time,
							max_del_kw_dmd,
							max_del_kw_dmd_time,
						 	max_pos_kvar_dmd,
							max_pos_kvar_dmd_time,
							max_neg_kvar_dmd,
							max_neg_kvar_dmd_time,
						 	v_ph_angle_a,
							v_ph_angle_b,
							v_ph_angle_c,
						 	i_ph_angle_a,
							i_ph_angle_b,
							i_ph_angle_c,
						 	mac_addr,
							soft_rev,
							relay_status)
                 VALUES
                           ('$location',
						   '$meter_id',
						   '$datetime',
                           '$vrms_a',
						   '$vrms_b',
						   '$vrms_c',
						   '$irms_a',
						   '$irms_b',
						   '$irms_c',
						   '$freq',
						   '$pf',
						   '$watt',
						   '$va',
						   '$var',
						   '$wh_del',
						   '$wh_rec',
						   '$wh_net',
						   '$wh_total',
						   '$varh_neg',
						   '$varh_pos',
						   '$varh_net',
						   '$varh_total',
						   '$vah_total',
						   '$max_rec_kw_dmd',
						   '$max_rec_kw_dmd_time',
						   '$max_del_kw_dmd',
						   '$max_del_kw_dmd_time',
						   '$max_pos_kvar_dmd',
						   '$max_pos_kvar_dmd_time',
						   '$max_neg_kvar_dmd',
						   '$max_neg_kvar_dmd_time',
						   '$v_ph_angle_a',
						   '$v_ph_angle_b',
						   '$v_ph_angle_c',
	  					   '$i_ph_angle_a',
						   '$i_ph_angle_b',
						   '$i_ph_angle_c',
						   '$mac_addr',
						   '$soft_rev',
					       '$relay_status'
)";	

						if (mysqli_query($conn, $sql)) {
							//echo "INSERT";
						} else {
						//echo "Error: " . $sql . "<br>" . mysqli_error($conn);		
						}
						
						/*UPDATE RTU DETAILS*/
						
						if($location!='103001'){
							
							$sql_rtu_details_update = "UPDATE meter_rtu
								   SET last_log_update = '$datetime',
								   soft_rev = '$soft_rev'
								WHERE `rtu_site_name` = '$location' and 
								mac_addr = '$mac_addr'";
						
							/*UPDATE METER DETAILS*/
							$sql_meter_details_update = "UPDATE meter_details
							   SET last_log_update = '$datetime' 
							   WHERE `meter_site_name` = '$location' and 
							   meter_name = '$meter_id' ";
						
						}else{
							
							$sql_rtu_details_update = "UPDATE meter_rtu
								   SET last_log_update = '$datetime',
								   soft_rev = '$soft_rev'
								WHERE `site_code` = '$location' and 
								gateway_mac = '$mac_addr'";	
								
							/*UPDATE METER DETAILS*/
							$sql_meter_details_update = "UPDATE meter_details
							   SET last_log_update = '$datetime' 
							   WHERE `site_code` = '$location' and 
							   meter_name = '$meter_id' ";	
								
						}		
						
						mysqli_query($conn, $sql_rtu_details_update);
						
						if (mysqli_query($conn, $sql_rtu_details_update)) {
						} else {
						echo "Error: " . $sql_rtu_details_update . "<br>" . mysqli_error($conn);
						}
							
						mysqli_query($conn, $sql_meter_details_update);
						
						if (mysqli_query($conn, $sql_meter_details_update)) {
						} else {
						echo "Error: " . $sql_meter_details_update . "<br>" . mysqli_error($conn);
						}
						
						if($location == 'LIMA_ENERZONE'){

								/*Disabled April 23, 2024*/
								/*include('/home/powerledger/post_reading.php');*/
								
						}
						 
	} else {

	//$sql_check_meter_id = "SELECT meter_id FROM live_meter_data WHERE meter_id = $meter_id";
	//$result_check_meter_id = mysqli_query($conn, $sql_check_meter_id);							
	
	if (mysqli_num_rows($result_check_meter_id)<1) {
		
    		$sql = "INSERT IGNORE INTO live_meter_data 
                                (location,meter_id,datetime,
                                vrms_a,vrms_b,vrms_c,
                                irms_a,irms_b,irms_c,
                                freq ,pf,watt,va,var,
                                wh_del,wh_rec,wh_net,wh_total,
                                varh_neg,varh_pos,varh_net,varh_total,vah_total,
								max_rec_kw_dmd, max_rec_kw_dmd_time, max_del_kw_dmd, max_del_kw_dmd_time,
 								max_pos_kvar_dmd, max_pos_kvar_dmd_time, max_neg_kvar_dmd, max_neg_kvar_dmd_time,
								 v_ph_angle_a,v_ph_angle_b,v_ph_angle_c,
								 i_ph_angle_a,i_ph_angle_b,i_ph_angle_c,
								mac_addr,soft_rev, relay_status
)
                     VALUES
                               ('$location','$meter_id','$datetime'
                               ,'$vrms_a','$vrms_b','$vrms_c'
                               ,'$irms_a','$irms_b','$irms_c'
                               ,'$freq','$pf','$watt','$va' ,'$var',
							   '$wh_del','$wh_rec','$wh_net','$wh_total'
                               ,'$varh_neg','$varh_pos','$varh_net','$varh_total','$vah_total',
							   '$max_rec_kw_dmd', '$max_rec_kw_dmd_time', '$max_del_kw_dmd', '$max_del_kw_dmd_time',
 								'$max_pos_kvar_dmd', '$max_pos_kvar_dmd_time', '$max_neg_kvar_dmd', '$max_neg_kvar_dmd_time',
							 '$v_ph_angle_a','$v_ph_angle_b','$v_ph_angle_c',
							 '$i_ph_angle_a','$i_ph_angle_b','$i_ph_angle_c',
								 '$mac_addr', '$soft_rev',
								 '$relay_status'
)";	
		
						if (mysqli_query($conn, $sql)) {
							//echo "INSERT";
						} else {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
						}

        } else {
				
            $sql = "UPDATE IGNORE live_meter_data
                       SET location = '$location',meter_id = '$meter_id',datetime = '$datetime'
                          ,vrms_a = '$vrms_a',vrms_b = '$vrms_b',vrms_c = '$vrms_c'
						  ,irms_a = '$irms_a',irms_b = '$irms_b',irms_c = '$irms_c'
                          ,freq = '$freq',pf = '$pf',watt = '$watt'
                          ,va = '$va',var = '$var',wh_del = '$wh_del'
                          ,wh_rec = '$wh_rec' ,wh_net = '$wh_net'
                          ,wh_total = '$wh_total',varh_neg = '$varh_neg'
                          ,varh_pos = '$varh_pos',varh_net = '$varh_net'
                          ,varh_total = '$varh_total',vah_total = '$vah_total',
						  max_rec_kw_dmd = '$max_rec_kw_dmd', max_rec_kw_dmd_time = '$max_rec_kw_dmd',
						   max_del_kw_dmd = '$max_del_kw_dmd', max_del_kw_dmd_time = '$max_del_kw_dmd_time',
							max_pos_kvar_dmd = '$max_pos_kvar_dmd', max_pos_kvar_dmd_time = '$max_pos_kvar_dmd_time', 
							max_neg_kvar_dmd = '$max_neg_kvar_dmd', max_neg_kvar_dmd_time = '$max_neg_kvar_dmd_time',										 						 
							v_ph_angle_a = '$v_ph_angle_a',v_ph_angle_b = '$v_ph_angle_b',v_ph_angle_c = '$v_ph_angle_c',
						 i_ph_angle_a = '$i_ph_angle_a',i_ph_angle_b = '$i_ph_angle_b',i_ph_angle_c = '$i_ph_angle_c',

							mac_addr = '$mac_addr', soft_rev = '$soft_rev',
							relay_status = '$relay_status'
                    WHERE meter_id='$meter_id'";

				if (mysqli_query($conn, $sql)) {
				//echo "UPDATE";
				} else {
						echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}

        }
    }

				/*UPDATE LOCATION DETAILS*/
		        $sql_location_details_update = "UPDATE meter_site
                       SET last_log_update = '$datetime' 
                    WHERE site_code = '$location'";

				if (mysqli_query($conn, $sql_location_details_update)) {
				/*echo "UPDATE";*/
				} else {
				echo "Error: " . $sql_location_details_update . "<br>" . mysqli_error($conn);
				}

	//RETURN DATETIME
    $server_time=date('Y-m-d H:i:s');
    echo "OK, $server_time";
	

	
?>