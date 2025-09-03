<?php


class EstBjrModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('est_bjr', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data est_bjr tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('bjr', 'ASC');
        $result = $this->db->get('est_bjr');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_bjr

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_bjr');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'blok_id' => $input['blok_id']['id'],
            'bjr' => $input['bjr'],
            'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('est_bjr', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_bjr

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_bjr', 1);
        return $result->row_array();
    }
	public function retrieve_by_blok_id($id)
    {
        $id = (int)$id;

        $this->db->where('blok_id', $id);
        $result = $this->db->get('est_bjr', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_bjr

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'blok_id' => $input['blok_id']['id'],
            'bjr' => $input['bjr'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('est_bjr', $data);
        return $this->db->insert_id();
    }
}
