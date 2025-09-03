<?php


class HrmsPermintaanSdmModel extends CI_Model
{
	private $table = 'hrms_permintaan_sdm';

	function __construct()
	{
		// if (!$this->db->table_exists($this->table)) {
		//     $this->create_table();
		// }
	}


	public function retrieve_all()
	{
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('hrms_permintaan_sdm');

		return $result->result_array();
	}
	public function retrieve_akun_by_tipe_supplier($tipe_supp)
	{	
		if ($tipe_supp=='SP') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_permintaan_sdm b", "b.id = a.hrms_permintaan_sdm_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_LOKAL");
		$result = $this->db->get();
		return $result->result_array();
		}
		elseif($tipe_supp=='KT') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_permintaan_sdm b", "b.id = a.hrms_permintaan_sdm_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_KONTRAKTOR");
		$result = $this->db->get();
		return $result->result_array();
		}
		elseif($tipe_supp=='TR') {
		$this->db->select("a.* ,b.nama as nama, b.kode as kode");
		$this->db->from("acc_auto_jurnal a");
		$this->db->join("hrms_permintaan_sdm b", "b.id = a.hrms_permintaan_sdm_id");
		$this->db->where("a.kode", "TIPE_SUPPLIER_TRANSPORTIR");
		$result = $this->db->get();
		return $result->result_array();
		}
		
	}
	public function retrieve_all_skill($permintaan_sdm_id)
	{
		$this->db->select("hrms_permintaan_sdm.*,hrms_permintaan_sdm_skill.skill_id,hrms_permintaan_sdm_skill.permintaan_sdm_id,hrms_skill.nama as nama_skill");
		$this->db->from("hrms_permintaan_sdm");
		$this->db->join('hrms_permintaan_sdm_skill', 'hrms_permintaan_sdm.id = hrms_permintaan_sdm_skill.permintaan_sdm_id');
		$this->db->join('hrms_skill', 'hrms_permintaan_sdm_skill.skill_id = hrms_skill.id');
		$this->db->where('hrms_permintaan_sdm_skill.permintaan_sdm_id', $permintaan_sdm_id);
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get()->result_array();
		return $result;
	}
	
	public function retrieve_all_akun_kasbank_by_lokasi_id($lokasi_id)
	{
		$this->db->select("hrms_permintaan_sdm.*");
		$this->db->from("hrms_permintaan_sdm");
		$this->db->join('hrms_permintaan_sdm_skill', 'hrms_permintaan_sdm.id = hrms_permintaan_sdm_skill.hrms_permintaan_sdm_id');
		$this->db->where('is_transaksi_akun', 1);
		$this->db->where('is_kasbank_akun', 1);
		$this->db->where('hrms_permintaan_sdm_skill.lokasi_id', $lokasi_id);
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get();

		return $result->result_array();
	}
	
	public function retrieveArray($array_where = array())
	{
		foreach ($array_where as $key => $val) {
			$this->db->where($key, $val);
		}
		$result = $this->db->get('hrms_permintaan_sdm');
		return $result->row_array();
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('hrms_permintaan_sdm', '1');
		return $result->row_array();
	}


	public function delete($id)
	{
		$this->db->where('posisi_id', $id);
		$this->db->delete('hrms_permintaan_sdm_skill');
		$this->db->where('id', $id);
		$this->db->delete('hrms_permintaan_sdm');
		
		return true;
	}


	public function update(
		$id,
		$input              = null

	) {
		$this->db->where('id', $id);
		$this->db->update('hrms_permintaan_sdm', array(
			'no_transaksi' => $input['no_transaksi'],
			'tanggal' => $input['tanggal'],
			'tanggal_dibutuhkan' => $input['tanggal_dibutuhkan'],
			'alokasi_karyawan' => $input['alokasi_karyawan'],
			'jabatan_id' => $input['jabatan_id'],
			'posisi_id' => $input['posisi_id'],
			'departement_id' => $input['departement_id'],
			'catatan' => $input['catatan'],
			'jumlah_sdm' => $input['jumlah_sdm'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
			
		));
		$this->db->where('permintaan_sdm_id', $id);
		$this->db->delete('hrms_permintaan_sdm_skill');
		$skill=$input['skill_id'];
		foreach ($skill as $key => $skill_id) {
			$data = array(
				'permintaan_sdm_id'      => $id,
				'skill_id' =>  $skill_id
			);
			$this->db->insert('hrms_permintaan_sdm_skill', $data);
		}		
		return true;
	}


	public function create(
		$input = null
	) {
		$this->db->insert('hrms_permintaan_sdm', array(
			'no_transaksi' => $input['no_transaksi'],
			'tanggal' => $input['tanggal'],
			'tanggal_dibutuhkan' => $input['tanggal_dibutuhkan'],
			'alokasi_karyawan' => $input['alokasi_karyawan'],
			'jabatan_id' => $input['jabatan_id'],
			'posisi_id' => $input['posisi_id'],
			'departement_id' => $input['departement_id'],
			'catatan' => $input['catatan'],
			'jumlah_sdm' => $input['jumlah_sdm'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		));
		$id = $this->db->insert_id();
		$this->db->where('permintaan_sdm_id', $id);
		$this->db->delete('hrms_permintaan_sdm_skill');
		$skill=$input['skill_id'];
		foreach ($skill as $key => $skill_id) {
			$data = array(
				'permintaan_sdm_id'      => $id,
				'skill_id' =>  $skill_id
			);
			$this->db->insert('hrms_permintaan_sdm_skill', $data);
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
