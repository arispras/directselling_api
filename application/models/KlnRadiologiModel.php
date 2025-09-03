<?php


class KlnRadiologiModel extends CI_Model
{

    public function retrieve_all()
    {
		$this->db->select('kln_radiologi.*,kln_jenis_biaya.kode as uom');
		$this->db->from('kln_radiologi');
		$this->db->join('kln_jenis_biaya', 'kln_radiologi.biaya_id = kln_jenis_biaya.id', 'left');
		$this->db->order_by('kln_radiologi.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
	

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_radiologi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'kategori_id' => $input['kategori_id']['id'],
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
        $this->db->update('kln_radiologi', $data);
        return true;
    }


    //  Method untuk mengambil satu record kln_radiologi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_radiologi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data kln_radiologi

    public function create($input)
    {
        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'kategori_id' => $input['kategori_id']['id'],
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
        $this->db->insert('kln_radiologi', $data);
        return $this->db->insert_id();
    }
}
