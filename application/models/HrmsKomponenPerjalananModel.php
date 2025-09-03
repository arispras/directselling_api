<?php


class HrmsKomponenPerjalananModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('hrms_komponen_perjalanan', $no_of_records, $page_no, $where);

        return $data;
    }

    /**
     * Method untuk mendapatkan semua data hrms_komponen_perjalanan tanpa pagging
     *
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve_all_jabatan()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('hrms_komponen_perjalanan');
        return $result->result_array();
    }

    /**
     * Method untuk menghapus record hrms_komponen_perjalanan
     *
     * @param  integer $id
     * @return boolean true jika berhasil
     * @author Almazari <almazary@gmail.com>
     */
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_komponen_perjalanan');
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
        $this->db->update('hrms_komponen_perjalanan', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record hrms_komponen_perjalanan
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_komponen_perjalanan', 1);
        return $result->row_array();
    }

    /**
     * Method untuk membuat data hrms_komponen_perjalanan
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
        $this->db->insert('hrms_komponen_perjalanan', $data);
        return $this->db->insert_id();
    }
}
