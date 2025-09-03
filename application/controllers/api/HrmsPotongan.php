<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class HrmsPotongan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsPotonganModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "select a.*,b.nip,b.nama,c.nama as tipe_gaji from payroll_potongan a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_tipe_gaji c on a.tipe_gaji_id=c.id
		";
		$search = array('a.tanggal', 'b.nip', 'b.nama','c.nama','a.keterangan');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->HrmsPotonganModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->HrmsPotonganModel->retrieve_all_item();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{


		$retrieve =  $this->HrmsPotonganModel->create($this->post());
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_lembur', 'action' => 'new', 'entity_id' => $retrieve);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $this->post()['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->HrmsPotonganModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->HrmsPotonganModel->update($item['id'], $this->post());
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_lembur', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->HrmsPotonganModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->HrmsPotonganModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'hrms_lembur', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function hitung_lembur_post()
	{
		$tipe_lembur     = $this->post('tipe_lembur');
		$jumlah_jam = $this->post('jumlah_jam');
		$karyawan_id = $this->post('karyawan_id');
		$gapok = 0;
		$res_gaji = $this->db->query("select a.*,b.lokasi_tugas_id from payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id where karyawan_id=" . $karyawan_id  . "")->row_array();
		if ($res_gaji) {
			$gapok = $res_gaji['gapok'];
		}
		$res =	$this->db->query("select * from payroll_basis_lembur where tipe_lembur='" . $tipe_lembur . "'
		and  basis_jam_lembur <= " . ceil($jumlah_jam) . "
		and lokasi_id=". $res_gaji['lokasi_tugas_id']." order by basis_jam_lembur ")->result_array();
		$jml_jam =  $jumlah_jam;
		$hasil_jam = 0.0;

		if ($res) {

			foreach ($res as $key => $lembur) {

				if ($jml_jam >= 1) {
					$hasil_jam = $hasil_jam + (1 * $lembur['jumlah_jam_lembur']);
					$jml_jam = $jml_jam - 1;
				} else {
					if ($jml_jam > 0) {
						$hasil_jam = $hasil_jam + ($jml_jam * $lembur['jumlah_jam_lembur']);
						$jml_jam = 0;
					}
				}
			}
		}
		$gapok_per_jam = 1 / 173 * $gapok;
		$data = array("gapok" => $gapok, "gapok_perjam" => $gapok_per_jam, "jumlah_jam" => $hasil_jam);
		$this->set_response(array("status" => "OK", "data" =>	$data), REST_Controller::HTTP_OK);
	}

// 	function laporan_lembur_post()
// 	{


// 		$karyawan_id     = $this->post('karyawan_id', true);
// 		$tgl_mulai =  $this->post('tgl_mulai', true);
// 		$tgl_akhir = $this->post('tgl_akhir', true);

// 		$retrieveKaryawan = $this->KaryawanModel->retrieve(
// 			$karyawan_id,
// 		);
// 		$retrieveAbsensi = $this->db->query("select a.*,b.nama as lokasi from payroll_lembur a
// 		left join payroll_lokasi b
// 		on a.lokasi_id=b.id 
// 		where a.karyawan_id=" . $karyawan_id . " and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
// 		order by tanggal")->result_array();

// 		// var_dump($results);
// 		$this->load->helper("terbilang");
// 		$this->load->helper("antech_helper");
// 		$this->load->library('pdfgenerator');
// 		$html = "<style type='text/css'>
// 		h3.title {
// 			margin-bottom: 0px;
// 			line-height: 30px;
// 		}
// 		hr.top {
// 			border: none;
// 			border-bottom: 2px solid #333;
// 			margin-bottom: 10px;
// 			margin-top: 10px;
// 		}
// 		.kop-print {
// 		  width: 700px;
// 		  margin: auto;
// 	  }
  
// 	  .kop-print img {
// 		  float: left;
// 		  height: 60px;
// 		  margin-right: 20px;
// 	  }
  
// 	  .kop-print .kop-info {
// 		  font-size: 15px;
// 	  }
  
// 	  .kop-print .kop-nama {
// 		  font-size: 25px;
// 		  font-weight: bold;
// 		  line-height: 35px;
// 	  }
  
// 	  .kop-print-hr {
// 		  border-width: 2px;
// 		  border-color: black;
// 		  margin-bottom: 0px;
// 	  }
// 	  table {
// 		border-collapse: separate;
// 		border-spacing: 0;
// 		color: #4a4a4d;
// 		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
// 	  }
// 	  th,
// 	  td {
// 		padding: 10px 15px;
// 		vertical-align: middle;
// 	  }
// 	  thead {
// 		background: #395870;
// 		background: linear-gradient(#49708f, #293f50);
// 		color: #fff;
// 		font-size: 11px;
// 		text-transform: uppercase;
// 	  }
// 	  th:first-child {
// 		border-top-left-radius: 5px;
// 		text-align: left;
// 	  }
// 	  th:last-child {
// 		border-top-right-radius: 5px;
// 	  }
// 	  tbody tr:nth-child(even) {
// 		background: #f0f0f2;
// 	  }
// 	  td {
// 		border-bottom: 1px solid #cecfd5;
// 		border-right: 1px solid #cecfd5;
// 	  }
// 	  td:first-child {
// 		border-left: 1px solid #cecfd5;
// 	  }
// 	  .book-title {
// 		color: #395870;
// 		display: block;
// 	  }
// 	  .text-offset {
// 		color: #7c7c80;
// 		font-size: 12px;
// 	  }
// 	  .item-stock,
// 	  .item-qty {
// 		text-align: center;
// 	  }
// 	  .item-price {
// 		text-align: right;
// 	  }
// 	  .item-multiple {
// 		display: block;
// 	  }
// 	  tfoot {
// 		text-align: right;
// 	  }
// 	  tfoot tr:last-child {
// 		background: #f0f0f2;
// 		color: #395870;
// 		font-weight: bold;
// 	  }
// 	  tfoot tr:last-child td:first-child {
// 		border-bottom-left-radius: 5px;
// 	  }
// 	  tfoot tr:last-child td:last-child {
// 		border-bottom-right-radius: 5px;
// 	  }
	  
  
// 	</style>
//   ";

// 		$html = $html . '<div class="row">
//   <div class="span12">
// 	  <br>
// 	  <div class="kop-print">
// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
// 	  </div>
// 	  <hr class="kop-print-hr">
//   </div>
//   </div>
//   <h2>Laporan Lembur</h2>
//   <h3>Karyawan:' . $retrieveKaryawan['nama'] . ' </h3>
//   <h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

// 		$html = $html . ' <table  >
// 			<thead>
// 				<tr>
// 				<th width="4%">No.</th>
// 				<th>Lokasi Kerja</th>
// 				<th>Tanggal</th>
// 				<th>Jam Mulai</th>
// 				<th>Jam Selesai</th>
// 				<th style="text-align: right;">Jumlah Jam </th>
// 				<th style="text-align: right;">Lembur Per Jam </th>
// 				<th style="text-align: right;">Nilai Lembur </th>
				
// 				</tr>
// 			</thead>
// 			<tbody>';


// 		$no = 0;
// 		$jumlah = 0;
// 		$jumlahJam = 0;



// 		foreach ($retrieveAbsensi as $key => $m) {
// 			$no++;
// 			$jumlah = $jumlah + $m['nilai_lembur'];
// 			$jumlahJam = $jumlahJam + $m['jumlah_jam'];
// 			$html = $html . ' 	<tr class=":: arc-content">
// 						<td style="position:relative;">
// 							' . ($no) . '

// 						</td>
// 						<td>
// 						' . $m['lokasi'] . ' 
							
// 						</td>
// 						<td>
// 						' . $m['tanggal'] . ' 
						
// 						</td>
// 						<td>
// 						' . $m['mulai'] . ' 
						
// 						</td>
// 						<td>
// 							' . $m['selesai'] . ' 
// 						</td>
// 						<td style="text-align: right;">' . number_format($m['jumlah_jam']) . ' 
// 						<td style="text-align: right;">' . number_format($m['lembur_perjam']) . ' 
// 						<td style="text-align: right;">' . number_format($m['nilai_lembur']) . ' 
							
// 						</td>';

// 			$html = $html . '
						
						
// 					</tr>';
// 		}

// 		$html = $html . ' 	
// 						<tr class=":: arc-content">
// 						<td style="position:relative;">
// 							&nbsp;

// 						</td>
// 						<td>
// 							&nbsp;
// 						</td>
// 						<td>
// 							&nbsp;
// 						</td>
// 						<td>
// 							&nbsp;
// 						</td>
// 						<td>
// 							&nbsp;
// 						</td>

// 						<td style="text-align: right;">
// 						' . number_format($jumlahJam) . ' 
// 						</td>
// 						<td style="text-align: right;">
							
// 						</td>
// 						<td style="text-align: right;">
// 						' . number_format($jumlah) . ' 
// 						</td>

						
// 						</tr>
// 								</tbody>
// 							</table>
// 						';
// 		// $filename = 'report_' . time();
// 		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
// 		echo $html;
// 	}
	function get_path_file($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	function import_post()
	{

		$config['upload_path']   = $this->get_path_file();
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_siswa.xls';
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];
			$excel = array();
			$excel = $this->import($config['upload_path'] . '/' . $filename);
			//   print_r(count( $excel));
			if (count($excel) > 0) {
				for ($i = 2; $i < (count($excel) + 1); $i++) {
					$data = $excel[$i];
					if (!empty($data['A'])) {
						$kode_lokasi   =  $data['A'];
						$nip          =  $data['B'];
						$tanggal         =  $data['C'];
						$tipe_lembur = $data['D'];
						$mulai =  $data['E'];
						$selesai  =  $data['F'];
						$istirahat  = (double) $data['G'];


						$result = $this->db->query("select * from karyawan where nip='" . $nip . "'");
						$karyawan = $result->row_array();
						$karyawan_id = $karyawan['id'];

						$gapok = 0;
						$res_gaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $karyawan_id  . "")->row_array();
						if ($res_gaji) {
							$gapok = $res_gaji['gapok'];
						}

						$result = $this->db->query("select * from gbm_organisasi where kode='" . $kode_lokasi . "'");
						$lokasi = $result->row_array();



						if (empty($karyawan) || empty($lokasi) || empty($res_gaji)) {
						} else {
							$jumlah_jam = 0;
							$tgl1 = strtotime($tanggal . ' ' . $mulai);
							$tgl2 = strtotime($tanggal . ' ' . $selesai);
							if ($tgl1 > $tgl2) {
								$tgl2 = $tgl2+86400;
							}
							// $diff = $tgl2->diff($tgl1);
							// $hours = $diff->h;
							$diff = $tgl2 - $tgl1;
							$jumlah_jam = $diff / ( 60 * 60 );
							$jml_jam =  $jumlah_jam - $istirahat;
							$res =	$this->db->query("select * from payroll_basis_lembur where tipe_lembur='" . $tipe_lembur . "'
									and  basis_jam_lembur <= " . ceil($jml_jam) . " order by basis_jam_lembur ")->result_array();
							
							
							$hasil_jam = 0.0;

							if ($res) {

								foreach ($res as $key => $lembur) {

									if ($jml_jam >= 1) {
										$hasil_jam = $hasil_jam + (1 * $lembur['jumlah_jam_lembur']);
										$jml_jam = $jml_jam - 1;
									} else {
										if ($jml_jam > 0) {
											$hasil_jam = $hasil_jam + ($jml_jam * $lembur['jumlah_jam_lembur']);
											$jml_jam = 0;
										}
									}
								}
							}
							$gapok_per_jam = 1 / 173 * $gapok;
							$lokasi_id      = $lokasi['id'];
						
							$data_save = array(
								'selesai' => $selesai,
								'mulai' => $mulai,
								'karyawan_id'    => $karyawan_id,
								'lokasi_id'    => $lokasi_id,
								'nilai_lembur' => ($gapok_per_jam) * $hasil_jam,
								'tanggal' => $tanggal,
								'jumlah_jam' => $jumlah_jam,
								'tipe_lembur' => $tipe_lembur,
								'istirahat' => $istirahat

							);

							# simpan data absen
							$id = $this->HrmsPotonganModel->create(
								$data_save
							);
						}
					}
				}
			}
			//  var_dump($excel);
			//  exit();
			$this->set_response([
				'status' => 'OK',
				'message' => 'Data berhasil diimport',
			], REST_Controller::HTTP_CREATED);
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response([
					'status' => 'NOT OK',
					'message' => 'Gagal import',
				], REST_Controller::HTTP_NOT_FOUND);
			}
		}
	}
	function import($path_file)
	{

		// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); //Excel 2007 or higher
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); //Excel 2003
		$spreadsheet = $reader->load($path_file);
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}
}
