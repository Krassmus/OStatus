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
        
        $own_host_meta = @file_get_contents("http://".$_SERVER['SERVER_NAME']."/.well-known/host-meta");
        if (!$own_host_meta) {
            PageLayout::postMessage(MessageBox::info(_("Dieser Server hat keine korrekte host-meta Datei.")));
        }
        
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
        $contact = OstatusContact::makefriend($adress);
        $template_factory = $this->get_template_factory();
        $contact_template = $template_factory->open("contacts/_contact.php");
        $contact_template->set_layout(null);
        $contact_template->set_attribute('contact', $contact);
        $html = $contact_template->render();
        
        $output = array(
            'contact_id' => $contact->getId(),
            'html' => $html
        );
        
        $this->render_json($output);
    }
    
}