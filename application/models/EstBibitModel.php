<?php


class EstBibitModel extends CI_Model
{



   
    public function retrieve_all()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('bibit', 'ASC');
        $result = $this->db->get('est_bibit');
        return $result->result_array();
    }

    
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_bibit');
        return true;
    }


    public function update($id, $bibit)
    {
        $id = (int)$id;


        $data = array(
            'bibit' => $bibit,

        );
        $this->db->where('id', $id);
        $this->db->update('est_bibit', $data);
        return true;
    }

  
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_bibit', 1);
        return $result->row_array();
    }

    
    public function create($bibit)
    {
        $data = array(
            'bibit' => $bibit
        );
        $this->db->insert('est_bibit', $data);
        return $this->db->insert_id();
    }
}
