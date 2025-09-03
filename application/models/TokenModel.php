<?php

class TokenModel extends CI_Model{
	public function updateToken($data,$id){
		$this->db->update('siswa', $data, ['id' => $id]);
		return $this->db->affected_rows();
}
}

?>