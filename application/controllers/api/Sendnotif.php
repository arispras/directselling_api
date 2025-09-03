<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

class Sendnotif extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		// $this->load->model('LoginModel');
		// $this->load->model('NotificationModel');
		// $this->load->model('TugasModel');
		// $this->load->model('PengajarModel');
		// $this->load->model('KelasModel');
		// $this->load->model('MapelModel');
		// $this->load->model('SiswaModel');
		// $this->load->model('ConfigModel');
		// $this->load->helper('file');
	}


	function sendNotif_post()
	{
		
		$usr=$this->db->query("select * from fwk_users where employee_id=150" )->row_array();

			$email_subject = 'Info Notifikasi Approval PP';
			$email_body    = 'Hallo, Bpk/Ibu. Admiin <br>
			<br>
			No PP : 001/202304 diajukan kepada Anda.<br> 
			<br> <br> Salam 
			 <br> (Antech Sistem)';
			//  $this->set_response($usr, REST_Controller::HTTP_OK);	 
			//  return;
		
	$ret= $this->sendNotification($usr['fcm_token'],$email_subject,$email_body ,'');	
		

		$message = [
			'id' => null,
			'data' => $ret,
		];
		$this->set_response($message, REST_Controller::HTTP_OK);
	}
	function sendNotif__old_post()
	{

		// $post =  $this->post();
		$kode_sekolah = $this->post('kode_sekolah');
		// var_dump($post);exit();
		$message = $this->post('pesan');;
		$dbThermo = $this->load->database('thermo', TRUE);
		$sql = "select * from sekolah where active=1 ";

		if ($kode_sekolah != null && $kode_sekolah != '') {
			$sql = "select * from sekolah where active=1 and kode_sekolah='" . $kode_sekolah . "' ";
		}
		$sekolah = $dbThermo->query($sql)->result_array();
		$data = array();
		$data['kode_sekolah'] = $kode_sekolah;

		foreach ($sekolah as $sk) {
			$nama_db = $sk['nama_database'];
			include APPPATH . 'config/database.php';
			$config = $db['default'];
			$config['database'] = $nama_db;
			$current_db = $this->load->database($config, true);
			$fcm_token = array();

			$siswa    = $current_db->query("select * from siswa")->result_array();
			foreach ($siswa as $key => $dat) {
				$info = '';
				if (!empty($dat['fcm_token'])) {
					$token = $dat['fcm_token'];
					$siswa = $dat['nama'];
					$Info = 'Hallo, ' . $dat['nama'] . '!  ' . $info;
					$payload = array('notification' => '');
					$this->sendNotification($token, $Info, $message, $payload);
					// $login=$this->LoginModel->getLoginSiswa( $dat['id']);
					// $this->NotificationModel->createNotification('5',$Info.' '.$message, $login['id'],'');
		
					$data['siswa'][$nama_db][] = $dat;
				}
			}
			$pengajar    = $current_db->query("select * from pengajar")->result_array();;
			foreach ($pengajar as $key => $dat) {
				if (!empty($dat['fcm_token'])) {
					// $fcm_token[$key] = $dat['fcm_token'];;
					$token = $dat['fcm_token'];
					$pengajar = $dat['nama'];
					$Info = 'Hallo, Bpk/Ibu ' . $dat['nama'] . '!  ' . $info;
					$payload = array('notification' => '');
					$this->sendNotification($token, $Info, $message, $payload);
					// $login=$this->LoginModel->getLoginPengajar( $dat['id']);
					// $this->NotificationModel->createNotification('5',$Info.' '.$message, $login['id'],'');
		
					$data['pengajar'][$nama_db][] = $dat;
				}
			}
		}

		$message = [
			'id' => null,
			'data' =>  $data,
		];
		$this->set_response($message, REST_Controller::HTTP_OK);
	}
	function ulangtahun_post()
	{

		$kode_sekolah = $this->post('kode_sekolah');
		$message = "Semoga panjang umur dan sehat selalu  ";
		$dbThermo = $this->load->database('thermo', TRUE);
		$sql = "select * from sekolah where active=1 ";

		if ($kode_sekolah != null && $kode_sekolah != '') {
			$sql = "select * from sekolah where active=1 and kode_sekolah='" . $kode_sekolah . "' ";
		}
		$sekolah = $dbThermo->query($sql)->result_array();
		$data = array();
		$data['kode_sekolah'] = $kode_sekolah;

		foreach ($sekolah as $sk) {
			$nama_db = $sk['nama_database'];
			include APPPATH . 'config/database.php';
			$config = $db['default'];
			$config['database'] = $nama_db;
			$current_db = $this->load->database($config, true);
			$fcm_token = array();

			$siswa    = $current_db->query("select * from siswa")->result_array();
			foreach ($siswa as $key => $dat) {
				if (!empty($dat['tgl_lahir'])) {
					$parts = explode('-', $dat['tgl_lahir']);
					$m = $parts[1];
					$d = $parts[2];
					$m_now = date('m');
					$d_now = date('d');
					if ($m == $m_now && $d == $d_now) {
						if (!empty($dat['fcm_token'])) {
							$token = $dat['fcm_token'];
							$siswa = $dat['nama'];
							$Info = 'Selamat Ulang tahun, ' . $dat['nama'] . '!  ';
							$payload = array('notification' => '');
							$this->sendNotification($token, $Info, $message, $payload);
							// $login=$this->LoginModel->getLoginSiswa( $dat['id']);
							// $this->NotificationModel->createNotification('5',$Info.' '.$message, $login['id'],'');

							$data['siswa'][$nama_db][] = $dat;
						}
					}
				}
			}
			$pengajar    = $current_db->query("select * from pengajar")->result_array();;


			foreach ($pengajar as $key => $dat) {
				if (!empty($dat['tgl_lahir'])) {
					$parts = explode('-', $dat['tgl_lahir']);
					$m = $parts[1];
					$d = $parts[2];
					$m_now = date('m');
					$d_now = date('d');
					if ($m == $m_now && $d == $d_now) {
						if (!empty($dat['fcm_token'])) {
							// $fcm_token[$key] = $dat['fcm_token'];;
							$token = $dat['fcm_token'];
							$pengajar = $dat['nama'];
							$Info = 'Selamat ulang tahun, Bpk/Ibu ' . $dat['nama'] . '!  ';
							$payload = array('notification' => '');
							$this->sendNotification($token, $Info, $message, $payload);
							// $login=$this->LoginModel->getLoginPengajar( $dat['id']);
							// $this->NotificationModel->createNotification('5',$Info.' '.$message, $login['id'],'');

							$data['pengajar'][$nama_db][] = $dat;
						}
					}
				}
			}
		}

		$message = [
			'id' => null,
			'data' =>  $data,
		];
		$this->set_response($message, REST_Controller::HTTP_OK);
	}

	function kirimNotif_get()
	{

		// $post =  $this->post();
		$kode_sekolah = "xxx";
		// var_dump($post);exit();
		$message = "Testing Cronjob....";
		$dbThermo = $this->load->database('thermo', TRUE);
		$sql = "select * from sekolah where active=1 ";

		if ($kode_sekolah != null && $kode_sekolah != '') {
			$sql = "select * from sekolah where active=1 and kode_sekolah='" . $kode_sekolah . "' ";
		}
		$sekolah = $dbThermo->query($sql)->result_array();
		$data = array();
		$data['kode_sekolah'] = $kode_sekolah;

		foreach ($sekolah as $sk) {
			$nama_db = $sk['nama_database'];
			include APPPATH . 'config/database.php';
			$config = $db['default'];
			$config['database'] = $nama_db;
			$current_db = $this->load->database($config, true);
			$fcm_token = array();

			$siswa    = $current_db->query("select * from siswa")->result_array();
			foreach ($siswa as $key => $dat) {
				$info = '';
				if (!empty($dat['fcm_token'])) {
					$token = $dat['fcm_token'];
					$siswa = $dat['nama'];
					$Info = 'Hallo, ' . $dat['nama'] . '!  ' . $info;
					$payload = array('notification' => '');
					$this->sendNotification($token, $Info, $message, $payload);
					$login=$this->LoginModel->getLoginSiswa( $siswa['id']);
					$this->NotificationModel->createNotification('5',$Info, $login['id'],'');

					$data['siswa'][$nama_db][] = $dat;
				}
			}
			$pengajar    = $current_db->query("select * from pengajar")->result_array();;
			foreach ($pengajar as $key => $dat) {
				if (!empty($dat['fcm_token'])) {
					// $fcm_token[$key] = $dat['fcm_token'];;
					$token = $dat['fcm_token'];
					$pengajar = $dat['nama'];
					$Info = 'Hallo, Bpk/Ibu ' . $dat['nama'] . '!  ' . $info;
					$payload = array('notification' => '');
					$this->sendNotification($token, $Info, $message, $payload);
					$login=$this->LoginModel->getLoginPengajar( $pengajar['id']);
					$this->NotificationModel->createNotification('5',$Info, $login['id'],'');

					$data['pengajar'][$nama_db][] = $dat;
				}
			}
		}

		$message = [
			'id' => null,
			'data' =>  $data,
		];
		$this->set_response($message, REST_Controller::HTTP_OK);
	}
	public function sendNotification($token, $title, $message, $payload)
	{
		$this->load->library('Fcm');
		// $token = $token;
		// $message = "Test notification message";
		$payload = array("click_action" => "FLUTTER_NOTIFICATION_CLICK");
		$this->fcm->setTitle($title);
		$this->fcm->setMessage($message);

		/**
		 * set to true if the notificaton is used to invoke a function
		 * in the background
		 */
		$this->fcm->setIsBackground(false);

		/**
		 * payload is userd to send additional data in the notification
		 * This is purticularly useful for invoking functions in background
		 * -----------------------------------------------------------------
		 * set payload as null if no custom data is passing in the notification
		 */
		//$payload = array('notification' => '');
		$this->fcm->setPayload($payload);

		/**
		 * Send images in the notification
		 */
		$this->fcm->setImage(base_url('logo_antech.png'));

		/**
		 * Get the compiled notification data as an array
		 */
		$json = $this->fcm->getPush();

		$p = $this->fcm->send($token, $json);

		return($p);
	}

	public function testKirimNotif_post(){
		$data=array();
		$data['to']="cdtVnF1sRFiHLqFS8c6HSF:APA91bFEe42F1ei_LjE2FvB7xeQ7-fqevgATn00HhdSO7K5D78LX1tQUAq48_kgRBFoIqiOeO36mYv51GpHfu2yrkBLuCDrB0Bia4l3yGk_jbZg0RSMApHYOzZgBKGVHPrUSwj-nbx4C";
		$data['notification']['body']="PP No xxx butuh approval";
		$data['notification']['title']="Approval PP";
		$data['data']['keterangan']="1234";
		// $data=array(
		// 	"to" => "cdtVnF1sRFiHLqFS8c6HSF:APA91bFEe42F1ei_LjE2FvB7xeQ7-fqevgATn00HhdSO7K5D78LX1tQUAq48_kgRBFoIqiOeO36mYv51GpHfu2yrkBLuCDrB0Bia4l3yGk_jbZg0RSMApHYOzZgBKGVHPrUSwj-nbx4C",
		// 	"notification" =>array (
		// 		array("body" => "Body of Your Notification"),
		// 		array("title"=> "Title of Your Notification"),
		// 	)
		// 	);
	   		
		  // var_dump(json_encode($data));
		$CI = &get_instance();
        $CI->load->config('androidfcm'); //loading of config file

        // Set POST variables
        $url = $CI->config->item('fcm_url');

        $headers = array(
            'Authorization: key=' . $CI->config->item('key'),
            'Content-Type: application/json',
        );

	
        // Open connection
        $ch = curl_init();
		// return ($ch);
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute post
        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl failed: ' . curl_error($ch));
			return curl_error($ch);
        }

        // Close connection
        curl_close($ch);
		var_dump( $result);

	}
	public function testSendNotification_post()
	{
		//$token = 'eHV1svCq0Uo:APA91bGlZuwws2N51M7YfowbpNAYe3xVE8lz_Y6sjuO1788VFC0Bmgia4p7NnGVCZLCTHPwRWWU_UP07pAjDZVRlHNHTpMWnKstBe0xb1rpQExJ1xNKJXhelSB1KQNmD-7uJtnX30iOU';//Registratin_id'; // push token
	 $token="cdtVnF1sRFiHLqFS8c6HSF:APA91bFEe42F1ei_LjE2FvB7xeQ7-fqevgATn00HhdSO7K5D78LX1tQUAq48_kgRBFoIqiOeO36mYv51GpHfu2yrkBLuCDrB0Bia4l3yGk_jbZg0RSMApHYOzZgBKGVHPrUSwj-nbx4C";
		$message = "Test notification message";

		$this->load->library('fcm');
		$this->fcm->setTitle('Test FCM Notification');
		$this->fcm->setMessage($message);

		/**
		 * set to true if the notificaton is used to invoke a function
		 * in the background
		 */
		$this->fcm->setIsBackground(false);

		/**
		 * payload is userd to send additional data in the notification
		 * This is purticularly useful for invoking functions in background
		 * -----------------------------------------------------------------
		 * set payload as null if no custom data is passing in the notification
		 */
		$payload = array('notification' => 'aaaa');
		$this->fcm->setPayload($payload);

		/**
		 * Send images in the notification
		 */
		$this->fcm->setImage('https://firebase.google.com/_static/9f55fd91be/images/firebase/lockup.png');

		/**
		 * Get the compiled notification data as an array
		 */
		$json = $this->fcm->getPush();

		$p = $this->fcm->send($token, $json);

		print_r($p);
	}
}
