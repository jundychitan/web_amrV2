<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_lock_model extends CI_Model {

    const INACTIVITY_SECONDS = 7200;

    // Returns the private browser token, FALSE if occupied, or NULL on failure.
    public function acquire($user_id)
    {
        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $exception) {
            log_message('error', 'Unable to generate an account session token.');
            return NULL;
        }

        $sql = 'UPDATE '.$this->db->dbprefix('user_tb').'
            SET active_session_hash = ?, active_session_activity = UNIX_TIMESTAMP()
            WHERE id = ? AND (active_session_hash IS NULL
                OR active_session_activity <= UNIX_TIMESTAMP() - ?)';
        $result = $this->lock_query($sql, array(
            hash('sha256', $token), $user_id, self::INACTIVITY_SECONDS
        ));

        if ($result === FALSE) {
            return NULL;
        }

        return $this->db->affected_rows() === 1 ? $token : FALSE;
    }

    // NULL means a database failure; FALSE means missing/expired ownership.
    public function refresh($user_id, $token)
    {
        if ( ! $this->valid_token($token)) {
            return FALSE;
        }

        $table = $this->db->dbprefix('user_tb');
        $bindings = array($user_id, hash('sha256', $token), self::INACTIVITY_SECONDS);
        $condition = ' WHERE id = ? AND active_session_hash = ?
            AND active_session_activity > UNIX_TIMESTAMP() - ?';
        $result = $this->lock_query('UPDATE '.$table.'
            SET active_session_activity = UNIX_TIMESTAMP()'.$condition, $bindings);

        if ($result === FALSE) {
            return NULL;
        }
        if ($this->db->affected_rows() === 1) {
            return TRUE;
        }

        // MySQL reports zero changed rows when two requests share a second.
        $query = $this->lock_query('SELECT id FROM '.$table.$condition, $bindings);
        return $query === FALSE ? NULL : $query->num_rows() === 1;
    }

    public function release($user_id, $token)
    {
        if ( ! $this->valid_token($token)) {
            return TRUE;
        }

        return $this->lock_query('UPDATE '.$this->db->dbprefix('user_tb').'
            SET active_session_hash = NULL, active_session_activity = NULL
            WHERE id = ? AND active_session_hash = ?',
            array($user_id, hash('sha256', $token))) !== FALSE;
    }

    private function valid_token($token)
    {
        return is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;
    }

    private function lock_query($sql, $bindings)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        try {
            return $this->db->query($sql, $bindings);
        } catch (Exception $exception) {
            log_message('error', 'Account session lock database operation failed.');
            return FALSE;
        } finally {
            $this->db->db_debug = $debug;
        }
    }
}
