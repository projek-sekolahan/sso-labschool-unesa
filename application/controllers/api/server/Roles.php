<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Roles extends RestController {
	private $_master;
	private $_AuthToken;
    private $_TokenKey;
    private $_ApiKey;
    private $_AuthCheck;
    private $_RsToken;
    function __construct() {
        parent::__construct();
        $this->load->model(['Tables','UploadFile']);
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
            if ($keterangan=='create_update') {
				// Mengambil data permissions_pages dari input POST
				$permissions_pages = $this->input->post('permissions_pages');
				// Memastikan bahwa permissions_pages adalah array
				if (is_array($permissions_pages)) {
					// Mengubah array menjadi string dengan tanda pemisah titik koma
					$permissions_pages_string = implode(',', $permissions_pages);
					// cek update
					$cek_update = $this->_master->get_row("groups_has_permissions",['groups_id'=>$this->input->post('param')])->row();
					if ($cek_update) {
						// Menghapus semua data terkait dari tabel groups_has_permissions
						$this->_master->update_data("groups_has_permissions",['groups_id'=>$this->input->post('param')],
						[
							'permission_id'	=> $permissions_pages_string,
							'groups_id'		=> $this->input->post('param')
						]);
					} else {
						// Menyimpan data ke dalam tabel groups_has_permissions
						$this -> _master->save_data('groups_has_permissions', [
							'permission_id'	=> $permissions_pages_string,
							'groups_id'		=> $this->input->post('roles_users')
						]);
					}
					// Menyiapkan output untuk memberi informasi bahwa data telah berhasil diperbarui
					$output = [
						'title'     => 'Data Updated',
						'message'   => 'Success Updated',
						'info'      => 'success',
						'location'  => 'dashboard',
					];
					$http   = RestController::HTTP_CREATED;
				} else {
					// Jika permissions_pages bukan array, beri respon error
					$output = [
						'title'     => 'Error',
						'message'   => 'Invalid permissions_pages data format',
						'info'      => 'error',
						'location'  => '',
					];
					$http   = RestController::HTTP_BAD_REQUEST;
				}
            }
            if ($keterangan=='menu_roles') {
				$rsparam	= $this->_master->get_data('groups_has_permissions',['groups_id'=>$this->input->post('param')])->result();
                $rsgroup	= $this->_master->get_custom_query('SELECT * from groups')->result();
				$rspermis	= $this->_master->get_custom_query('SELECT * from permissions')->result();
				$http		= RestController::HTTP_CREATED;
				$output		= array('result'=>$rsparam,'roles'=>$rsgroup,'permissions'=>$rspermis);
            }
            if ($keterangan=='table') {
                $key	= $this->input->post('key');
                $table	= $this->input->post('table');
				$select = "a.id,c.description as roles_user,REPLACE(GROUP_CONCAT(b.name ORDER BY b.id ASC), ',', ', ') AS permission_pages";
				$column = "c.description";
                    //WHERE
                    $where	= null;
                    //where2 
                    $where2	= null;
                    //join
                    $join['data'][] = array(
						'table' => 'permissions b',
						'join'	=> 'FIND_IN_SET(b.id, a.permission_id)',
						'type'	=> 'left'
					);
					$join['data'][] = array(
						'table' => 'groups  c',
						'join'	=> 'c.id = a.groups_id',
						'type'	=> 'left'
					);
                    // group by
                    $group_by   =   'a.id, a.groups_id';
                    //ORDER
                    $index_order = $this->input->get('order[0][column]');
                    $order['data'][] = array(
                        'column' => $this->input->get('columns['.$index_order.'][name]'),
                        'type'	 => $this->input->get('order[0][dir]')
                    );
                    //LIMIT
                    $limit = array(
                        'start'  => $this->input->get('start'),
                        'finish' => $this->input->get('length')
                    );
					//WHERE LIKE
					$where_like['data'][] = array(
						'column' => $column,
						'param'	 => $this->input->get('search[value]')
					);
                $createTables   =   $this->Tables->detailTables($select,$table,$limit,$where_like,$order,$join,$where,$where2,$group_by,$key);
                $http   = RestController::HTTP_CREATED;
                $output = $createTables;
            }
			$this->response($this->_AuthCheck->response($output),$http);
        } else {
            $this->eResponse();
        }
    }
}
?>
