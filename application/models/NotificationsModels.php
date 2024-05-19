<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Firebase\JWT\JWT;

class NotificationsModels extends CI_Model {
    
    protected $CI;
    protected $storage;
    protected $auth;
    private $_client;
    private $_serviceAccount;
	private $_firebaseConfig;
    private $_firebase;

    function __construct() {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->config('firebase');

        // Load the configuration
        $this->_firebaseConfig	= $this->CI->config->item('firebase');

        // Create ServiceAccount manually
        $this->_serviceAccount = ServiceAccount::fromValue([
			'type'			=> 'service_account',
            'project_id'	=> $this->_firebaseConfig['project_id'],
            'client_email'	=> $this->_firebaseConfig['client_email'],
            'private_key'	=> $this->_firebaseConfig['private_key'],
        ]);

        $this->_firebase	= (new Factory)->withServiceAccount($this->_serviceAccount);
        $this->storage		= $this->_firebase->createStorage();
        $this->auth			= $this->_firebase->createAuth();

        $this->_client = new \GuzzleHttp\Client([
            'base_uri'			=> "https://fcm.googleapis.com/v1/projects/",
            'allow_redirects'	=> true,
        ]);
    }

    function createNotify($token, $tokenFCM, $type, $category, $title, $message, $url, $isRead) {
        $notifications = array(
            'token'	=> $token,
            'type'	=> $type, 
            'category'	=> $category, 
            'title'	=> $title,
            'message'	=> $message, 
            'url'	=> $url, 
            'is_read'	=> $isRead,
        );
        $this->Master->save_data('notifications', $notifications);
        return $this->sendNotification($tokenFCM, $category, $title, $message, $type, $url);
    }

    function getAccessToken() {
        $clientEmail	= $this->CI->config->item('firebase')['client_email'];
        $privateKey		= $this->CI->config->item('firebase')['private_key'];
        $projectId		= $this->CI->config->item('firebase')['project_id'];

        $now = time();
        $token = array(
            "iss" => $clientEmail,
            "sub" => $clientEmail,
            "aud" => "https://oauth2.googleapis.com/token",
            "iat" => $now,
            "exp" => $now + 3600,
            "scope" => "https://www.googleapis.com/auth/firebase.messaging"
        );

        $jwt	= JWT::encode($token, $privateKey, 'RS256');
        $client	= new \GuzzleHttp\Client();
        $response = $client->post(
            'https://oauth2.googleapis.com/token',
            ['form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]
            ]
        );
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }

    function sendNotification($deviceToken, $category, $title, $message, $type, $url) {
        $accessToken = $this->getAccessToken();
        $data = array(
            'message'	=> array(
                'token'	=> $deviceToken,
                'notification'	=> array(
                    'title'	=> $title,
                    'body'	=> $message,
                    'image'	=> 'https://devop-sso.smalabschoolunesa1.sch.id/assets/images/favicon.png'
                ),
                'data'	=> array(
                    'type'	=> $type,
                    'url'	=> $url
                ),
                'webpush'	=> array(
                    'fcm_options'	=> array(
                        'link'	=> 'https://dev-labschool-unesa.vercel.app/notifikasi',
                    )
                )
            )
        );
        try {
            $response = $this->_client->post(
                $this->_firebaseConfig['project_id']."/messages:send",
                [
                    'headers' => [
                        'Authorization'	=> 'Bearer '.$accessToken,
                        'Cache-Control'	=> 'no-cache',
                        'Content-Type'	=> 'application/json',
                    ],
                    'json'	=> $data,
                ]
            );
			$responseBody = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = $response->getBody()->getContents();
            } else {
                $responseBody = $e->getMessage();
            }
        }
        return json_decode($responseBody, true);
    }
}
?>