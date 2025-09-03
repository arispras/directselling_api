<?php

class AccPermohonanBayarModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'tanggal' => $input['tanggal'],
			'supplier_id' => $input['supplier_id']['id'],
			'supplier' => $input['supplier'],
			'no_transaksi' => $input['no_transaksi'],
			'no_referensi' => $input['no_referensi'],
			'diminta_oleh' => $input['diminta_oleh'],
			'divisi' => $input['divisi'],
			'periode' => $input['periode'],
			'ket' => $input['ket'],

			'nama_bank' => $input['nama_bank'],
			'no_rek' => $input['no_rek'],
			'atas_nama' => $input['atas_nama'],
			
			'subtotal' => $input['subtotal'],
			'diskon' => $input['diskon'],
			'dpp' => $input['dpp'],
			'pph' => $input['pph'],
			'ppn' => $input['ppn'],
			'total' => $input['total'],
			
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('acc_permohonan_bayar_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_permohonan_bayar_dt", array(
				'permohonan_bayar_id' => $id,
				'keterangan' => $value['keterangan'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'jumlah' => $value['jumlah'],
				
			));
		}
		return $id;
	}
	public function update($id,	$input) 
	{
		$id = (int)$id;
		$data['supplier'] = $input['supplier'];
		$data['supplier_id'] = $input['supplier_id']['id'];
		$data['tanggal'] = $input['tanggal'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['no_referensi'] = $input['no_referensi'];
		$data['diminta_oleh'] = $input['diminta_oleh'];
		$data['divisi'] = $input['divisi'];
		$data['periode'] = $input['periode'];
		$data['ket'] = $input['ket'];
		
		$data['nama_bank'] = $input['nama_bank'];
		$data['no_rek'] = $input['no_rek'];
		$data['atas_nama'] = $input['atas_nama'];


		$data['subtotal'] = $input['subtotal'];
		$data['diskon'] = $input['diskon'];
		$data['dpp'] = $input['dpp'];
		$data['pph'] = $input['pph'];
		$data['ppn'] = $input['ppn'];
		$data['total'] = $input['total'];

		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('acc_permohonan_bayar_ht', $data);

		// hapus  detail
		$this->db->where('permohonan_bayar_id', $id);
		$this->db->delete('acc_permohonan_bayar_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_permohonan_bayar_dt", array(
				'permohonan_bayar_id' => $id,
				'keterangan' => $value['keterangan'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'jumlah' => $value['jumlah'],
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
		$this->db->update('acc_permohonan_bayar_ht', $data);
		

		return true;
	}
	public function delete($id)
	{
		$this->db->where('permohonan_bayar_id', $id);
		$this->db->delete('acc_permohonan_bayar_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_permohonan_bayar_ht');
		return true;
	}

	public function retrieve_all()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('acc_permohonan_bayar_ht');
        return $result->result_array();
    }
	
	public function retrieve_all_detail($id)
	{
		$this->db->from('acc_permohonan_bayar_dt');
		$this->db->where('permohonan_bayar_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('acc_permohonan_bayar_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('acc_permohonan_bayar_dt');
		$this->db->where('permohonan_bayar_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	
}



