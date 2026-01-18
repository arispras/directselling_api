<?php


class InvItemModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('inv_item');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('inv_item', 1);
		return $result->row_array();
	}
	public function retrieve_all_item()
	{
		$this->db->select('a.*,b.kode as uom');
		$this->db->from('inv_item a');
		$this->db->join("gbm_uom b", "b.id = a.uom_id", "left");
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_item_produk()
	{
		$this->db->select('a.*,b.kode as uom');
		$this->db->from('inv_item a');
		$this->db->join("gbm_uom b", "b.id = a.uom_id", "left");
		$this->db->where("tipe_produk<>''", NULL, FALSE);
		$this->db->where('tipe_produk is NOT NULL', NULL, FALSE);
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_item_bahan_bkm()
	{
		$this->db->select('a.*,b.kode as uom');
		$this->db->from('inv_item a');
		$this->db->join("gbm_uom b", "b.id = a.uom_id", "left");
		$this->db->join("inv_kategori c", "c.id = a.inv_kategori_id", "left");
		$this->db->where_in("c.id", [4,18,23,24]); // pupuk,bahan kimia,kacanagan
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_item_suku_cadang()
	{
		$this->db->select('a.*,b.kode as uom');
		$this->db->from('inv_item a');
		$this->db->join("gbm_uom b", "b.id = a.uom_id", "left");
		$this->db->join("inv_kategori c", "c.id = a.inv_kategori_id", "left");
		$this->db->where_in("c.id", [17,19]); // bbm pelumas,bahan suku cadang
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {

		$kode = $arrdata['kode'];
		$nama    =  $arrdata['nama'];
		$inv_kategori_id  = (int) $arrdata['inv_kategori_id'];
		$uom_id    = (int)  $arrdata['uom_id'];
		$min_stok    =  $arrdata['min_stok'];
		$harga_jual1    =  $arrdata['harga_jual1'];
		$harga_jual2    =  $arrdata['harga_jual2'];
		$komisi_sales    =  $arrdata['komisi_sales'];
		$bonus_sales    =  $arrdata['bonus_sales'];
		$jenis_item    =  $arrdata['jenis_item'];
		$aktif    =  $arrdata['aktif'];

		$data = array(
			'kode' => $kode,
			'nama' => $nama,
			'inv_kategori_id'    => $inv_kategori_id,
			'uom_id'    => $uom_id,
			'harga_jual1' => $harga_jual1,
			'harga_jual2' => $harga_jual2,
			'komisi_sales' => $komisi_sales,
			'bonus_sales' => $bonus_sales,
			'min_stok' => $min_stok,
			'jenis_item' => $jenis_item,
			'aktif' => $aktif
		);
		$this->db->insert('inv_item', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$kode = $arrdata['kode'];
		$nama    =  $arrdata['nama'];
		$inv_kategori_id  = (int) $arrdata['inv_kategori_id'];
		$uom_id    = (int) $arrdata['uom_id'];
		$min_stok    =  $arrdata['min_stok'];
		$harga_jual1    =  $arrdata['harga_jual1'];
		$harga_jual2    =  $arrdata['harga_jual2'];
		$komisi_sales    =  $arrdata['komisi_sales'];
		$bonus_sales    =  $arrdata['bonus_sales'];
		$jenis_item    =  $arrdata['jenis_item'];
		$aktif    =  $arrdata['aktif'];

		$data = array(
			'kode' => $kode,
			'nama' => $nama,
			'inv_kategori_id'    => $inv_kategori_id,
			'uom_id'    => $uom_id,
			'min_stok' => $min_stok,
			'harga_jual1' => $harga_jual1,
			'harga_jual2' => $harga_jual2,
			'komisi_sales' => $komisi_sales,
			'bonus_sales' => $bonus_sales,
			'jenis_item' => $jenis_item,
			'aktif' => $aktif
		);
		$this->db->where('id', $id);
		$this->db->update('inv_item', $data);
		return true;
	}
	function getStok($item_id, $gudang_id)
	{
		// $item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
		// 	  and item_id=" . $item_id . "")->row_array();
		$item_dt = $this->db->query("select sum(qty_masuk-qty_keluar)as qty from inv_transaksi_harian  where gudang_id=" . $gudang_id . "
		and item_id=" . $item_id . "")->row_array();

		$harga = 0;
		$stok = 0;
		if ($item_dt) {
			//$harga = $item_dt['nilai'] / $item_dt['qty'];
			$stok =  $item_dt['qty'];
		}
		// $nilai =  ($avg_price * $value['qty']);
		return $stok;

	}
	function getStokPerTanggal($item_id, $gudang_id, $tgl)
	{
		$item = $this->db->query("select sum(qty_masuk-qty_keluar)as stok from inv_transaksi_harian  where gudang_id=" . $gudang_id . "
			  and item_id=" . $item_id . " and tanggal<='" . $tgl . "'")->row_array();
		$harga = 0;
		$stok = 0;
		if ($item) {
			//$harga = $item['nilai'] / $item['qty'];
			$stok =  $item['stok'];
		}
		// $nilai =  ($avg_price * $value['qty']);
		return $stok;

	}
	public function cek_stok_lokasi_get($lokasi_id, $item_id, $tanggal)
	{
		$query = "SELECT e.id, a.item_id,
		sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
		inner join inv_item c on a.item_id=c.id
		inner join gbm_organisasi d on a.gudang_id=d.id
		inner join gbm_organisasi e on d.parent_id=e.id
		left join gbm_uom f on c.uom_id=f.id
		where e.id=" . $lokasi_id . " and a.item_id=" . $item_id . " and a.tanggal <='" . $tanggal . "'
		group by  e.id,a.item_id";

		$res   = $this->db->query($query)->row_array();
		$stok=0;
		if ( $res['stok']){
			$stok=$res['stok'];
		}
		return $stok;
	}
	
}
