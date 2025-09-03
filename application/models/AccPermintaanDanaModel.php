<?php


class AccPermintaanDanaModel extends CI_Model
{

    
	
    //  Method untuk mendapatkan semua data acc_permintaan_dana tanpa pagging

    public function retrieve_all()
    {
        $this->db->order_by('no_transaksi', 'ASC');
        $result = $this->db->get('acc_permintaan_dana');
        return $result->result_array();
    }

//  Method untuk mendapatkan semua data acc_permintaan_dana tanpa pagging

public function retrieve_all_by_unit($lokasi_id)
{
	$this->db->where ('lokasi_id', $lokasi_id);
	$this->db->order_by('no_transaksi', 'ASC');
	$result = $this->db->get('acc_permintaan_dana');
	return $result->result_array();
}

    //   Method untuk menghapus record acc_permintaan_dana

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_permintaan_dana');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id'],
             'no_transaksi' => $input['no_transaksi'],
            'tanggal' => $input['tanggal'],
            'nilai' => $input['nilai'],
            'keterangan' => $input['keterangan'],
			// 'dibuat_tanggal' => date('Y-m-d H:i:s'),
			// 'dibuat_oleh' =>  $input['dibuat_oleh'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh']
        );
        $this->db->where('id', $id);
        $this->db->update('acc_permintaan_dana', $data);
        return true;
    }

	public function save_upload($inv, $file)
	{
		$id = (int) $inv['id'];
	
		$data['upload_file'] = $file;

		$this->db->where('id', $id);
		$this->db->update('acc_permintaan_dana', $data);

		return true;
	}

    //  Method untuk mengambil satu record acc_permintaan_dana

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_permintaan_dana', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_permintaan_dana

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id'],
            'tanggal' => $input['tanggal'],
            'no_transaksi' => $input['no_transaksi'],
            'nilai' => $input['nilai'],
            'keterangan' => $input['keterangan'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
        );
        $this->db->insert('acc_permintaan_dana', $data);
        return $this->db->insert_id();
    }


    public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_permintaan_dana', $data);

		return true;
	}
}
