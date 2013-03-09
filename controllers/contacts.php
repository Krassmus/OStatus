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
        $mod_hex = bin2hex(MagicSignature::base64_url_decode($key[1]));
        $ex_hex = bin2hex(MagicSignature::base64_url_decode($key[2]));
        $raw_key = array(
            'modulus' => new Math_BigInteger($mod_hex, 16),
            'exponent' => new Math_BigInteger($ex_hex, 16)
        );
        $rsa->loadKey($raw_key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
        //var_dump($rsa);
        
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