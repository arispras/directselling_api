<?php

class InvPermintaanBarangModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'gudang_id' => $input['gudang_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			'lokasi_afd_id' => $input['lokasi_afd_id']['id'],
			'lokasi_traksi_id' => $input['lokasi_traksi_id']['id'],
			'tipe' => $input['tipe']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'catatan' => $input['catatan'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_permintaan_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_permintaan_dt", array(
				'inv_permintaan_id' => $id,
				'item_id' => $value['item_id']['id'],
				
				'qty' => $value['qty'],
				'traksi_id' => $value['traksi_id']['id'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'ket' => $value['ket'],
			));
		}


		return $id;
	}
	public function update($id,	$input) 
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['lokasi_afd_id'] = $input['lokasi_afd_id']['id'];
		$data['lokasi_traksi_id'] = $input['lokasi_traksi_id']['id'];
		$data['gudang_id'] = $input['gudang_id']['id'];
		$data['karyawan_id'] = $input['karyawan_id']['id'];
		$data['tipe'] = $input['tipe']['id'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['catatan'] = $input['catatan'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_permintaan_ht', $data);

		// hapus  detail
		$this->db->where('inv_permintaan_id', $id);
		$this->db->delete('inv_permintaan_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_permintaan_dt", array(
				'inv_permintaan_id' => $id,
				'item_id' => $value['item_id']['id'],
			
				'traksi_id' => $value['traksi_id']['id'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'qty' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}

	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('inv_permintaan_ht', $data);
		

		return true;
	}
	public function delete($id)
	{
		$this->db->where('inv_permintaan_id', $id);
		$this->db->delete('inv_permintaan_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_permintaan_ht');
		return true;
	}

	public function retrieve_all()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('inv_permintaan_ht');
        return $result->result_array();
    }

	public function retrieve_traksi()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('trk_kendaraan');
        return $result->result_array();
    }
	
	public function retrieve_all_detail($id)
	{
		$this->db->from('inv_permintaan_dt');
		$this->db->where('inv_permintaan_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_permintaan_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('inv_permintaan_dt');
		$this->db->where('inv_permintaan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,
		b.nama as lokasi_afd,
		b.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM inv_permintaan_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		LEFT JOIN gbm_organisasi bb ON a.lokasi_traksi_id=bb.id
		LEFT JOIN gbm_organisasi d ON a.gudang_id=d.id
		LEFT JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}



