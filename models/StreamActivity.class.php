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

require_once dirname(__file__)."/TinyXMLParser.php";

class StreamActivity {
    
    public $id = null;
    public $title = null;
    
    public $links = array();
    public $author = array();
    public $actor = array();
    public $verb = null;
    public $published = null;
    public $updated = null;
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
        $activity_entries = TinyXMLParser::getArray($xml);
        foreach ($activity_entries as $activity_entry) {
            if ($activity_entry['name'] === "ENTRY") {
                $id = $title = null;
                $links = array();
                foreach ($activity_entry['children'] as $attribute) {
                    if ($attribute['name'] === "ID") {
                        $id = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "TITLE") {
                        $title = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "AUTHOR") {
                        $author = array();
                        foreach ($attribute['children'] as $author_attributes) {
                            if ($author_attributes['name'] === "URI") {
                                $author['acct'] = $author_attributes['tagData'];
                            }
                            if ($author_attributes['name'] === "ID") {
                                $author['id'] = $author_attributes['tagData'];
                            }
                            //and for status.net compliance:
                            if ($author_attributes['name'] === "LINK" && $author_attributes['attrs']['REL'] === "alternate") {
                                $author['id'] = $author_attributes['attrs']['HREF'];
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
                        foreach ($attribute['children'] as $object_attribute) {
                            if ($object_attribute['name'] === "ACTIVITY:OBJECT-TYPE") {
                                $actor['objectType'] = $object_attribute['tagData'];
                                $actor['type'] = $object_attribute['tagData']; //deprecated
                            }
                            if ($object_attribute['name'] === "ID") {
                                $actor['id'] = $object_attribute['tagData'];
                            }
                        }
                    }
                    if ($attribute['name'] === "ACTIVITY:VERB") {
                        $verb = $attribute['tagData'];
                        if (strpos($verb, "/") === false) {
                            $verb = "http://activitystrea.ms/schema/1.0/".$verb;
                        }
                    }
                    if ($attribute['name'] === "ACTIVITY:OBJECT") {
                        $object = array();
                        foreach ($attribute['children'] as $object_attribute) {
                            if ($object_attribute['name'] === "ACTIVITY:OBJECT-TYPE") {
                                $object['objectType'] = $object_attribute['tagData'];
                            }
                            if ($object_attribute['name'] === "ID") {
                                $object['id'] = $object_attribute['tagData'];
                            }
                            if ($object_attribute['name'] === "TITLE") {
                                $object['title'] = $object_attribute['tagData'];
                            }
                        }
                    }
                    if ($attribute['name'] === "CONTENT") {
                        $content = $attribute['tagData'];
                    }
                }
                $activity = new StreamActivity($id, $title);
                $activity->links = $links;
                $activity->author = $author;
                $activity->actor = $actor ? $actor : $author;
                $activity->verb = $verb ? $verb : "http://activitystrea.ms/schema/1.0/post";
                $activity->published = isset($published) ? $published : time();
                $activity->content = $content;
                $activity->object = $object;
                $activity->reply_to = $reply_to; //warum nicht target verwenden?
                $activity->context = array('inReplyTo' => array('id' => $reply_to));
                return $activity;
            }
        }
    }
    
    /**
     * Returns an xml-document for this activity in utf8
     * @return string : xml-document in utf8 
     */
    public function toXML() {
        $template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../views");
        $template = $template_factory->open("salmon/activity.php");
        $template->set_attribute('activity', $this);
        return $template->render();
    }
    
    /**
     * Returns an array that can be accessed like this object and looks like
     * the json-representation of the activity. 
     * @return array : asociative array for this activity
     */
    public function toArray() {
        $arr = array();
        $arr['id'] = $this->id;
        $arr['title'] = $this->title;
        $arr['author'] = $this->author;
        $arr['actor'] = $this->actor;
        $arr['verb'] = $this->verb;
        $arr['object'] = $this->object;
        $arr['published'] = $this->published;
        $arr['updated'] = $this->updated;
        $arr['content'] = $this->content;
        $arr['target'] = $this->target;
        
        return $arr;
    }
    
    /**
     * Returns a json object of this activity. 
     * See http://activitystrea.ms/specs/json/1.0/
     * @return string : json-object of activity 
     */
    public function toJSON() {
        return json_encode(studip_utf8encode($this->toArray()));
    }
    
    /**
     * constructor
     * @param type $id
     * @param type $title 
     */
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