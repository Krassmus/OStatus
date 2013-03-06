<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__file__)."/application.php";

class SalmonController extends ApplicationController {
    
    public function user_action() {
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
                //$signature = MagicSignature::base64_url_decode($signature);
                //we need a public key now:
                $activity = StreamActivity::fromXML($data);
                if ($activity->author['id'] === $activity->actor['id']) {
                    $webfinger = substr($activity->author['acct'], 0, 5) === "acct:" 
                        ? substr($activity->author['acct'], 5)
                        : $activity->author['acct'];
                    $actor = OstatusContact::findByEmail($webfinger);
                    if ($actor->isNew()) {
                        $actor = OstatusContact::import_contact($webfinger);
                    }
                }
                if ($actor->getId()) {
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
                    $rsa->loadKey($raw_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
                    $verified = MagicSignature::verify($data, $signature, $rsa);
                    if ($verified) {
                        $activity->process();
                    }
                }
            }
        }
        
        $this->render_nothing();
    }
    
    public function replies_action() {
        $this->render_nothing();
    }
    
    public function mention_action($user_id) {
        $this->render_nothing();
    }
}