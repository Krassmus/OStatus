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
    
    static public function createFromActivity($event, $activity) {
        
    }

    static public function createFromArray($entry, $external_contact_id) {
        $id = $verb = $content = $object_type = $mkdate = $reply_to = "";
        foreach ($entry['children'] as $entry_attributes) {
            if ($entry_attributes['name'] === "ID") {
                $id = $entry_attributes['tagData'];
            }
            if ($entry_attributes['name'] === "ACTIVITY:VERB") {
                $verb = $entry_attributes['tagData'];
            }
            if ($entry_attributes['name'] === "ACTIVITY:OBJECT-TYPE") {
                $object_type = $entry_attributes['tagData'];
            }
            if ($entry_attributes['name'] === "CONTENT") {
                $content = $entry_attributes['tagData'];
            }
            if ($entry_attributes['name'] === "PUBLISHED") {
                $mkdate = strtotime($entry_attributes['tagData']);
            }
            if ($entry_attributes['name'] === "THR:IN-REPLY-TO") {
                $reply_to = $entry_attributes['attrs']['HREF'];
            }
            if ($entry_attributes['name'] === "OSTATUS:CONVERSATION") {
                $conversation = $entry_attributes['attrs']['HREF'];
            }
        }
        if ($id && $verb && $content && $object_type) {
            if ($verb === "http://activitystrea.ms/schema/1.0/post") {
                switch ($object_type) {
                    case "http://activitystrea.ms/schema/1.0/note":
                        $posting = OstatusPosting::getByForeignId($id);
                        $posting['mkdate'] = $mkdate;
                        $posting['description'] = $content;
                        $posting['user_id'] = $posting['Seminar_id'] = $external_contact_id;
                        $posting['external_contact'] = 1;
                        $posting['context_type'] = "public";
                        $posting['parent_id'] = 0;
                        if ($posting->isNew() && !$posting->getId()) {
                            $posting->store();
                            $posting['root_id'] = $posting->getId();
                        }
                        $posting->store();
                        break;
                    case "http://activitystrea.ms/schema/1.0/comment":
                        //only insert if we already know the thread
                        if ($reply_to) {
                            $replied_posting = OstatusPosting::getByForeignId($reply_to);
                            if (!$replied_posting->isNew()) {
                                $posting = OstatusPosting::getByForeignId($id);
                                $posting['mkdate'] = $mkdate;
                                $posting['description'] = $content;
                                $posting['user_id'] = $posting['Seminar_id'] = $external_contact_id;
                                $posting['external_contact'] = 1;
                                $posting['context_type'] = "public";
                                $posting['parent_id'] = $replied_posting->getId();
                                if ($posting->isNew() && !$posting->getId()) {
                                    $posting->store();
                                    $posting['root_id'] = $replied_posting['root_id'];
                                }
                                $posting->store();
                            }
                        }
                        break;
                }
                return $posting ? $posting : false;
            }
        }
    }

    public function store() {
        $mkdate = $this['mkdate'];
        $new = $this->isNew();
        $success = parent::store();
        if ($success && $this->getId() && $this->foreign_id) {
            $statement = DBManager::get()->prepare(
                "INSERT IGNORE INTO ostatus_mapping " .
                "SET item_id = :blubber_id, " .
                    "foreign_id = :foreign_id, " .
                    "type = 'posting' " .
            "");
            $statement->execute(array('blubber_id' => $this->getId(), 'foreign_id' => $this->foreign_id));
            if ($new && $mkdate != $this['mkdate']) {
                $statement = DBManager::get()->prepare(
                    "UPDATE blubber " .
                    "SET mkdate = :mkdate " .
                    "WHERE topic_id = :topic_id " .
                "");
                $statement->execute(array('topic_id' => $this->getId(), 'mkdate' => $mkdate));
            }
        }
        return $success;
    }
    
}