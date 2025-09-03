<?php




require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
require 'vendor/autoload.php';


class PksSjppModel extends CI_Model
{


    

 
   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_sjpp');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $sjpp['mill_id'] = $input['mill_id']['id'];
        $sjpp['intruksi_id'] = $input['intruksi_id']['id'];
        $sjpp['no_surat'] = $input['no_surat'];
        $sjpp['no_ktp_sim'] = $input['no_ktp_sim'];
        $sjpp['alamat_pengiriman'] = $input['alamat_pengiriman'];
        $sjpp['tanggal'] = $input['tanggal'];
        $sjpp['diubah_oleh']=$input['diubah_oleh'];
        $sjpp['diubah_tanggal']=date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update('pks_sjpp', $sjpp);
        
        
        $timb['ffa'] = $input['ffa'];
        $timb['moisture'] = $input['moisture'];
        $timb['dirt'] = $input['dirt'];
        $timb['dobi'] = $input['dobi'];
        $timb['no_segel'] = $input['no_segel'];
        
        $this->db->where("id", $input['pks_timbangan_kirim_id']);
        $this->db->update("pks_timbangan_kirim", $timb);


        return true;
    }
	public function update_sjpp_customer($id, $input)
    {
        $id = (int)$id;
		$data=array(
			'tanggal_customer'=>$input['tanggal_customer'],
			'ffa_customer'=>$input['ffa_customer'],
			'dobi_customer'=>$input['dobi_customer'],
            'moist_customer'=>$input['moist_customer'],
			'dirt_customer'=>$input['dirt_customer'],
			'bruto_customer'=>$input['bruto_customer'],
			'tara_customer'=>$input['tara_customer'],
			'netto_customer'=>$input['netto_customer']

		);
        $this->db->where('id', $id);
        $this->db->update('pks_sjpp', $data);
        return true;
    }

   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_sjpp', 1);
        return $result->row_array();
    }

	public function retrieveSJCustomer($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_timbangan_kirim_sj_vw', 1);
        return $result->row_array();
    }
	public function retrieveAll()
    {
     
        $result = $this->db->get('pks_timbangan_kirim_sj_vw');
        return $result->result_array();
    }

	public function retrieveRekapKirim($spk_id)
    {
		$spk_id = (int)$spk_id;

        $result = $this->db->query("select * from pks_timbangan_kirim_sj_vw where 
		spk_id=".$spk_id ." and surat_jalan_id not in  ( select sjpp_id from sls_rekap_dt ) and tanggal_terima!='' 
		order by tanggal_timbang,no_tiket");
        return $result->result_array();
    }
	public function retrieveRekapKirimBySpkPeriode($spk_id,$periode_kt_dari,$periode_kt_sd)
    {
		$spk_id = (int)$spk_id;
        $result = $this->db->query("select * from pks_timbangan_kirim_sj_vw where 
		spk_id=".$spk_id ." and surat_jalan_id not in  ( select sjpp_id from sls_rekap_dt ) and tanggal_terima!='' 
		and (tanggal_timbang between '". $periode_kt_dari ."' and '".$periode_kt_sd."')
		order by tanggal_timbang,no_tiket");
        return $result->result_array();
    }
	public function retrieveRekapTransportirByPeriode($transportir_id,$periode_kt_dari,$periode_kt_sd,$produk_id,$sls_kontrak_id)
    {
		$transportir_id = (int)$transportir_id;
        $result = $this->db->query("select * from pks_timbangan_kirim_sj_vw 
		where transportir_id=".$transportir_id ."
		 and surat_jalan_id not in  ( select sjpp_id from prc_rekap_angkut_dt ) and (tanggal_terima !='' or tanggal_terima is not null) 
		and (tanggal_timbang between '". $periode_kt_dari ."' and '".$periode_kt_sd."')
		and produk_id=". $produk_id ."
		and spk_id=". $sls_kontrak_id ."
		order by tanggal_timbang,no_tiket");
        return $result->result_array();
    }
	public function retrieveRekapInternalTransportirByPeriode($transportir_id,$periode_kt_dari,$periode_kt_sd,$produk_id,$sls_kontrak_id)
    {
		$transportir_id = (int)$transportir_id;
        $result = $this->db->query("select * from pks_timbangan_kirim_sj_vw 
		where transportir_id=".$transportir_id ."
		 and surat_jalan_id not in  ( select sjpp_id from prc_rekap_angkut_dt ) 
		and (tanggal_timbang between '". $periode_kt_dari ."' and '".$periode_kt_sd."')
		and produk_id=". $produk_id ."
		and spk_id=". $sls_kontrak_id ."
		order by tanggal_timbang,no_tiket");
        return $result->result_array();
    }
    public function retrieveRekapById($id)
    {
		$id = (int)$id;

        $result = $this->db->query("select * from pks_timbangan_kirim_sj_vw where surat_jalan_id in ( select sjpp_id from sls_rekap_dt where rekap_id=".$id." ) and tanggal_terima!='' ");
        return $result->result_array();
    }

    
    public function create($input)
    {
        $sjpp['mill_id'] = $input['mill_id']['id'];
        $sjpp['intruksi_id'] = $input['intruksi_id']['id'];
        // $sjpp['customer_id'] = $input['customer_id']['id'];
        $sjpp['no_surat'] = $input['no_surat'];
        $sjpp['no_ktp_sim'] = $input['no_ktp_sim'];
        $sjpp['alamat_pengiriman'] = $input['alamat_pengiriman'];
        $sjpp['pks_timbangan_kirim_id'] = $input['pks_timbangan_kirim_id'];
        $sjpp['tanggal'] = $input['tanggal'];
        $sjpp['dibuat_oleh']=$input['dibuat_oleh'];
        $sjpp['dibuat_tanggal']=date('Y-m-d H:i:s');
        
        $this->db->insert('pks_sjpp', $sjpp);
        $id = $this->db->insert_id();
        
        
        $timb['ffa'] = $input['ffa'];
        $timb['moisture'] = $input['moisture'];
        $timb['dirt'] = $input['dirt'];
        $timb['dobi'] = $input['dobi'];
        $sjpp['no_segel'] = $input['no_segel'];
        
        $this->db->where("id", $input['pks_timbangan_kirim_id']);
        $this->db->update("pks_timbangan_kirim", $timb);

        return $id;
    }

	public function create_from_import($input)
    {
      
		// try {
			//$input['diubah_tanggal'] = date('Y-m-d H:i:s');
			$this->db->insert('pks_sjpp', $input);
				$db_error = $this->db->error();
			if (!empty($db_error)) {

			  if(	$db_error['message']){
				return $db_error['message']; 

			  }
			
				
			}
			return $this->db->insert_id();
		// } catch (Exception $e) {
		// 	return var_dump($e);
		// }
		
    }


    public function print_slip(
		$id = null
	) {
		$query = "SELECT 
            
            d.nama AS nama_barang,
            g.no_spk AS no_do,
            b.no_kontrak_timbangan AS no_kontrak,
            e.no_transaksi AS no_ip,
            b.no_kendaraan,
            b.nama_supir,
            b.tara_kirim,
            b.bruto_kirim,
            b.netto_kirim,
            b.no_segel,
            b.ffa,
            b.moisture,
            b.dirt,
            b.dobi,
            c.nama_customer,
            a.no_surat,
            a.tanggal,
            a.no_ktp_sim,
            b.no_tiket AS no_karcis,
            d.kode AS kode_barang
            -- d.*
            
            -- c.kode,
            -- c.nama,
            -- c.satuan
		FROM pks_sjpp a 
        INNER JOIN pks_timbangan_kirim b ON a.pks_timbangan_kirim_id = b.id    
        INNER JOIN inv_item d ON b.item_id = d.id
        INNER JOIN sls_intruksi_kirim e ON b.instruksi_id = e.id
        INNER JOIN pks_tanki f on b.tangki_id = f.id
        INNER JOIN sls_kontrak g on e.spk_id = g.id
		INNER JOIN gbm_customer c ON g.customer_id = c.id
        -- inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}

    public function laporanRekapPengiriman( $input='' )
    {
        // $this->db->select("
        //     c.nama as nama_produk,

        //     b.no_spk as no_kontrak,
        //     b.dobi as dobi_kirim,
        //     b.mi as mi_kirim,
        //     b.moisture as moist_kirim,

        //     a.nama_pelanggan as customer,

        //     a.no_polisi as no_polisi,
        //     a.nama_pengemudi as nama_supir,
        //     a.nama_pengemudi,
        //     a.tanggal as tanggal_kirim,
        //     a.tanggal_customer as tanggal_terima,
            
        //     a.ffa as ffa_kirim,
        //     a.dirt as dirt_kirim,
        //     a.tara_kirim as tara_kirim,
        //     a.tara_kirim as tare_kirim,
        //     a.bruto_kirim as gross_kirim,
        //     a.bruto_kirim as bruto_kirim,
        //     a.netto_kirim as netto_kirim,
            
        //     a.ffa_customer as ffa_terima,
        //     a.dirt_customer as dirt_terima,
        //     a.tara_customer as tara_terima,
        //     a.tara_customer as tare_terima,
        //     a.bruto_customer as gross_terima,
        //     a.bruto_customer as bruto_terima,
        //     a.netto_customer as netto_terima
        // ");
        $this->db->select('*');
        $this->db->from("pks_sjpp a");
        $this->db->join("pks_timbangan_kirim b", "a.pks_timbangan_kirim_id = b.id");
        $this->db->join("sls_intruksi_kirim c", "a.intruksi_id = c.id");
        // $this->db->join("sls_intruksi_kirim c", "b.spk_id = b.spk_id");
        // $this->db->join("sls_kontrak b", "a.kontrak_id = b.id");
        // $this->db->join("inv_item c", "a.produk_id = c.id");
        
        // $this->db->where("b.id", $input['kontrak_id']);
        // $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        // $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

		$data = $this->db->get()->result();
        return $data;
    }
    public function laporanRekapPengirimanCount( $input='' )
    {
        $this->db->from("pks_sjpp a");
        $this->db->join("sls_kontrak b", "a.kontrak_id = b.id");
        $this->db->join("inv_item c", "a.produk_id = c.id");
        $this->db->select_avg("a.ffa");
        $this->db->select_avg("b.mi");
        $this->db->select_avg("b.moisture");
        $this->db->select_avg("a.dirt");
        $this->db->select_sum("b.dobi");
        $this->db->select_sum("a.bruto_kirim");
        $this->db->select_sum("a.tara_kirim");
        $this->db->select_sum("a.netto_kirim");
        $this->db->select_avg("a.ffa_customer");
        $this->db->select_sum("a.bruto_customer");
        $this->db->select_sum("a.tara_customer");
        $this->db->select_sum("a.netto_customer");
        
        $this->db->where("b.id", $input['kontrak_id']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $data = $this->db->get()->row();
        return $data;
    }


    public function exportAbsensiCPO( $Data=null ) {
        $ParamW = [$Data["MONTHX"]];
        $result = [];

        // echo '<pre>';
        // print_r($Data);
        // die;

        $FRange = "Bulan : " . substr($Data["MONTHX"],0,7);
        $GExport = "Date Export : " . date("Y/m/d") ;
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()->setCreator("IT THERMO")
                ->setLastModifiedBy("IT THERMO")
                ->setTitle("Report Entry Data")
                ->setSubject("Report Entry Data")
                ->setDescription("Data Absensi Digital Siswa, $FRange, $GExport")
                ->setKeywords("Report Entry Data")
                ->setCategory("Report Entry Data");
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Data');
        $i = 1;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(3);
        // $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(38);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(13.57);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(2.30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15.14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(2.30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15.14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(33);
        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(36);
        $objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(42);

        $objPHPExcel->getActiveSheet()->mergeCells('B2:D2');
        $objPHPExcel->getActiveSheet()->mergeCells('B3:D5');
        $objPHPExcel->getActiveSheet()->mergeCells('E2:G3');
        $objPHPExcel->getActiveSheet()->mergeCells('B15:C15');
        $objPHPExcel->getActiveSheet()->mergeCells('B17:C17');
        $objPHPExcel->getActiveSheet()->mergeCells('E15:F15');
        $objPHPExcel->getActiveSheet()->mergeCells('E17:F17');


        $objPHPExcel->getActiveSheet()->getStyle('A1:I18')->applyFromArray([
            'borders'=> [
                'outline' => [
                    'borderStyle' => Border::BORDER_THICK,
                    'color' => array('argb' => '000000')
                ]
            ]
        ]);
        $objPHPExcel->getActiveSheet()->getStyle('E8:G11')->applyFromArray([
            'borders'=> [
                'outline' => [
                    'borderStyle' => Border::BORDER_THICK,
                    'color' => array('argb' => '000000')
                ]
            ]
        ]);



        $objPHPExcel->getActiveSheet()->setCellValue('H3', "PT. KUTAI REFINERY NUSANTARA");
        $objPHPExcel->getActiveSheet()->getStyle('H3')->applyFromArray([
            'alignment'=> [
                'horizontal'=> 'left',
                'vertical'=> 'center',
                'wrapText' => TRUE
            ]
        ]);


        $objPHPExcel->getActiveSheet()->setCellValue('E4', "No.");
        $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F4', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G4', $Data['no_surat']);

        $objPHPExcel->getActiveSheet()->setCellValue('E5', "Tanggal");
        $objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F5', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G5', date('Y-m-d'));

        $objPHPExcel->getActiveSheet()->setCellValue('B7', "Nama Barang");
        $objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray([
            'font' => [ 'bold' => TRUE ],
            'alignment'=> [ 'vertical'=>'top' ] 
        ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C7', ":");
        $objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray([ 'alignment'=> [ 'vertical'=>'top' ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('D7', $Data['nama_barang']);
        $objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray([ 'alignment'=> [ 'vertical'=>'top' ] ]);

        $objPHPExcel->getActiveSheet()->setCellValue('B8', "No. Kontrak");
        $objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C8', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D8', $Data['no_kontrak']);

        $objPHPExcel->getActiveSheet()->setCellValue('B9', "No. IP");
        $objPHPExcel->getActiveSheet()->getStyle('B9')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C9', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D9', $Data['no_ip']);

        $objPHPExcel->getActiveSheet()->setCellValue('B10', "No. Mobil");
        $objPHPExcel->getActiveSheet()->getStyle('B10')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C10', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D10', $Data['no_kendaraan']);

        $objPHPExcel->getActiveSheet()->setCellValue('B11', "Bruto kirim");
        $objPHPExcel->getActiveSheet()->getStyle('B11')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C11', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D11', $Data['bruto_kirim']);

        $objPHPExcel->getActiveSheet()->setCellValue('B12', "Tara Kirim");
        $objPHPExcel->getActiveSheet()->getStyle('B12')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C12', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D12', $Data['tara_kirim']);

        $objPHPExcel->getActiveSheet()->setCellValue('B13', "Netto Kirim");
        $objPHPExcel->getActiveSheet()->getStyle('B13')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('C13', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('D13', $Data['netto_kirim']);

        $objPHPExcel->getActiveSheet()->setCellValue('E7', "No Segel");
        $objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray([
            'font' => [ 'bold' => TRUE ],
            'alignment'=> [ 'vertical'=>'top' ] 
        ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F7', ":");
        $objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray([ 'alignment'=> [ 'vertical'=>'top' ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('G7', $Data['no_segel']);
        $objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray([ 'alignment'=> [ 'vertical'=>'top' ] ]);

        $objPHPExcel->getActiveSheet()->setCellValue('E8', "FFA");
        $objPHPExcel->getActiveSheet()->getStyle('E8')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F8', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G8', $Data['ffa']);

        $objPHPExcel->getActiveSheet()->setCellValue('E9', "MOISTURE");
        $objPHPExcel->getActiveSheet()->getStyle('E9')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F9', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G9', $Data['moisture']);

        $objPHPExcel->getActiveSheet()->setCellValue('E10', "DIRT");
        $objPHPExcel->getActiveSheet()->getStyle('E10')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F10', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G10', $Data['dirt']);

        $objPHPExcel->getActiveSheet()->setCellValue('E11', "DOBI");
        $objPHPExcel->getActiveSheet()->getStyle('E11')->applyFromArray(['font' => [ 'bold' => TRUE ] ]);
        $objPHPExcel->getActiveSheet()->setCellValue('F11', ":");
        $objPHPExcel->getActiveSheet()->setCellValue('G11', $Data['dobi']);



        $objPHPExcel->getActiveSheet()->setCellValue('B15', "Hormat kami");
        $objPHPExcel->getActiveSheet()->getStyle('B15')->applyFromArray([
            'font'=> [ 'bold' => TRUE ],
            'alignment'=> [ 'horizontal'=> 'center' ]
        ]);
        $objPHPExcel->getActiveSheet()->setCellValue('B17', "(…………………………)");


        $objPHPExcel->getActiveSheet()->setCellValue('E15', "Supir");
        $objPHPExcel->getActiveSheet()->getStyle('E15')->applyFromArray([
            'font'=> [ 'bold' => TRUE ],
            'alignment'=> [ 'horizontal'=> 'center' ]
        ]);
        $objPHPExcel->getActiveSheet()->setCellValue('E17', "(…………………………)");

        $objPHPExcel->getActiveSheet()->setCellValue('H15', "Penerima");
        $objPHPExcel->getActiveSheet()->getStyle('H15')->applyFromArray([
            'font'=> [ 'bold' => TRUE ],
            'alignment'=> [ 'horizontal'=> 'center' ]
        ]);
        $objPHPExcel->getActiveSheet()->setCellValue('H17', "(…………………………)");



        //  ==== STYLING
        // $StyleDefault = [
        //     'bold' => FALSE,
        //     'color' => array('rgb' => '000000'),
        //     'size' => 10,
        //     'name' => 'Calibri'
        // ];
        // $StyleBold = [
        //     'bold' => TRUE,
        //     'color' => array('rgb' => '000000'),
        //     'size' => 12,
        //     'name' => 'Calibri'
        // ];
        // $StyleCenterAll = [
        //     'vertical' => 'center',
        //     'horizontal' => 'center'
        // ];
        // $StyleBorder = [
        //     'inside' => [
        //         'borderStyle' => Border::BORDER_THIN,
        //         'color' => array('argb' => '000000')
        //     ],
        //     'outline' => [
        //         'borderStyle' => Border::BORDER_THICK,
        //         'color' => array('argb' => '000000')
        //     ]
        // ];



        // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "Data Absensi Siswa Digital");
        // $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray([
        //     'font' => [
        //         'bold' => TRUE,
        //         'color' => array('rgb' => '000000'),
        //         'size' => 14,
        //         'name' => 'Calibri'
        //     ]
        // ]);
        
        // loopline 1
        $i++;

        $objPHPExcel->getActiveSheet()->setCellValue('b' . $i, "PT. PALM MAS ASRI");
        $objPHPExcel->getActiveSheet()->getStyle('b' . $i)->applyFromArray([
            'font' => [
                'bold' => TRUE,
                'color' => array('rgb' => '000000'),
                'size' => 12,
                'name' => 'Calibri'
            ],
            'alignment'=> [
                'horizontal'=> 'left',
                'vertical'=> 'center'
            ]
        ]);
        

        $objPHPExcel->getActiveSheet()->setCellValue('e' . $i, "SURAT JALAN");
        $objPHPExcel->getActiveSheet()->getStyle('e' . $i)->applyFromArray([
            'font' => [
                'bold' => TRUE,
                'color' => array('rgb' => '000000'),
                'size' => 18,
                'name' => 'Calibri'
            ],
            'alignment'=> [
                'horizontal'=> 'center',
                'vertical'=> 'center'
            ]
        ]);


        $objPHPExcel->getActiveSheet()->setCellValue('h' . $i, "Kepada Yth, Bapak/Ibu/PT");
        $objPHPExcel->getActiveSheet()->getStyle('h' . $i)->applyFromArray([
            'font' => [
                'bold' => TRUE,
                'color' => array('rgb' => '000000'),
                'size' => 12,
                'name' => 'Calibri'
            ],
            'alignment'=> [
                'horizontal'=> 'left',
                'vertical'=> 'center',
                'wrapText' => TRUE
            ]
        ]);

        $i++;


        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, "Jln. Pluit Permai Ruko No. 21-23 Pluit Village Jakarta 14440-INDONESIA Telp.6211 668 4055 (Hunting) Fax.6211 668 4055");
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->applyFromArray([
            'alignment' => [
                'vertical' => 'top',
                'wrapText' => TRUE
            ]
        ]);
        





        // $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':A' . ($i + 2))->applyFromArray(['font' => $StyleDefault]);
        $i++;
        // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $FRange);
        $i++;
        // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $GExport);
        $i++;



        // $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':BL' . ($i + 2))->applyFromArray([
        //     'font' => $StyleBold, 'alignment' => $StyleCenterAll, 'borders' => $StyleBorder
        // ]);
        // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'NO');
        // $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Nama');
        


        if (count($result) > 0) {
            $iDtAwal = $i;
            $No = 1;
            foreach ($result as $values) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $No);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $values->nama);
                $i++;
                $No++;
            }
            $objPHPExcel->getActiveSheet()->getStyle('A' . $iDtAwal . ':BL' . ($i - 1))->applyFromArray([
                // 'font' => $StyleDefault, 'borders' => $StyleBorder
            ]);
        } else {
            // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "No Data");
            // $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':BL' . $i);
            // $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':BL' . $i)->applyFromArray([
            //     'font' => $StyleDefault, 'alignment' => $StyleCenterAll, 'borders' => $StyleBorder
            // ]);
            $i++;
        }

        // print_r($objPHPExcel);


        $return = [
            'STATUS' => TRUE,
            'Data' => $objPHPExcel
        ];

        return $return;

    }
    public function exportAbsensi( $Data = null )
    {
		// $this->load->model('KelasModel');
		
        try {
			
            $ParamW = [$Data["MONTHX"]];
            // $kelas_nama = $this->KelasModel->retrieve($Data['DEPARTMENT']);
            // $kelas_nama = $kelas_nama['nama'];
            // $result = $this->db->query($SQL)->result();
            $result = [];
			// $FRange = "Bulan : " . Carbon::parse($Data["MONTHX"])->format('M-Y');
            // $GExport = "Date Export : " . Carbon::now('Asia/Jakarta')->format('d-M-Y');
			$FRange = "Bulan : " . substr($Data["MONTHX"],0,7);
            $GExport = "Date Export : " . date("Y/m/d") ;
            $objPHPExcel = new Spreadsheet();
            $objPHPExcel->getProperties()->setCreator("IT THERMO")
                    ->setLastModifiedBy("IT THERMO")
                    ->setTitle("Report Entry Data")
                    ->setSubject("Report Entry Data")
                    ->setDescription("Data Absensi Digital Siswa, $FRange, $GExport")
                    ->setKeywords("Report Entry Data")
                    ->setCategory("Report Entry Data");
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setTitle('Data');
            $i = 1;
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(38);

            $StyleDefault = [
                'bold' => FALSE,
                'color' => array('rgb' => '000000'),
                'size' => 10,
                'name' => 'Calibri'
            ];
            $StyleBold = [
                'bold' => TRUE,
                'color' => array('rgb' => '000000'),
                'size' => 12,
                'name' => 'Calibri'
            ];
            $StyleCenterAll = [
                'vertical' => 'center',
                'horizontal' => 'center'
            ];
            $StyleBorder = [
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => array('argb' => '000000')
                ],
                'outline' => [
                    'borderStyle' => Border::BORDER_THICK,
                    'color' => array('argb' => '000000')
                ]
            ];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "Data Absensi Siswa Digital");
            $objPHPExcel->getActiveSheet()->getStyle('A' . $i)->applyFromArray([
                'font' => [
                    'bold' => TRUE,
                    'color' => array('rgb' => '000000'),
                    'size' => 14,
                    'name' => 'Calibri'
                ]
            ]);
            $i++;
            // $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'Kelas : ' .$kelas_nama);


            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':A' . ($i + 2))->applyFromArray(['font' => $StyleDefault]);
            //$objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $FDepartment);
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $FRange);
            $i++;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $GExport);
            $i++;

            $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':BL' . ($i + 2))->applyFromArray([
                'font' => $StyleBold, 'alignment' => $StyleCenterAll, 'borders' => $StyleBorder
            ]);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'NO');
            // $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':A' . ($i + 2));
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, 'Nama');
            // $objPHPExcel->getActiveSheet()->mergeCells('B' . $i . ':B' . ($i + 2));

            if (count($result) > 0) {
				//var_dump($result);

                $iDtAwal = $i;
                $No = 1;
                foreach ($result as $values) {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $No);
					$objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $values->nama);
					$i++;
                    $No++;
                }
                $objPHPExcel->getActiveSheet()->getStyle('A' . $iDtAwal . ':BL' . ($i - 1))->applyFromArray([
                    'font' => $StyleDefault, 'borders' => $StyleBorder
                ]);
                // $objPHPExcel->getActiveSheet()->getStyle('G' . $iDtAwal . ':G' . ($i - 1))->applyFromArray(['alignment' => $StyleCenterAll]);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "No Data");
                $objPHPExcel->getActiveSheet()->mergeCells('A' . $i . ':BL' . $i);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $i . ':BL' . $i)->applyFromArray([
                    'font' => $StyleDefault, 'alignment' => $StyleCenterAll, 'borders' => $StyleBorder
                ]);
                $i++;
            }
            $return = [
                'STATUS' => TRUE,
                'Data' => $objPHPExcel
            ];
        } catch (Exception $ex) {
            $return = [
                'STATUS' => FALSE,
                'Data' => $ex->getMessage()
            ];
        }
        $this->db->close();
        return $return;
    }


}
