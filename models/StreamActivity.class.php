<?php

class StreamActivity {
    
    public $id = null;
    public $title = null;
    
    public $author = array();
    public $actor = array();
    public $verb = null;
    public $published = null;
    public $content = null;
    public $object = array();
    public $target = array();
    public $reply_to = null;
    
    /**
     * Creates an activity from XML-atom-entry as described here:
     * http://activitystrea.ms/head/atom-activity.html
     * Note that this method is creating only one StreamActivity which represents
     * the first found entry in the XML structure.
     * @param string $xml
     * @return \StreamActivity 
     */
    static public function fromXML($xml) {
        $envelope_array = TinyXMLParser::getArray($xml);
        $activity_entries = TinyXMLParser::getArray($envelope_array);
        foreach ($activity_entries as $activity_entry) {
            if ($activity_entry['name'] === "ENTRY") {
                $id = $title = null;
                foreach ($activity_entry['children'] as $attribute) {
                    if ($attribute['name'] === "AUTHOR") {
                        $author = array();
                        foreach ($attribute['children'] as $author_attributes) {
                            if ($author_attributes['name'] === "URI") {
                                $author['acct'] = $author_attributes['tagData'];
                            }
                            if ($author_attributes['name'] === "LINK" && $author_attributes['attrs']['REL'] === "alternate") {
                                $author['id'] = $author_attributes['attrs']['HRFE'];
                            }
                        }
                    }
                    if ($attribute['name'] === "PUBLISHED") {
                        $published = strtotime($attribute['tagData']);
                    }
                    if ($attribute['name'] === "THR:IN-REPLY-TO") {
                        $reply_to = $attribute['attrs']['HREF'];
                    }
                    if ($attribute['name'] === "ACTIVITY:ACTOR") {
                        $actor = array();
                        if ($object_attribute['name'] === "ACTIVITY:OBJECT-TYPE") {
                            $actor['objectType'] = $object_attribute['tagData'];
                            $actor['type'] = $object_attribute['tagData']; //deprecated
                        }
                        if ($object_attribute['name'] === "ID") {
                            $actor['id'] = $object_attribute['tagData'];
                        }
                    }
                    if ($attribute['name'] === "ACTIVITY:VERB") {
                        $verb = $attribute['tagData'];
                        if (strpos($verb, "/") === false) {
                            $verb = "http://activitystrea.ms/schema/1.0/".$verb;
                        }
                    }
                    if ($attribute['name'] === "ACTIVITY:OBJECT") {
                        foreach ($attribute['children'] as $object_attribute) {
                            $object = array();
                            if ($object_attribute['name'] === "ACTIVITY:OBJECT-TYPE") {
                                $object['objectType'] = $object_attribute['tagData'];
                            }
                            if ($object_attribute['name'] === "ID") {
                                $object['id'] = $object_attribute['tagData'];
                            }
                        }
                    }
                    if ($attribute['name'] === "CONTENT") {
                        $content = $attribute['tagData'];
                    }
                }
                $activity = new StreamActivity($id, $title);
                $activity->author = $author;
                $activity->actor = $actor;
                $activity->verb = $verb ? $verb : "http://activitystrea.ms/schema/1.0/post";
                $activity->published = isset($published) ? $published : time();
                $activity->content = $content;
                $activity->object = $object;
                $activity->reply_to = $reply_to; //warum nicht target verwenden?
                return $activity;
            }
        }
    }
    
    public function __construct($id = null, $title = null) {
        $this->id = $id;
        $this->title = $title;
    }
    
    /**
     * Handles the activity and inserts it into Stud.IP database (if necessary).
     * This only posts notifications 'ActivityStreamProcesses' and 
     * 'ActivityStreamDidProcess' via NotificationCenter. So you may be writing 
     * your own plugins to process your own activities or do additional stuff 
     * with the usual activities like "post" or "follow".
     */
    public function import() {
        NotificationCenter::postNotification("ActivityStreamProcesses", $this);
        NotificationCenter::postNotification("ActivityStreamDidProcess", $this);
    }
    
    
    
}