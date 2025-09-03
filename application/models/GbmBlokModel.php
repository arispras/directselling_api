<?php


class GbmBlokModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('gbm_blok');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('gbm_blok', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$this->db->order_by('nama', 'ASC');
		$this->db->get('gbm_blok');
		$result = $this->db->get('gbm_blok');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {


		$organisasi_id  = (int) $arrdata['organisasi_id'];
		$tahuntanam  = (int) $arrdata['tahuntanam'];
		$luasareaproduktif    =  $arrdata['luasareaproduktif'];
		$luasareanonproduktif    =  $arrdata['luasareanonproduktif'];
		$jumlahpokok    =  $arrdata['jumlahpokok'];
		$statusblok    =  $arrdata['statusblok'];
		$topografi    =  $arrdata['topografi'];
		$klasifikasitanah    =  $arrdata['klasifikasitanah'];
		$jenisbibit    =  $arrdata['jenisbibit'];
		$intiplasma    =  $arrdata['intiplasma'];


		$data = array(
		
			'organisasi_id'    => $organisasi_id,
			'tahuntanam'    => $tahuntanam,
			'luasareaproduktif' => $luasareaproduktif,
			'luasareanonproduktif' => $luasareanonproduktif,
			'jumlahpokok' => $jumlahpokok,
			'statusblok' => $statusblok,
			'topografi' => $topografi,
			'klasifikasitanah' => $klasifikasitanah,
			'jenisbibit' => $jenisbibit,
			'intiplasma' => $intiplasma
		);
		$this->db->insert('gbm_blok', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$organisasi_id  = (int) $arrdata['organisasi_id'];
		$tahuntanam  = (int) $arrdata['tahuntanam'];
		$luasareaproduktif    =  $arrdata['luasareaproduktif'];
		$luasareanonproduktif    =  $arrdata['luasareanonproduktif'];
		$jumlahpokok    =  $arrdata['jumlahpokok'];
		$statusblok    =  $arrdata['statusblok'];
		$topografi    =  $arrdata['topografi'];
		$klasifikasitanah    =  $arrdata['klasifikasitanah'];
		$jenisbibit    =  $arrdata['jenisbibit'];
		$intiplasma    =  $arrdata['intiplasma'];


		$data = array(
			'organisasi_id'    => $organisasi_id,
			'tahuntanam'    => $tahuntanam,
			'luasareaproduktif' => $luasareaproduktif,
			'luasareanonproduktif' => $luasareanonproduktif,
			'jumlahpokok' => $jumlahpokok,
			'statusblok' => $statusblok,
			'topografi' => $topografi,
			'klasifikasitanah' => $klasifikasitanah,
			'jenisbibit' => $jenisbibit,
			'intiplasma' => $intiplasma
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_blok', $data);
		return true;
	}

	public function laporan_area()
	{
		

		$query = "SELECT * FROM gbm_blok_organisasi_vw";
		$data = $this->db->query($query)->result_array();
		return $data;
	}

}
