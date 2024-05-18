<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Reports extends RestController {
	private $_master;
	private $_AuthToken;
    private $_TokenKey;
    private $_ApiKey;
    private $_AuthCheck;
    private $_RsToken;
    function __construct() {
        parent::__construct();
        $this->load->model(['Tables','UploadFile','PresensiModels']);
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

    public function index_post($keterangan) {
        if (is_object($this->_RsToken)) {
			$users	= $this->_master->get_row('token',['key'=>$this->_RsToken->apikey])->row();
            if ($keterangan=='table') {
                $key	= $this->input->post('key');
                $table	= $this->input->post('table');
				if ($table=='presensi') {
					$query	= $this->PresensiModels->table("reports",$users->user_id,$key,$table);
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
}
?>
