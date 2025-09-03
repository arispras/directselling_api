<?php


class PrcPoTtdModel extends CI_Model
{
    private $table = 'prc_po_ttd';

    function __construct()
    {
        // if (!$this->db->table_exists($this->table)) {
        //     $this->create_table();
        // }
    }


    public function retrieve_all(

    ) {
        $this->db->order_by('nama','ASC');
            $result = $this->db->get('prc_po_ttd');
  
        return $result->result_array();
    }
	public function retrieve_all_by_tipe($tipe

		) {
			$this->db->where('tipe', $tipe);
			$this->db->order_by('nama','ASC');
			$result = $this->db->get('prc_po_ttd');
	  
			return $result->result_array();
		}
	public function retrieve_all_akun_detail(

		) {
			$this->db->where('is_transaksi_akun', 1);
			$this->db->order_by('nama','ASC');
				$result = $this->db->get('prc_po_ttd');
	  
			return $result->result_array();
		}
	
    public function retrieve($array_where = array())
    {
        foreach ($array_where as $key => $val) {
            $this->db->where($key, $val);
        }
        $result = $this->db->get($this->table);
        return $result->row_array();
    }

    public function retrievebyId($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);

        $result = $this->db->get('prc_po_ttd', '1');
        return $result->row_array();
    }


    public function delete($id)
    {
        $id = (int)$id;
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return true;
    }

   
    public function update(
        $id,
        $input=null) {
        $this->db->where('id', $id);
        $this->db->update($this->table, array(
            // 'kode'=> $input['kode'],
            'nama'=> $input['nama'],
            'tipe'=> $input['tipe'],
            // 'ket'=> $input['ket'],
            // 'jenis_piutang'=> $input['jenis_piutang']
        ));

        return true;
    }

  
     public function create( 
        $input =null
    ) {
        $this->db->insert($this->table, array(
         
            // 'kode'=> $input['kode'],
            'nama'=> $input['nama'],
            'tipe'=> $input['tipe'],
            // 'ket'=> $input['ket'],
            // 'jenis_piutang'=> $input['jenis_piutang']
        ));

        return $this->db->insert_id();
    }

    /**
     * Method untuk membuat tabel pengumuman
     */
    public function create_table()
    {
        // $CI =& get_instance();
        // $CI->load->model('config_model');

        // $CI->config_model->create_tb_pengumuman();

    }
}
