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

require_once dirname(__file__).'/../vendor/Math/BigInteger.php';
require_once dirname(__file__).'/../vendor/Crypt/Random.php';
require_once dirname(__file__).'/../vendor/Crypt/Hash.php';
require_once dirname(__file__).'/../vendor/Crypt/RSA.php';

class OstatusUsersKeys extends SimpleORMap {
    
    static public function get($user_id) {
        $keys = new OstatusUsersKeys($user_id);
        if (!$keys['private_key']) {
            $keys->createKeys();
            $keys->setId($user_id);
            $keys->store();
        }
        return $keys;
    }
    
    public function __construct($id = null) {
        $this->db_table = "ostatus_users_keys";
        parent::__construct($id);
    }
    
    public function createKeys() {
        $rsa = new Crypt_RSA();
        $keypair = $rsa->createKey();
        $this['private_key'] = $keypair['privatekey'];
        $this['public_key'] = $keypair['publickey'];
    }
    
    public function getPublicRSA() {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($this['public_key']);
        return $rsa;
    }
}