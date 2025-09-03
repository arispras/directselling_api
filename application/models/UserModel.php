<?php

use \Firebase\JWT\JWT;

class UserModel extends CI_Model
{
	// function __construct()
	// {
	// 	// Construct the parent class
	// 	date_default_timezone_set("Asia/Jakarta");
	// 	parent::__construct();
	// 	$this->auth();
	// 	$theCredential = $this->user_data;
	// }
	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('fwk_users');
		return true;
	}


	public function retrieve_all_user()
	{

		$this->db->order_by('user_name', 'ASC');
		$result = $this->db->get('fwk_users')->result_array();
		// print_r($result->result_array());exit();


		return $result;
	}



	public function update(
		$id,
		$user_full_name,
		$user_name,
		$password,
		$email,
		$status,
		$employee_id
	) {
		$id          = (int)$id;

		$data = array(
			'user_full_name'           => $user_full_name,
			'user_name'          => $user_name,
			'user_password' => $password,
			'user_email'  => $email,
			'status'     => $status,
			'employee_id'     => $employee_id
		);

		$this->db->where('id', $id);
		$this->db->update('fwk_users', $data);
		return true;
	}



	public function create(
		$user_full_name,
		$user_name,
		$password,
		$email,
		$status,
		$employee_id = null
	) {

		$data = array(
			'user_full_name'           => $user_full_name,
			'user_name'          => $user_name,
			'user_password' => md5($password),
			'user_email'  => $email,
			'status'     => $status,
			'employee_id'     => $employee_id
		);
		$this->db->insert('fwk_users', $data);
		$res_id = $this->db->insert_id();
		return $res_id;
	}

	public function retrieve($id = null)
	{

		if ($id == null) {
			$result = $this->db->query("SELECT * from fwk_users order by user_name");

			return $result->result_array();
		} else {
			$result = $this->db->query("SELECT * from fwk_users where id=" . $id . " ;");

			return $result->row_array();
		}
	}

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('fwk_users', array('foto' => null));
		return true;
	}

	public function update_password(
		$id,
		$password
	
	) {
		$id          = (int)$id;

		$data = array(
			
			'user_password' => md5($password),
			
		);

		$this->db->where('id', $id);
		$this->db->update('fwk_users', $data);
		return true;
	}
	public function count($by, $param = array())
	{
		switch ($by) {
			case 'kelas':
				$kelas_id = $param['kelas_id'];

				$this->db->join('fwk_users', 'kelas_fwk_users.fwk_users_id = fwk_users.id');
				$this->db->where('kelas_fwk_users.kelas_id', $kelas_id);
				$this->db->where('fwk_users.status_id', 1);
				$result = $this->db->get('kelas_fwk_users');
				return $result->num_rows();
				break;

			case 'total':
				$this->db->select("COUNT(*) as jml");
				$this->db->where('status_id !=', '0');
				$result = $this->db->get('fwk_users');
				$result = $result->row_array();
				return $result['jml'];
				break;

			case 'pending':
				$this->db->select("COUNT(*) as jml");
				$this->db->where('status_id', '0');
				$result = $this->db->get('fwk_users');
				$result = $result->row_array();
				return $result['jml'];
				break;

			default:
				return 0;
				break;
		}
	}
}
