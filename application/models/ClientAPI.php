<?php
	class ClientAPI extends CI_model {

        private $_client;
        private $_CookieJar;
        public function __construct() {
            parent::__construct();
            // Ambil nilai cookie cf_clearance dari input (misal dari framework CodeIgniter)
            $cfClearanceValue = $this->input->cookie('cf_clearance'); 

            // Inisialisasi CookieJar
            $this->_CookieJar   =   new \GuzzleHttp\Cookie\CookieJar();

            // Jika cookie cf_clearance tersedia
            if ($cfClearanceValue) {
// Buat SetCookie dengan array
$setCookie = new \GuzzleHttp\Cookie\SetCookie([
    'Name'     => 'cf_clearance',
    'Value'    => $cfClearanceValue,
    'Path'     => '/',
]);

// Tambahkan cookie ke CookieJar
$this->_CookieJar->setCookie($setCookie);
            }

            $this->_client      =   new \GuzzleHttp\Client([
                'base_uri'          => base_url()."api/server/",
                'cookies'           => $this->_CookieJar,
                'verify'            => true,
                'allow_redirects'   => true,
            ]);
        }

        function crToken($url,$authKey) {
            try {
                $response = $this->_client->get($url,
                    [
                        'headers'       => [
                            'Authorization'     => 'Basic '.$authKey,
                            'Cache-Control'     => 'no-cache',
                            'Connection'        => 'keep-alive',
                            'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                            'Accept-Language'   => 'en-US,en;q=0.5',
                            'Content-Type' => 'application/json',
                        ],
                        'query'         =>  [explode('.',$_SERVER['HTTP_HOST'])[0]=>hash('sha1',$authKey)]
                    ],
                );
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                }
            }
            $result			= json_decode($response->getBody()->getContents(),true);
            // $cookieJar      = $this->_client->getConfig('cookies');
            // var_dump($this->input->cookie('cf_clearance'));
            var_dump($response); return false; die;
            $cookieArray    = $cookieJar->getCookieByName('ci_sso_csrf_cookie')->getValue();
            return $cookieArray;
        }

        function geToken($url,$authKey,$csrf){
            $data = explode(":",base64_decode($authKey));
            $datalogin = array(
                'email'     => $data[0],
                'password'  => $data[1],
                explode('.',$_SERVER['HTTP_HOST'])[0] => hash('sha1',$authKey),
                'csrf_token'=> $csrf,
            );
            var_dump($datalogin); return false; die;
            try {
                $response = $this->_client->post($url,
                    [
                        'headers'       => [
                            'Authorization'     => 'Basic '.$authKey,
                            'Cache-Control'     => 'no-cache',
                        ],
                        'form_params'   =>  $datalogin,
                    ],
                );
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                }
            }
            return $response;
    	}

        function postContent($url,$authKey,$param) {
            try {
                $response = $this->_client->post($url,
                    [
                        'headers'       => [
                            'Authorization'     => 'Basic '.$authKey,
                            'Cache-Control'     => 'no-cache',
                        ],
                        'form_params'   =>  $param,
                    ],
                );
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                }
            }
            return $response;
    	}        
        
    }
?>
