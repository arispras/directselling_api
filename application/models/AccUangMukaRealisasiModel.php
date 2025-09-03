<?php


class AccUangMukaRealisasiModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('realisasi_id', $id);
		$this->db->delete('acc_uang_muka_realisasi_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_uang_muka_realisasi');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$query  = "SELECT a.* from acc_uang_muka_realisasi a
		where a.id=" . $id . "";
		$result = $this->db->query($query)->row_array();

		return $result;
	}
	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('acc_uang_muka_realisasi_dt');
		$this->db->where('realisasi_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_all()
	{
		$query  = "SELECT * from acc_uang_muka_realisasi ";

		return $this->db->query($query)->result_array();;
	}

	public function create(
		$arrdata
	) {

		$lokasi_id = $arrdata['lokasi_id']['id'];
		$acc_uang_muka_id = $arrdata['acc_uang_muka']['id'];
		$acc_akun_uang_muka_id  = $arrdata['acc_akun_uang_muka']['id'];
		$acc_akun_kasbank_id = $arrdata['acc_akun_kasbank']['id'];
		// $acc_akun_realisasi_id  = $arrdata['acc_akun_realisasi']['id'];

		$no_transaksi  = $arrdata['no_transaksi'];
		$keterangan    =  $arrdata['keterangan'];
		$tanggal    =  $arrdata['tanggal'];

		$nilai_uang_muka    =  $arrdata['nilai_uang_muka'];
		$nilai_realisasi    =  $arrdata['nilai_realisasi'];

		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'acc_uang_muka_id' => $acc_uang_muka_id,
			'lokasi_id' => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'acc_akun_uang_muka_id' => $acc_akun_uang_muka_id,
			'acc_akun_kasbank_id' => $acc_akun_kasbank_id,
			// 'acc_akun_realisasi_id' => $acc_akun_realisasi_id,
			'keterangan' => $keterangan,
			'nilai_uang_muka' => $nilai_uang_muka,
			'tanggal' => $tanggal,
			'nilai_realisasi' => $nilai_realisasi,
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $arrdata['dibuat_oleh']
		);
		$this->db->insert('acc_uang_muka_realisasi', $data);
		$id = $this->db->insert_id();

		$details = $arrdata['details'];

		foreach ($details as $key => $value) {
			$this->db->insert("acc_uang_muka_realisasi_dt", array(
				'realisasi_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => null,
				'kegiatan_id' => null,
				'kendaraan_mesin_id' => null,

			));
		}

		return $id;
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;
		$lokasi_id = $arrdata['lokasi_id']['id'];
		$acc_uang_muka_id = $arrdata['acc_uang_muka']['id'];
		$acc_akun_uang_muka_id  = $arrdata['acc_akun_uang_muka']['id'];
		$acc_akun_kasbank_id = $arrdata['acc_akun_kasbank']['id'];
		// $acc_akun_realisasi_id  = $arrdata['acc_akun_realisasi']['id'];

		$no_transaksi  = $arrdata['no_transaksi'];
		$keterangan    =  $arrdata['keterangan'];
		$tanggal    =  $arrdata['tanggal'];

		$nilai_uang_muka    =  $arrdata['nilai_uang_muka'];
		$nilai_realisasi    =  $arrdata['nilai_realisasi'];
		$data = array(
			'acc_uang_muka_id' => $acc_uang_muka_id,
			'lokasi_id' => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'acc_akun_uang_muka_id' => $acc_akun_uang_muka_id,
			'acc_akun_kasbank_id' => $acc_akun_kasbank_id,
			// 'acc_akun_realisasi_id' => $acc_akun_realisasi_id,
			'keterangan' => $keterangan,
			'nilai_uang_muka' => $nilai_uang_muka,
			'tanggal' => $tanggal,
			'nilai_realisasi' => $nilai_realisasi,
			// 'dibuat_tanggal' => date('Y-m-d H:i:s'),
			// 'dibuat_oleh' =>  $input['dibuat_oleh'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $arrdata['diubah_oleh']
		);
		$this->db->where('id', $id);
		$this->db->update('acc_uang_muka_realisasi', $data);
		// Hapus  detail //
		$this->db->where('realisasi_id', $id);
		$this->db->delete('acc_uang_muka_realisasi_dt');
		$details = $arrdata['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_uang_muka_realisasi_dt", array(
				'realisasi_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => null,
				'kegiatan_id' => null,
				'kendaraan_mesin_id' => null,

			));
		}

		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_uang_muka_realisasi', $data);


		return true;
	}
	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,b.no_spk, c.nama_customer  from acc_uang_muka_realisasi a left join sls_kontrak b on a.sls_kontrak_id=b.id
		inner join gbm_customer c on a.customer_id=c.id
		inner join gbm_organisasi d on a.lokasi_id=d.id
		where a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}
