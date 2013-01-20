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