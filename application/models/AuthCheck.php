<?php
#[\AllowDynamicProperties]
	class AuthCheck extends CI_model {
        private $_AuthKey;
        private $_ApiKey;
        function __construct() {
            parent::__construct();
            $this->_master      = new Master();
            $this->_AuthKey     = $this->session->userdata('authkey');
            $this->_ApiKey      = $this->session->userdata('apikey');
        }
        function checkTokenApi($pages,$key,$auth) {
			$sqluser	= "select a.* from token a, users_login b where a.user_id = b.id and b.active = 1 and a.key = '$key'";
			$users		= $this->_master->get_custom_query($sqluser)->row();
            if ($pages!='login') {
                if (($key==$this->_ApiKey) && ($auth==$this->_AuthKey)) {
                    return true;
                } else {
                    if (!empty($users)) {
                        return true;
                    } else {
                        $this->session->sess_destroy();
                        return false;
                    }
                }
            } else {
                if (!empty($users)) {
					return true;
				} else {
					$this->session->sess_destroy();
					return false;
				}
            }
        }
        function response($data) {
            $response = array(
                'data'      => $data,
                'csrfHash'  => $this->security->get_csrf_hash(),
            );
            return $response;
        }
    }
?>
