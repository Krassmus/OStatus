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
require_once dirname(__file__)."/../models/OstatusContact.class.php";

class ContactsController extends ApplicationController {
    
    public function before_filter($action, $args) {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException("Forbidden planet");
        }
        parent::before_filter($action, $args);
    }
    
    public function my_action() {
        PageLayout::setTitle(_("Externe Kontakte"));
        $key = OstatusUsersKeys::get($GLOBALS['user']->id);
        
        $rsa = new Crypt_RSA();
        $key = "RSA.8zK369nRrd2grj5BO3izZt9AsHZvOu4oouLPed-jgjC1LfTMg210jK3vf7t3ZjdAhRmF7sgnhvas-4SNSta-8S84w4xDuHpqutNEBNhirFFEBbGD-y0l1eyvPaFwG9-7H5nVT9FeV9dcaBUo6v4bV7kkj_3x5J85yZROjYVKdas=.AQAB";
        $key = explode(".", $key);
        $raw_key = array(
            'modulus' => new Math_BigInteger(MagicSignature::base64_url_decode($key[1]), 256),
            'exponent' => new Math_BigInteger($key[2] === "AQAB" ? 65537 : MagicSignature::base64_url_decode($key[2]))
        );
        $rsa->loadKey($raw_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
        var_dump($rsa);
        
        $this->contacts = OstatusContact::findMine();
    }
    
    public function add_action() {
        if ($GLOBALS['user']->id === "nobody") {
            return;
        }
        $adress = Request::get("contact_id");
        if (strpos($adress, "@") === false) {
            $this->render_json(array('error' => "No @ character in user-adress."));
            return;
        }
        OstatusContact::makefriend($adress);
        
        $this->render_nothing();
    }
    
}