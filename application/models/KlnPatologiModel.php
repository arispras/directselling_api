<?php


class KlnPatologiModel extends CI_Model
{

   
    public function retrieve_all()
    {
        $this->db->select('kln_patologi.*,kln_jenis_biaya.kode as uom');
        $this->db->from('kln_patologi');
        $this->db->join('kln_jenis_biaya', 'kln_patologi.biaya_id = kln_jenis_biaya.id', 'left');
        $this->db->order_by('kln_patologi.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_patologi');
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
            'diubah_oleh' => $input['diubah_oleh'],
            'diubah_tanggal' => date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('kln_patologi', $data);
        return true;
    }


    //  Method untuk mengambil satu record kln_patologi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_patologi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data kln_patologi

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
            'dibuat_oleh' => $input['dibuat_oleh'],
            'dibuat_tanggal' => date("Y-m-d H:i:s"),
            'diubah_oleh' => $input['diubah_oleh'],
            'diubah_tanggal' => date("Y-m-d H:i:s"),
        );
        $this->db->insert('kln_patologi', $data);
        return $this->db->insert_id();
    }
}
