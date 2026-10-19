<?php  
  
class Login_model extends CI_Model {  
  
    public function log_in_correctly() {  
  
        $this->db->where('user_name', $this->input->post('user_name'));  
        $this->db->where('user_password', md5($this->input->post('user_password')));  
        //$this->db->select('user_name','user_type');
		$query = $this->db->get('user_tb');  
		
		return $query !== FALSE && $query->num_rows() === 1 ? $query->result_array() : array();
  
    }  
  
      
} 
