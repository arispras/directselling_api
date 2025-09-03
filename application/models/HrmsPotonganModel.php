<?php


class HrmsPotonganModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('payroll_potongan');
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

		$where['inv_kategori'] = array('payroll_potongan.inv_kategori_id = inv_kategori.id', 'join', 'left');
		if (!empty($inv_kategori_id)) {
			$where['payroll_potongan.inv_kategori_id'] = array($inv_kategori_id, 'where_in');
		}

		$like = 0;
		if (!empty($nama)) {
			$where['payroll_potongan.nama'] = array($nama, 'like');
			$like = 1;
		}
		if (!empty($kode)) {
			if ($like) {
				$value = array($kode, 'or_like');
			} else {
				$value = array($kode, 'like');
			}
			$where['payroll_potongan.kode'] = $value;
		}
		$orderby = array(
			'payroll_potongan.id' => 'DESC'
		);

		if ($pagination) {
			$data = $this->pager->set('payroll_potongan', $no_of_records, $page_no, $where, $orderby, 'payroll_potongan.*', $group_by);
		} else {
			# cari jumlah semua pengajar
			$no_of_records = $this->db->count_all('payroll_potongan');
			$search_all    = $this->pager->set('payroll_potongan', $no_of_records, $page_no, $where, $orderby, 'payroll_potongan.*', $group_by);
			$data          = $search_all['results'];
		}

		return $data;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('payroll_potongan', 1);
		return $result->row_array();
	}
	public function retrieve_all_item()
	{
		$this->db->order_by('nama', 'ASC');
		$this->db->get('payroll_potongan');
		$result = $this->db->get('payroll_potongan');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {

		$tanggal = $arrdata['tanggal'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$nilai_potongan    =  $arrdata['nilai_potongan'];
		$tipe_gaji_id    =  $arrdata['tipe_gaji_id'];
		$keterangan    =  $arrdata['keterangan'];
		$data = array(
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai' => $nilai_potongan,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'tipe_gaji_id' => $tipe_gaji_id
		);
		$this->db->insert('payroll_potongan', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;

		$tanggal = $arrdata['tanggal'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$nilai_potongan    =  $arrdata['nilai_potongan'];
		$tipe_gaji_id    =  $arrdata['tipe_gaji_id'];
		$keterangan    =  $arrdata['keterangan'];
		$data = array(
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai' => $nilai_potongan,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'tipe_gaji_id' => $tipe_gaji_id
		);
		$this->db->where('id', $id);
		$this->db->update('payroll_potongan', $data);
		return true;
	}
}
