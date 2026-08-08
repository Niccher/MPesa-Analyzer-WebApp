<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

class ModCryption extends Model
{
    private string $cipherAlgo = "AES-128-CBC";
    private string $cryptKey;
    private string $cryptIv;

    public function __construct()
    {
        parent::__construct();
        $this->cryptKey = (string) env('MPESA_CRYPT_KEY', 'a:r2yt>N3_\\Py,f=');
        $this->cryptIv  = (string) env('MPESA_CRYPT_IV', '[M[@_w[F4a>yQsJW');
    }

    public function decode_content($value)
    {
        $options = OPENSSL_RAW_DATA;

        log_message('debug', 'Decrypt input length: ' . strlen($value));
        log_message('debug', 'Decrypt input first 32 bytes: ' . bin2hex(substr($value, 0, 32)));

        $dec_val = openssl_decrypt($value, $this->cipherAlgo, $this->cryptKey, $options, $this->cryptIv);

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
        $options = 0;

        $enc_val = openssl_encrypt($value, $this->cipherAlgo, $this->cryptKey, $options, $this->cryptIv);

        return ($enc_val);
    }
}
