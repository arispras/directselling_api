<?php

class KaryawanModel extends CI_Model
{

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('karyawan', array('foto' => null));
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
		$orderby['karyawan.id'] = 'DESC';

		if (!empty($nip)) {
			$nip = (int)$nip;
			$where['karyawan.nip'] = array($nip, 'like', 'after');
		}

		if (!empty($nama)) {
			$nama = (string)$nama;
			$where['karyawan.nama'] = array($nama, 'like');
		}

		if (!empty($jenis_kelamin) and is_array($jenis_kelamin)) {
			$where['karyawan.jenis_kelamin'] = array($jenis_kelamin, 'where_in');
		}

		if (!empty($tempat_lahir)) {
			$tempat_lahir = (string)$tempat_lahir;
			$where['karyawan.tempat_lahir'] = array($tempat_lahir, 'like');
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
			$where['karyawan.alamat'] = array($alamat, 'like');
		}

		if (!empty($status_id) and is_array($status_id)) {
			$where['karyawan.status_id'] = array($status_id, 'where_in');
		}

		if (!empty($username)) {
			$username                = (string)$username;
			$where['login']          = array('karyawan.id = login.karyawan_id', 'join', 'inner');
			$where['login.username'] = array($username, 'like');
		}

		if (!empty($is_admin)) {
			if (empty($username)) {
				$where['login'] = array('karyawan.id = login.karyawan_id', 'join', 'inner');
			}
			$where['login.is_admin'] = array($is_admin, 'where');
		}

		if ($pagination) {
			$data = $this->pager->set('karyawan', 50, $page_no, $where, $orderby, 'karyawan.*');
		} else {
			# cari jumlah semua karyawan
			$no_of_records = $this->db->count_all('karyawan');
			$search_all    = $this->pager->set('karyawan', $no_of_records, 1, $where, $orderby, 'karyawan.*');
			$data          = $search_all['results'];
		}

