<?php

class UserLogModel extends CI_Model
{
	public function getUserLog($login_id)
	{
		if ($login_id === null ) {
			return $this->db->get('login_log_mobile')->result_array();
		} else {

			$query = $this->db->query(
			" select * from login_log_mobile
			  where
			 login_id = '" . $login_id . "' "
			);
			return $query->result_array();
		}
	}
	
	public function Simpan(
		$data	
	) {
		$data = array(
			'login_id'     => $data['login_id'],
			'lasttime'     => $data['lasttime'],
			'agent' =>   $data['agent'],
			'last_activity'  => $data['last_activity'],			
		);
		$this->db->insert('login_log_mobile', $data);
		return $this->db->insert_id();
	}
}
