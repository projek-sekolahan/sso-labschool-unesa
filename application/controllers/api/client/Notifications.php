<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
class Notifications extends RestController {

    private $_clientAPI;
    private $_AuthToken;
    private $_AuthCheck;
    private $_csrfToken;
    private $_paramToken;
	private $_RsToken;
    
    function __construct() {
        parent::__construct();
        $this->_clientAPI   = new ClientAPI();
        $this->_AuthToken   = new AuthToken();
        $this->_AuthCheck   = new AuthCheck();
        $this->_csrfToken   = $this->_clientAPI->crToken('notifications',$this->input->post('AUTH_KEY'));
		$this->_RsToken     = $this->_AuthToken->validateTimestamp((empty($this->session->userdata('token'))) ? $this->input->post('token'):$this->session->userdata('token'),$this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]));
        $this->_paramToken  = array(
            'token'     => $this->_RsToken,
            explode('.',$_SERVER['HTTP_HOST'])[0] => $this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]),
            'AUTH_KEY'  => $this->input->post('AUTH_KEY'),
            'csrf_token'=> $this->_csrfToken,
        );
    }
    
    private function responsejson($result,$dtAPI) {
        if ($result->getStatusCode()==400 || $result->getStatusCode()==403) {
            $http   = RestController::HTTP_BAD_REQUEST;
            $output = $dtAPI['data'];
        } else {
			$decode = $this->_AuthToken->validateTimestamp($this->_paramToken['token'],$this->_paramToken[explode('.',$_SERVER['HTTP_HOST'])[0]]);
			if (is_object($decode)) {
				$encrypted	= $this->_AuthToken->encrypt(json_encode($dtAPI['data']),hash('sha256', $decode->apikey),substr(hash('sha256', $decode->session_hash), 0, 16));
				$http       = RestController::HTTP_CREATED;
				$output = $this->_AuthToken->generateToken(['data'=>$encrypted],$this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]));
			}
			else {
				return $this->eResponse();
			}
        }
        return $this->response($this->_AuthCheck->response($output),$http);
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
        if ($this->_AuthCheck->checkTokenApi($keterangan,$this->input->post(explode('.',$_SERVER['HTTP_HOST'])[0]),$this->input->post('AUTH_KEY'))) {
			$urlAPI	= 'notifications/'.$keterangan;
			if ($keterangan=='create' || $keterangan=='detail') {
				$dataparam	= array_merge($this->input->post(),$this->_paramToken);
			}
			$result	= $this->_clientAPI->postContent($urlAPI,$this->input->post('AUTH_KEY'),$dataparam);
            $dtAPI	= json_decode($result->getBody()->getContents(),true);
            $this->responsejson($result,$dtAPI);
        } else {
            $this->eResponse();
        }
    }

}
?>
