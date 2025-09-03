<?php


class KlnKategoriRadiologiModel extends CI_Model
{



    public function retrieve_all()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('kln_kategori_radiologi');
        return $result->result_array();
    }

 
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_kategori_radiologi');
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
        $this->db->update('kln_kategori_radiologi', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record kln_kategori_radiologi
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_kategori_radiologi', 1);
        return $result->row_array();
    }

    /**
     * Method untuk membuat data kln_kategori_radiologi
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
        $this->db->insert('kln_kategori_radiologi', $data);
        return $this->db->insert_id();
    }
}
