<?php
class PresensiModels extends CI_Model {
	function summary($where) {
		$sqlsum    = "
		SELECT
		ud.user_id,
		ud.nama_lengkap,
		SUM(CASE WHEN kehadiran = 'Hadir' THEN 1 ELSE 0 END)AS hadir, SUM(
			CASE WHEN kehadiran = 'Tidak Hadir' THEN 1 ELSE 0 END
		)AS tidak_hadir, SUM(
			CASE WHEN kehadiran IN('Hadir', 'Tidak Hadir')THEN 1 ELSE 0 END
		)AS total_presensi, SUM(
			CASE WHEN kehadiran = 'Terlambat' OR kehadiran = 'Cepat Pulang' THEN 1 ELSE 0 END
		)AS terlambat_pulang_cepat
		FROM(
			SELECT CASE WHEN a.status_kehadiran IN('masuk', 'dinas-luar')THEN 'Hadir' WHEN a.status_kehadiran IN('sakit', 'izin')THEN 'Tidak Hadir' WHEN a.status_kehadiran = 'masuk' AND TIME(a.tanggal_presensi)BETWEEN '07:05:00' AND '10:00:00' THEN 'Terlambat' WHEN a.status_kehadiran = 'pulang' AND TIME(a.tanggal_presensi)BETWEEN '12:00:00' AND '14:59:00' THEN 'Cepat Pulang' END AS kehadiran,
			a.user_id FROM trx_presensi a WHERE ".$where." a.tanggal_presensi >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 30 DAY), '%Y-%m-%d')AND(a.status_kehadiran IN('masuk', 'pulang', 'dinas-luar')OR a.status_kehadiran IN('sakit', 'izin'))
		)AS kehadiran_summary
		JOIN users_details ud ON kehadiran_summary.user_id = ud.user_id
		GROUP BY ud.user_id,
		ud.nama_lengkap";
		$result     = $this->Master->get_custom_query($sqlsum)->row();
		if ($result==null) {
			return false;
		} else {
			return $result;
		}
	}
	function details($paramid,$paramtgl) {
		$sqldetail="select a.status_kehadiran, a.foto_presensi, a.keterangan_kehadiran, a.foto_surat, b.nama_lengkap,
		DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d') AS tanggal_presensi, TIME(a.tanggal_presensi) AS waktu_presensi,  
		CASE
		WHEN a.status_kehadiran = 'masuk' AND TIME(a.tanggal_presensi) BETWEEN '05:30:00' AND '07:04:00' THEN 'Masuk Normal'
		WHEN a.status_kehadiran = 'pulang' AND TIME(a.tanggal_presensi) BETWEEN '15:00:00' AND '17:00:00' THEN 'Pulang Normal'
		WHEN a.status_kehadiran = 'masuk' AND TIME(a.tanggal_presensi) BETWEEN '07:05:00' AND '11:59:00' THEN 'Terlambat Masuk'
		WHEN a.status_kehadiran = 'pulang' AND TIME(a.tanggal_presensi) BETWEEN '12:00:00' AND '14:59:00' THEN 'Pulang Cepat'
		WHEN a.status_kehadiran = 'izin' THEN 'Izin'
		WHEN a.status_kehadiran = 'sakit' THEN 'Sakit'
		WHEN a.status_kehadiran = 'dinas-luar' THEN 'Dinas Luar'
		ELSE NULL
		END AS keterangan, a.id, b.user_id,  a.status_dinas, a.facecam_id, a.geolocation
		FROM trx_presensi a
		JOIN users_details b ON a.user_id = b.user_id
		WHERE a.user_id = '".$paramid."'
		AND DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d') = '".str_replace(' ', '', $paramtgl)."'
		AND (
			(a.status_kehadiran = 'masuk' AND TIME(a.tanggal_presensi) BETWEEN '05:30:00' AND '11:59:00') OR (a.status_kehadiran = 'pulang' AND TIME(a.tanggal_presensi) BETWEEN '12:00:00' AND '17:00:00') OR (a.status_kehadiran = 'dinas-luar') OR (a.status_kehadiran = 'izin') OR (a.status_kehadiran = 'sakit')
		)";
		$result     = $this->Master->get_custom_query($sqldetail)->result();
		return $result;
	}
	function table($pilihan,$id,$key,$table) {
		if ($pilihan=='presensi') {
			$where['data'][]=array(
				'column'	=>'b.user_id',
				'param'		=> $id
			);
			$select = $this->select($id,$key,$where,$table,"
		,MIN(CASE WHEN a.status_kehadiran = 'masuk' THEN TIME(a.tanggal_presensi)ELSE NULL END)AS jam_masuk, CASE
		WHEN MIN(
			CASE WHEN a.status_kehadiran = 'masuk' THEN TIME(a.tanggal_presensi)ELSE NULL END
		)BETWEEN '05:30:00' AND '07:04:00' THEN 'Masuk Normal'
		WHEN MIN(
			CASE WHEN a.status_kehadiran = 'masuk' THEN TIME(a.tanggal_presensi)ELSE NULL END
		)BETWEEN '07:05:00' AND '11:59:00' THEN 'Terlambat Masuk'
		ELSE NULL
		END AS status_masuk,
		MAX(
			CASE WHEN a.status_kehadiran = 'pulang' THEN TIME(a.tanggal_presensi)ELSE NULL END
		)AS jam_pulang, CASE
		WHEN MAX(
			CASE WHEN a.status_kehadiran = 'pulang' THEN TIME(a.tanggal_presensi)ELSE NULL END
		)BETWEEN '15:00:00' AND '17:00:00' THEN 'Pulang Normal'
		WHEN MAX(
			CASE WHEN a.status_kehadiran = 'pulang' THEN TIME(a.tanggal_presensi)ELSE NULL END
		)BETWEEN '12:00:00' AND '14:59:00' THEN 'Pulang Cepat'
		ELSE NULL
		END AS status_pulang,
		CASE
		WHEN a.status_kehadiran IN('masuk', 'pulang', 'dinas-luar')THEN 'Hadir'
		WHEN a.status_kehadiran IN('sakit', 'izin')THEN 'Tidak Hadir'
		ELSE NULL
		END AS kehadiran,
		CASE
		WHEN a.status_kehadiran = 'izin' THEN 'Izin'
		WHEN a.status_kehadiran = 'sakit' THEN 'Sakit'
		WHEN a.status_kehadiran = 'dinas-luar' THEN 'Dinas Luar'
		ELSE NULL
		END AS keterangan
			");
		}
		if ($pilihan=='reports') {
			$users_groups	= $this->Master->get_row('users_groups',['user_id'=>$id])->row();
			if($users_groups->group_id==1 || $users_groups->group_id==3) {
				$where	= null;
			} else {
				$where['data'][]=array(
					'column'	=>'user_id',
					'param'		=> $users->user_id
				);
			}
			$select =$this->select($id,$key,$where,$table,"");
		}
		return $select;
	}
	function select($id,$key,$where,$table,$select="") {
		$select = "c.img_location,DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d')AS tanggal_presensi, b.user_id, b.nama_lengkap $select";
		$column = "b.nama_lengkap";
		//where2 
		(empty($key)) ? $whr = null : $whr = "a.tanggal_presensi >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL .$key.), '%Y-%m-%d') AND ";
		$where2	= "(a.status_kehadiran IN ('masuk', 'pulang', 'dinas-luar') OR a.status_kehadiran IN ('sakit', 'izin'))";
		//join
		$join['data'][] = array(
			'table' => 'users_details b',
			'join'	=> 'b.user_id=a.user_id',
			'type'	=> 'left'
		);
		$join['data'][] = array(
			'table' => 'users_img c',
			'join'	=> 'a.user_id = c.user_id',
			'type'	=> 'left'
		);
		// group by
		$group_by   =   "DATE_FORMAT(a.tanggal_presensi, '%Y-%m-%d'), b.user_id, b.nama_lengkap";
		//ORDER
		$order['data'][] = array(
			'column' => 'a.tanggal_presensi',
			'type'	 => 'desc'
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
		// Inisialisasi array untuk menyimpan komponen query
		$queryComponents = [
			'select'	=> $select,
			'table'		=> $table,
			'limit'		=> $limit,
			'where_like'	=> $where_like,
			'order'	=> $order,
			'join'	=> $join,
			'where'	=> $where,
			'where2'	=> $where2,
			'group_by'	=> $group_by,
			'key'	=> $key
		];
		// Kembalikan array tersebut
		return $queryComponents;
	}
}
?>
