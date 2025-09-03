<?php


class BgtProduksiModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('bgt_produksi_afd', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data bgt_produksi_afd tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('bgt_produksi_afd');
        return $result->result_array();
    }


    //   Method untuk menghapus record bgt_produksi_afd

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('bgt_produksi_afd');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            // 'kegiatan_id' => $input['kegiatan_id']['id'],
            'tahun' => $input['tahun'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'b01' => $input['b01'],
            'b02' => $input['b02'],
            'b03' => $input['b03'],
            'b04' => $input['b04'],
            'b05' => $input['b05'],
            'b06' => $input['b06'],
            'b07' => $input['b07'],
            'b08' => $input['b08'],
            'b09' => $input['b09'],
            'b10' => $input['b10'],
            'b11' => $input['b11'],
            'b12' => $input['b12'],
            'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('bgt_produksi_afd', $data);
        return true;
    }


    //  Method untuk mengambil satu record bgt_produksi_afd

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('bgt_produksi_afd', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data bgt_produksi_afd

    public function create($input)
    {
        $data = array(
            // 'kegiatan_id' => $input['kegiatan_id']['id'],
            'tahun' => $input['tahun'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'b01' => $input['b01'],
            'b02' => $input['b02'],
            'b03' => $input['b03'],
            'b04' => $input['b04'],
            'b05' => $input['b05'],
            'b06' => $input['b06'],
            'b07' => $input['b07'],
            'b08' => $input['b08'],
            'b09' => $input['b09'],
            'b10' => $input['b10'],
            'b11' => $input['b11'],
            'b12' => $input['b12'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('bgt_produksi_afd', $data);
        return $this->db->insert_id();
    }
}
