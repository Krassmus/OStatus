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

require_once dirname(__file__)."/../../../core/Blubber/models/BlubberUser.class.php";
require_once dirname(__file__)."/../../../core/Blubber/models/BlubberExternalContact.class.php";

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
        $contact = OstatusContact::get($adress);
        if ($contact->getId()) {
            $success = $contact->follow_user();
            if ($success !== true) {
                var_dump($success);
            }
            return $contact;
        } else {
            return false;
        }
    }
    
    static public function externalFollower($activity) {
        if ($activity->verb === "http://activitystrea.ms/schema/1.0/follow") {
            //Find users
            $actor = ($activity->author['id'] === $activity->actor['id']) && $activity->author['acct']
                ? OstatusContact::get($activity->author['acct'])
                : OstatusContact::get($activity->actor['id']);
            $user_homepage = $activity->object['id'];
            if (stripos($user_homepage, $GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username=') === 0) {
                $username = str_replace($GLOBALS['ABSOLUTE_URI_STUDIP'].'dispatch.php/profile?username=', "", $user_homepage);
                $user_id = get_userid($username);
            }
            if (!$user_id or $user_id === "nobody") {
                return;
            }
            
            $follow_statement = DBManager::get()->prepare(
                "INSERT IGNORE INTO blubber_follower " .
                "SET studip_user_id = :user_id, " .
                    "external_contact_id = :contact_id, " .
                    "left_follows_right = '0' " .
            "");
            $success = $follow_statement->execute(array(
                'user_id' => $user_id,
                'contact_id' => $actor->getId()
            ));
            if ($success) {
                PersonalNotifications::add(
                    $user_id,
                    $actor->getURL(),
                    sprintf(_("%s hat Sie als Buddy hinzugefügt"), $actor->getName()),
                    null,
                    $actor->getAvatar()->getURL(Avatar::MEDIUM)
                );
            }
        }
    }
    
    static public function get($identifier) {
        if (preg_match("/^[a-f0-9]{32}$/", $identifier)) {
            //md5-id
            return new OstatusContact($identifier);
        } elseif(strpos($identifier, "@") !== false) {
            //acct
            if (strpos($identifier, "acct:") === 0) {
                $identifier = substr($identifier, 5);
            }
            $contact = self::findBySQL("mail_identifier = ?", array($identifier));
            if (count($contact) > 0) {
                return $contact[0];
            } else {
                return self::import_contact($identifier);
            }
        } else {
            //uri
            $get_mapped_contact = DBManager::get()->prepare(
                "SELECT item_id " .
                "FROM ostatus_mapping " .
                "WHERE type = 'http://activitystrea.ms/schema/1.0/person' " .
                    "AND foreign_id = :id " .
            "");
            $get_mapped_contact->execute(array('id' => $identifier));
            $contact_id = $get_mapped_contact->fetch(PDO::FETCH_COLUMN, 0);
            if ($contact_id) {
                return new OstatusContact($contact_id);
            }
        }
        return false;
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
        return $new_contact;
    }
    
    public function __construct($id = null) {
        parent::__construct($id);
    }

    public function getURL() {
        return URLHelper::getURL(
            "plugins.php/Blubber/streams/profile", 
            array('user_id' => $this->getId(), 'extern' => 1)
        );
    }
    
    public function mention($posting) {
        //irgendwas mit Salmon oder pubhubsubbub
    }

    public function refresh_lrdd() {
        if (time() - $this['chdate'] < 3) {
            return;
        }
        $data = $this['data'];
        $lrdd = TinyXMLParser::getArray(
            file_get_contents(str_replace("{uri}", urlencode($this['mail_identifier']), $data['lrdd_template']))
        );
        foreach ($lrdd as $entry1) {
            if ($entry1['name'] === "XRD") {
                foreach ($entry1['children'] as $entry2) {
                    if ($entry2['name'] === "ALIAS") {
                        $data['alias'] = $entry2['tagData'];
                    }
                    if ($entry2['name'] === "SUBJECT") {
                        $data['subject'] = $entry2['tagData'];
                    }
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "http://schemas.google.com/g/2010#updates-from") {
                        $data['feed_url'] = $entry2['attrs']['HREF'];
                    }
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "salmon") {
                        $data['salmon_url'] = $entry2['attrs']['HREF'];
                    }
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "magic-public-key") {
                        $data['magic-public-key'] = $entry2['attrs']['HREF'];
                    }
                }
            }
        }
        if ($this['data']['alias']) {
            $create_mapping = DBManager::get()->prepare(
                "INSERT IGNORE INTO ostatus_mapping " .
                "SET item_id = :contact_id, " .
                    "foreign_id = :id, " .
                    "type = :type " .
            "");
            $create_mapping->execute(array(
                'contact_id' => $this->getId(),
                'id' => $this['data']['alias'],
                'type' => "http://activitystrea.ms/schema/1.0/person"
            ));
        }
        $this['data'] = $data;
        $this->store();
    }
    
    public function getOstatusID() {
        $find_id = DBManager::get()->prepare(
            "SELECT foreign_id " .
            "FROM ostatus_mapping " .
            "WHERE item_id = :contact_id " .
            "AND type = 'http://activitystrea.ms/schema/1.0/person' " .
        "");
        $find_id->execute(array('contact_id' => $this->getId()));
        return $find_id->fetch(PDO::FETCH_COLUMN, 0);
    }
    
    public function refresh_feed() {
        if (time() - $this['chdate'] < 3) {
            return;
        }
        $data = $this['data'];
        $feed = TinyXMLParser::getArray(file_get_contents($data['feed_url']));
        foreach ($feed as $entry1) {
            if ($entry1['name'] === "FEED") {
                foreach (array_reverse($entry1['children']) as $entry2) {
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "hub") {
                        //get hub
                        $data['pubsubhubbub'] = $entry2['attrs']['HREF'];
                    }
                    if ($entry2['name'] === "LINK" && $entry2['attrs']['REL'] === "alternate") {
                        //get hub
                        $data['alternate'] = $entry2['tagData'];
                    }
                    if ($entry2['name'] === "AUTHOR") {
                        //informationen about the user
                        $name = "";
                        $avatars = array();
                        foreach ($entry2['children'] as $entry3) {
                            if ($entry3['name'] === "NAME" && !$name) {
                                $name = $entry3['tagData'];
                            }
                            if ($entry3['name'] === "POCO:DISPLAYNAME") {
                                $name = $entry3['tagData'];
                            }
                            if ($entry3['name'] === "LINK" && $entry3['attrs']['REL'] === "avatar") {
                                $avatars[$entry3['attrs']['HREF']] = $entry3['attrs']['MEDIA:WIDTH'];
                            }
                        }
                        if ($name) {
                            $this['name'] = $name;
                        }
                        if (count($avatars)) {
                            $href = array_shift(array_keys($avatars, max($avatars)));
                            $file_content = file_get_contents($href);
                            if ($file_content) {
                                $tmp_file = $GLOBALS['TMP_PATH']."/".md5(uniqid());
                                file_put_contents($tmp_file, $file_content);
                                $folder = $GLOBALS['ABSOLUTE_PATH_STUDIP']."/pictures/blubbercontact";
                                if (!file_exists($folder)) {
                                    mkdir($folder, 0777);
                                }
                                BlubberContactAvatar::getAvatar($this->getId())->createFrom($tmp_file);
                                @unlink($tmp_file);
                            }
                        }
                    }
                    if ($entry2['name'] === "ENTRY") {
                        //create the postings in our database
                        OstatusPosting::createFromArray($entry2, $this['external_contact_id']);
                    }
                }
            }
        }
        $this['data'] = $data;
        $this->store();
    }
    
    public function follow_user($follower_user_id = null) {
        if (!$follower_user_id) {
            $follower_user_id = $GLOBALS['user']->id;
        }
        $user = new BlubberUser($follower_user_id);
        $already_friends = DBManager::get()->prepare(
            "SELECT 1 " .
            "FROM blubber_follower " .
            "WHERE studip_user_id = :me " .
                "AND external_contact_id = :contact_id " .
                "AND left_follows_right = '1' " .
        "");
        $already_friends->execute(array('me' => $follower_user_id, 'contact_id' => $this->getId()));
        if (!$already_friends->fetch(PDO::FETCH_COLUMN, 0)) {
            $statement = DBManager::get()->prepare(
                "INSERT IGNORE INTO blubber_follower " .
                "SET studip_user_id = :me, " .
                    "external_contact_id = :contact_id, " .
                    "left_follows_right = '1' " .
            "");
            $statement->execute(array('me' => $follower_user_id, 'contact_id' => $this->getId()));
        }
        
        $template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../views");
        $follow_template = $template_factory->open("feed/follow.php");
        $follow_template->set_attribute('user', $user);
        $follow_template->set_attribute('whiterabbit', $this);
        $xml = $follow_template->render();
        $envelope_xml = $this->createEnvelope($xml);
        
        //POST-Request
        $request = curl_init($this['data']['salmon_url']);
        curl_setopt($request, CURLOPT_POST, 1);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Content-type: application/magic-envelope+xml',
            'Content-Length: ' . strlen($envelope_xml)
        ));
        curl_setopt($request, CURLOPT_POSTFIELDS, $envelope_xml);
        $response = curl_exec($request);
        $code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        $error = curl_error($request);
        curl_close($request);
        var_dump($code);
        die($response);
        
        //and the other server does the rest.
        return $error ? $error : true;
    }
    
    public function createEnvelope($xml) {
        $keys = OstatusUsersKeys::get($GLOBALS['user']->id);
        $data = MagicSignature::base64_url_encode($xml);
        $sig = MagicSignature::sign($xml, $keys['private_key']);
        
        $template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../views");
        $follow_template = $template_factory->open("salmon/envelope.php");
        $follow_template->set_attribute('base64data', $data);
        $follow_template->set_attribute('sig', $sig);
        return $follow_template->render();
    }
}