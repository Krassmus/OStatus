<?php

require_once dirname(__file__)."/application.php";

class ResourcesController extends ApplicationController {
    
    public function webfinger_profile_action() {
        $resource = Request::get("resource");
        $username = substr($resource, 0, stripos($resource, "@"));
        
        $this->set_layout(null);
        $this->user = new User(get_userid($username));
    }
    
}