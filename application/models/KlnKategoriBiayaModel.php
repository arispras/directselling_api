<?php


class KlnKategoriBiayaModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('kln_kategori_biaya', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data kln_kategori_biaya tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('kln_kategori_biaya');
        return $result->result_array();
    }


    //   Method untuk menghapus record kln_kategori_biaya

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_kategori_biaya');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('kln_kategori_biaya', $data);
        return true;
    }


    //  Method untuk mengambil satu record kln_kategori_biaya

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_kategori_biaya', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data kln_kategori_biaya

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),

        );
        $this->db->insert('kln_kategori_biaya', $data);
        return $this->db->insert_id();
    }
}
