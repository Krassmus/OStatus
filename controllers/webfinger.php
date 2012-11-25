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

class WebfingerController extends ApplicationController {
    
    public function profile_action() {
        $resource = Request::get("resource");
        $username = substr($resource, 0, stripos($resource, "@"));
        
        $this->set_layout(null);
        $this->user = new User(get_userid($username));
    }
    
}