<?php


class HrmsPosisiModel extends CI_Model
{
	private $table = 'hrms_posisi';

	function __construct()
	{
		// if (!$this->db->table_exists($this->table)) {
		//     $this->create_table();
		// }
	}


	public function retrieve_all()
	{
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('hrms_posisi');

		return $result->result_array();
	}
	public function retrieve_akun_by_tipe_supplier($tipe_supp)
	{	
		if ($tipe_supp=='SP') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_posisi b", "b.id = a.hrms_posisi_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_LOKAL");
		$result = $this->db->get();
		return $result->result_array();
		}
		elseif($tipe_supp=='KT') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_posisi b", "b.id = a.hrms_posisi_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_KONTRAKTOR");
		$result = $this->db->get();
		return $result->result_array();
		}
		elseif($tipe_supp=='TR') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_posisi b", "b.id = a.hrms_posisi_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_TRANSPORTIR");
		$result = $this->db->get();
		return $result->result_array();
		}
		
	}
	public function retrieve_all_by_posisi_id($posisi_id)
	{
		$this->db->select("hrms_posisi.*,hrms_posisi_skill.skill_id,hrms_posisi_skill.posisi_id,hrms_skill.nama as nama_skill");
		$this->db->from("hrms_posisi");
		$this->db->join('hrms_posisi_skill', 'hrms_posisi.id = hrms_posisi_skill.posisi_id');
		$this->db->join('hrms_skill', 'hrms_posisi_skill.skill_id = hrms_skill.id');
		$this->db->where('hrms_posisi_skill.posisi_id', $posisi_id);
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get()->result_array();
		return $result;
	}
	public function retrieve_all_akun_detail()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('hrms_posisi');

		return $result->result_array();
	}

	public function retrieve_all_akun_kasbank()
	{
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('hrms_posisi');

		return $result->result_array();
	}
	
	
	public function retrieve_all_akun_kasbank_by_lokasi_id($lokasi_id)
	{
		$this->db->select("hrms_posisi.*");
		$this->db->from("hrms_posisi");
		$this->db->join('hrms_posisi_skill', 'hrms_posisi.id = hrms_posisi_skill.hrms_posisi_id');
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->where('hrms_posisi_skill.lokasi_id', $lokasi_id);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get();

		return $result->result_array();
	}
	
	public function retrieve($array_where = array())
	{
		foreach ($array_where as $key => $val) {
			$this->db->where($key, $val);
		}
		$result = $this->db->get('hrms_posisi');
		return $result->row_array();
	}

	public function retrievebyId($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('hrms_posisi', '1');
		return $result->row_array();
	}


	public function delete($id)
	{
		$this->db->where('posisi_id', $id);
		$this->db->delete('hrms_posisi_skill');
		$this->db->where('id', $id);
		$this->db->delete('hrms_posisi');
		
		return true;
	}


	public function update(
		$id,
		$input              = null

	) {
		$this->db->where('id', $id);
		$this->db->update('hrms_posisi', array(
			'nama' => $input['nama'],
			'jabatan_id' => $input['jabatan_id'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));
		$this->db->where('posisi_id', $id);
		$this->db->delete('hrms_posisi_skill');
		$skill=$input['skill_id'];
		foreach ($skill as $key => $skill_id) {
			$data = array(
				'posisi_id'      => $id,
				'skill_id' =>  $skill_id
			);
			$this->db->insert('hrms_posisi_skill', $data);
		}		
		return true;
	}


	public function create(
		$input = null
	) {
		$this->db->insert('hrms_posisi', array(
			'nama' => $input['nama'],
			'jabatan_id' => $input['jabatan_id'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));
		$id = $this->db->insert_id();
		$this->db->where('posisi_id', $id);
		$this->db->delete('hrms_posisi_skill');
		$skill=$input['skill_id'];
		foreach ($skill as $key => $skill_id) {
			$data = array(
				'posisi_id'      => $id,
				'skill_id' =>  $skill_id
			);
			$this->db->insert('hrms_posisi_skill', $data);
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
