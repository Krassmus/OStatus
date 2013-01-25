<?php

require_once dirname(__file__)."/../../../core/Blubber/models/BlubberPosting.class.php";

class OstatusPosting extends BlubberPosting {
    
    public $foreign_id = null;
    
    static public function getByForeignId($id) {
        $statement = DBManager::get()->prepare(
            "SELECT item_id " .
            "FROM ostatus_mapping " .
            "WHERE foreign_id = :id " .
                "AND type = 'posting' " .
        "");
        $statement->execute(array('id' => $id));
        $blubber_id = $statement->fetch(PDO::FETCH_COLUMN, 0);
        if ($blubber_id) {
            return new OstatusPosting($blubber_id);
        } else {
            $posting = new OstatusPosting();
            $posting->foreign_id = $id;
            return $posting;
        }
    }
    
    public function store() {
        $success = parent::store();
        if ($success && $this->getId() && $this->foreign_id) {
            $statement = DBManager::get()->prepare(
                "INSERT INTO ostatus_mapping " .
                "SET item_id = :blubber_id, " .
                    "foreign_id = :foreign_id, " .
                    "type = 'posting' " .
            "");
            $statement->execute(array('blubber_id' => $this->getId(), 'foreign_id' => $this->foreign_id));
        }
        return $success;
    }
    
}