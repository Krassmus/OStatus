<?php

class UsersOstatusKeys extends SimpleORMap {
    
    public function __construct($id = null) {
        $this->db_table = "ostatus_users_keys";
        parent::__construct($id);
    }
    
    public function createKeys() {
        $res = openssl_pkey_new($config);

        var_dump($private_key);

        openssl_pkey_export($res, $privkey);

        var_dump($privkey);

        $pubkey=openssl_pkey_get_details($res);
        $pubkey=$pubkey["key"];

        var_dump($pubkey);
    }
}