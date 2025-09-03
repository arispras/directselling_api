<?php

class InvPermintaanPindahGudangModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'tipe' => $input['tipe']['id'],
			'dari_gudang_id' => $input['dari_gudang_id']['id'],
			'ke_gudang_id' => $input['ke_gudang_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'nama_peminta' => $input['nama_peminta'],
			'catatan' => $input['catatan'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_permintaan_pindah_gudang_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_permintaan_pindah_gudang_dt", array(
				'inv_permintaan_pindah_gudang_id' => $id,
				'item_id' => $value['item_id']['id'],				
				'qty' => $value['qty'],
				'ket' => $value['ket'],
			));
		}


		return $id;
	}
	public function update($id,	$input) 
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['tipe'] = $input['tipe']['id'];
		$data['ke_gudang_id'] = $input['ke_gudang_id']['id'];
		$data['dari_gudang_id'] = $input['dari_gudang_id']['id'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['nama_peminta'] = $input['nama_peminta'];
		$data['catatan'] = $input['catatan'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_permintaan_pindah_gudang_ht', $data);

		// hapus  detail
		$this->db->where('inv_permintaan_pindah_gudang_id', $id);
		$this->db->delete('inv_permintaan_pindah_gudang_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_permintaan_pindah_gudang_dt", array(
				'inv_permintaan_pindah_gudang_id' => $id,
				'item_id' => $value['item_id']['id'],
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
		$this->db->update('inv_permintaan_pindah_gudang_ht', $data);

		
		return true;
	}
	public function delete($id)
	{
		$this->db->where('inv_permintaan_pindah_gudang_id', $id);
		$this->db->delete('inv_permintaan_pindah_gudang_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_permintaan_pindah_gudang_ht');
		return true;
	}

	public function retrieve_all()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('inv_permintaan_pindah_gudang_ht');
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
		$this->db->from('inv_permintaan_pindah_gudang_dt');
		$this->db->where('inv_permintaan_pindah_gudang_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_permintaan_pindah_gudang_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('inv_permintaan_pindah_gudang_dt');
		$this->db->where('inv_permintaan_pindah_gudang_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


}
