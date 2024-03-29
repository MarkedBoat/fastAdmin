<?php

    namespace models\ext\tool;


    Class RSA {
        const password = 'saas';
        const expires  = 36500;

        public function __construct(&$publicKey, &$privateKey) {
            $dn = array(
                "countryName"            => 'CN',
                "stateOrProvinceName"    => 'Beijing',
                "localityName"           => 'Beijing',
                "organizationName"       => 'baofeng Tv',
                "organizationalUnitName" => 'bftv',
                "commonName"             => 'beijing baofeng tv',
                "emailAddress"           => 'user@beijing.bftv.com'
            );
            //RSA encryption and 1024 bits length
            $res_private = openssl_pkey_new(array(
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ));
            $res_csr     = openssl_csr_new($dn, $res_private);
            $res_cert    = openssl_csr_sign($res_csr, null, $res_private, static::expires);
            $res_pubkey  = openssl_pkey_get_public($res_cert);
            openssl_pkey_export($res_private, $privateKey);
            $publicKeyDetail = openssl_pkey_get_details($res_pubkey);
            $publicKey       = $publicKeyDetail['key'];


        }

        //Encryption with public key
        public static function en($pubKey, $source) {
            openssl_get_publickey($pubKey);
            $crt = '';
            $r   = openssl_public_encrypt($source, $crt, $pubKey);
            return $r === false ? false : base64_encode($crt);
        }

        //Decryption with private key
        public static function de($priKey, $source) {
            $crypttext = base64_decode($source);
            $res1      = openssl_get_privatekey($priKey, static::password);
            $str       = '';
            $r         = openssl_private_decrypt($crypttext, $str, $res1);
            return $r === false ? false : $str;
        }

        public static function sign($str, $priKey) {
            $priKeyRes = openssl_pkey_get_private($priKey);
            openssl_sign($str, $signature, $priKeyRes, 'sha1WithRSAEncryption');
            //openssl_free_key($priKeyRes);
            $signature = base64_encode($signature);
            return $signature;
        }

        /**
         * @param $str
         * @param $sign
         * @param $pubKey
         * @return int
         * @throws \Exception
         */
        public static function verify($str, $sign, $pubKey) {
            $pubKeyRes = openssl_get_publickey($pubKey);
            if ($pubKeyRes === false)
                throw  new \Exception('公钥初始化错误', 400);
            return openssl_verify($str, base64_decode($sign), $pubKeyRes, 'sha1WithRSAEncryption');
            //openssl_free_key($pubKeyRes);
            //return $result;
        }
    }