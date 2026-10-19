<?php /*?><script type="text/javascript">
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "admin_datatables/site_list/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

}

<!-- Search for Duplicate -->
function showResult() {

	ids=$("#add_edit_site").val();
	site_name=$("#site_name").val();
	
		$("#check_duplicate").fadeIn("slow");

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("check_duplicate").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","<?php echo base_url();?>index.php/admin_action/site_duplicate?site_id="+ids+"&site_name="+site_name,true);
        xmlhttp.send();

}

function add_site( event )
{
event.preventDefault(); 
var user_id = $("#add_edit_site").val();
var site_code = $("#site_code").val();
var site_name = $("#site_name").val();
var site_cut_off = $("#site_cut_off").val();

jQuery.ajax({

type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/site_management_add_post",
dataType: 'html',
data: {
'site_code': site_code,
'user_id': user_id,
'site_name': site_name, 
'site_cut_off': site_cut_off
},
success : function(result) { 

result_message = result.split(/\t/);

user_id_message =result_message[0];
site_name_message =result_message[1];
site_code_message =result_message[2];
site_cut_off_message =result_message[3];
message_all =result_message[4];

$('#user_id_message').html(user_id_message);
$('#site_code_message').html(site_name_message);
$('#site_name_message').html(site_name_message);
$('#site_cut_off_message').html(site_cut_off_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1800); 
$('#table').DataTable().draw();
}
});
}

function edit_site(event)
{
event.preventDefault();
var site_id = $("#add_edit_site").val();
var site_code = $("#site_code").val();
var site_name = $("#site_name").val();
var site_cut_off = $("#site_cut_off").val();

jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/site_management_edit_post",
dataType: 'html',
data: {
'site_code': site_code,
'site_id': site_id,
'site_name': site_name, 
'site_cut_off': site_cut_off
},
success : function(result) { 
result_message = result.split(/\t/);
user_id_message =result_message[0];
site_name_message =result_message[1];
site_code_message =result_message[2];
site_cut_off_message =result_message[3];
message_all =result_message[4];

$('#user_id_message').html(user_id_message);
$('#site_name_message').html(site_name_message);
$('#site_code_message').html(site_code_message);
$('#site_cut_off_message').html(site_cut_off_message);

$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},2500); 
$('#table').DataTable().draw();

}
});
}

function delete_site_info(event)
{
event.preventDefault();
var site_id = $("#delete_site").val();
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/delete_site",
dataType: 'html',
data: {
'site_id': site_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},1500); 

setTimeout(function() {
$('#form_float').fadeOut('slow');
},1800); 

$('#table').DataTable().draw();
$('#site_name').val('');
$('#site_code').val('');
$('#site_cut_off').val('');

$('#user_id_message').html('');
}
});
}

function clear_site_field(event)
{
event.preventDefault();
var user_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_site_action",
dataType: 'html',
data: {
'user_id': user_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},700); 
$('#site_name').val('');
$('#site_code').val('');
$('#user_id_message').html('');
$('#site_name_message').html('');
$('#site_code_message').html('');
$('#site_cut_off_message').html('');
}
});
}

function clear_site_field(event)
{
event.preventDefault();
var user_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_site_action",
dataType: 'html',
data: {
'user_id': user_id
},
success : function(result) { 
result_message = result.split(/\t/);
edit_button =result_message[0];
message_all =result_message[1];
$('#edit_button').html(edit_button);
$('#message_all').html(message_all).show();
setTimeout(function() {
$('#message_all').fadeOut('slow');
},700); 
$('#site_name').val('');
$('#site_code').val('');
$('#site_cut_off').val('');
$('#user_id_message').html('');
$('#site_name_message').html('');
$('#site_code_message').html('');
$('#site_cut_off_message').html('');
}
});
}

function clear_site_field_form(event)
{
event.preventDefault();
var user_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/clear_site_action",
dataType: 'html',
data: {
'user_id': user_id
},
success : function(result) { 
result_message = result.split(/\t/);

edit_button =result_message[0];
message_all =result_message[1];

$('#site_name').val('');
$('#site_code').val('');
$('#site_cut_off').val('');
$('#edit_button').html(edit_button); 
}

});
}
function selected_site_id(id)
{
var site_id = id;
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/admin_action/select_site",
dataType: 'html',
data: {'site_id': id },
success : function(result) { 
result_col = result.split(/\t/);
 
var x = document.getElementById("form_float");
x.style.display = "block";

$('#edit_form').html(result);

}
});
}

function myFunction() {
var x = document.getElementById("form_float");
if (x.style.display === "none") {
x.style.display = "block";
} else {
x.style.display = "none";
clear_site_field_form(event);
$('#item_info').html(''); 
}
}
</script>
<?php */?>
<div id="form_float" style="display:none;">
                                    <div class="col-md-2">
                                    </div>
                                    <div class="col-md-8">
                                    <div class="panel panel-default" style="overflow:hidden; padding:10px">
                                    <div id="message_all"></div>
                                    <div id="edit_form">
					            <div class="panel-title" style="background-color:#1b755c; color:#FFF; padding:20px; border-radius:7px;">Site</div>
                                   
									<div class="row">
                                    <div class="col-md-12">
                                        <label>Business Entity/Code</label>
                                        <input type="text"class="form-control" id="site_code" name="site_code" onchange="showResult()" onkeydown="showResult()" onkeyup="showResult()">
                                        <div id="site_code_message"></div>
                                        <hr class="user-admin-hr">
								    </div>
								    </div>
                                                                       
                                    <div class="row">
                                    <div class="col-md-12">
                                        <label>Site Name</label>
                                        <input type="text"class="form-control" id="site_name" name="site_name" onchange="showResult()" onkeydown="showResult()" onkeyup="showResult()">
                                        <div id="site_name_message"></div>
                                        <hr class="user-admin-hr">
								    </div>
								    </div>
                                    		
                                    <div class="row">
                                    <div class="col-md-12">
                                        <label>Cut Off</label>
                                        <input type="number"class="form-control" id="site_cut_off" name="site_cut_off" max="31" min="01">
                                        <div id="site_cut_off_message"></div>
                                        <hr class="user-admin-hr">
								    </div>
								    </div>
                                                                                					
                                    <div class="row">

                                    <div class="col-md-12">
                                    	<div id="edit_button">
                                         <div class="btn-group btn-group-justified">
                                         <div class="btn-group">
                                         <button type="submit" name="add_edit_site" id="add_edit_site" class="btn btn-primary" value="" onClick="add_site(event)">Save</button>
                                         </div>
                                         <div class="btn-group">
                                         <button type="button" name="clear_field" id="clear_field" class="btn btn-primary" onClick="clear_site_field(event)">Clear</button>
                                         </div>
                                         <div class="btn-group">
                                         <button type="close" name="close" onclick="myFunction()" class="btn btn-primary" value="">Close</button>	
                                         </div> 
                                         </div>
                                        </div>
                                    </div>		

								</div>
                                
                                <div class="row">
                                
                                <div class="col-md-2">
                                </div>
                                
                                <div class="col-md-8">
                                
                                <div id="check_duplicate">
                                </div>
                                
                                </div>
                                
                                <div class="col-md-2">
                                </div>
                                
                                </div>                                
                                	</div>
                                	</div>    
                                    
                                    <div class="col-md-2">
                                    </div>
								
                                
                                </div>
                                </div>
 
