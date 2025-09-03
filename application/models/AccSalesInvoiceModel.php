<?php


class AccSalesInvoiceModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('acc_sales_invoice');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$query  = "SELECT a.*,b.no_spk, c.nama_customer as customer from acc_sales_invoice a 
		left join sls_kontrak b on a.sls_kontrak_id=b.id
		left join gbm_customer c on a.customer_id=c.id
		where a.id=" . $id . "";
		$result = $this->db->query($query)->row_array();

		return $result;
	}
	public function retrieve_all()
	{
		$query  = "SELECT * from acc_sales_invoice ";

		return $this->db->query($query)->result_array();;
	}

	public function create(
		$arrdata
	) {

		$lokasi_id = $arrdata['lokasi_id']['id'];
		$sls_kontrak_id = $arrdata['sls_kontrak_id']['id'];
		$customer_id = $arrdata['customer_id']['id'];
		$jenis_invoice = $arrdata['jenis_invoice']['id'];
		$acc_akun_id_kredit = $arrdata['acc_akun_id_kredit']['id'];
		$acc_akun_id_debet = $arrdata['acc_akun_id_debet']['id'];
		$user_ttd = $arrdata['user_ttd']['id'];
		$no_invoice  = $arrdata['no_invoice'];
		$deskripsi    =  $arrdata['deskripsi'];
		$no_referensi    =  $arrdata['no_referensi'];
		$tanggal    =  $arrdata['tanggal'];
		$tanggal_tempo    =  $arrdata['tanggal_tempo'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$diskon    =  $arrdata['diskon'];
		$uang_muka    =  $arrdata['uang_muka'];
		$premi    =  $arrdata['premi'];
		$ppn    =  $arrdata['ppn'];
		$qty    =  $arrdata['qty'];
		$grand_total    =  $arrdata['grand_total'];
		$qty_real    =  $arrdata['qty_real'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'sls_kontrak_id' => $sls_kontrak_id,
			'lokasi_id' => $lokasi_id,
			'no_invoice' => $no_invoice,
			'jenis_invoice' => $jenis_invoice,
			'acc_akun_id_debet' => $acc_akun_id_debet,
			'acc_akun_id_kredit' => $acc_akun_id_kredit,
			'user_ttd' => $user_ttd,
			'customer_id' => $customer_id,
			'deskripsi' => $deskripsi,
			'no_referensi' => $no_referensi,
			'uang_muka' => $uang_muka,
			'premi' => $premi,
			'tanggal' => $tanggal,
			'tanggal_tempo' => $tanggal_tempo,
			'diskon' => $diskon,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'qty' => $qty,
			'ppn' => $ppn,
			'grand_total' => $grand_total,
			'qty_real' => $qty_real,
			'dibuat_tanggal' => $dibuat_tanggal,
			'dibuat_oleh'=>$arrdata['dibuat_oleh']
		);
		$this->db->insert('acc_sales_invoice', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;
		$lokasi_id = $arrdata['lokasi_id']['id'];
		$sls_kontrak_id = $arrdata['sls_kontrak_id']['id'];
		$jenis_invoice = $arrdata['jenis_invoice']['id'];
		$acc_akun_id_kredit = $arrdata['acc_akun_id_kredit']['id'];
		$acc_akun_id_debet = $arrdata['acc_akun_id_debet']['id'];
		$user_ttd = $arrdata['user_ttd']['id'];
		$customer_id = $arrdata['customer_id']['id'];
		$no_invoice  = $arrdata['no_invoice'];
		$deskripsi    =  $arrdata['deskripsi'];
		$no_referensi    =  $arrdata['no_referensi'];
		$tanggal    =  $arrdata['tanggal'];
		$tanggal_tempo    =  $arrdata['tanggal_tempo'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$diskon    =  $arrdata['diskon'];
		$uang_muka    =  $arrdata['uang_muka'];
		$premi    =  $arrdata['premi'];
		$ppn    =  $arrdata['ppn'];
		$qty    =  $arrdata['qty'];
		$grand_total    =  $arrdata['grand_total'];
		$qty_real    =  $arrdata['qty_real'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$data = array(
			'sls_kontrak_id' => $sls_kontrak_id,
			'lokasi_id' => $lokasi_id,
			'no_invoice' => $no_invoice,
			'customer_id' => $customer_id,
			'jenis_invoice' => $jenis_invoice,
			'acc_akun_id_debet' => $acc_akun_id_debet,
			'acc_akun_id_kredit' => $acc_akun_id_kredit,
			'user_ttd' => $user_ttd,
			'deskripsi' => $deskripsi,
			'no_referensi' => $no_referensi,
			'uang_muka' => $uang_muka,
			'premi' => $premi,
			'tanggal' => $tanggal,
			'tanggal_tempo' => $tanggal_tempo,
			'diskon' => $diskon,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'qty' => $qty,
			'ppn' => $ppn,
			'grand_total' => $grand_total,
			'qty_real' => $qty_real,
			'diubah_tanggal' => $diubah_tanggal,
			'diubah_oleh' =>  $arrdata['diubah_oleh'],
		);
		$this->db->where('id', $id);
		$this->db->update('acc_sales_invoice', $data);
		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_sales_invoice', $data);


		return true;
	}
	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,b.no_spk, c.nama_customer,c.alamat, c.no_npwp  from acc_sales_invoice a 
		left join sls_kontrak b on a.sls_kontrak_id=b.id
		inner join gbm_customer c on a.customer_id=c.id
		inner join gbm_organisasi d on a.lokasi_id=d.id
		where a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}
