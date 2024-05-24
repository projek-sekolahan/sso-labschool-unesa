<?php
class Tables extends CI_Model {
	public $data = [];
	
	function detailTables($select,$tabID,$limit,$like,$order,$join,$where,$where2,$group_by,$key) {
 		$columns = array();
		if ($tabID=='users') {
			$access = '/api/client/users/profile_pengguna';
			$table	= 'users_details a';
		}
		if ($tabID=='pages') {
			$access = '/api/client/pages/menu_pages';
			$table	= 'pages a';
		}
		if ($tabID=='roles') {
			$access = '/api/client/roles/menu_roles';
			$table	= 'groups_has_permissions a';
		}
		if ($tabID=='presensi') {
			$access = '/api/client/presensi/detail_presensi';
			$table	= 'trx_presensi a';
		}
		/* if ($tabID=='splash_screen') {
			$access = 'splash_screen';
			$table	= 'splash_screen a';
		}
		if ($tabID=='calendars_month') {
			$access = 'calendars_month';
			$table	= 'calendars_month a';
		} */
		$query_total  = $this->Master->select($select,$table,$limit,$like,$order,$join,$where,$where2,$group_by);
		$query_filter = $this->Master->select($select,$table,$limit,$like,$order,$join,$where,$where2,$group_by);
		$query        = $this->Master->select($select,$table,$limit,$like,$order,$join,$where,$where2,$group_by);
		if ($query<>false) {
			$no		= $limit['start']+1;
			$response['data'] = [];
		    foreach ($query->result() as $val) {
		        if ($query_total->num_rows()>0) {
					// data
					if ($tabID=='users') {
						$btn	=	$this->buttonTables($val->email,$access,null);
					}
					if ($tabID=='pages' || $tabID=='roles') {
						$btn	=	$this->buttonTables($val->id,$access,null);
					}
					if ($tabID=='presensi') {
						$param	= array(
							'user_id'	=> $val->user_id,
							'tanggal'	=> $val->tanggal_presensi,
						);
						$param	=	implode(', ', $param);
						$btn	=	$this->buttonTables($param,$access,null);
					}
					// Dapatkan array dari objek
					$valArray = (array) $val;
					// Buat array baru untuk menyimpan data yang sudah dimodifikasi
    				$modifiedArray = [];
					// Iterasi melalui setiap elemen array
					foreach ($valArray as $key => $value) {
						// Ubah kunci menjadi capitalize
						$modifiedKey = ucwords(str_replace('_',' ',$key)?? '---');
						// Ubah nilai menjadi capitalize
						$modifiedValue = $value=== null ? '---' : ucwords(str_replace(',',', ',$value)?? '---');
						// Memeriksa apakah kunci asli adalah 'img'
						if ($key == 'img_location' || $key == 'foto_surat' || $key == 'foto_presensi') {
							// Jika ya, ubah kunci menjadi 'Image'
							$modifiedKey = 'Image';
						}						
						// Cek apakah $key mengandung kata "id" atau "_id"
						if (strpos($modifiedKey, 'Id') !== false) {
							// Jika kunci mengandung "Id", lanjut ke iterasi berikutnya
							continue;
						}
						// Ubah nilai untuk key 'tipe_site'
						if ($modifiedKey == 'Is Child') {
							// Jika nilai adalah '1', ubah menjadi 'dashboard', jika tidak biarkan nilai yang sama
							$modifiedValue = ($modifiedValue == '1') ? 'Yes Child Pages' : 'No Parent Pages';
						}
						// Jika $value mengandung kata "Https", tambahkan url img ke kunci
						if (strpos($modifiedValue, 'Https') !== false) {
							$modifiedValue = '<div class="avatar-xs img-fluid rounded-circle"><img src="'.$value.'" alt class="member-img img-fluid d-block rounded-circle"></div>';
						}
						// Tambahkan ke array baru
						$modifiedArray[$modifiedKey] = $modifiedValue;
					}
					// Menambahkan kunci dan nilai baru di awal array
    				$modifiedArray = array_merge(['No' => $no++], $modifiedArray);
					// Menambahkan kunci dan nilai baru di akhir array
					$modifiedArray['Action'] = $btn;
					$response['data'][] = $modifiedArray;
					if ($response['data']!="" || $response['data']!=null) {
					// coloumn
						foreach($response['data'][0] as $column=>$relativeValue) {
							$columns[] = array(
								"name"=>$column,
								"data"=>$column
							);
						}
						$response['columns'] = array_unique($columns, SORT_REGULAR);
					}
				}
				else {
					$response['data']		= '';
					$response['columns']	= '';
				}
		    }
		}
		else {
			$response['data']		= '';
			$response['columns']	= '';
		}
		$response['recordsTotal']       = 0;
		if ($query_total<>false) {
			$response['recordsTotal']   = $query_total->num_rows();
		}
		$response['recordsFiltered']    = 0;
		if ($query_filter<>false) {
			$response['recordsFiltered']= $query_filter->num_rows();
		}
		$response['csrfHash']           = $this->security->get_csrf_hash();
		$response['message']            = 'Success Created Data';
	    return $response;
	}

	function buttonTables($paramID,$action,$status) {
		$btndet = '
			<a type="button" tabindex="0" class="dropdown-item text-info btn-action" data-view="detail" data-action="'.$action.'" data-param="'.$paramID.'">
				<i class="align-middle mdi mdi-account-details font-size-18"></i> <span>Detail</span>
			</a>';
			$btn1	= $btndet;
			$btn2 = '';
		$button = 
		'<div class="btn-group" role="group">
			<button type="button" class="btn btn-primary text-center">Pilih</button>
			<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
				<i class="mdi mdi-chevron-down"></i>
			</button>
			<div class="dropdown-menu">
				'.$btn1.$btn2.'
			</div>
		</div>';
        return $button;
	}

    function stringToSecret($string) {
        $length = strlen($string);
        $visibleCount = (int) round($length / 4);
        $hiddenCount = $length - ($visibleCount * 2);
        return substr($string, 0, $visibleCount) . str_repeat('*', $hiddenCount) . substr($string, ($visibleCount * -1), $visibleCount);
    }	
}
?>
