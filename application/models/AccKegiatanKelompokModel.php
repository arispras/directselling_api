<?php


class AccKegiatanKelompokModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('acc_kegiatan_kelompok', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data acc_kegiatan_kelompok tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('acc_kegiatan_kelompok');
        return $result->result_array();
    }


    //   Method untuk menghapus record acc_kegiatan_kelompok

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_kegiatan_kelompok');
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
        $this->db->update('acc_kegiatan_kelompok', $data);
        return true;
    }


    //  Method untuk mengambil satu record acc_kegiatan_kelompok

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_kegiatan_kelompok', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_kegiatan_kelompok

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
        $this->db->insert('acc_kegiatan_kelompok', $data);
        return $this->db->insert_id();
    }
}
