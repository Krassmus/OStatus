<?php
/*
 * Copyright (c) 2013 Rasmus Fuhse <fuhse@data-quest.de>
 * 
 * MIT license (http://opensource.org/licenses/MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
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