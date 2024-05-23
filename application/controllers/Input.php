<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input extends CI_Controller {

    public function __construct() {
		parent::__construct();
		$this->load->library(['ion_auth']);
        $this->load->model(['Master','UploadFile']);
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->getURL = $_SERVER['REQUEST_URI'];
		if($this->method != 'POST') {
			redirect('dashboard/404','location', 404);
		}
	}

	public function recover() {
		$identity_column = $this->config->item('identity', 'ion_auth');
		$identity = $this->ion_auth->where($identity_column, $this->input->post('username'))->users()->row();
		if (empty($identity)) {
			$this->ion_auth->set_error('forgot_password_email_not_found');
			echo json_encode([
				'success'	=> 'Error',
				'status'    => False,
				'title'		=> 'Recover Gagal',
				'info'		=> 'error',
				'message'   => $this->ion_auth->errors(),
				'location'	=> 'recover',
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		} else {
			// run the forgotten password method to email an activation code to the user
			if($this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')})) {
				$output = array(
					'title'		=> 'Recover Success',
					'info'		=> 'success',
					'message'   => $this->ion_auth->messages(),
					'location'	=> 'verify',
				);
				echo json_encode([
					'success'	=> 'success',
					'status'    => True,
					'data'      => $output,
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			}
		}
	}

	public function facecam() {
		$user	= $this->Master->get_row('users_login',['mail_code'=>$this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0])])->row();
		if ($user) {
			$hasil_img = NULL;
				$jsonimg = json_decode($this->input->post('img'),true);
                    if (count($jsonimg)!=0) {
                        for ($i=0; $i < count($jsonimg); $i++) {
                            $hasil_img = $this->UploadFile->photo('img','users',['user_id'=>$user->id,'img'=>$jsonimg[$i],'table'=>'users_img']);
                        }
                    }
					$hasil_img == NULL ? $userimg = NULL: 
					$userimg = array(
						'user_id'	=> $user->id,
						'img_location'  => $hasil_img,
					);
			if (!empty($jsonimg) && is_array($jsonimg) && !empty($hasil_img)) {
				$cekimg = $this->Master->get_row('users_img',['user_id'=>$user->id])->row();
				if ($cekimg) {
					$this->Master->update_data('users_img',['user_id'=>$user->id],$userimg);
				} else {
					$this->Master->save_data('users_img' , $userimg);
				}
			}
			$data_face = array(
				'facecam_id'=> $this->input->post('param'),
			);
			$this->Master->update_data('users_login',['mail_code'=>$user->mail_code],$data_face);
			$output = array(
				'title'		=> 'Facecam Success',
				'info'		=> 'success',
				'message'   => 'Verifikasi Facecam Success',
				'location'	=> 'setPassword',
				'token'		=> $user->mail_code,
			);
			echo json_encode([
				'success'	=> 'success',
				'status'    => True,
				'data'      => $output,
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		} else {
			echo json_encode([
				'success'	=> 'Error',
				'status'    => False,
				'title'		=> 'Set Facecam Gagal',
				'info'		=> 'error',
				'message'   => $this->ion_auth->errors(),
				'location'	=> 'facecam',
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		}
	}

	public function setpassword() {
		$user	= $this->ion_auth->forgotten_password_check($this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]));
		if ($user) {
			$tokenkey	= hash('sha1',base64_encode($user->email.':'.$this->input->post('password')));
			$user_group = $this->ion_auth->get_users_groups($user->id)->row();
			$change		= $this->ion_auth->reset_password($user->email,$this->input->post('password'));
			if ($change) {
				$data_token = array(
					'user_id'		=> $user->id,
					'key'			=> $tokenkey,
					'level'			=> $user_group->id,
					'ip_addresses'	=> $this->input->ip_address(),
					'date_created'	=> time(),
				);
				if ($this->Master->get_row('token',['user_id'=>$user->id])->row()) {
					$this->Master->update_data('token',['user_id'=>$user->id],$data_token);
				} else {
					$this->Master->save_data('token' , $data_token);
				}
				$this->ion_auth_model->clear_forgotten_password_code($user->id);
				$output = array(
					'title'		=> 'Set Password Success',
					'info'		=> 'success',
					'message'   => $this->ion_auth->messages(),
					'location'	=> 'login',
				);
				echo json_encode([
					'success'	=> 'success',
					'status'    => True,
					'data'      => $output,
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			} else {
				echo json_encode([
					'success'	=> 'Error',
					'status'    => False,
					'title'		=> 'Set Password Gagal',
					'info'		=> 'error',
					'message'   => $this->ion_auth->errors(),
					'location'	=> 'setPassword',
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			}
		} else {
			echo json_encode([
				'success'	=> 'Error',
				'status'    => False,
				'title'		=> 'Set Password Gagal',
				'info'		=> 'error',
				'message'   => $this->ion_auth->errors(),
				'location'	=> 'setPassword',
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		}
	}

	public function verify() {
		$code = "";
		$data = $this->input->post('digit-input');
		for($i=0;$i<count($data);$i++) {
			$code .= $data[$i];
		}
		$valid_code	=	$this->Master->get_row('users_login',['SUBSTR(mail_code,-4)'=>$code])->row();
		if ($valid_code) {
			if ($this->ion_auth->activate($valid_code->id,$valid_code->mail_code)) {
				// for register
				$user		= $this->Master->get_row('users_details',['user_id'=>$valid_code->id])->row();
				$setpass	= $this->ion_auth->forgotten_password($user->email);
				$title		= 'Verify Register Success';
				$messages	= 'Account Activated';
			} else {
				// for recover
				$user		= $this->ion_auth->forgotten_password_check($valid_code->mail_code);
				$setpass	= $user->mail_code;
				$title		= 'Verify Recover Success';
				$messages	= 'Account Recovered';
			}
			$output = array(
				'title'		=> $title,
				'info'		=> 'success',
				'message'   => $messages,
				'location'	=> 'setPassword',
				'token'		=> $setpass,
			);
			echo json_encode([
				'success'	=> 'success',
				'status'    => True,
				'data'      => $output,
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		} else {
			echo json_encode([
				'success'	=> 'Error',
				'status'    => False,
				'title'		=> 'Verify Gagal',
				'info'		=> 'error',
				'message'   => 'Kode Salah Cek Inbox/Spam Email',
				'location'	=> 'verify',
				'csrfHash'  => $this->security->get_csrf_hash()
			]);
		}
	}

	public function register() {
			$email			= strtolower($this->input->post('username'));
			$checkidentity	= $this->ion_auth->email_check($email);
			if (!$checkidentity) {
				// save new user
				// check dummy data user_data & user_siswa
				$sqlcekusrdata	= "SELECT * FROM users_data WHERE phone_number = '".$this->input->post('phone')."'";
				$cekuserdata	= $this->Master->get_custom_query($sqlcekusrdata)->row();
				$sqlcekusrsiswa = "SELECT * FROM users_siswa WHERE phone_number = '".$this->input->post('phone')."'";
				$cekusersiswa = $this->Master->get_custom_query($sqlcekusrsiswa)->row();
				if ($cekuserdata || $cekusersiswa) {
					// Users password default
					if ($this->input->post('sebagai') == 4) {
						$fullname	= ucwords(strtolower($cekusersiswa->full_name));
						$phone		= $cekusersiswa->phone_number;
						$inputemail	= strtolower($cekusersiswa->email);
					}
					else if ($this->input->post('sebagai') == 5 || $this->input->post('sebagai') == 6) {
						$fullname	= ucwords(strtolower($cekuserdata->full_name));
						$phone		= $cekuserdata->phone_number;
						$inputemail	= strtolower($cekuserdata->email);
					}
					else {
						$fullname	= ucwords(strtolower($this->input->post('namaLengkap')));
						$phone		= $this->input->post('phone');
						$inputemail	= strtolower($this->input->post('username'));
					}
					$hash				= $this->ion_auth_model->hash_password('User@12345');
					$tokenkey			= hash('sha1',base64_encode($email.':'.'User@12345'));
					$identity_column	= $this->config->item('identity', 'ion_auth');
					$identity			= ($identity_column === 'email') ? $email : $inputemail;
					$ip_address			= $this->input->ip_address();
					$additional_data	= ['key'=>$tokenkey,'ip_addresses'=>$ip_address,'password'=>$hash,'phone'=>$phone,'nama_lengkap'=>$fullname];
					$additional_group	= ['id'=>$this->input->post('sebagai')];
					if ($this->ion_auth->register($identity, $email, $additional_data, $additional_group)) {
						$output = array(
							'title'		=> 'Register Success',
							'info'		=> 'success',
							'message'   => $this->ion_auth->messages(),
							'location'	=> 'verify',
						);
						echo json_encode([
							'success'	=> 'success',
							'status'    => True,
							'data'      => $output,
							'csrfHash'  => $this->security->get_csrf_hash()
						]);
					}
				} else {
					echo json_encode([
						'success'	=> 'Error',
						'status'    => False,
						'title'		=> 'Register Gagal',
						'info'		=> 'error',
						'message'   => 'Data User Tidak Valid',
						'location'	=> 'register',
						'csrfHash'  => $this->security->get_csrf_hash()
					]);
				}
			} else {
				echo json_encode([
					'success'	=> 'Error',
					'status'    => False,
					'title'		=> 'Register Gagal',
					'info'		=> 'error',
					'message'   => 'Email Sudah Terdaftar',
					'location'	=> 'recover',
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			}
    }

	public function sendOTP() {
		$otp	= ($this->input->post('email')==='null' || $this->input->post('email')===null) ? FALSE:$this->ion_auth->activOtp($this->input->post('email'));
			if ($otp === FALSE || $otp === NULL) {
				echo json_encode([
					'success'	=> 'Error',
					'status'    => False,
					'title'		=> 'Send OTP Gagal',
					'info'		=> 'error',
					'message'   => $this->ion_auth->errors(),
					'location'	=> 'verify',
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			} else {
				echo json_encode([
					'success'	=> 'Success',
					'status'    => True,
					'title'		=> 'Send OTP Berhasil',
					'info'		=> 'success',
					'message'   => $this->ion_auth->messages(),
					'location'	=> 'verify',
					'csrfHash'  => $this->security->get_csrf_hash()
				]);
			}
	}
	
}
