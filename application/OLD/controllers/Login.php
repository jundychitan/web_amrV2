<?php
header('Access-Control-Allow-Origin: localhost');
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends CI_Controller {  
  
    public function index()  
    {  
        $this->user_login();  
    }  
  
    public function user_login()  
    {  
				if($this->session->userdata('currently_logged_in_as')=='Admin'){
				
				redirect('admin/user_management'); 	
					
				}
				else{
	
				$data['title'] = 'User Login';
				$this->load->view('templates/header', $data);
				$this->load->view('login_view');
				$this->load->view('templates/footer');
				
				}
    }  
  
    public function data()  
    {  
        if ($this->session->userdata('currently_logged_in_as')=='Admin')   
        {  
            $this->load->view('exd');  
        } else {  
            redirect('Login/invalid');  
        }  
    }  
  
    public function invalid()  
    {  
        $this->load->view('invalid');  
    }  
  
    public function login_action()  
    {  
        $this->load->helper('security');  
        $this->load->library('form_validation');  
  
        $this->form_validation->set_rules('user_name', 'Username:', 'required|trim|xss_clean|callback_validation');  
        $this->form_validation->set_rules('user_password', 'Password:', 'required|trim');  
  
        if ($this->form_validation->run())   
        {  
			$data_user = $this->login_model->log_in_correctly();
			$user_name = $data_user[0]['user_name'];
			$user_real_name = $data_user[0]['user_real_name'];
			$user_id = $data_user[0]['id'];
			$user_type = $data_user[0]['user_type'];
			
			$user_list_src = $data_user[0]['user_list_src'];
			
			if($user_list_src != 'AMR'){
			 $user_id_sap = $data_user[0]['user_id_sap'];
			}else{
			 $user_id_sap = $data_user[0]['id'];
			}

			
				$data = array(  
                'user_name' => $user_name,  
				'real_name' => $user_real_name, 
				'user_id' => $user_id,  
				'user_id_sap' => $user_id_sap,  
                'currently_logged_in_as' => $user_type  
                );   
                $this->session->set_userdata($data);  
               	//REDIRECT BASE ON USERTYPE 
				if($user_type=='Admin'){
				redirect('admin/welcome');  
				}
				else if($user_type=='Manager' || $user_type=='Supervisor'){
				redirect('site_admin/welcome');  
				echo 'l';
				}
				else{
				redirect('officer/welcome');  
				echo 'g';
				}
        }   
        else {  
				$data['title'] = 'User Login';
				$this->load->view('templates/header', $data);
				$this->load->view('login_view', $data);
				$this->load->view('templates/footer');
        }  
    }  
   
    public function validation()  
    {  
        $this->load->model('login_model');  
  
        if ($this->login_model->log_in_correctly())  
        {  
  
            return true;  
        } else {  
            $this->form_validation->set_message('validation', 'Incorrect username/password.');  
            return false;  
        }  
    }  
  
    public function logout()  
    {  
        $this->session->sess_destroy();  
		$data['title'] = 'User Login';
		$this->load->view('templates/header', $data);
		$this->load->view('login_view', $data);
		$this->load->view('templates/footer');
    }  
  
}  
