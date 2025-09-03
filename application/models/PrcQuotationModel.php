<?php


class PrcQuotationModel extends CI_Model
{


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('prc_quotation');
		return true;
	}

 
    public function retrieve_all()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('no_quotation', 'ASC');
        $result = $this->db->get('prc_quotation');
        return $result->result_array();
    }

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('prc_quotation', 1);

		return $result->row_array();
	}

	public function update(
		$id,
		$input,
		$file_name = null
	) {


		$data = array(
			'no_quotation' => $input['no_quotation'],
			'no_referensi' => $input['no_referensi'],
			'catatan' => $input['catatan'],
			'upload_file' => 	$file_name,
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);



		$this->db->where('id', $id);
		$this->db->update('prc_quotation', $data);
		return true;
	}



	public function create(
		$input = null,
		$upload = null
	) {
		$data = array(
			'no_quotation' => $input['no_quotation'],
			'no_referensi' => $input['no_referensi'],
			'upload_file' => $upload['upload_data']['file_name'],
			'catatan' => $input['catatan'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('prc_quotation', $data);
		return $this->db->insert_id();
	}


}
