<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class UploadFile extends CI_Model {

    protected $CI;
    protected $storage;
    protected $auth;
    private $_serviceAccount;
    private $_firebaseConfig;
    private $_firebase;

    function __construct() {
        parent::__construct();
        $this->CI = &get_instance();
        $this->CI->load->config('firebase');

        // Load the configuration
        $this->_firebaseConfig = $this->CI->config->item('firebase');

        // Create ServiceAccount manually
        $this->_serviceAccount = ServiceAccount::fromValue([
            'type' => 'service_account',
            'project_id' => $this->_firebaseConfig['project_id'],
            'client_email' => $this->_firebaseConfig['client_email'],
            'private_key' => $this->_firebaseConfig['private_key'],
        ]);

        $this->_firebase = (new Factory)->withServiceAccount($this->_serviceAccount);
        $this->storage = $this->_firebase->createStorage();
        $this->auth = $this->_firebase->createAuth();
    }

    function uploadBucket($filePath, $fileName) {
        $bucket = $this->storage->getBucket($this->_firebaseConfig['storage_bucket']);
		// Pastikan file path yang diberikan benar
        if (!file_exists($filePath)) {
            throw new Exception("File path does not exist: $filePath");
        }
        $file = fopen($filePath, 'r');
        $bucket->upload($file, ['name' => $fileName]);

        // Make the file publicly accessible
        $object = $bucket->object($fileName);
        $object->update(['acl' => []], ['predefinedAcl' => 'publicRead']);
        return "https://storage.googleapis.com/" . $this->_firebaseConfig['storage_bucket'] . "/" . $fileName;
    }

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

		// Simpan file sementara ke direktori yang ada di server
		$fileName = uniqid() . $name . time() . '.' . $extension;
        $tempFilePath = sys_get_temp_dir() . '/' . $fileName;
        file_put_contents($tempFilePath, $fileData);

        // Upload file ke Firebase Storage
        $filePath = $subfolder . '/' . $fileName;
        
        $fileUrl = $this->uploadBucket($tempFilePath, $filePath);

        // Hapus file sementara setelah diupload
        unlink($tempFilePath);

        // Return the file URL
        return $fileUrl;
    }
}
?>
