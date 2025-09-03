<?php


class AccAssetModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('acc_asset', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data acc_asset tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('acc_asset');
        return $result->result_array();
    }


    //   Method untuk menghapus record acc_asset

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_asset');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'asset_tipe_id' => $input['asset_tipe_id']['id'],
            'posisi_asset_id' => $input['posisi_asset_id']['id'],
            'status' => $input['status']['id'],
            'metode_penyusutan' => $input['metode_penyusutan']['id'],
            'akun_penyusutan_id' => $input['akun_penyusutan_id']['id'],
            'akun_akumulasi_id' => $input['akun_akumulasi_id']['id'],
            'akun_biaya_id' => $input['akun_biaya_id']['id'],
            'tgl_beli' => $input['tgl_beli'],
            'tgl_mulai_pakai' => $input['tgl_mulai_pakai'],
            'harga_beli' => $input['harga_beli'],
            'nilai_asset' => $input['nilai_asset'],
            'nilai_residu' => $input['nilai_residu'],
            'lama_bulan_penyusutan' => $input['lama_bulan_penyusutan'],
            'ket' => $input['ket'],
        );
        $this->db->where('id', $id);
        $this->db->update('acc_asset', $data);
        return true;
    }


    //  Method untuk mengambil satu record acc_asset

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_asset', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_asset

    public function create($input)
    {
        $data = array(
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'asset_tipe_id' => $input['asset_tipe_id']['id'],
            'posisi_asset_id' => $input['posisi_asset_id']['id'],
            'status' => $input['status']['id'],
            'metode_penyusutan' => $input['metode_penyusutan']['id'],
            'akun_penyusutan_id' => $input['akun_penyusutan_id']['id'],
            'akun_akumulasi_id' => $input['akun_akumulasi_id']['id'],
            'akun_biaya_id' => $input['akun_biaya_id']['id'],
            'tgl_beli' => $input['tgl_beli'],
            'tgl_mulai_pakai' => $input['tgl_mulai_pakai'],
            'harga_beli' => $input['harga_beli'],
            'nilai_asset' => $input['nilai_asset'],
            'nilai_residu' => $input['nilai_residu'],
            'lama_bulan_penyusutan' => $input['lama_bulan_penyusutan'],
            'ket' => $input['ket'],
        );
        $this->db->insert('acc_asset', $data);
        return $this->db->insert_id();
    }
}
