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
    
    public function endpoint_action() {
        $this->user_action();
        if (!$this->performed) {
            $this->render_nothing();
        }
    }
    
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
                    $rsa->loadKey($raw_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
                    $verified = MagicSignature::verify($data, $signature, $rsa);
                    if ($verified) {
                        $activity->import();
                    }
                } //else: throw away message, we have no possibility to get actor
            } // else: message has unknown encoding, we cannot verify it (yet?)
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