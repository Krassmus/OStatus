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

class SalmonController extends ApplicationController {
    
    public function user_action() {
        $this->render_nothing();
    }
    
    public function replies_action() {
        $this->render_nothing();
    }
    
    public function mention_action($user_id) {
        $this->render_nothing();
    }
}