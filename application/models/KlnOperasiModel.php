<?php


class KlnOperasiModel extends CI_Model
{

    

    public function retrieve_all()
    {
		$this->db->select('kln_operasi.*,kln_jenis_biaya.kode as uom');
		$this->db->from('kln_operasi');
		$this->db->join('kln_jenis_biaya', 'kln_operasi.biaya_id = kln_jenis_biaya.id', 'left');
		$this->db->order_by('kln_operasi.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
	

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_operasi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'biaya_id' => $input['biaya_id']['id'],
			'metode' => $input['metode'],
            'unit' => $input['unit'],
            'keterangan' => $input['keterangan'],
			'harga' => $input['harga'],
			'diskon' => $input['diskon'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('kln_operasi', $data);
        return true;
    }


    //  Method untuk mengambil satu record kln_operasi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_operasi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data kln_operasi

    public function create($input)
    {
        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'biaya_id' => $input['biaya_id']['id'],
			'metode' => $input['metode'],
            'unit' => $input['unit'],
            'keterangan' => $input['keterangan'],
			'harga' => $input['harga'],
			'diskon' => $input['diskon'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->insert('kln_operasi', $data);
        return $this->db->insert_id();
    }
}
