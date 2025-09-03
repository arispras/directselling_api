<?php


class AccUangMukaModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('acc_uang_muka', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data acc_uang_muka tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('acc_uang_muka');
        return $result->result_array();
    }


    //   Method untuk menghapus record acc_uang_muka

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_uang_muka');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id'],
            'acc_akun_id' => $input['acc_akun_id'],
            'acc_akun_kasbank_id' => $input['acc_akun_kasbank_id'],
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
        $this->db->update('acc_uang_muka', $data);
        return true;
    }


    //  Method untuk mengambil satu record acc_uang_muka

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_uang_muka', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_uang_muka

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id'],
            'acc_akun_id' => $input['acc_akun_id'],
            'acc_akun_kasbank_id' => $input['acc_akun_kasbank_id'],
            'tanggal' => $input['tanggal'],
            'no_transaksi' => $input['no_transaksi'],
            'nilai' => $input['nilai'],
            'keterangan' => $input['keterangan'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
        );
        $this->db->insert('acc_uang_muka', $data);
        return $this->db->insert_id();
    }


    public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_uang_muka', $data);

		return true;
	}
}
