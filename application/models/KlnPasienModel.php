<?php

class KlnPasienModel extends CI_Model
{

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('kln_pasien', array('foto' => null));
		return true;
	}


	public function retrieve_all_by_name($nama)
	{
		$search_result = $this->retrieve_all_filter(
			$nip           = '',
			$nama,
			$jenis_kelamin = array(),
			$tempat_lahir  = '',
			$tgl_lahir     = '',
			$bln_lahir     = '',
			$thn_lahir     = '',
			$alamat        = '',
			$status_id     = array(),
			$username      = '',
			$is_admin      = '',
			$page_no       = 1,
			$pagination    = false
		);
		return $search_result;
	}


	public function retrieve_all_filter(
		$nip           = '',
		$nama          = '',
		$jenis_kelamin = array(),
		$tempat_lahir  = '',
		$tgl_lahir     = '',
		$bln_lahir     = '',
		$thn_lahir     = '',
		$alamat        = '',
		$status_id     = array(),
		$username      = '',
		$is_admin      = '',
		$page_no       = 1,
		$pagination    = true
	) {
		$where = array();
		$orderby['kln_pasien.id'] = 'DESC';

		if (!empty($nip)) {
			$nip = (int)$nip;
			$where['kln_pasien.nip'] = array($nip, 'like', 'after');
		}

		if (!empty($nama)) {
			$nama = (string)$nama;
			$where['kln_pasien.nama'] = array($nama, 'like');
		}

		if (!empty($jenis_kelamin) and is_array($jenis_kelamin)) {
			$where['kln_pasien.jenis_kelamin'] = array($jenis_kelamin, 'where_in');
		}

		if (!empty($tempat_lahir)) {
			$tempat_lahir = (string)$tempat_lahir;
			$where['kln_pasien.tempat_lahir'] = array($tempat_lahir, 'like');
		}

		if (!empty($tgl_lahir)) {
			$tgl_lahir = (int)$tgl_lahir;
			$where['DAY(tgl_lahir)'] = array($tgl_lahir, 'where');
		}

		if (!empty($bln_lahir)) {
			$bln_lahir = (int)$bln_lahir;
			$where['MONTH(tgl_lahir)'] = array($bln_lahir, 'where');
		}

		if (!empty($thn_lahir)) {
			$thn_lahir = (int)$thn_lahir;
			$where['YEAR(tgl_lahir)'] = array($thn_lahir, 'where');
		}

		if (!empty($alamat)) {
			$alamat = (string)$alamat;
			$where['kln_pasien.alamat'] = array($alamat, 'like');
		}

		if (!empty($status_id) and is_array($status_id)) {
			$where['kln_pasien.status_id'] = array($status_id, 'where_in');
		}

		if (!empty($username)) {
			$username                = (string)$username;
			$where['login']          = array('kln_pasien.id = login.kln_pasien_id', 'join', 'inner');
			$where['login.username'] = array($username, 'like');
		}

		if (!empty($is_admin)) {
			if (empty($username)) {
				$where['login'] = array('kln_pasien.id = login.kln_pasien_id', 'join', 'inner');
			}
			$where['login.is_admin'] = array($is_admin, 'where');
		}

		if ($pagination) {
			$data = $this->pager->set('kln_pasien', 50, $page_no, $where, $orderby, 'kln_pasien.*');
		} else {
			# cari jumlah semua kln_pasien
			$no_of_records = $this->db->count_all('kln_pasien');
			$search_all    = $this->pager->set('kln_pasien', $no_of_records, 1, $where, $orderby, 'kln_pasien.*');
			$data          = $search_all['results'];
		}

		return $data;
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_pasien');
		return true;
	}


	public function retrieve($id = null, $nip = null)
	{
		if (!is_null($id)) {
			$id = (int)$id;
			$this->db->where('id', $id);
		} else {
			$nip = (int)$nip;
			$this->db->where('nip', $nip);
		}


		$result = $this->db->get('kln_pasien', 1);
		return $result->row_array();
	}


	public function retrieve_by_lokasi_tugas($org_id)
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('kln_pasien a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_kln_pasien_gaji d', 'a.id = d.kln_pasien_id', 'left');
		$this->db->where('lokasi_id', $org_id);
		$this->db->where('a.status_id',1);
		$this->db->order_by('a.nama');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_pasien()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS nama_lokasi');
		$this->db->from('kln_pasien a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id', 'left');
	
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_kln_pasien_aktif()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('kln_pasien a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_kln_pasien_gaji d', 'a.id = d.kln_pasien_id', 'left');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_kln_pasien_aktif_estate()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('kln_pasien a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_kln_pasien_gaji d', 'a.id = d.kln_pasien_id', 'left');
		 $this->db->where('b.tipe','ESTATE');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function update(
		$id,
		$lokasi_id = 0,
		$nip          = null,
		$nama,
		$jenis_kelamin,
		$tempat_lahir = null,
		$tgl_lahir    = null,
		$alamat       = null,
		$foto         = null,
		$status_id    = 0,
		$status_kawin,
		$golongan_darah,
		$telp,
		$email,
		$no_hp,
		$no_ktp,
		$tgl_daftar,
		$tgl_non_aktif,
		$agama,
		$alergi,
		$catatan,
		$penanggung_jawab,
		$diubah_oleh=null

	) {
		$id        = (int)$id;
		$status_id = (int)$status_id;

		$data = array(
			'lokasi_id'     => $lokasi_id,
			'nip'           => $nip,
			'nama'          => $nama,
			'jenis_kelamin' => $jenis_kelamin,
			'tempat_lahir'  => $tempat_lahir,
			'tgl_lahir'     => $tgl_lahir,
			'alamat'        => $alamat,
			'foto'          => $foto,
			'status_id'     => $status_id,
			'status_kawin' => $status_kawin,
			'golongan_darah' => $golongan_darah,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_ktp'   => $no_ktp,
			'tgl_daftar'   => $tgl_daftar,
			'tgl_non_aktif'   => $tgl_non_aktif,
			'alergi'   => $alergi,
			'catatan'   => $catatan,
			'agama'   => $agama,
			'penanggung_jawab'   => $penanggung_jawab,
			'diubah_oleh'=>$diubah_oleh,
			'diubah_tanggal'=>date("Y-m-d H:i:s")

		);
		$this->db->where('id', $id);
		$this->db->update('kln_pasien', $data);
		return true;
	}

	public function create(
		$lokasi_id = 0,
		$nip          = null,
		$nama,
		$jenis_kelamin,
		$tempat_lahir = null,
		$tgl_lahir    = null,
		$alamat       = null,
		$foto         = null,
		$status_id    = 0,
		$status_kawin,
		$golongan_darah,
		$telp,
		$email,
		$no_hp,
		$no_ktp,
		$tgl_daftar,
		$tgl_non_aktif,
		$agama,
		$alergi,
		$catatan,
		$penanggung_jawab,
		$dibuat_oleh=null
	) {
		$status_id = (int)$status_id;
		$data = array(
			'lokasi_id'     => $lokasi_id,
			'nip'           => $nip,
			'nama'          => $nama,
			'jenis_kelamin' => $jenis_kelamin,
			'tempat_lahir'  => $tempat_lahir,
			'tgl_lahir'     => $tgl_lahir,
			'alamat'        => $alamat,
			'foto'          => $foto,
			'status_id'     => $status_id,
			'status_kawin' => $status_kawin,
			'golongan_darah' => $golongan_darah,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_ktp'   => $no_ktp,
			'alergi'   => $alergi,
			'catatan'   => $catatan,
			'penanggung_jawab'   => $penanggung_jawab,
			'tgl_daftar'   => $tgl_daftar,
			'tgl_non_aktif'   => $tgl_non_aktif,
			'agama'   => $agama,
			'dibuat_oleh'=>$dibuat_oleh,
			'dibuat_tanggal'=>date("Y-m-d H:i:s")
		);
		$this->db->insert('kln_pasien', $data);
		return $this->db->insert_id();
	}
	public function retrieve_role($id)
	{
		$this->db->where('kln_pasien_id', $id);
		$this->db->order_by('role', 'ASC');
		$result = $this->db->get('kln_pasien_role');
		return $result->result_array();
	}
	public function delete_role(
		$id

	) {
		$id        = (int)$id;

		$this->db->where('kln_pasien_id', $id);
		$this->db->delete('kln_pasien_role');
		return true;
	}
	public function insert_role(
		$id,
		$role          = null
	) {
		$id        = (int)$id;

		$data = array(
			'role'           => $role,
			'kln_pasien_id' => $id
		);

		$this->db->insert('kln_pasien_role', $data);
		return true;
	}
}
