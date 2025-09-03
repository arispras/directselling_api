<?php


class EstTaksasiModel extends CI_Model
{


    //  Method untuk mendapatkan semua data est_taksasi tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('est_taksasi');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_taksasi

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_taksasi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'blok_id' => $input['blok_id']['id'],
            'tanggal' => $input['tanggal'],       
			'ha_sisa' => $input['ha_sisa'],
            'ha_besok' => $input['ha_besok'],
            'jumlah_pokok' => $input['jumlah_pokok'],
            'persen_buah_matang' => $input['persen_buah_matang'],
            'jjg_output' => $input['jjg_output'],
            'hk' => $input['hk'],
            'bjr' => $input['bjr'],
            'berat_kg' => $input['berat_kg'],
            'seksi_panen' => $input['seksi_panen'],
            'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('est_taksasi', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_taksasi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_taksasi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_taksasi

    public function create($input)
    {
        $data = array(
            
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'blok_id' => $input['blok_id']['id'],
            'tanggal' => $input['tanggal'],       
			'ha_sisa' => $input['ha_sisa'],
            'ha_besok' => $input['ha_besok'],
            'jumlah_pokok' => $input['jumlah_pokok'],
            'persen_buah_matang' => $input['persen_buah_matang'],
            'jjg_output' => $input['jjg_output'],
            'hk' => $input['hk'],
            'bjr' => $input['bjr'],
            'berat_kg' => $input['berat_kg'],
            'seksi_panen' => $input['seksi_panen'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('est_taksasi', $data);
        return $this->db->insert_id();
    }
}
