<?php


class AuthModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->library('user_agent');
	}
	public function Login($username, $password)
	{
		 $password = md5($password);
		$this->db->where('user_name', $username);
		$this->db->where('user_password', $password);
		$res = $this->db->get('fwk_users', '1')->row_array();;
		$data = array();
		if (!empty($res)) {

			$this->db->where('id', $res['employee_id']);
			$res_karyawan = $this->db->get('karyawan', '1')->row_array();		
			$res['karyawan']=$res_karyawan;
			if ($res['status']=='1') {			
				$data = array("status" => "OK", "data" => $res);
				
			} else {
				$data = array("status" => 'NOT_AKTIF', "data" => null);
			}
		} else {
			$data = array("status" => 'NOT_FOUND', "data" => null);
		}
		return $data;
	}
	public function update_fcm_token($id, $token)
	{
		$id = (int)$id;

		$data = array('fcm_token' => $token);
		$this->db->where('id', $id);
		$this->db->update('fwk_users', $data);
		return true;
	}
	public function retrieve_new_log($limit = 10)
	{
		$this->db->order_by('lasttime', 'desc');
		$results = $this->db->get('login_log', $limit);
		return $results->result_array();
	}


	
	public function retrieve_last_activity($log_id)
	{
		$log = $this->retrieve_log($log_id);
		return $log['last_activity'];
	}

	public function update_last_activity($log_id, $time = "")
	{
		$this->db->where('id', $log_id);
		$this->db->update('login_log', array(
			'last_activity' => empty($time) ? time() : $time,
			'lasttime_logout'=>date('Y-m-d H:i:s')
		));
		return true;
	}

	
	public function retrieve_last_log($login_id)
	{
		$this->db->where('login_id', $login_id);
		$this->db->order_by('id', 'desc');
		$result = $this->db->get('login_log', 1);
		return $result->row_array();
	}

	public function retrieve_admin_list($username)
	{
		$this->db->where('username', $username);
		$result = $this->db->get('admin_list');
		return $result->row_array();
	}
	public function create_admin_list($username, $karyawanid)
	{
		$this->db->insert('admin_list', array(
			'username' => $username,
			'karyawanid' => $karyawanid

		));

		return true;
	}
	
	public function retrieve_log($id)
	{
		$this->db->where('id', $id);
		$result = $this->db->get('login_log');
		return $result->row_array();
	}



	public function create_log($login_id)
	{
		$this->db->insert('login_log', array(
			'login_id' => $login_id, /// user id
			'lasttime' => date('Y-m-d H:i:s'),
			'agent'    => json_encode(array(
				'is_mobile'    => ($this->agent->is_mobile()) ? 1 : 0,
				'browser'      => ($this->agent->is_browser()) ? $this->agent->browser() . ' ' . $this->agent->version() : '',
				'platform'     => $this->agent->platform(),
				'agent_string' => $this->agent->agent_string(),
				'ip'           => get_ip(),
			))
		));

		return $this->db->insert_id();
	}

	
	public function retrieve_all_users()
	{
	
	}

	
	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('login');
		return true;
	}

	
	
	
		public function update_password($id, $password)
	{
		$id = (int)$id;

		$data = array('password' => md5($password));
		$this->db->where('id', $id);
		$this->db->update('login', $data);
		return true;
	}

	
}
