<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Notifications extends RestController {
	private $_master;
	private $_AuthToken;
    private $_TokenKey;
    private $_ApiKey;
    private $_AuthCheck;
    private $_RsToken;
	
    function __construct() {
        parent::__construct();
        $this->load->model(['UsersModels','NotificationsModels']);
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

    public function index_post($keterangan) {
        if (is_object($this->_RsToken)) {
			$users	= $this->_master->get_row('token',['key'=>$this->_RsToken->apikey])->row();
			if ($keterangan=='create') {
				$notify = $this->NotificationsModels->createNotify(
					$this->_ApiKey,
					$this->input->post('token_fcm'),
					$this->input->post('type'),
					$this->input->post('category'),
					$this->input->post('title'),
					$this->input->post('message'),
					$this->input->post('url'),
					$this->input->post('is_read')
				);
				if ($notify==false) {
					$http   = RestController::HTTP_BAD_REQUEST;
					$output = array(
						'title'     => 'Notify Not Send',
						'message'   => 'Notify Not Send',
						'info'		=> 'error',
						'location'	=> 'dashboard',
					);
				} else {
					$http	= RestController::HTTP_CREATED;
					$output = array(
						'title'     => 'Notify Send',
						'message'   => $notify['name'],
						'info'		=> 'success',
						'location'	=> 'dashboard',
					);
				}
			}
			if ($keterangan=='detail') {
				$result	= $this->_master->get_row('notifications',['token'=>$this->_RsToken->apikey])->result();
				if ($result==null) {
					$http   = RestController::HTTP_BAD_REQUEST;
					$output = array(
						'title'     => 'Data Not Found',
						'message'   => 'Detail Presensi Not Found',
						'info'		=> 'error',
						'location'	=> 'dashboard',
					);
				} else {
					$http       = RestController::HTTP_CREATED;
					$output     = $result;
				}
			}
			$this->response($this->_AuthCheck->response($output),$http);
        } else {
            $this->eResponse();
        }
    }
}
?>
