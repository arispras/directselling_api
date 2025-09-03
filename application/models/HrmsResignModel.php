<?php


class HrmsResignModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('hrms_resign', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data hrms_resign tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('no_transaksi', 'ASC');
        $result = $this->db->get('hrms_resign');
        return $result->result_array();
    }


    //   Method untuk menghapus record hrms_resign

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_resign');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            
            'karyawan_id' => $input['karyawan_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tgl' => $input['tgl'],
            'tgl_resign' => $input['tgl_resign'],
            'status' => $input['status'],
            'alasan' => $input['alasan'],
            'catatan' => $input['catatan'],

			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('hrms_resign', $data);
        return true;
    }


    //  Method untuk mengambil satu record hrms_resign

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_resign', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data hrms_resign

    public function create($input)
    {
        $data = array(
            
            'karyawan_id' => $input['karyawan_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tgl' => $input['tgl'],
            'tgl_resign' => $input['tgl_resign'],
            'status' => $input['status'],
            'alasan' => $input['alasan'],
            'catatan' => $input['catatan'],
            
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('hrms_resign', $data);
        return $this->db->insert_id();
    }
}
