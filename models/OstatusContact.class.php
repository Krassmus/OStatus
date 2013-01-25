<?php

require_once dirname(__file__)."/../../../core/Blubber/models/BlubberUser.class.php";

class OstatusContact extends BlubberExternalContact implements BlubberContact {
    
    static public function findMine() {
        $db = DBManager::get();
        $contacts = $db->query(
            "SELECT blubber_external_contact.* " .
            "FROM blubber_external_contact " .
                "INNER JOIN blubber_follower ON (blubber_external_contact.external_contact_id = blubber_follower.external_contact_id) " .
            "WHERE blubber_follower.studip_user_id = ".$db->quote($GLOBALS['user']->id)." " .
                "AND blubber_follower.left_follows_right = 1 " .
        "")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($contacts as $key => $contact) {
            $contacts[$key] = new OstatusContact();
            $contacts[$key]->setData($contact);
        }
        return $contacts;
    }
    
    static public function makefriend($adress) {
        $contact = OstatusContact::findByEmail($adress);
        if ($contact->isNew()) {
            $contact = self::import_contact($adress);
        } else {
            $contact->refresh_lrdd();
            $contact->refresh_feed();
        }
        //Freundschaft eintragen und Folge-Nachricht schicken
        if ($contact->getId()) {
            $statement = DBManager::get()->prepare(
                "INSERT IGNORE INTO blubber_follower " .
                "SET studip_user_id = :me, " .
                    "external_contact_id = :contact_id, " .
                    "left_follows_right = '1' " .
            "");
            $statement->execute(array('me' => $GLOBALS['user']->id, 'contact_id' => $contact->getId()));
            return $contact;
        } else {
            return false;
        }
    }
    
    static public function import_contact($adress) {
        list($username, $server) = explode("@", $adress, 2);
        if (!$username or !$server) {
            return false;
        }
        $new_contact = new OstatusContact();
        $new_contact['mail_identifier'] = $adress;
        $new_contact['name'] = $adress;
        $new_contact['contact_type'] = __class__;
        $data = array();
        
        $xrd = TinyXMLParser::getArray(file_get_contents("http://".$server."/.well-known/host-meta"));
        foreach ($xrd as $entry1) {
            if ($entry1['name'] === "XRD") {
                foreach ($entry1['children'] as $entry2) {
                    //get hub
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "lrdd") {
                        $data['lrdd_template'] = $entry2['attrs']['TEMPLATE'];
                    }
                }
            }
        }
        $new_contact['data'] = $data;
        
        $new_contact->refresh_lrdd();
        $new_contact->refresh_feed();
        
        var_dump($new_contact['data']['pubsubhubbub']);
        
        //now follow the user
        
    }
    
    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    public function refresh_lrdd() {
        $data = $this['data'];
        $lrdd = TinyXMLParser::getArray(
            file_get_contents(str_replace("{uri}", urlencode($this['mail_identifier']), $data['lrdd_template']))
        );
        foreach ($lrdd as $entry1) {
            if ($entry1['name'] === "XRD") {
                foreach ($entry1['children'] as $entry2) {
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "http://schemas.google.com/g/2010#updates-from") {
                        $data['feed_url'] = $entry2['attrs']['HREF'];
                    }
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "salmon") {
                        $data['salmon_url'] = $entry2['attrs']['HREF'];
                    }
                }
            }
        }
        $this['data'] = $data;
        $this->store();
    }
    
    public function refresh_feed() {
        $data = $this['data'];
        $feed = TinyXMLParser::getArray(file_get_contents($data['feed_url']));
        foreach ($feed as $entry1) {
            if ($entry1['name'] === "FEED") {
                foreach ($entry1['children'] as $entry2) {
                    //get hub
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "hub") {
                        $data['pubsubhubbub'] = $entry2['attrs']['HREF'];
                    }
                    if ($entry2['name'] === "AUTHOR") {
                        $name = "";
                        foreach ($entry2['children'] as $entry3) {
                            if ($entry3['name'] === "NAME" && !$name) {
                                $name = $entry3['tagData'];
                            }
                            if ($entry3['name'] === "POCO:DISPLAYNAME") {
                                $name = $entry3['tagData'];
                            }
                        }
                        if ($name or !$this['name']) {
                            $this['name'] = $name;
                        }
                    }
                    if ($entry2['name'] === "ENTRY") {
                        var_dump($entry2);
                        $id = $verb = $content = $object_type = $mkdate = "";
                        foreach ($entry2['children'] as $entry_attributes) {
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
                        }
                        if ($id && $verb && $content && $object_type) {
                            switch ($verb) {
                                case "http://activitystrea.ms/schema/1.0/post":
                                    $posting = OstatusPosting::getByForeignId($id);
                                    $posting['mkdate'] = $mkdate;
                                    $posting['description'] = $content;
                                    $posting['user_id'] = $posting['Seminar_id'] = $this['external_contact_id'];
                                    $posting['external_contact'] = 1;
                                    $posting['context_type'] = "public";
                                    $posting['parent_id'] = 0;
                                    $posting->store();
                                    $posting['root_id'] = $posting->getId();
                                    break;
                                case "http://activitystrea.ms/schema/1.0/comment":
                                    $parent_id;
                                    break;
                            }
                        }
                    }
                }
            }
        }
        $this['data'] = $data;
        $this->store();
    }
}