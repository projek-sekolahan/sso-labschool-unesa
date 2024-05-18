<?php
class UploadFile extends CI_Model {
	function photo($folder, $subfolder, $data) {
		$mime_type = @mime_content_type($data['img']);
		$allowed_file_types = ['image/png', 'image/jpeg', 'image/jpg'];
		if (!in_array($mime_type, $allowed_file_types)) {
			return false;
		}
	
		// Decode the image data and get file size
		list($type, $data['img']) = explode(';', $data['img']);
		list(,$extension) = explode('/', $type);
		list(,$data['img']) = explode(',', $data['img']);
		$fileData = base64_decode($data['img']);
		
		// Check the file size (in bytes)
		$fileSize = strlen($fileData); // size in bytes
		$maxFileSize = 2000000; // maximum file size in bytes (e.g., 5 MB)
	
		if ($fileSize > $maxFileSize) {
			return false; // File too large
		}
	
		// Generate unique file name
		$table = $data['table'];
		if ($table == 'calendars_article') {
			$name = '-artikel-' . base64_encode($data['articleid'] . ':' . strtotime(date('d-m-Y H:m:s')));
		}
		if ($table == 'users_img' || $table == 'trx_presensi') {
			$name = base64_encode(str_replace(' ', '', $data['user_id']) . ':' . strtotime(date('d-m-Y H:m:s')));
		}
		$filePath = 'assets/' . $folder . '/' . $subfolder . '/';
		$fileName = uniqid() . $name . time() . '.' . $extension;
		$filesPhoto = $filePath . $fileName;
		
		// Save the file to disk
		file_put_contents($filesPhoto, $fileData);
		return $filesPhoto;
	}	
}
?>
