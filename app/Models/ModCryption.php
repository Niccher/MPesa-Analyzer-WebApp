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

        $dec_val=openssl_decrypt ($value,  $cipher_algo, $crypt_key, $options, $crypt_iv);

        return( base64_decode($dec_val));
    }
}
