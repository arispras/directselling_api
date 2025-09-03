<?php


class AccAkunModel extends CI_Model
{
	private $table = 'acc_akun';

	function __construct()
	{
		// if (!$this->db->table_exists($this->table)) {
		//     $this->create_table();
		// }
	}


	public function retrieve_all_akun()
	{
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('acc_akun');

		return $result->result_array();
	}
	public function retrieve_all_akun_by_lokasi_id($lokasi_id)
	{
		$this->db->select("acc_akun.*");
		$this->db->from("acc_akun");
		$this->db->join('acc_akun_dt', 'acc_akun.id = acc_akun_dt.acc_akun_id');
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('acc_akun_dt.lokasi_id', $lokasi_id);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get()->result_array();
		return $result;
	}
	public function retrieve_all_akun_detail()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('acc_akun');

		return $result->result_array();
	}

	public function retrieve_all_akun_kasbank()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('acc_akun');

		return $result->result_array();
	}
	public function retrieve_all_akun_supplier()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where_in('kode', ['2110101','2111101','2111201']);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('acc_akun');

		return $result->result_array();
	}
	public function retrieve_all_akun_kasbank_by_lokasi_id($lokasi_id)
	{
		$this->db->select("acc_akun.*");
		$this->db->from("acc_akun");
		$this->db->join('acc_akun_dt', 'acc_akun.id = acc_akun_dt.acc_akun_id');
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->where('acc_akun_dt.lokasi_id', $lokasi_id);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get();

		return $result->result_array();
	}
	public function retrieve_all_akun_kasbank_by_acces($user_id)
	{
		$sql = " select * from acc_akun where id in
			(select acc_akun_id from fwk_users_kasbank where user_id=" . $user_id . ")
			order by nama";
		return	$this->db->query($sql)->result_array();
	}
	public function retrieve($array_where = array())
	{
		foreach ($array_where as $key => $val) {
			$this->db->where($key, $val);
		}
		$result = $this->db->get('acc_akun');
		return $result->row_array();
	}

	public function retrievebyId($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('acc_akun', '1');
		return $result->row_array();
	}


	public function delete($id)
	{
		$this->db->where('acc_akun_id', $id);
		$this->db->delete('acc_akun_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_akun');
		
		return true;
	}


	public function update(
		$id,
		$input              = null

	) {
		$this->db->where('id', $id);
		$this->db->update('acc_akun', array(
			'kode' => $input['kode'],
			'nama' => $input['nama'],
			'tipe' => $input['tipe'],
			'kelompok_biaya' => $input['kelompok_biaya'],
			'ket' => $input['ket'],
			'is_kasbank_akun'=> $input['is_kasbank_akun'],
			'is_transaksi_akun'=> $input['is_transaksi_akun'],
			'aktif'=> $input['aktif'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));
		$this->db->where('acc_akun_id', $id);
		$this->db->delete('acc_akun_dt');
		$lokasi=$input['lokasi_id'];
		foreach ($lokasi as $key => $lokasi_id) {
			$data = array(
				'acc_akun_id'      => $id,
				'lokasi_id' =>  $lokasi_id
			);
			$this->db->insert('acc_akun_dt', $data);
		}		
		return true;
	}


	public function create(
		$input = null
	) {
		$this->db->insert('acc_akun', array(

			'kode' => $input['kode'],
			'nama' => $input['nama'],
			'tipe' => $input['tipe'],
			'ket' => $input['ket'],
			'kelompok_biaya' => $input['kelompok_biaya'],
			'is_kasbank_akun'=> $input['is_kasbank_akun'],
			'is_transaksi_akun'=> $input['is_transaksi_akun'],
			'aktif'=> $input['aktif'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));
		$lokasi=$input['lokasi_id'];
		$id = $this->db->insert_id();
		$this->db->where('acc_akun_id', $id);
		$this->db->delete('acc_akun_dt');
		foreach ($lokasi as $key => $lokasi_id) {
			$data = array(
				'acc_akun_id'      => $id,
				'lokasi_id' =>  $lokasi_id
			);
			$this->db->insert('acc_akun_dt', $data);
		}		
		return $id;
	}

	/**
	 * Method untuk membuat tabel pengumuman
	 */
	public function create_table()
	{
		// $CI =& get_instance();
		// $CI->load->model('config_model');

		// $CI->config_model->create_tb_pengumuman();

		return true;
	}
}
