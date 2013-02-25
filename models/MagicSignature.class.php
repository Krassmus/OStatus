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
        $message64 = self::base64_url_encode($message);
        $sha256_hash = sha256($message64);
        openssl_sign($sha256_hash, $signature, $private_key);
        return $signature;
    }
    
    static public function verify($message, $signature, $public_key) {
        return true;
    }
}

class OstatusMagicSignature extends MagicSignature {
    
}