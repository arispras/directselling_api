<?php

class HrmsResumeModel extends CI_Model
{

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('hrms_resume', array('foto' => null));
		return true;
	}


	public function retrieve_all_by_name($nama)
	{
		$search_result = $this->retrieve_all_filter(
			$no_resume           = '',
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
		$no_resume           = '',
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
		$orderby['hrms_resume.id'] = 'DESC';

		if (!empty($no_resume)) {
			$no_resume = (int)$no_resume;
			$where['hrms_resume.no_resume'] = array($no_resume, 'like', 'after');
		}

		if (!empty($nama)) {
			$nama = (string)$nama;
			$where['hrms_resume.nama'] = array($nama, 'like');
		}

		if (!empty($jenis_kelamin) and is_array($jenis_kelamin)) {
			$where['hrms_resume.jenis_kelamin'] = array($jenis_kelamin, 'where_in');
		}

		if (!empty($tempat_lahir)) {
			$tempat_lahir = (string)$tempat_lahir;
			$where['hrms_resume.tempat_lahir'] = array($tempat_lahir, 'like');
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
			$where['hrms_resume.alamat'] = array($alamat, 'like');
		}

		if (!empty($status_id) and is_array($status_id)) {
			$where['hrms_resume.status_id'] = array($status_id, 'where_in');
		}

		if (!empty($username)) {
			$username                = (string)$username;
			$where['login']          = array('hrms_resume.id = login.resume_id', 'join', 'inner');
			$where['login.username'] = array($username, 'like');
		}

		if (!empty($is_admin)) {
			if (empty($username)) {
				$where['login'] = array('hrms_resume.id = login.resume_id', 'join', 'inner');
			}
			$where['login.is_admin'] = array($is_admin, 'where');
		}

		if ($pagination) {
			$data = $this->pager->set('hrms_resume', 50, $page_no, $where, $orderby, 'hrms_resume.*');
		} else {
			# cari jumlah semua hrms_resume
			$no_of_records = $this->db->count_all('hrms_resume');
			$search_all    = $this->pager->set('hrms_resume', $no_of_records, 1, $where, $orderby, 'hrms_resume.*');
			$data          = $search_all['results'];
		}

		return $data;
	}


	public function delete($id)
	{
		$id = (int)$id;
		$resume_id = $id;


		// $this->db->where('resume_id', $resume_id);
		// $this->db->delete('payroll_riwayat_bahasa');

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pendidikan');


		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pengalaman');

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pelatihan');

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_profesional');

		$this->db->where('id', $id);
		$this->db->delete('hrms_resume');
		return true;
	}


	public function retrieve($id = null, $no_resume = null)
	{
		if (!is_null($id)) {
			$id = (int)$id;
			$this->db->where('id', $id);
		} else {
			$no_resume = (int)$no_resume;
			$this->db->where('no_resume', $no_resume);
		}


		$result = $this->db->get('hrms_resume', 1);
		return $result->row_array();
	}


	public function retrieve_by_lokasi_tugas($org_id)
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('hrms_resume a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_hrms_resume_gaji d', 'a.id = d.resume_id', 'left');
		$this->db->where('lokasi_tugas_id', $org_id);
		$this->db->where('a.status_id', 1);
		$this->db->order_by('a.nama');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_hrms_resume()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('hrms_resume a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_hrms_resume_gaji d', 'a.id = d.resume_id', 'left');
		// $this->db->from('hrms_resume');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_hrms_resume_aktif()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, e.nama as jabatan, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('hrms_resume a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_hrms_resume_gaji d', 'a.id = d.resume_id', 'left');
		$this->db->join('payroll_jabatan e', 'a.jabatan_id = e.id', 'left');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_hrms_resume_aktif_estate()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('hrms_resume a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_hrms_resume_gaji d', 'a.id = d.resume_id', 'left');
		$this->db->where('b.tipe', 'ESTATE');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function update(
		$id,
		$no_resume          = null,
		$nama,
		$jenis_kelamin,
		$tempat_lahir = null,
		$tgl_lahir    = null,
		$alamat       = null,
		// $foto         = null,
		$status_kawin,
		$golongan_darah,
		$posisi_id    = 0,
		$telp,
		$email,
		$no_hp,
		$no_kk,
		$no_ktp,
		$tgl_terima,
		$status_pajak,
		$agama,
		$pendidikan_terakhir,
		$status_resume,
		$tgl_status_resume,
		$alokasi_karyawan,
		$diubah_oleh = null

	) {
		$id        = (int)$id;
		$data = array(
			'no_resume'           => $no_resume,
			'nama'          => $nama,
			'jenis_kelamin' => $jenis_kelamin,
			'tempat_lahir'  => $tempat_lahir,
			'tgl_lahir'     => $tgl_lahir,
			'alamat'        => $alamat,
			// 'foto'          => $foto,
			'tgl_terima'     => $tgl_terima,
			'status_kawin' => $status_kawin,
			'golongan_darah' => $golongan_darah,
			'posisi_id'    => $posisi_id,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_kk'   => $no_kk,
			'no_ktp'   => $no_ktp,
			'status_pajak'   => $status_pajak,
			'agama'   => $agama,
			'pendidikan_terakhir'   => $pendidikan_terakhir,
			'status_resume'   => $status_resume,
			'tgl_status_resume'   => $tgl_status_resume,
			'alokasi_karyawan'=>$alokasi_karyawan,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => date("Y-m-d H:i:s")

		);
		$this->db->where('id', $id);
		$this->db->update('hrms_resume', $data);
		return true;
	}


	public function create(
		$no_resume          = null,
		$nama,
		$jenis_kelamin,
		$tempat_lahir = null,
		$tgl_lahir    = null,
		$alamat       = null,
		$foto         = null,
		$status_kawin,
		$golongan_darah,
		$posisi_id    = 0,
		$telp,
		$email,
		$no_hp,
		$no_kk,
		$no_ktp,
		$tgl_terima,
		$status_pajak,
		$agama,
		$pendidikan_terakhir,
		$status_resume,
		$tgl_status_resume,
		$alokasi_karyawan,
		$dibuat_oleh = null
	) {
		$data = array(
			'no_resume'           => $no_resume,
			'nama'          => $nama,
			'jenis_kelamin' => $jenis_kelamin,
			'tempat_lahir'  => $tempat_lahir,
			'tgl_lahir'     => $tgl_lahir,
			'alamat'        => $alamat,
			'foto'          => $foto,
			'tgl_terima'     => $tgl_terima,
			'status_kawin' => $status_kawin,
			'golongan_darah' => $golongan_darah,
			'posisi_id'    => $posisi_id,
			'telp'   => $telp,
			'email'   => $email,
			'no_hp'   => $no_hp,
			'no_kk'   => $no_kk,
			'no_ktp'   => $no_ktp,
			'status_pajak'   => $status_pajak,
			'agama'   => $agama,
			'pendidikan_terakhir'   => $pendidikan_terakhir,
			'status_resume'   => $status_resume,
			'tgl_status_resume'   => $tgl_status_resume,
			'alokasi_karyawan'=>$alokasi_karyawan,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => date("Y-m-d H:i:s")
		);
		$this->db->insert('hrms_resume', $data);
		return $this->db->insert_id();
	}
}
