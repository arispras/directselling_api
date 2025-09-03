<?php
date_default_timezone_set('Asia/Jakarta');
class NotificationModel extends CI_Model
{
	public function getNotification($login_id)
	{
		if ($login_id === null) {
			return $this->db->get('notifications')->result_array();
		} else {

			$query = $this->db->query(
				" select * from notifications
			  where
			  notifiable_id = '" . $login_id . "' and updated_at is not null order by created_at DESC "
			);
			return $query->result_array();
		}
	}

	public function Simpan(
		$data
	) {
		$data = array(
			'type'     => $data['type'],
			'messsage'     => $data['messsage'],
			'notifiable_id' =>   $data['notifiable_id'],
			'data'  => $data['data'],
			'read_at'  => $data['read_at'],
			'created_at'  => $data['created_at'],
			'updated_at'  => $data['updated_at']

		);
		$this->db->insert('notifications', $data);
		return $this->db->insert_id();
	}
	public function createNotification($type, $messsage, $notifiable_id, $data)
	{
		$data = array(
			'type' => $type,
			'messsage' => $messsage,
			'notifiable_id' => $notifiable_id,
			'data' => $data,
			'read_at'  => null,
			'created_at'  =>  date('Y-m-d H:i:s'),
			'updated_at'  =>  date('Y-m-d H:i:s')

		);
		if ($notifiable_id != null) {
			$this->db->insert('notifications', $data);
			return $this->db->insert_id();
		} else {

			return false;
		}
	}
	public function readNotification(
		$data_post
	) {

		$id = $data_post['id'];
		$data = array(
			'read_at'  => $data_post['read_at']

		);
		$this->db->where('id', $id);
		$this->db->update('notifications', $data);
		return $id;
	}
	public function removeNotification(
		$data_post
	) {

		$id = $data_post['id'];
		$data = array(
			'read_at'  => $data_post['read_at'],
			'updated_at'  => null

		);
		$this->db->where('id', $id);
		$this->db->update('notifications', $data);
		return $id;
	}
}
