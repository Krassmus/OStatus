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

require_once dirname(__file__)."/application.php";

class SalmonController extends ApplicationController {
    
    public function endpoint_action() {
        $body = @file_get_contents('php://input');
        $envelope_array = TinyXMLParser::getArray($body);
        foreach ($envelope_array as $envelope) {
            $data = $encoding = $alg = $signature = null;
            if ($envelope['name'] === "ME:ENV") {
                foreach ($envelope['children'] as $attribute) {
                    if ($attribute['name'] === "ME:DATA") {
                        $data = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:ENCODING") {
                        $encoding = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:ALG") {
                        $alg = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:SIG") {
                        $signature = $attribute['tagData'];
                    }
                }
            }
            if ($data && $signature && strtolower($encoding) === "base64url" && strtolower($alg) === "rsa-sha256") {
                $data = MagicSignature::base64_url_decode($data);
                var_dump($data);
                //$signature = MagicSignature::base64_url_decode($signature);
                //we need a public key now:
                $activity = StreamActivity::fromXML($data);
                $actor = ($activity->author['id'] === $activity->actor['id']) && $activity->author['acct']
                    ? OstatusContact::get($activity->author['acct']) //works even with unknown contacts
                    : OstatusContact::get($activity->actor['id']);
                if ($actor && $actor->getId()) {
                    $public_key = $actor['data']['magic-public-key'];
                    if (strpos($public_key, ",") !== false) {
                        $public_key = substr($public_key, strpos($public_key, ","));
                    }
                    $public_key = explode(".", $public_key);
                    $mod_hex = bin2hex(MagicSignature::base64_url_decode($public_key[1]));
                    $ex_hex = bin2hex(MagicSignature::base64_url_decode($public_key[2]));
                    $raw_key = array(
                        'modulus' => new Math_BigInteger($mod_hex, 16),
                        'exponent' => new Math_BigInteger($ex_hex, 16)
                    );
                    $rsa = new Crypt_RSA();
                    $rsa->loadKey($raw_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
                    $verified = MagicSignature::verify($data, $signature, $rsa);
                    if ($verified) {
                        $activity->import();
                    }
                } //else: throw away message, we have no possibility to get actor
            } // else: message has unknown encoding, we cannot verify it (yet?)
        }
        $code = SalmonDriver::$code ? SalmonDriver::$code : "510 Not Extended";
        $this->set_status($code);
        
        $this->render_nothing();
    }
    
}