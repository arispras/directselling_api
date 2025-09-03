<?php


class AccInterUnitModel extends CI_Model
{
	private $table = 'acc_inter_unit';

	function __construct()
	{
		// if (!$this->db->table_exists($this->table)) {
		//     $this->create_table();
		// }
	}


	public function retrieve_all_akun()
	{
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('acc_inter_unit');

		return $result->result_array();
	}

	public function retrieve_all_akun_detail()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('acc_inter_unit');

		return $result->result_array();
	}

	public function retrieve_all_akun_kasbank()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('acc_inter_unit');

		return $result->result_array();
	}

	public function retrieve($array_where = array())
	{
		foreach ($array_where as $key => $val) {
			$this->db->where($key, $val);
		}
		$result = $this->db->get($this->table);
		return $result->row_array();
	}

	public function retrievebyId($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('acc_inter_unit', '1');
		return $result->row_array();
	}


	public function delete($id)
	{
		$this->db->where('id', $id);
		$this->db->delete($this->table);
		return true;
	}


	public function update(
		$id,
		$input

	) {
		$this->db->where('id', $id);
		$this->db->update($this->table, array(
			'acc_akun_id' => $input['acc_akun_id'],
			'lokasi_id' => $input['lokasi_id'],
			'lokasi_id_2' => $input['lokasi_id_2'],
			'tipe' => $input['tipe'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));

		return true;
	}


	public function create(
		$input 
	) {
		$this->db->insert($this->table, array(
			'acc_akun_id' => $input['acc_akun_id'],
			'lokasi_id' => $input['lokasi_id'],
			'lokasi_id_2' => $input['lokasi_id_2'],
			'tipe' => $input['tipe'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));

		return $this->db->insert_id();
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
