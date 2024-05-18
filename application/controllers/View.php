<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class View extends CI_Controller {

	public $data = [];
	private $_client;
	private $_CookieJar;
	public function __construct() {
		parent::__construct();
		$this->load->library(['ion_auth']);
        $this->load->helper('cookie');
		$this->lang->load('auth');
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->getURL = $_SERVER['REQUEST_URI'];
		$this->_CookieJar   =   new \GuzzleHttp\Cookie\CookieJar();
		$this->_client      =   new \GuzzleHttp\Client([
			'base_uri'          => base_url()."view/",
			'cookie'            => true,
			'cookies'           => $this->_CookieJar,
			'verify'            => false,
			'allow_redirects'   => true,
		]);
	}

	public function tokenSendCsrf() {
		try {
			$response = $this->_client->get('tokenGetCsrf');
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			if ($e->hasResponse()) {
				$response = $e->getResponse();
			}
		}
		$result			= json_decode($response->getBody()->getContents(),true);
		$cookieJar      = $this->_client->getConfig('cookies');
		$cookieArray    = $cookieJar->getCookieByName('ci_sso_csrf_cookie')->getValue();
		echo json_encode(
            [
            'status'    => true,
			'csrfHash'	=> $this->security->get_csrf_hash(),
			'cookieHash'=> $cookieArray,
            ]
        );
	}

	public function tokenGetCsrf() {
		echo json_encode(
            [
				'status'    => true,
				'csrfHash'	=> $this->security->get_csrf_hash(),
            ]
        );
	}

	public function index() {
        if (!$this->ion_auth->logged_in()) {
            $pages  = 'login';
        } else {
            $pages  = 'dashboard';
        }
        $this->data['content'] = [
            'csrfHash'	=>	$this->security->get_csrf_hash(),
            'pages'     =>  $pages,
        ];
        $this->load->view('layout/'.'frame',$this->data);
    }

    public function content($pages) {
        if($this->method != 'POST' || $pages=='undefined') { 
            redirect('dashboard/404','location', 404);
        } else {
            if (!$this->ion_auth->logged_in()) {
                $view   = 'auth/'.$pages;
            } else {
                $pages  = 'dashboard';
                $view   = 'layout/'.$pages;
            }
            $this->data['content'] = [
                'csrfHash'	=>	$this->security->get_csrf_hash(),
                'pages'     =>  $pages,
            ];
            $this->load->view($view,$this->data);
        }
    }

    public function subcontent($pages) {
		$sqluser	=	"select a.username,lower(b.email) email,b.user_id from users_login a,users_details b where a.id=b.user_id and a.id=".$this->session->userdata('user_id');
        $result		=	$this->Master->get_custom_query($sqluser)->row();
        if ($pages=='header') {
			$usersimg	=	$this->Master->get_row('users_img',['user_id'=>$result->user_id])->row();
			$this->data['content'] = [
				'imgloc'	=>	$usersimg? $usersimg->img_location:'assets/images/user_icon.png',
				'username'	=>	$result->username,
				'email'		=>	$result->email,
			];
            $this->load->view('layout/header',$this->data);
        }
        if ($pages=='leftSidebar') {
			$sqlpage	=	"select a.* from pages a,permissions b,groups_has_permissions c,users_groups d where a.id=b.id and FIND_IN_SET(b.id, c.permission_id) and c.groups_id=d.group_id and d.user_id=".$result->user_id;
        	$result		=	$this->Master->get_custom_query($sqlpage)->result();
			$this->data['content'] = [
				'pages'		=>	$result
			];
            $this->load->view('layout/left-sidebar',$this->data);
        }
        if ($pages=='content') {
            $this->load->view($pages.'/overview',$this->data);
        }
        if ($pages=='rightSidebar') {
            $this->load->view('layout/right-sidebar',$this->data);
        }
        if ($pages=='footer') {
            $this->load->view('layout/footer',$this->data);
        }
    }

    public function menu($pages) {
		if($pages=='undefined') {
			redirect('dashboard/404','location', 404);
		} else {
			if($pages=='overview') {
				$this->load->view('content/'.$pages,$this->data);
			} else {
				$result	=	$this->Master->get_row('pages',['title'=>$pages])->row();
				$this->load->view('content/listable',$result);
			}
		}
    }

    public function modal($pages) {
        $this->load->view('modal/'.$pages,$this->data);
    }

}
