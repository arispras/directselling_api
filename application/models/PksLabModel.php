<?php


class PksLabModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('pks_lab');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('pks_lab', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		// $query  = "select a.*,b.nama as mill,c.nama_supplier from pks_lab a left join gbm_organisasi b on a.mill_id=b.id left join gbm_supplier c on a.supplier_id=c.id ";
		$query  = "SELECT 
			a.*,
			b.nama AS mill,
			c.nama_tanki AS tanki 
		FROM pks_lab a 
		LEFT JOIN gbm_organisasi b ON a.mill_id=b.id 
		LEFT JOIN pks_tanki c ON a.tanki_id=c.id
		";
		
		return $this->db->query($query)->result_array();;

	}

	public function create(
		$arrdata
	) {

		$mill_id  = (int) $arrdata['mill_id'];
		$tanki_id  = (int) $arrdata['tanki_id'];
		$tanggal    =  $arrdata['tanggal'];
		
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');
		
		$data = array(
			'mill_id'    => $mill_id,
			'tanki_id' => $tanki_id,
			'tanggal' => $tanggal,

			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
		);
		$data = array_merge($data, $arrdata);
		$this->db->insert('pks_lab', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$mill_id  = (int) $arrdata['mill_id'];
		$tanki_id  = (int) $arrdata['tanki_id'];
		
		$tanggal    =  $arrdata['tanggal'];
		
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tanki_id' => $tanki_id,
			'tanggal' => $tanggal,

			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);

		$data = array_merge($data, $arrdata);

		$this->db->where('id', $id);
		$this->db->update('pks_lab', $data);
		return true;
	}

	public function check_tanki( $tanki_id ,$tanggal)
	{
		$this->db->from("pks_lab");
		$this->db->where('tanki_id', $tanki_id);
		$this->db->where('tanggal',$tanggal);
		$cek = $this->db->get()->row();
		if ($cek) {
			return false;
		}else {
			return true;
		}
	}
}
