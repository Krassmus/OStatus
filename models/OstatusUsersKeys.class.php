<?php

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
        $res = openssl_pkey_new(array('private_key_bits' => 1024));
        if ($res === false) {
            var_dump(openssl_error_string());
        }
        echo "private:";
        var_dump($res);
        

        openssl_pkey_export($res, $privkey);
        
        var_dump($privkey);

        $pubkey=openssl_pkey_get_details($res);
        $pubkey=$pubkey["key"];
        $this['public_key'] = $pubkey;
        $this['private_key'] = $privkey;

        var_dump($pubkey);
        die();
    }
}