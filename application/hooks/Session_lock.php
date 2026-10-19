<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_lock {

    public function enforce()
    {
        $CI =& get_instance();
        if ( ! $CI->session->userdata('currently_logged_in_as')) {
            return;
        }

        $CI->load->model('session_lock_model');
        $valid = $CI->session_lock_model->refresh(
            $CI->session->userdata('user_id'),
            $CI->session->userdata('active_session_token')
        );

        if ($valid === NULL) {
            show_error('Unable to verify your session. Please try again shortly.', 503);
            return;
        }
        if ( ! $valid) {
            // session_destroy() alone does not clear this request's $_SESSION.
            $CI->session->unset_userdata(array_keys($CI->session->userdata()));
            $CI->session->sess_destroy();
            redirect('Login/invalid');
        }
    }
}
