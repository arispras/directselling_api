<?php


class PksSoundingModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('pks_sounding');
		return true;
	}
	public function deleteProduksi($idSounding)
	{
		$id = (int)$idSounding;

		$this->db->where('sounding_id', $id);
		$this->db->delete('pks_produksi');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('pks_sounding', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "select a.*,b.nama as mill,c.nama_supplier from pks_sounding a left join gbm_organisasi b on a.mill_id=b.id left join gbm_supplier c on a.supplier_id=c.id ";

		return $this->db->query($query)->result_array();;
	}

	public function createProduksi($idSounding, $input)
	{
		$data = [];
		// $data['diubah_tanggal'] = date('Y-m-d H:i:s');
		$data['sounding_id'] = $idSounding;
		$data['tanki_id'] = $input['tanki_id']['id'];
		$data['lokasi_id'] = $input['mill_id']['id'];
		$data['nilai'] = $input['volume'];
		$data['tanggal'] = $input['tanggal'];

		$this->db->insert('pks_produksi', $data);
		return $this->db->insert_id();
	}

	public function create(
		$arrdata
	) {

		$mill_id  = (int) $arrdata['mill_id']['id'];
		$tanki_id  = (int) $arrdata['tanki_id']['id'];
		$tanggal    =  $arrdata['tanggal'];
		$no_transaksi    =  $arrdata['no_transaksi'];
		$tinggi    =  $arrdata['tinggi'];
		$sounding    =  $arrdata['sounding'];
		$meja_ukur    =  $arrdata['meja_ukur'];
		$hasil_1    =  $arrdata['hasil_1'];
		$hasil_2    =  $arrdata['hasil_2'];
		$hasil_total    =  $arrdata['hasil_total'];
		$suhu    =  $arrdata['suhu'];
		$cal    =  $arrdata['cal'];
		$density    =  $arrdata['density'];
		// $kg    =  $arrdata['kg'];

		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tanki_id' => $tanki_id,
			'tanggal' => $tanggal,
			'no_transaksi' => $no_transaksi,
			'tinggi' => $tinggi,
			'sounding' => $sounding,
			'meja_ukur' => $meja_ukur,
			'hasil_1' => $hasil_1,
			'hasil_2' => $hasil_2,
			'hasil_total' => $hasil_total,
			// 'kg'=> $kg,
			'suhu' => $suhu,
			'cal' => $cal,
			'density' => $density,

			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
		);
		$this->db->insert('pks_sounding', $data);
		return $this->db->insert_id();
	}


	public function updateProduksi($idSounding, $input)
	{
		$data = [];
		// $data['diubah_tanggal'] = date('Y-m-d H:i:s');
		$data['sounding_id'] = $idSounding;
		$data['tanki_id'] = $input['tanki_id']['id'];
		$data['lokasi_id'] = $input['mill_id']['id'];
		// $data['nilai'] = $input['volume'];
		$data['tanggal'] = $input['tanggal'];


		$this->db->where('sounding_id', $idSounding);
		$this->db->update('pks_produksi', $data);
		return true;
	}
	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$mill_id  = (int) $arrdata['mill_id']['id'];
		$tanki_id  = (int) $arrdata['tanki_id']['id'];
		$tanggal    =  $arrdata['tanggal'];
		$no_transaksi    =  $arrdata['no_transaksi'];
		$tinggi    =  $arrdata['tinggi'];
		$sounding    =  $arrdata['sounding'];
		$meja_ukur    =  $arrdata['meja_ukur'];
		$hasil_1    =  $arrdata['hasil_1'];
		$hasil_2    =  $arrdata['hasil_2'];
		$hasil_total    =  $arrdata['hasil_total'];
		$suhu    =  $arrdata['suhu'];
		$cal    =  $arrdata['cal'];
		// $kg    =  $arrdata['kg'];
		$density = $arrdata['density'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tanki_id' => $tanki_id,
			'tanggal' => $tanggal,
			'no_transaksi' => $no_transaksi,
			'tinggi' => $tinggi,
			'sounding' => $sounding,
			'meja_ukur' => $meja_ukur,
			'hasil_1' => $hasil_1,
			'hasil_2' => $hasil_2,
			'hasil_total' => $hasil_total,
			// 'kg'=> $kg,
			'suhu' => $suhu,
			'cal' => $cal,
			'density' => $density,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('pks_sounding', $data);
		return true;
	}

	public function check_tanki($tanki_id, $tanggal)
	{
		$this->db->from("pks_sounding");
		$this->db->where('tanki_id', $tanki_id['id']);
		$this->db->where('tanggal', $tanggal);
		$cek = $this->db->get()->row();
		if ($cek) {
			return false;
		} else {
			return true;
		}
	}


	public function process_sounding($input)
	{
		$tanki = $this->db->query("SELECT * FROM pks_tanki WHERE id=" . $input['tanki_id'])->row();
		if ($tanki->kode_tanki == 'ST-500A') {
			$meja_ukur = $tanki->meja_ukur;
			$tinggi = ($input['sounding'] * 10) + $meja_ukur;

			$parse = (string) $tinggi;
			$last_num = $parse[strlen($parse) - 1];
			$norm_num = substr($parse, 0, strlen($parse) - 1) . "0";

			$tanki_formula1 = $this->db->query("SELECT * FROM pks_tanki_formula1 WHERE tanki_id=" . $input['tanki_id'] . " AND tinggi=" . $norm_num)->row();
			$hasil_1 = $tanki_formula1->hasil;

			if ($last_num == 0) {

				$hasil_2 = 0;
			} else {
				$tanki_formula2 = $this->db->query("SELECT * FROM pks_tanki_formula2 WHERE tanki_id=" . $input['tanki_id'] . " AND awal<=" . $tanki_formula1->tinggi . " AND akhir>=" . $tanki_formula1->tinggi)->row();
				$tanki_formula3 = $this->db->query("SELECT * FROM pks_tanki_formula3 WHERE tanki_id=" . $input['tanki_id'] . " AND simbol='" . $tanki_formula2->simbol . "' AND qty=" . $last_num)->row();

				$hasil_2 = $tanki_formula3->hasil;
			}
			$tanki_density = $this->db->query("SELECT * FROM pks_tanki_density WHERE suhu >=" . $input['suhu'] . " order by suhu asc limit 1 ")->row();
			$density = $tanki_density->nilai;
			// $cal = 1 + (0.0000348 * ( (int) $input['suhu'] - 45));
			$cal = 1 + (0.0000348 * ($input['suhu'] - 45));

			$hasil_total = ($hasil_1 + $hasil_2) * $cal * $density;


			$return = [
				'meja_ukur' => $tanki->meja_ukur,
				'tinggi' => $tinggi,
				'hasil_1' => $hasil_1,
				'hasil_2' => $hasil_2,
				'hasil_total' => $hasil_total,
				'cal' => $cal,
				'density' => $density
			];
			return $return;
		}else{
			$meja_ukur = $tanki->meja_ukur;
			$tinggi = ($input['sounding'] * 10) + $meja_ukur;

			$parse = (string) $tinggi;
			$last_num = $parse[strlen($parse) - 1];
			$norm_num = substr($parse, 0, strlen($parse) - 1) . "0";

			$tanki_formula = $this->db->query("SELECT * FROM pks_tanki_formula_2000 WHERE kode_tanki='" . $tanki->kode_tanki . "' AND tinggi>=" .$norm_num ."
			order by tinggi asc limit 2")->result_array();

			/* === hitungan lama === */
			// $selisih_minyak1=$tanki_formula[1]['minyak1']-$tanki_formula[0]['minyak1'];
			// $selisih_minyak1_cm=$selisih_minyak1/10;
			// $selisih_tinggi=$tinggi-$tanki_formula[0]['tinggi'];
			// $hasil_selisih = $selisih_minyak1_cm*$selisih_tinggi;
			// $hasil_1 = $hasil_selisih+$tanki_formula[0]['minyak2'];

			$selisih_minyak=$tanki_formula[1]['minyak2']-$tanki_formula[0]['minyak2'];
			$selisih_minyak_cm=$selisih_minyak/10;
			$hasil_1 = ($selisih_minyak_cm*$last_num )+ $tanki_formula[0]['minyak2'];
			$tanki_density = $this->db->query("SELECT * FROM pks_tanki_density WHERE suhu >=" . $input['suhu'] . " order by suhu asc limit 1 ")->row();
			$density = $tanki_density->nilai;
			// $cal = 1 + (0.0000348 * ( (int) $input['suhu'] - 45));
			$cal = 1 + (0.0000348 * ($input['suhu'] -34));

			$hasil_total = ($hasil_1) * $cal * $density;


			$return = [
				'meja_ukur' => $tanki->meja_ukur,
				'tinggi' => $tinggi,
				'hasil_1' => $hasil_1,
				'hasil_2' => 0,
				'hasil_total' => $hasil_total,
				'cal' => $cal,
				'density' => $density,
				'selisih_minyak1'=>$selisih_minyak,
				'selisih_minyak1cm'=>$selisih_minyak_cm,
				'selisih_tinggi'=>0,
				'hasil_selisih'=>0,
				'minyak1_0'=>$tanki_formula[0]['minyak1'],
				'minyak1_1'=>$tanki_formula[1]['minyak1'],
				'tangki_formula'=>$tanki_formula ,
				'meja_ukur'=>$meja_ukur, 
				'tinggi'=>$tinggi ,
				'last_num'=>$last_num,
				'norm_num'=>$norm_num
			];
			return $return;

		}
	}
}
