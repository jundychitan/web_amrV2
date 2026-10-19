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
				
				redirect('admin/site_management'); 	
					
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
				$data['title'] = 'User Login';
				$this->load->view('templates/header', $data);
				$this->load->view('login_view', $data);
				$this->load->view('templates/footer');
        }  
    }  
  
    public function invalid()  
    {  
				$data['title'] = 'User Login';
				$data['session_expired'] = '<b>Session Expired!</b>';
				
				$this->load->view('templates/header', $data);
				$this->load->view('login_view', $data);
				$this->load->view('templates/footer');
    }  
  
    public function login_action()  
    {  
        if ($this->session->userdata('currently_logged_in_as')) {
            // Do not orphan the current account's lock by switching accounts.
            $this->_login_error('You are already logged in. Please log out before logging in again.');
            return;
        }
        $this->load->helper('security');  
        $this->load->library('form_validation');  
  
        $this->form_validation->set_rules('user_name', 'Username:', 'required|trim|xss_clean|callback_validation');  
        $this->form_validation->set_rules('user_password', 'Password:', 'required|trim');  
  
        if ($this->form_validation->run())   
        {  
			$data_user = $this->login_model->log_in_correctly();
			if (count($data_user) !== 1) {
				$this->_login_error('Incorrect username/password.');
				return;
			}
			$user_name = $data_user[0]['user_name'];
			$user_real_name = $data_user[0]['user_real_name'];
			$user_id = $data_user[0]['id'];
			$user_type = $data_user[0]['user_type'];
			$this->load->model('session_lock_model');
			$token = $this->session_lock_model->acquire($user_id);
			if ($token === NULL) {
				$this->_login_error('Unable to start your session. Please try again shortly.');
				return;
			}
			if ($token === FALSE) {
				$this->_login_error('This account is already logged in. Please log out from the other terminal first.');
				return;
			}
			
			$user_list_src = $data_user[0]['user_list_src'];

			$user_access = $data_user[0]['user_access'];
			
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
                'currently_logged_in_as' => $user_type,  
                'user_access' => $user_access,
                'active_session_token' => $token
                );   
				
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata($data);  
               	//REDIRECT BASE ON USERTYPE 
				if($user_type=='Admin'){
				redirect('admin/welcome');  
				}
				else if($user_type=='BA'){
				redirect('ba/welcome');  
				//echo 'l';
				}
				else if($user_type=='OM'){
				redirect('om/welcome');  
				//echo 'l';
				}
				else{
				//redirect('officer/welcome');
				redirect('re/welcome');  
				//echo 'g';
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
        $this->load->model('session_lock_model');
        if ( ! $this->session_lock_model->release(
            $this->session->userdata('user_id'),
            $this->session->userdata('active_session_token')
        )) {
            show_error('Unable to log out. Please try again shortly.', 503);
            return;
        }
        $this->session->unset_userdata(array_keys($this->session->userdata()));
        $this->session->sess_destroy();  
		$data['title'] = 'User Login';
		$this->load->view('templates/header', $data);
		$this->load->view('login_view', $data);
		$this->load->view('templates/footer');
    }  
  
    private function _login_error($message)
    {
        $data['title'] = 'User Login';
        $data['login_error'] = $message;
        $this->load->view('templates/header', $data);
        $this->load->view('login_view', $data);
        $this->load->view('templates/footer');
    }
}
