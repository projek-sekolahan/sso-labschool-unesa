<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Presensi extends RestController {
	private $_master;
	private $_AuthToken;
    private $_TokenKey;
    private $_ApiKey;
    private $_AuthCheck;
    private $_RsToken;
    function __construct() {
        parent::__construct();
        $this->load->model(['Tables','UsersModels','PresensiModels','NotificationsModels','UploadFile']);
        $this->load->library(['ion_auth']);
		$this->_master      = new Master();
		$this->_AuthToken   = new AuthToken();
        $this->_AuthCheck   = new AuthCheck();
        $this->_TokenKey    = $this->input->post('token');
        $this->_ApiKey      = $this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]);
        $this->_RsToken     = $this->_AuthToken->validateTimestamp($this->_TokenKey,$this->_ApiKey);
    }

	public function index_get() {
		$this->response([
			'status'    => true,
			'csrfHash'  => $this->security->get_csrf_hash(),
			'info'      => 'csrf token created',
		], RestController::HTTP_CREATED);
	}

    private function eResponse() {
        $http   = RestController::HTTP_BAD_REQUEST;
        $output = array(
            'title'     => 'Invalid',
            'message'   => $this->lang->line('text_rest_invalid_credentials'),
            'info'		=> 'error',
            'location'	=> 'dashboard',
        );
        $this->response($this->_AuthCheck->response($output),$http);
    }

	private function presensi_hadir($id,$data) {
		$hasil_img = NULL;
		$jsondecod	= json_decode($data['geolocation'],true);
		if(isset($jsondecod['longitude']) && isset($jsondecod['latitude'])) {
			$jsonimg = json_decode($data['foto_presensi'],true);
			if (count($jsonimg)!=0) {
				for ($i=0; $i < count($jsonimg); $i++) {
					$hasil_img = $this->UploadFile->photo('img','presensi',['user_id'=>$id,'img'=>$jsonimg[$i],'table'=>'trx_presensi']);
				}
				if($hasil_img==false) {
					$output	= array(
						'title'		=> 'Data Not Image Format',
						'message'	=> 'Error Upload',
						'info'		=> 'error',
						'location'	=> 'dashboard',
					);
					$http	= RestController::HTTP_BAD_REQUEST;
					$this->response($this->_AuthCheck->response($output),$http);
				} else {
					$presensi	= array(
						'user_id'			=> $id,
						'status_dinas'		=> $data['status_dinas'], // non-dinas
						'status_kehadiran'	=> $data['status_kehadiran'], // masuk,pulang
						'facecam_id'		=> $data['facecam_id'], // facecam_id
						'geolocation'		=> $data['geolocation'], // lat-long
						'foto_presensi'		=> $hasil_img, // foto format image presensi
					);
					// $this->NotificationsModels->createNotify($this->_ApiKey,'success','notifikasi','Presensi Berhasil','Terima Kasih Telah Mengisi Presensi, Untuk Lihat Detailnya Silahkan Cek Menu Riwayat Presensi','/riwayat','0');
					return $this->_master->save_data('trx_presensi',$presensi);
				}
			}
		} else {
			$output	= array(
				'title'		=> 'Data Not Geolocation Format',
				'message'	=> 'Error Updated',
				'info'		=> 'error',
				'location'	=> 'dashboard',
			);
			$http	= RestController::HTTP_BAD_REQUEST;
			$this->response($this->_AuthCheck->response($output),$http);
		}
	}

	private function presensi_tidak_hadir($id,$data) {
		if (array_key_exists('foto_surat', $data)) {
			// Kunci ada, Anda bisa menggunakannya dengan aman
			$jsonimg = json_decode($data['foto_surat'],true);
			if (count($jsonimg)!=0) {
				for ($i=0; $i < count($jsonimg); $i++) {
					$hasil_img = $this->UploadFile->photo('img','presensi',['user_id'=>$id,'img'=>$jsonimg[$i],'table'=>'trx_presensi']);
				}
				if($hasil_img==false) {
					$output = array(
						'title'     => 'Data Not Image Format',
						'message'   => 'Error Upload',
						'info'      => 'error',
						'location'  => 'dashboard',
					);
					$http = RestController::HTTP_BAD_REQUEST;
					$this->response($this->_AuthCheck->response($output), $http);
				}
			}
		} else {
			// Kunci tidak ada
			$hasil_img = NULL; // atau Anda dapat menangani kasus di mana kunci tidak didefinisikan
		}
		$presensi = array(
			'user_id'               => $id,
			'status_dinas'          => $data['status_dinas'], // dinas
			'status_kehadiran'      => $data['status_kehadiran'], // izin,sakit,dinas-luar
			'keterangan_kehadiran'  => $data['keterangan_kehadiran'], // ket. izin,sakit,dinas-luar
			'foto_surat'            => $hasil_img, // foto surat format image izin,sakit,dinas-luar
		);
		// $this->NotificationsModels->createNotify($this->_ApiKey,'success','notifikasi','Presensi Berhasil','Terima Kasih Telah Mengisi Presensi, Untuk Lihat Detailnya Silahkan Cek Menu Riwayat Presensi','/riwayat','0');
		return $this->_master->save_data('trx_presensi', $presensi);		
	}

    public function index_post($keterangan) {
        if (is_object($this->_RsToken)) {
			$users	= $this->_master->get_row('token',['key'=>$this->_RsToken->apikey])->row();
            if ($keterangan=='process') {
				$todayDate = date('Y-m-d');
				if($users) {
					$sqlNotPresensi = "SELECT a.* FROM trx_presensi a WHERE a.user_id='$users->user_id' AND DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d')='$todayDate' AND a.status_kehadiran IN ('izin', 'sakit') ORDER BY a.tanggal_presensi DESC LIMIT 1";
					$izinAtauSakit = $this->_master->get_custom_query($sqlNotPresensi)->row();
					if(isset($izinAtauSakit)) {
						$output = array(
							'title'     => 'Data Presensi ' . ucwords($izinAtauSakit->status_kehadiran) . ' Sudah Ada',
							'message'   => 'Error Updated',
							'info'      => 'error',
							'location'  => 'dashboard',
						);
						$http = RestController::HTTP_BAD_REQUEST;
					} else {
						$sqlPresensi = "SELECT a.* FROM trx_presensi a WHERE a.user_id='$users->user_id' AND DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d')='$todayDate' ORDER BY a.tanggal_presensi DESC LIMIT 1";
						$cekPresensi = $this -> _master -> get_custom_query($sqlPresensi) -> row();
						if (!isset($cekPresensi) || $cekPresensi -> status_kehadiran != $this -> input -> post('status_kehadiran')) {
							if ($this -> input -> post('status_dinas') == 'non-dinas') {
								if ($this -> input -> post('status_kehadiran') == 'masuk' || $this -> input -> post('status_kehadiran') == 'pulang') {
									$sqlPresensiMasuk = "SELECT a.* FROM trx_presensi a WHERE a.user_id='$users->user_id' AND DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d')='$todayDate' AND a.status_kehadiran='masuk' ORDER BY a.tanggal_presensi DESC LIMIT 1";
									$cekPresensiMasuk = $this -> _master -> get_custom_query($sqlPresensiMasuk) -> row();
									if ($this -> input -> post('status_kehadiran') == 'pulang') {
										if (isset($cekPresensiMasuk)) {
											$this -> presensi_hadir($users -> user_id, $this -> input -> post());
											$output = array(
												'title' => 'Data Presensi '.ucwords($this -> input -> post('status_kehadiran')).' Success',
												'message' => 'Success Updated',
												'info' => 'success',
												'location' => 'dashboard',
											);
											$http = RestController::HTTP_CREATED;
										} else {
											$output = array(
												'title' => 'Data Not Updated',
												'message' => 'Error Presensi Masuk Belum Ada',
												'info' => 'error',
												'location' => 'dashboard',
											);
											$http = RestController::HTTP_BAD_REQUEST;
										}
									} else if($this -> input -> post('status_kehadiran') == 'masuk' && !isset($cekPresensiMasuk)) {
										$this -> presensi_hadir($users -> user_id, $this -> input -> post());
										$output = array(
											'title' => 'Data Presensi '.ucwords($this -> input -> post('status_kehadiran')).' Success',
											'message' => 'Success Updated',
											'info' => 'success',
											'location' => 'dashboard',
										);
										$http = RestController::HTTP_CREATED;
									} else {
										$output = array(
											'title' => 'Data Not Updated',
											'message' => 'Error Presensi Masuk Sudah Ada',
											'info' => 'error',
											'location' => 'dashboard',
										);
										$http = RestController::HTTP_BAD_REQUEST;
									}
								} else {
									$this -> presensi_tidak_hadir($users -> user_id, $this -> input -> post());
									$output = array(
										'title' => 'Data Presensi '.ucwords($this -> input -> post('status_kehadiran')).' Success',
										'message' => 'Success Updated',
										'info' => 'success',
										'location' => 'dashboard',
									);
									$http = RestController::HTTP_CREATED;
								}
							} else {
								if ($this -> input -> post('status_kehadiran') == 'masuk' || $this -> input -> post('status_kehadiran') == 'pulang') {
									$output = array(
										'title' => 'Data Not Updated',
										'message' => 'Error Presensi Silakan Pilih Non-Dinas-Luar',
										'info' => 'error',
										'location' => 'dashboard',
									);
									$http = RestController::HTTP_BAD_REQUEST;
								} else {
									$this -> presensi_tidak_hadir($users -> user_id, $this -> input -> post());
									$output = array(
										'title' => 'Data Presensi '.ucwords($this -> input -> post('status_kehadiran')).' Success',
										'message' => 'Success Updated',
										'info' => 'success',
										'location' => 'dashboard',
									);
									$http = RestController::HTTP_CREATED;
								}
							}
						} else {
							$output = array(
								'title' => 'Data Presensi '.ucwords($this -> input -> post('status_kehadiran')).' Sudah Ada',
								'message' => 'Error Updated',
								'info' => 'error',
								'location' => 'dashboard',
							);
							$http = RestController::HTTP_BAD_REQUEST;
						}
					}
				} else {
					$output	= array(
						'title'		=> 'Data Not Updated',
						'message'	=> 'Error Updated',
						'info'		=> 'error',
						'location'	=> 'dashboard',
					);
					$http	= RestController::HTTP_BAD_REQUEST;
				}
            }
			if ($keterangan=='detail_presensi') {
				$result = $this->PresensiModels->details($this->input->post('user_id'),$this->input->post('tanggal'));
				if ($result==null) {
					$http   = RestController::HTTP_BAD_REQUEST;
					$output = array(
						'title'     => 'Data Not Found',
						'message'   => 'Detail Presensi Not Found',
						'info'		=> 'error',
						'location'	=> 'dashboard',
					);
				} else {
					$result		= array('result'=>$result);
					$users		= $this->UsersModels->getDetail($this->input->post('user_id'));
					$http       = RestController::HTTP_CREATED;
					$output     = array_merge($result,$users);
				}
			}
            if ($keterangan=='summary') {
				$result	= $this->UsersModels->getDetail($users->user_id);
				$where	= "a.user_id = '".$result['user_id']."' and ";
				$rsum	= $this->PresensiModels->summary($where);
                if ($rsum) {
					$http       = RestController::HTTP_CREATED;
                    $output     = get_object_vars($rsum);
                } else {
                    $http   = RestController::HTTP_BAD_REQUEST;
                    $output = array(
                        'title'     => 'Data Not Found',
                        'message'   => 'Summary Not Found',
                        'info'		=> 'error',
                        'location'	=> 'dashboard',
                    );
                }
            }
            if ($keterangan=='table' || $keterangan=='reports') {
				$key	= $this->input->post('key');
				$table	= $this->input->post('table');
				$query	= $this->PresensiModels->table("presensi",$users->user_id,$key,$table);
				$createTables   =   $this->Tables->detailTables(
					$query['select'],
					$query['table'],
					$query['limit'],
					$query['where_like'],
					$query['order'],
					$query['join'],
					$query['where'],
					$query['where2'],
					$query['group_by'],
					$query['key']
				);
                $http   = RestController::HTTP_CREATED;
                $output = $createTables;
            }
			$this->response($this->_AuthCheck->response($output),$http);
        } else {
            $this->eResponse();
        }
    }
	
}
?>
