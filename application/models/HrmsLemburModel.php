<?php


class HrmsLemburModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('payroll_lembur');
		return true;
	}


	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1,
		$nama   = '',
		$kode = '',
		$inv_kategori_id = array(),
		$pagination    = true
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where    = array();
		$group_by = array();

		$where['inv_kategori'] = array('payroll_lembur.inv_kategori_id = inv_kategori.id', 'join', 'left');
		if (!empty($inv_kategori_id)) {
			$where['payroll_lembur.inv_kategori_id'] = array($inv_kategori_id, 'where_in');
		}

		$like = 0;
		if (!empty($nama)) {
			$where['payroll_lembur.nama'] = array($nama, 'like');
			$like = 1;
		}
		if (!empty($kode)) {
			if ($like) {
				$value = array($kode, 'or_like');
			} else {
				$value = array($kode, 'like');
			}
			$where['payroll_lembur.kode'] = $value;
		}
		$orderby = array(
			'payroll_lembur.id' => 'DESC'
		);

		if ($pagination) {
			$data = $this->pager->set('payroll_lembur', $no_of_records, $page_no, $where, $orderby, 'payroll_lembur.*', $group_by);
		} else {
			# cari jumlah semua pengajar
			$no_of_records = $this->db->count_all('payroll_lembur');
			$search_all    = $this->pager->set('payroll_lembur', $no_of_records, $page_no, $where, $orderby, 'payroll_lembur.*', $group_by);
			$data          = $search_all['results'];
		}

		return $data;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('payroll_lembur', 1);
		return $result->row_array();
	}
	public function retrieve_all_item()
	{
		$this->db->order_by('nama', 'ASC');
		$this->db->get('payroll_lembur');
		$result = $this->db->get('payroll_lembur');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {

		$tanggal = $arrdata['tanggal'];
		$mulai    =  $arrdata['mulai'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$selesai    =  $arrdata['selesai'];
		$jumlah_jam    =  $arrdata['jumlah_jam'];
		$nilai_lembur    =  $arrdata['nilai_lembur'];
		$tipe_lembur    =  $arrdata['tipe_lembur'];
		$istirahat    =  $arrdata['istirahat'];
		// $lembur_perjam    =  $arrdata['lembur_perjam'];
		// $basis_lembur_id    =  $arrdata['basis_lembur_id'];

		$data = array(
			'selesai' => $selesai,
			'mulai' => $mulai,
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai_lembur' => $nilai_lembur,
			// 'lembur_perjam'=>$lembur_perjam,
			'tanggal' => $tanggal,
			'jumlah_jam' => $jumlah_jam,
			'istirahat' => $istirahat,
			'tipe_lembur' => $tipe_lembur
		);
		$this->db->insert('payroll_lembur', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;

		$tanggal = $arrdata['tanggal'];
		$mulai    =  $arrdata['mulai'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$selesai    =  $arrdata['selesai'];
		$jumlah_jam    =  $arrdata['jumlah_jam'];;
		$nilai_lembur    =  $arrdata['nilai_lembur'];
		// //  $lembur_perjam    =  $arrdata['lembur_perjam'];
		// $basis_lembur_id    =  $arrdata['basis_lembur_id'];
		$tipe_lembur    =  $arrdata['tipe_lembur'];
		$istirahat    =  $arrdata['istirahat'];

		$data = array(
			'selesai' => $selesai,
			'mulai' => $mulai,
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai_lembur' => $nilai_lembur,
			//  'lembur_perjam'=>$lembur_perjam,
			'tanggal' => $tanggal,
			'jumlah_jam' => $jumlah_jam,
			// 'basis_lembur_id'=>$basis_lembur_id,
			'istirahat' => $istirahat,
			'tipe_lembur' => $tipe_lembur

		);
		$this->db->where('id', $id);
		$this->db->update('payroll_lembur', $data);
		return true;
	}
}
