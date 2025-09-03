<?php


class HrmsBasisLemburModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('payroll_basis_lembur', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data payroll_basis_lembur tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tipe_lembur', 'ASC');
        $result = $this->db->get('payroll_basis_lembur');
        return $result->result_array();
    }


    //   Method untuk menghapus record payroll_basis_lembur

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('payroll_basis_lembur');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'tipe_lembur' => $input['tipe_lembur']['id'],
            'basis_jam_lembur' => $input['basis_jam_lembur'],
            'jumlah_jam_lembur' => $input['jumlah_jam_lembur'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('payroll_basis_lembur', $data);
        return true;
    }


    //  Method untuk mengambil satu record payroll_basis_lembur

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('payroll_basis_lembur', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data payroll_basis_lembur

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'tipe_lembur' => $input['tipe_lembur']['id'],
            'basis_jam_lembur' => $input['basis_jam_lembur'],
            'jumlah_jam_lembur' => $input['jumlah_jam_lembur'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('payroll_basis_lembur', $data);
        return $this->db->insert_id();
    }
}
