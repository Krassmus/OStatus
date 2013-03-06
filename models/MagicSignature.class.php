<?php
/*
 *  Copyright (c) 2013  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class MagicSignature {
    static public function base64_url_encode($input) {
        return strtr(base64_encode($input), '+/', '-_');
    }

    static public function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
    
    static public function sign($message, $private_key) {
        //RSASSA-PKCS1-V1_5-SIGN using sha256
        $rsa = new Crypt_RSA();
        $rsa->loadKey($private_key);
        $rsa->signatureMode = CRYPT_RSA_SIGNATURE_PKCS1;
        $rsa->setHash("sha256");
        $signature = $rsa->sign($message);
        $signature64 = self::base64_url_encode($signature);
        
        return $signature64;
    }
    
    static public function verify($message, $signature64, $public_key) {
        if (is_string($public_key)) {
            $rsa = new Crypt_RSA();
            $rsa->loadKey($public_key);
        } else {
            $rsa = $public_key;
        }
        $rsa->signatureMode = CRYPT_RSA_SIGNATURE_PKCS1;
        $rsa->setHash("sha256");
        
        $signature = self::base64_url_decode($signature64);
        return $rsa->verify($message, $signature);
    }
    
}

class OstatusMagicSignature extends MagicSignature {
    
}