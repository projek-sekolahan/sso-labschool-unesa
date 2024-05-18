<?php
class NotificationsModels extends CI_Model {
	function createNotify($token,$tokenFCM,$type,$category,$title,$message,$url,$isRead) {
		$notifications = array(
			'token'		=> $token,
			'type'		=> $type, 
			'category'	=> $category, 
			'title'		=> $title,
			'message'	=> $message, 
			'url'		=> $url, 
			'is_read'	=> $isRead,
		);
		$this->Master->save_data('notifications', $notifications);
		return $this->sendNotification($tokenFCM, $category, $title, $message, $type, $url);
	}
	function sendNotification($deviceToken, $category, $title, $message, $type, $url) {
		// $apiKey		= 'AIzaSyANCfphvM408UXtVutV3s3JUWcv50Wox4s'; // 'token'	=> $deviceToken,
		// $serverKey	= 'AAAAuYpNa94:APA91bEwtLmt0JEuVoren6rEjWBtns2sIlhpoYoFQkQqbkW-n0S8liltdHUH31Cb9W9Qo0r4OlNZnZVU9xqeXvFintDI3xs8yRABNPrzpLDV-crwWe58-mQhlhDV1uTCqcCRiJHqF4s0'; // Ganti dengan Server Key Anda dari Firebase Console 
		$tokenKey	= 'ya29.a0AXooCgt4Ye9kFVBafK_u0C0cq9Dxi52L4cUHFHd6jTjjp-otk5VgXurlSqTopHGv28Eo-vLO8RUy3plwo6jZBS1-E0unBx7W_yKnx4InekYIk1rmjwa2gH9qE-QM_MLSQiMU6eINdE13KJKV1MNopWymxPV8E3j4UIQuaCgYKAbISARESFQHGX2MiAS8zzrevY33_vVvgbDMkgw0171';
		$url		= 'https://fcm.googleapis.com/v1/projects/projek-sekolah-1acb4/messages:send';

		$data = array(
			'message'	=> array(
				'token'	=> $deviceToken,
				// 'topic'	=> $category,
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
	
		$headers = [
			'Authorization: Bearer ' . $tokenKey,
			'Content-Type: application/json'
		];
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	
		$result = curl_exec($ch);
	
		if ($result === FALSE) {
			// Gagal mengirim notifikasi
			return false;
		}
	
		curl_close($ch);
	
		return $result;
	}
}
?>
