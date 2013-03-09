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
    
    static public function createFromActivity($activity) {
        if ($activity->verb === "http://activitystrea.ms/schema/1.0/post") {
            $posting = OstatusPosting::getByForeignId($activity->id);
            //identifiziere Autor
            $actor = ($activity->author['id'] === $activity->actor['id']) && $activity->author['acct']
                ? OstatusContact::get($activity->author['acct']) //works even for unknow users
                : OstatusContact::get($activity->actor['id']);
            $posting['user_id'] = $actor->getId();
            $posting['external_contact'] = 1;
            $posting['description'] = $activity->content;
            $posting['mkdate'] = $activity->published;
            switch ($activity->object['objectType']) {
                case "http://activitystrea.ms/schema/1.0/note":
                    $posting['Seminar_id'] = $posting['user_id'];
                    $posting['context_type'] = "public";
                    $posting['parent_id'] = 0;
                    if ($posting->isNew() && !$posting->getId()) {
                        $posting->store();
                        $posting['root_id'] = $posting->getId();
                    }
                    if ($posting['user_id']) {
                        $posting->store();
                    }
                    break;
                case "http://activitystrea.ms/schema/1.0/comment":
                    //Mutterposting finden:
                    $replied_posting = OstatusPosting::getByForeignId($activity->reply_to);
                    if (!$replied_posting->isNew() && $posting['user_id']) {
                        $posting['context_type'] = $replied_posting['context_type'];
                        $posting['Seminar_id'] = $replied_posting['Seminar_id'];
                        $posting['parent_id'] = $replied_posting->getId();
                        $posting['root_id'] = $replied_posting['root_id'];
                        $posting->store();
                    }
                    break;
            }
        }
    }

    /** 
     * @deprecated
     */
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