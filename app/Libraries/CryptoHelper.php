<?php

namespace App\Libraries;

class CryptoHelper
{
    private string $cipherAlgo = "AES-128-CBC";
    private string $cryptKey;
    private string $cryptIv;

    public function __construct()
    {
        $this->cryptKey = (string) env('MPESA_CRYPT_KEY', 'a:r2yt>N3_\\Py,f=');
        $this->cryptIv  = (string) env('MPESA_CRYPT_IV', '[M[@_w[F4a>yQsJW');
    }

    public function decode_content($value)
    {
        if (strlen($value) < 16) {
            throw new \RuntimeException("Payload too short to extract IV");
        }

        $iv = substr($value, 0, 16);
        $ciphertext = substr($value, 16);
        $options = OPENSSL_RAW_DATA;

        log_message('debug', 'Decrypt input length: ' . strlen($value));
        log_message('debug', 'Extracted IV: ' . bin2hex($iv));
        log_message('debug', 'Ciphertext length: ' . strlen($ciphertext));

        $dec_val = openssl_decrypt($ciphertext, $this->cipherAlgo, $this->cryptKey, $options, $iv);

        if ($dec_val === false) {
            $error = openssl_error_string();
            log_message('error', 'OpenSSL decrypt error: ' . $error);
            throw new \RuntimeException("OpenSSL decrypt failed: " . $error);
        }

        log_message('debug', 'Decrypt output length: ' . strlen($dec_val));
        log_message('debug', 'Decrypt output first 100 chars: ' . substr($dec_val, 0, 100));

        return $dec_val;
    }

    public function encode_content($value)
    {
        $iv = openssl_random_pseudo_bytes(16);
        $options = OPENSSL_RAW_DATA;

        $enc_val = openssl_encrypt($value, $this->cipherAlgo, $this->cryptKey, $options, $iv);

        return $iv . $enc_val;
    }
}
