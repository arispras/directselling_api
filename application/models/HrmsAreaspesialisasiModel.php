<?php


class HrmsAreaspesialisasiModel extends CI_Model
{



    
    public function retrieve_all()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('hrms_area_spesialisasi');
        return $result->result_array();
    }

    /**
     * Method untuk menghapus record hrms_area_spesialisasi
     *
     * @param  integer $id
     * @return boolean true jika berhasil
     * @author Almazari <almazary@gmail.com>
     */
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_area_spesialisasi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;


        $data = array(
            'nama' => $input['nama'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),


        );
        $this->db->where('id', $id);
        $this->db->update('hrms_area_spesialisasi', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record hrms_area_spesialisasi
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_area_spesialisasi', 1);
        return $result->row_array();
    }

    /**
     * Method untuk membuat data hrms_area_spesialisasi
     *
     * @param  string       $nama
     * @param  null|string  $info
     * @return integer      last insert id
     * @author Almazari <almazary@gmail.com>
     */
    public function create($input)
    {
        $data = array(
            'nama' => $input['nama'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->insert('hrms_area_spesialisasi', $data);
        return $this->db->insert_id();
    }
}
