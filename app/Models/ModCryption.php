<?php

namespace App\Models;

use CodeIgniter\API\ResponseTrait;

use CodeIgniter\Model;

class ModCryption extends Model
{
    public function decode_content($value){

        $cipher_algo = "AES-128-CBC";

        $options = OPENSSL_RAW_DATA;

        $crypt_iv = '[M[@_w[F4a>yQsJW';

        $crypt_key = "a:r2yt>N3_\\Py,f=";

        log_message('debug', 'Decrypt input length: ' . strlen($value));
        log_message('debug', 'Decrypt input first 32 bytes: ' . bin2hex(substr($value, 0, 32)));

        $dec_val = openssl_decrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);

        if ($dec_val === false) {
            $error = openssl_error_string();
            log_message('error', 'OpenSSL decrypt error: ' . $error);
            throw new \RuntimeException("OpenSSL decrypt failed: " . $error);
        }

        log_message('debug', 'Decrypt output length: ' . strlen($dec_val));
        log_message('debug', 'Decrypt output first 100 chars: ' . substr($dec_val, 0, 100));

        return $dec_val;
    }

    public function encode_content($value){

        $cipher_algo = "AES-128-CBC";

        $options = OPENSSL_RAW_DATA;

        $crypt_iv = '[M[@_w[F4a>yQsJW';

        $crypt_key = "a:r2yt>N3_\\Py,f=";

        $options = 0;

        $enc_val = openssl_encrypt($value, $cipher_algo, $crypt_key, $options, $crypt_iv);

        return ($enc_val);
    }
}
