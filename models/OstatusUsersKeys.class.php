<?php

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
}