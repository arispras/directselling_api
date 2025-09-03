<?php


class PksProduksiHarianModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_produksi_harian', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_produksi_harian tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('no_transaksi', 'ASC');
        $result = $this->db->get('pks_produksi_harian');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_produksi_harian

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_produksi_harian');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;


        $data = array(
			'mill_id' => $input['mill_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tanggal' => $input['tanggal'],
            'tbs_olah' => $input['tbs_olah'],
            'tbs_kemarin' => $input['tbs_kemarin'],
            'tbs_masuk' => $input['tbs_masuk'],
            'tbs_sisa' => $input['tbs_sisa'],
            'cpo_stok' => $input['cpo_kg'],
            'cpo_produksi' => $input['produksi_cpo_kg'],
            'cpo_kirim' => $input['kirim_cpo_kg'],
            'kernel_stok' => $input['kernel_kg'],
            'kernel_produksi' => $input['produksi_kernel_kg'],
            'kernel_kirim' => $input['kirim_kernel_kg'],

            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_produksi_harian', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_produksi_harian

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_produksi_harian', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_produksi_harian

    public function create($input)
    {
        $data = array(

            'mill_id' => $input['mill_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tanggal' => $input['tanggal'],
            'tbs_olah' => $input['tbs_olah'],
            'tbs_kemarin' => $input['tbs_kemarin'],
            'tbs_masuk' => $input['tbs_masuk'],
            'tbs_sisa' => $input['tbs_sisa'],
            'cpo_stok' => $input['cpo_kg'],
            'cpo_produksi' => $input['produksi_cpo_kg'],
            'cpo_kirim' => $input['kirim_cpo_kg'],
            'kernel_stok' => $input['kernel_kg'],
            'kernel_produksi' => $input['produksi_kernel_kg'],
            'kernel_kirim' => $input['kirim_kernel_kg'],

            'dibuat_oleh'=> $input['dibuat_oleh'],
            'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('pks_produksi_harian', $data);
        return $this->db->insert_id();
    }
}
