<?php


class KlnKasurModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_kasur');
		return true;
	}


	public function retrieve_all_old(
		$no_of_records = 10,
		$page_no       = 1,
		$nama   = '',
		$kode = '',
		$ruangan_id = array(),
		$pagination    = true
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where    = array();
		$group_by = array();

		$where['inv_kategori'] = array('kln_kasur.ruangan_id = inv_kategori.id', 'join', 'left');
		if (!empty($ruangan_id)) {
			$where['kln_kasur.ruangan_id'] = array($ruangan_id, 'where_in');
		}

		$like = 0;
		if (!empty($nama)) {
			$where['kln_kasur.nama'] = array($nama, 'like');
			$like = 1;
		}
		if (!empty($kode)) {
			if ($like) {
				$value = array($kode, 'or_like');
			} else {
				$value = array($kode, 'like');
			}
			$where['kln_kasur.kode'] = $value;
		}
		$orderby = array(
			'kln_kasur.id' => 'DESC'
		);

		if ($pagination) {
			$data = $this->pager->set('kln_kasur', $no_of_records, $page_no, $where, $orderby, 'kln_kasur.*', $group_by);
		} else {
			# cari jumlah semua pengajar
			$no_of_records = $this->db->count_all('kln_kasur');
			$search_all    = $this->pager->set('kln_kasur', $no_of_records, $page_no, $where, $orderby, 'kln_kasur.*', $group_by);
			$data          = $search_all['results'];
		}

		return $data;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('kln_kasur', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$this->db->select('a.*,b.nama as nama_kelas,c.nama as nama_ruangan');
		$this->db->from('kln_kasur a');
		$this->db->join("kln_kelas b", "b.id = a.kelas_id", "left");
		$this->db->join("kln_ruangan c", "c.id = a.ruangan_id", "left");
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}
	
	public function create(
		$arrdata
	) {

		$kode = $arrdata['kode'];
		$nama    =  $arrdata['nama'];
		$ruangan_id  = (int) $arrdata['ruangan_id'];
		$kelas_id    = (int)  $arrdata['kelas_id'];
		$aktif    =  $arrdata['aktif'];

		$data = array(
			'nama' => $nama,
			'ruangan_id'    => $ruangan_id,
			'kelas_id'    => $kelas_id,
			'aktif' => $aktif
		);
		$this->db->insert('kln_kasur', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$nama    =  $arrdata['nama'];
		$ruangan_id  = (int) $arrdata['ruangan_id'];
		$kelas_id    = (int) $arrdata['kelas_id'];
		$aktif    =  $arrdata['aktif'];

		$data = array(
			'nama' => $nama,
			'ruangan_id'    => $ruangan_id,
			'kelas_id'    => $kelas_id,
			'aktif' => $aktif
		);
		$this->db->where('id', $id);
		$this->db->update('kln_kasur', $data);
		return true;
	}
	
	
}