<div class="col-md-12">
<div class="row">
<div class="col-md-12">

<div align="left" style="height: 45px; padding: 9px; font-size: 20px; font-weight: bold !important;"><?php echo $title; ?></div>

<div class="row">
<div class="col-md-6">

</div>

<div class="col-md-6">

<div align="right" style="font-size:14px; font-weight:bold; color:#e61111 !important;">
<!--[ <a href="#" onclick="myFunction()" class=""> &plus;Add</a> ]-->
</div>

</div>
</div>
<div class="table-responsive">
<div id="search_table"><!--TABLE FOR SEARCH</b>--></div>
<div id="default_table">
<table id="table" class="table table-striped" cellspacing="0" width="100%">
<thead>
<tr>
<!--<th>&nbsp;</th>-->
<th>#</th>
<th >Business Entity</th>
<th >Site Description</th>
<th >Cut Off</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Gateway List</th>
<th >Meter List</th>
</tr>
</thead>
<tbody>
</tbody>

<tfoot>
<tr>
<!--/*<th>&nbsp;</th>*/-->
<th>#</th>
<th >Business Entity</th>
<th >Site Description</th>
<th >Cut Off</th>
<th >Created by</th>
<th >Date Created</th>
<th >Modified by</th>
<th >Date Modified</th>
<th >Last Update</th>
<th >Gateway List</th>
<th >Meter List</th>
</tr>
</tfoot>
</table>
<script type="text/javascript">
function clear_search() {

event.preventDefault();
var meter_id = 'NA';
jQuery.ajax({
type: "POST",
url: "<?php echo base_url(); ?>" + "index.php/<?php echo "admin_datatables/site_list/1"; ?>",
dataType: 'html',
data: {
'meter_id': meter_id
},

});

location.reload();

}
var table;

$(document).ready(function() {

<?php /*datatables*/ ?> 
table = $('#table').DataTable({ 
"scrollY":        350,
"scrollCollapse": true,
// "jQueryUI":       true,
"processing": false,<?php /*Feature control the processing indicator.*/ ?>
"serverSide": true,<?php /*Feature control DataTables' server-side processing mode.*/ ?>
"order": [],<?php /*Initial no order.*/ ?>

 <?php /* Load data for the table's content from an Ajax source*/ ?>
"ajax": {
"url": "<?php echo site_url('site_admin_datatables/site_list/0')?>",
"type": "POST"
}, 

 <?php /*Set column definition initialisation properties.*/ ?>
"columnDefs": [
{ 
"targets": [ 0 ],<?php /*first column / numbering column*/ ?>
"orderable": false,<?php /*set not orderable*/ ?>
},
],

});
/*Append Clear Last Seach to seach box*/
$('<div class="clear_search" onClick="clear_search(event)">Clear Last Search</div>').appendTo('#table_filter');
});

</script>
</div>
</div>


</div>
</div>
</div>
</div>
</div>
<style>
.clear_search{
	position: inherit;
    display: inline;
    background-color: #0081ce;
    padding: 4px;
    border-radius: 12px;
    font-weight: bold;
    color: #FFF;
    cursor: pointer;
}
</style>
