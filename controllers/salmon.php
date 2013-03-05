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
        $body = @file_get_contents('php://input');
        $envelope_array = TinyXMLParser::getArray($body);
        foreach ($envelope_array as $envelope) {
            $data = $encoding = $alg = $signature = null;
            if ($envelope['name'] === "ME:ENV") {
                foreach ($envelope['children'] as $attribute) {
                    if ($attribute['name'] === "ME:DATA") {
                        $data = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:ENCODING") {
                        $encoding = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:ALG") {
                        $alg = $attribute['tagData'];
                    }
                    if ($attribute['name'] === "ME:SIG") {
                        $signature = $attribute['tagData'];
                    }
                }
            }
            if ($data && $signature && strtolower($encoding) === "base64url" && strtolower($alg) === "rsa-sha256") {
                $data = MagicSignature::base64_url_decode($data);
                $signature = MagicSignature::base64_url_decode($signature);
                //we need a public key now:
                $activity_entries = TinyXMLParser::getArray($data);
                foreach ($activity_entries as $activity_entry) {
                    if ($activity_entry['name'] === "ENTRY") {
                        foreach ($activity_entry['children'] as $attribute) {
                            if ($attribute['name'] === "AUTHOR") {
                                foreach ($attribute['children'] as $author_attributes) {
                                    if ($author_attributes['name'] === "URI") {
                                        $acct = $author_attributes['tagData'];
                                    }
                                }
                            }
                            if ($attribute['name'] === "ACTIVITY:ACTOR") {
                                
                            }
                            if ($attribute['name'] === "ACTIVITY:VERB") {
                                $verb = $attribute['tagData'];
                            }
                            if ($attribute['name'] === "ACTIVITY:OBJECT") {
                                foreach ($attribute['children'] as $object_attribute) {
                                    if ($object_attribute['name'] === "ID") {
                                        $user_id = $object_attribute['tagData'];
                                    }
                                }
                            }
                            
                        }
                        if ($acct && $verb === "http://activitystrea.ms/schema/1.0/follow" && $user_id) {
                            $user = new BlubberUser($user_id);
                            //get or create OstatusContact
                            if (substr($acct, 0, 5) === "acct:") {
                                $acct = substr($acct, 5);
                            }
                            $contact = OstatusContact::findByEmail($acct);
                            if ($contact->isNew()) {
                                $contact = self::import_contact($acct);
                            }
                            $follow_statement = DBManager::get()->prepare(
                                "INSERT IGNORE INTO blubber_follower " .
                                "SET studip_user_id = :user_id, " .
                                    "external_contact_id = :contact_id, " .
                                    "left_follows_right = '0' " .
                            "");
                            $success = $follow_statement->execute(array(
                                'user_id' => $user_id,
                                'contact_id' => $contact->getId()
                            ));
                            if ($success) {
                                PersonalNotifications::add(
                                    $user_id,
                                    $contact->getURL(),
                                    sprintf(_("%s hat Sie als Buddy hinzugefügt"), $contact->getName()),
                                    null,
                                    $contact->getAvatar()->getURL(Avatar::MEDIUM)
                                );
                            }
                        }
                    }
                }
            }
        }
        
        $this->render_nothing();
    }
    
    public function replies_action() {
        $this->render_nothing();
    }
    
    public function mention_action($user_id) {
        $this->render_nothing();
    }
}