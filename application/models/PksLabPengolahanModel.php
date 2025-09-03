<?php


class PksLabPengolahanModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_lab_pengolahan', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_lab_pengolahan tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('no_transaksi', 'ASC');
        $result = $this->db->get('pks_lab_pengolahan');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_lab_pengolahan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_lab_pengolahan');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;


        $data = array(
            
            'mill_id' => $input['mill_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tanggal' => $input['tanggal'],
         
            'cpo_moisture' => $input['cpo_moisture'],
            'cpo_dobi' => $input['cpo_dobi'],
            'cpo_ffa' => $input['cpo_ffa'],
            'cpo_dirt' => $input['cpo_dirt'],
            
            'kernel_moisture' => $input['kernel_moisture'],
            'kernel_dobi' => $input['kernel_dobi'],
            'kernel_ffa' => $input['kernel_ffa'],
            'kernel_dirt' => $input['kernel_dirt'],
            
            'cpo_los_fruit' => $input['cpo_los_fruit'],
            'cpo_los_press' => $input['cpo_los_press'],
            'cpo_los_nut' => $input['cpo_los_nut'],
            'cpo_los_e_bunch' => $input['cpo_los_e_bunch'],
            'cpo_los_effluent' => $input['cpo_los_effluent'],
            
            'kernel_los_fruit' => $input['kernel_los_fruit'],
            'kernel_los_fiber_cyclone' => $input['kernel_los_fiber_cyclone'],
            'kernel_los_ltds1' => $input['kernel_los_ltds1'],
            'kernel_los_ltds2' => $input['kernel_los_ltds2'],
            'kernel_los_claybath' => $input['kernel_los_claybath'],

            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_lab_pengolahan', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_lab_pengolahan

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_lab_pengolahan', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_lab_pengolahan

    public function create($input)
    {
        $data = array(
            'mill_id' => $input['mill_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tanggal' => $input['tanggal'],
         
            'cpo_moisture' => $input['cpo_moisture'],
            'cpo_dobi' => $input['cpo_dobi'],
            'cpo_ffa' => $input['cpo_ffa'],
            'cpo_dirt' => $input['cpo_dirt'],
            
            'kernel_moisture' => $input['kernel_moisture'],
            'kernel_dobi' => $input['kernel_dobi'],
            'kernel_ffa' => $input['kernel_ffa'],
            'kernel_dirt' => $input['kernel_dirt'],
            
            'cpo_los_fruit' => $input['cpo_los_fruit'],
            'cpo_los_press' => $input['cpo_los_press'],
            'cpo_los_nut' => $input['cpo_los_nut'],
            'cpo_los_e_bunch' => $input['cpo_los_e_bunch'],
            'cpo_los_effluent' => $input['cpo_los_effluent'],
            
            'kernel_los_fruit' => $input['kernel_los_fruit'],
            'kernel_los_fiber_cyclone' => $input['kernel_los_fiber_cyclone'],
            'kernel_los_ltds1' => $input['kernel_los_ltds1'],
            'kernel_los_ltds2' => $input['kernel_los_ltds2'],
            'kernel_los_claybath' => $input['kernel_los_claybath'],

            'dibuat_oleh'=> $input['dibuat_oleh'],
            'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('pks_lab_pengolahan', $data);
        return $this->db->insert_id();
    }
}
