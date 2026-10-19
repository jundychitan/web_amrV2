<!DOCTYPE html>  
<html>  
<head>  
    <title></title>  
</head>  
<body>  
    <h1>Welcome, You are successfully logged in.</h1>  
  
    <?php  
    echo "<pre>";  
    echo print_r($this->session->all_userdata());  
		if(!isset($_SESSION['user_name'])){ echo 'A'; } else{ echo $_SESSION['user_name']; }
		if(!isset($_SESSION['user_type'])){ echo 'A'; } else{ echo 'B'; }

    echo "</pre>";  
    ?>  
  
    <a href='<?php echo base_url()."index.php/Main/logout"; ?>'>Logout</a>  
  
</body>  
</html>  