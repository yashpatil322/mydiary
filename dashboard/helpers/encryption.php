<?php
// /helpers/encryption.php

// Define your master key (in production, retrieve from a secure location or env variable)
define('MASTER_KEY',); // Must be 32 characters for AES-256

function encryptData($plaintext) {
    $key = MASTER_KEY;
    $iv = openssl_random_pseudo_bytes(16); // AES block size is 16 bytes

    $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    // Combine IV and encrypted data for storage
    $encryptedWithIv = base64_encode($iv . $encrypted);

    return $encryptedWithIv;
}

function decryptData($encryptedWithIv) {
    $key = MASTER_KEY;
    $data = base64_decode($encryptedWithIv);

    $iv = substr($data, 0, 16); // First 16 bytes are the IV
    $encrypted = substr($data, 16); // Rest is the actual encrypted data

    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}
?>
