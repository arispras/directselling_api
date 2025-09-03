<?php


class PksTankiFormula3Model extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_tanki_formula3', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_tanki_formula3 tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('simbol', 'ASC');
        $result = $this->db->get('pks_tanki_formula3');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_tanki_formula3

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_tanki_formula3');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'tanki_id' => $input['tanki_id']['id'],
            'simbol' => $input['simbol']['id'],
            'qty' => $input['qty'],
            'hasil' => $input['hasil'],
        );
        $this->db->where('id', $id);
        $this->db->update('pks_tanki_formula3', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_tanki_formula3

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_tanki_formula3', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_tanki_formula3

    public function create($input)
    {
        $data = array(
            'tanki_id' => $input['tanki_id']['id'],
            'simbol' => $input['simbol']['id'],
            'qty' => $input['qty'],
            'hasil' => $input['hasil'],

        );
        $this->db->insert('pks_tanki_formula3', $data);
        return $this->db->insert_id();
    }
}
