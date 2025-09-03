<?php


class SlsInvoiceModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('sls_invoice');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;
		$result = $this->db->query("SELECT a.*,c.no_spk,c.customer_id, d.nama_customer from sls_invoice a inner join sls_rekap_hd b on a.sls_rekap_id=b.id
inner join sls_kontrak c on b.spk_id=c.id
inner join gbm_customer d on c.customer_id=d.id
where a.id=" . $id . "")->row_array();
		$this->db->where('id', $id);

		return $result;
	}
	public function retrieve_all()
	{
		$query  = "SELECT * from sls_invoice ";

		return $this->db->query($query)->result_array();;
	}

	public function create(
		$arrdata
	) {

		$lokasi_id = $arrdata['lokasi_id']['id'];
		$sls_rekap_id = $arrdata['sls_rekap_id'];

		$no_invoice  = $arrdata['no_invoice'];
		$no_rekap    =  $arrdata['no_rekap'];
		$tanggal    =  $arrdata['tanggal'];

		$total_berat_terima    =  $arrdata['total_berat_terima'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$sub_total    =  $arrdata['sub_total'];

		$disc    =  $arrdata['disc'];
		$uang_muka    =  $arrdata['uang_muka'];
		$ppn    =  $arrdata['ppn'];
		$dpp    =  $arrdata['dpp'];
		$grand_total    =  $arrdata['grand_total'];

		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'sls_rekap_id' => $sls_rekap_id,
			'lokasi_id' => $lokasi_id,
			'no_invoice' => $no_invoice,
			'no_rekap' => $no_rekap,
			'uang_muka' => $uang_muka,
			'tanggal' => $tanggal,
			'total_berat_terima' => $total_berat_terima,
			'disc' => $disc,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'sub_total' => $sub_total,
			'ppn' => $ppn,
			'dpp' => $dpp,
			'grand_total' => $grand_total,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->insert('sls_invoice', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;
		$lokasi_id = $arrdata['lokasi_id']['id'];
		$sls_rekap_id = $arrdata['sls_rekap_id'];

		$no_invoice  = $arrdata['no_invoice'];
		$no_rekap    =  $arrdata['no_rekap'];
		$tanggal    =  $arrdata['tanggal'];
		$total_berat_terima    =  $arrdata['total_berat_terima'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$sub_total    =  $arrdata['sub_total'];

		$disc    =  $arrdata['disc'];
		$uang_muka    =  $arrdata['uang_muka'];
		$ppn    =  $arrdata['ppn'];
		$dpp    =  $arrdata['dpp'];
		$grand_total    =  $arrdata['grand_total'];

		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'sls_rekap_id' => $sls_rekap_id,
			'lokasi_id' => $lokasi_id,
			'no_invoice' => $no_invoice,
			'no_rekap' => $no_rekap,
			'uang_muka' => $uang_muka,
			'tanggal' => $tanggal,
			'total_berat_terima' => $total_berat_terima,
			'disc' => $disc,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'sub_total' => $sub_total,
			'ppn' => $ppn,
			'dpp' => $dpp,
			'grand_total' => $grand_total,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('sls_invoice', $data);
		return true;
	}

	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,c.no_spk,c.customer_id, d.nama_customer from sls_invoice a inner join sls_rekap_hd b on a.sls_rekap_id=b.id
		inner join sls_kontrak c on b.spk_id=c.id
		inner join gbm_customer d on c.customer_id=d.id
		where a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}
