<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method != 'POST') {
            redirect('dashboard/404', 'location', 404);
        }
    }

    private function send_response($status, $title, $info, $message, $location, $data) {
        $data_array = [
            'title' => $title,
            'info' => $info,
            'message' => $message,
            'location' => $location,
            is_array($data) ? 'facecam' : 'token' => $data
        ];
        
        // var_dump($data_array); return false;
        echo json_encode([
            'status' => $status,
            'data' => $data_array,
            'csrfHash' => $this->security->get_csrf_hash()
        ]);
    }    

    private function handle_identity_check($username, $identity_column) {
        $identity = $this->ion_auth->where($identity_column, $username)->users()->row();

        if (!$identity) {
            // $verifsiswa = $this->Master->get_row('users_siswa', [$identity_column => $username]);
            // $verifuser = $this->Master->get_row('users_data', [$identity_column => $username]);

            // if ($verifsiswa || $verifuser) {
            //     $this->send_response(false, 'Email Belum Pernah Terdaftar', 'error', 'Silakan Register Dahulu', 'register', null);
            // } else {
                $this->ion_auth->set_error('forgot_password_email_not_found');
                $this->send_response(false, 'Recover Gagal', 'error', $this->ion_auth->errors(), 'recover', null);
            // }
        } else {
            if ($this->ion_auth->forgotten_password($identity->email)) {
                $this->send_response(true, 'Recover Success', 'success', $this->ion_auth->messages(), 'verify', null);
            } else {
                $this->send_response(false, 'Recover Gagal', 'error', $this->ion_auth->messages(), 'recover', null);
            }
        }
    }

    public function recover() {
        $identity_column = $this->config->item('identity', 'ion_auth');
        $username = $this->input->post('username');
        $this->handle_identity_check($username, $identity_column);
    }

    public function loadFace() {
        $user = $this->Master->get_row('users_login', [
            'mail_code' => $this->input->post(explode('.', $_SERVER['HTTP_HOST'])[0])
        ])->row();
        
        if ($user) {
            $loadfaceid = $this->FacesModels->loadFaces($user->id, $user->facecam_id);
            $this->send_response(true, 'Load Data Success', 'success', 'Data Facecam Success', 'facecam', $loadfaceid);
        } else {
            $this->send_response(false, 'Load Data Failed', 'error', 'User not found', 'recover', null);
        }
    }

    private function handle_facecam_upload($user, $jsonimg) {
        $hasil_img = null;
        foreach ($jsonimg as $img) {
            $hasil_img = $this->UploadFile->photo('img', 'users', [
                'user_id' => $user->id,
                'img' => $img,
                'table' => 'users_img'
            ]);
        }

        if ($hasil_img) {
            $userimg = ['user_id' => $user->id, 'img_location' => $hasil_img];
            $cekimg = $this->Master->get_row('users_img', ['user_id' => $user->id])->row();

            if ($cekimg) {
                $this->Master->update_data('users_img', ['user_id' => $user->id], $userimg);
            } else {
                $this->Master->save_data('users_img', $userimg);
            }
        }

        return $hasil_img;
    }

    public function facecam() {
        $user = $this->Master->get_row('users_login', [
            'mail_code' => $this->input->post(explode('.', $_SERVER['HTTP_HOST'])[0])
        ])->row();

        if ($user) {
            $jsonimg = json_decode($this->input->post('img'), true);
            $this->handle_facecam_upload($user, $jsonimg);

            $data_face = ['facecam_id' => $this->input->post('param')];
            $this->Master->update_data('users_login', ['mail_code' => $user->mail_code], $data_face);

            $this->send_response(true, 'Facecam Success', 'success', 'Verifikasi Facecam Success', 'setPassword', $user->mail_code);
        } else {
            $this->send_response(false, 'Set Facecam Gagal', 'error', $this->ion_auth->errors(), 'facecam', null);
        }
    }

    private function handle_password_reset($user, $password) {
        $tokenkey = hash('sha1', base64_encode($user->email . ':' . $password));
        $user_group = $this->ion_auth->get_users_groups($user->id)->row();
        $change = $this->ion_auth->reset_password($user->email, $password);

        if ($change) {
            $data_token = [
                'user_id' => $user->id,
                'key' => $tokenkey,
                'level' => $user_group->id,
                'ip_addresses' => $this->input->ip_address(),
                'date_created' => time(),
            ];

            if ($this->Master->get_row('token', ['user_id' => $user->id])->row()) {
                $this->Master->update_data('token', ['user_id' => $user->id], $data_token);
            } else {
                $this->Master->save_data('token', $data_token);
            }

            $this->ion_auth_model->clear_forgotten_password_code($user->id);
            $this->send_response(true, 'Set Password Success', 'success', $this->ion_auth->messages(), 'login', null);
        } else {
            $this->send_response(false, 'Set Password Gagal', 'error', $this->ion_auth->errors(), 'setPassword', null);
        }
    }

    public function setpassword() {
        $user = $this->ion_auth->forgotten_password_check($this->input->post(explode('.', $_SERVER['HTTP_HOST'])[0]));

        if ($user) {
            $this->handle_password_reset($user, $this->input->post('password'));
        } else {
            $this->send_response(false, 'Set Password Gagal', 'error', $this->ion_auth->errors(), 'setPassword', null);
        }
    }

    public function verify() {
        $code = implode('', $this->input->post('digit-input'));
        $valid_code = $this->Master->get_row('users_login',['SUBSTR(mail_code,-4)'=>$code])->row();

        if ($valid_code) {
            $this->Master->update_data('users_login', ['id' => $valid_code->id], ['facecam_id' => null]);

            if ($this->ion_auth->activate($valid_code->id, $valid_code->mail_code)) {
                $user = $this->Master->get_row('users_details', ['user_id' => $valid_code->id])->row();
                $setpass = $this->ion_auth->forgotten_password($user->email);
                $title = 'Verify Register Success';
                $messages = 'Account Activated';
            } else {
                $user = $this->ion_auth->forgotten_password_check($valid_code->mail_code);
                $setpass = $user->mail_code;
                $title = 'Verify Recover Success';
                $messages = 'Account Recovered';
            }

            $this->send_response(true, $title, 'success', $messages, 'setPassword', $setpass);
        } else {
            $this->send_response(false, 'Verify Gagal', 'error', 'Kode Salah Cek Inbox/Spam Email', 'verify', null);
        }
    }

    private function handle_register($email, $phone, $role, $name) {
        // $cekuserdata = $this->Master->get_custom_query("SELECT * FROM users_data WHERE phone_number = '$phone'")->row();
        // $cekusersiswa = $this->Master->get_custom_query("SELECT * FROM users_siswa WHERE phone_number = '$phone'")->row();

        // $fullname = '';
        // $inputemail = '';

        // if ($cekuserdata || $cekusersiswa) {
        //     $fullname = $role == 4 && $cekusersiswa ? ucwords(strtolower($cekusersiswa->full_name)) : ucwords(strtolower($cekuserdata->full_name));
        //     $inputemail = $role == 4 && $cekusersiswa ? strtolower($cekusersiswa->email) : strtolower($cekuserdata->email);
        // }

        // $fullname = $fullname ?: $name;
        // $inputemail = $inputemail ?: $email;

        $fullname = $name;
        $inputemail = $email;

        $this->form_validation->set_rules('username', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('phone', 'Phone', 'required');
        $this->form_validation->set_rules('namaLengkap', 'Name', 'required');
        $this->form_validation->set_rules('sebagai', 'Role', 'required');

        if ($this->form_validation->run() == true) {
            $identity = $email;
            $hash = hash('sha1', $email . ':' . time());
            $additional_data = [
                'full_name' => $fullname,
                'token_code' => $hash,
                'phone' => $phone,
                'nama_lengkap' => $fullname
            ];
            $additional_group = ['id' => $role];

            if ($this->ion_auth->register($identity, $email, $additional_data, $additional_group)) {
                $this->send_response(true, 'Register Success', 'success', $this->ion_auth->messages(), 'verify', null);
            } else {
                $this->send_response(false, 'Register Error', 'error', $this->ion_auth->errors(), 'register', null);
            }
        } else {
            $this->send_response(false, 'Register Error', 'error', 'Data User Tidak Valid', 'register', null);
        }
    }

    public function register() {
        $identity_column = $this->config->item('identity', 'ion_auth');
        $email = $this->input->post('username');
        $phone = $this->input->post('phone');
        $name = $this->input->post('namaLengkap');
        $role = $this->input->post('sebagai');
        $username = $email;

        if (!$this->ion_auth->where($identity_column, $username)->users()->row()) {
            $this->handle_register($email, $phone, $role, $name);
        } else {
            $this->send_response(false, 'Register Error', 'error', 'Email Sudah Terdaftar', 'recover', null);
        }
    }

    public function sendOTP() {
        $email = $this->input->post('email');
        if ($email === 'null' || $email === null) {
            $this->send_response(false, 'Send OTP Gagal', 'error', $this->ion_auth->errors(), 'verify', null);
        } else {
            $otp = $this->ion_auth->activOtp($email);
            if ($otp === false) {
                $this->send_response(false, 'Send OTP Gagal', 'error', $this->ion_auth->errors(), 'verify', null);
            } else {
                $this->send_response(true, 'Send OTP Berhasil', 'success', $this->ion_auth->messages(), 'verify', null);
            }
        }
    }
}
?>