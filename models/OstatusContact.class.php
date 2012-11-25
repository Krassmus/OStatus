<?php

require_once dirname(__file__)."/../../Blubber/models/BlubberUser.class.php";

class OstatusContact extends BlubberExternalContact implements BlubberContact {
    
    static public function findMine() {
        return self::findBySQL(__class__, "contact_type = 'ostatus' ORDER BY name ASC");
    }
}