		return $data;
	}


	public function delete($id)
	{
		$id = (int)$id;
		$karyawan_id=$id;
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keluarga');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_bahasa');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pendidikan');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_jabatan');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_kepangkatan');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_penghargaan');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_hukuman');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pengalaman');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pelatihan');

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keahlian');

		$this->db->where('id', $id);
		$this->db->delete('karyawan');
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


		$result = $this->db->get('karyawan', 1);
		return $result->row_array();
	}


	public function retrieve_by_lokasi_tugas($org_id)
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('karyawan a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_karyawan_gaji d', 'a.id = d.karyawan_id', 'left');
		$this->db->where('lokasi_tugas_id', $org_id);
		$this->db->where('a.status_id',1);
		$this->db->order_by('a.nama');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_karyawan()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('karyawan a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_karyawan_gaji d', 'a.id = d.karyawan_id', 'left');
		// $this->db->from('karyawan');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_karyawan_aktif()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('karyawan a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_karyawan_gaji d', 'a.id = d.karyawan_id', 'left');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_karyawan_aktif_estate()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('karyawan a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_karyawan_gaji d', 'a.id = d.karyawan_id', 'left');
		 $this->db->where('b.tipe','ESTATE');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function update(
		$id,
		$lokasi_tugas_id    = 0,
		$sub_bagian_id = 0,
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
		$jabatan_id    = 0,
		$departemen_id    = 0,
		$golongan_id    = 0,
		$pangkat_id    = 0,
		$tipe_karyawan_id    = 0,
		$telp,
		$email,
		$no_hp,
		$no_npwp,
		$no_kk,
		$no_ktp,
		$no_rek_bank,
		$nama_bank,
		$no_bpjs,
		$no_bpjs_ks,
		$tgl_masuk,
		$tgl_keluar,
		$tgl_hbs_kontrak,
		$status_pajak,
		$agama,
		$is_jht,
		$is_jp,
		$is_jks,
		$diubah_oleh=null

	) {
		$id        = (int)$id;
		$status_id = (int)$status_id;

		$data = array(
			'lokasi_tugas_id'     => $lokasi_tugas_id,
			'sub_bagian_id'     => $sub_bagian_id,
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
			'jabatan_id'    => $jabatan_id,
			'departemen_id'    => $departemen_id,
			'golongan_id'   => $golongan_id,
			'pangkat_id'    => $pangkat_id,
			'tipe_karyawan_id'   => $tipe_karyawan_id,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_npwp'   => $no_npwp,
			'no_kk'   => $no_kk,
			'no_ktp'   => $no_ktp,
			'no_rek_bank'   => $no_rek_bank,
			'nama_bank'   => $nama_bank,
			'no_bpjs'   => $no_bpjs,
			'no_bpjs_ks'   => $no_bpjs_ks,
			'tgl_masuk'   => $tgl_masuk,
			'tgl_keluar'   => $tgl_keluar,
			'tgl_hbs_kontrak'   => $tgl_hbs_kontrak,
			'status_pajak'   => $status_pajak,
			'agama'   => $agama,
			'is_jht'   => $is_jht,
			'is_jp'   => $is_jp,
			'is_jks'   => $is_jks,
			'diubah_oleh'=>$diubah_oleh,
			'diubah_tanggal'=>date("Y-m-d H:i:s")

		);
		$this->db->where('id', $id);
		$this->db->update('karyawan', $data);
		return true;
	}

	public function create(
		$lokasi_tugas_id = 0,
		$sub_bagian_id = 0,
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
		$jabatan_id    = 0,
		$departemen_id    = 0,
		$golongan_id    = 0,
		$pangkat_id    = 0,
		$tipe_karyawan_id    = 0,
		$telp,
		$email,
		$no_hp,
		$no_npwp,
		$no_kk,
		$no_ktp,
		$no_rek_bank,
		$nama_bank,
		$no_bpjs,
		$no_bpjs_ks,
		$tgl_masuk,
		$tgl_keluar,
		$tgl_hbs_kontrak,
		$status_pajak,
		$agama,
		$is_jht,
		$is_jp,
		$is_jks,
		$dibuat_oleh=null
	) {
		$status_id = (int)$status_id;
		$data = array(
			'lokasi_tugas_id'     => $lokasi_tugas_id,
			'sub_bagian_id'     => $sub_bagian_id,
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
			'jabatan_id'    => $jabatan_id,
			'departemen_id'    => $departemen_id,
			'golongan_id'   => $golongan_id,
			'pangkat_id'    => $pangkat_id,
			'tipe_karyawan_id'   => $tipe_karyawan_id,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_npwp'   => $no_npwp,
			'no_kk'   => $no_kk,
			'no_ktp'   => $no_ktp,
			'no_rek_bank'   => $no_rek_bank,
			'nama_bank'   => $nama_bank,
			'no_bpjs'   => $no_bpjs,
			'no_bpjs_ks'   => $no_bpjs_ks,
			'tgl_masuk'   => $tgl_masuk,
			'tgl_keluar'   => $tgl_keluar,
			'tgl_hbs_kontrak'   => $tgl_hbs_kontrak,
			'status_pajak'   => $status_pajak,
			'agama'   => $agama,
			'is_jht'   => $is_jht,
			'is_jp'   => $is_jp,
			'is_jks'   => $is_jks,
			'dibuat_oleh'=>$dibuat_oleh,
			'dibuat_tanggal'=>date("Y-m-d H:i:s")
		);
		$this->db->insert('karyawan', $data);
		return $this->db->insert_id();
	}
	public function retrieve_role($id)
	{
		$this->db->where('karyawan_id', $id);
		$this->db->order_by('role', 'ASC');
		$result = $this->db->get('karyawan_role');
		return $result->result_array();
	}
	public function delete_role(
		$id

	) {
		$id        = (int)$id;

		$this->db->where('karyawan_id', $id);
		$this->db->delete('karyawan_role');
		return true;
	}
	public function insert_role(
		$id,
		$role          = null
	) {
		$id        = (int)$id;

		$data = array(
			'role'           => $role,
			'karyawan_id' => $id
		);

		$this->db->insert('karyawan_role', $data);
		return true;
	}
}
