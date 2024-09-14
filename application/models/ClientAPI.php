<?php
	class ClientAPI extends CI_model {

        private $_client;
        private $_CookieJar;

        public function __construct() {
            parent::__construct();
    
            // Inisialisasi Symfony HTTP Client
            $this->client = new \Symfony\Component\HttpClient\HttpClient;;
        }
    
        public function crToken($url, $authKey) {
            // Dapatkan cookie cf_clearance secara manual
            $cfClearanceValue = 'TAMH2vl.vwJPCznrpJ5vuGPUk68gHcnW.sEfCZTiNQY-1726294621-1.2.1.1-7ABaTPDrFz.0G1IVA3JzBiGCbId_zQInx_U9vJnwiRDrqV.LaUfzGH4WWZlw7gn.ZSsjX6tj5035wweSlGNhsATGJ5PQORWT2Ls8J_bfLAxf71qROHUyvNYjtlIOokx_GiQ6xJE86U.xetuFDOKDgRgmaenzHVq8bNU_d1LAlhRBAB2sJ3_2KcwLMJCeDhpyG23gMJMFPvsTB8ycVR54PULGwCQfV8xjO0es8OX.lXlWDxN9XkMUNHfL19XwBhsOKW1hIcxlpI6K90Uvlv20qHA8nBKUoULmET4ntrmYY1J4Shq_IIVMIHbDjT0jJJz3K.8m7q9_TWP.0jthhw7KMjHntOXAzA4cRHYER43z9adqOb52wOt6CpVkAnJqRFZceO.sP87KQlU2B9fqN78E6hCKjJqATbNDwAOo.HAvNo_jCliRjVxaCj.39xQxgGA.';  // Ganti dengan nilai cookie yang Anda dapatkan
    
            try {
                // Lakukan request menggunakan Symfony HTTP Client
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'Authorization'     => 'Basic ' . $authKey,
                        'Cache-Control'     => 'no-cache',
                        'Connection'        => 'keep-alive',
                        'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                        'Accept'            => 'application/json',
                        'Accept-Language'   => 'en-US,en;q=0.5',
                        'Content-Type'      => 'application/json',
                    ],
                    // Tambahkan cookie cf_clearance ke dalam request
                    'cookies' => [
                        'cf_clearance' => $cfClearanceValue
                    ],
                ]);
    
                // Jika respons berhasil, ambil isi response
                $statusCode = $response->getStatusCode(); // 200
                $content = $response->getContent(); // Hasil dalam format JSON
                $result = json_decode($content, true);
    
                // Kembalikan hasil
                return $result;
    
            } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
                // Tangani error
                echo "Error: " . $e->getMessage();
                return false;
            }
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
