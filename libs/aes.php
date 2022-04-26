<?php
namespace Libs;

class AES {

    public function decrypt($key, $enc, $cipher = "aes-256-cbc") {
        $iv = substr($enc, 0, 16);
        $data = substr($enc, 16);
        return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    }
    
    
    public function encrypt($key, $plain, $cipher = "aes-256-cbc"){
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        $enc = openssl_encrypt($plain, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return $iv.$enc;
    }


}