<?php


class UserAccessModel extends CI_Model
{

	public function retrieve_all($parent_id = null, $array_where = array())
	{
		$this->db->where('parent_id', $parent_id);

		foreach ($array_where as $key => $value) {
			$this->db->where($key, $value);
		}

		$this->db->order_by('sort_order', 'ASC');
		$result = $this->db->get('fwk_users_acces');
		return $result->result_array();
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('fwk_users_acces', '1');
		return $result->row_array();
	}
	public function retrieveByUserId($user_id)
	{
		$user_id = (int)$user_id;

		$this->db->where('user_id', $user_id);

		$result = $this->db->get('fwk_users_acces');
		return $result->result_array();
	}
	public function retrieveByUserIdMenuName($user_id, $menu_name)
	{
		$user_id = (int)$user_id;
		$this->db->select('fwk_users_acces.*');
		$this->db->from("fwk_users_acces");
		$this->db->join('fwk_menu', 'fwk_users_acces.menu_id = fwk_menu.id');
		$this->db->where('fwk_menu.name', $menu_name);
		$this->db->where('fwk_users_acces.user_id', $user_id);
		$result = $this->db->get();
		return $result->row_array();
	}
	public function retrieveByUserIdMenuUrl($user_id, $menu_url)
	{
		$user_id = (int)$user_id;
		$this->db->select('fwk_menu.*');
		$this->db->from("fwk_users_acces");
		$this->db->join('fwk_menu', 'fwk_users_acces.menu_id = fwk_menu.id');
		$this->db->where('fwk_menu.url', $menu_url);
		$this->db->where('fwk_users_acces.user_id', $user_id);
		$result = $this->db->get();
		return $result->row_array();
	}
	public function retrieveMenuByUserId($user_id)
	{
		$user_id = (int)$user_id;
		$this->db->select('fwk_menu.*');
		$this->db->from("fwk_users_acces");
		$this->db->join('fwk_menu', 'fwk_users_acces.menu_id = fwk_menu.id');
		$this->db->where('fwk_menu.is_child', 1);
		$this->db->where('fwk_users_acces.user_id', $user_id);
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_by_name($nama)
	{


		$this->db->where('nama', $nama);
		$result = $this->db->get('menu', '1');
		return $result->row_array();
	}


	public function create(
		$user_id,
		$menu_id,
		$new_ = 0,
		$edit_ = 0,
		$delete_ = 0,
		$print_ = 0,
		$posting_ = 0

	) {

		$data = array(
			'user_id'      => $user_id,
			'menu_id' => $menu_id,
			'new_'    => $new_,
			'edit_' => $edit_,
			'delete_' => $delete_,
			'print_' => $print_,
			'posting_' => $posting_,

		);
		$this->db->insert('fwk_users_acces', $data);
		return $this->db->insert_id();
	}
	public function createLocation(
		$user_id,
		$location_id

	) {
		$data = array(
			'user_id'      => $user_id,
			'location_id' => $location_id


		);
		$this->db->insert('fwk_users_location', $data);
		return $this->db->insert_id();
	}
	public function createAfdeling(
		$user_id,
		$afdeling_id

	) {
		$data = array(
			'user_id'      => $user_id,
			'afdeling_id' => $afdeling_id


		);
		$this->db->insert('fwk_users_afdeling', $data);
		return $this->db->insert_id();
	}
	public function createKasbank(
		$user_id,
		$afdeling_id

	) {
		$data = array(
			'user_id'      => $user_id,
			'acc_akun_id' => $afdeling_id

		);
		$this->db->insert('fwk_users_kasbank', $data);
		return $this->db->insert_id();
	}
	public function createPosting(
		$user_id,
		$posting_id

	) {
		$data = array(
			'user_id'      => $user_id,
			'posting_id' => $posting_id


		);
		$this->db->insert('fwk_users_posting', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$user_id,
		$menu_id,
		$new_ = 0,
		$edit_ = 0,
		$delete_ = 0,
		$print_ = 0,
		$posting_ = 0


	) {

		$data = array(
			'user_id'      => $user_id,
			'menu_id' => $menu_id,
			'new_'    => $new_,
			'edit_' => $edit_,
			'delete_' => $delete_,
			'print_' => $print_,
			'posting_' => $posting_,

		);
		$this->db->where('id', $id);
		$this->db->update('fwk_users_acces', $data);
		return true;
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('fwk_users_acces');
		return true;
	}
	public function deleteByUserId($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('fwk_users_acces');
		return true;
	}
	public function deleteLocationByUserId($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('fwk_users_location');
		return true;
	}
	public function deleteAfdelingByUserId($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('fwk_users_afdeling');
		return true;
	}
	public function deleteKasbankByUserId($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('fwk_users_kasbank');
		return true;
	}
	public function deletePostingByUserId($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->delete('fwk_users_posting');
		return true;
	}
	public function delete_audit_all()
	{
		// $this->db->where('datum < DATE_SUB(NOW(), INTERVAL 60 DAY)');
		$this->db->where("last_modified < (now() - interval 30 DAY)");
		$this->db->delete('fwk_user_audit');
		return true;
	}
}
