<?php
function redirect_ssl() {

    $CI = &get_instance(); // Dapatkan instance CodeIgniter
    $class = $CI->router->fetch_class();
    $exclude = array('client'); // Tambahkan nama controller yang tidak menggunakan SSL.

    // Daftar folder yang harus dilindungi
    $protected_folders = array('assets/libs', 'assets/lang', 'assets/js', 'assets/images', 'assets/fonts', 'assets/fileQR', 'assets/dbimport', 'assets/css', 'assets/img');

    // Redirect ke SSL jika belum menggunakan SSL, kecuali untuk controller yang dikecualikan
    if (!in_array($class, $exclude)) {
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
            $CI->config->config['base_url'] = str_replace(
                'http://',
                'https://',
                base_url()
            );
            redirect(current_url()); // Gunakan current_url() untuk redirect ke URL penuh
        }
    } else {
        // Redirect ke non-SSL jika sudah menggunakan SSL, untuk controller yang dikecualikan
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") {
            $CI->config->config['base_url'] = str_replace(
                'https://',
                'http://',
                base_url()
            );
            redirect(current_url()); // Gunakan current_url() untuk redirect ke URL penuh
        }
    }

    // Logika untuk memeriksa akses ke semua aset di folder assets
    $uri = uri_string();
    foreach ($protected_folders as $folder) {
        if (strpos($uri, $folder) === 0) {
            // Cek apakah pengguna sudah login
            if (!$CI->ion_auth->logged_in()) {
                show_error('Unauthorized access', 403);
            }
        }
    }
}

?>