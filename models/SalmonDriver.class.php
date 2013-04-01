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

class SalmonDriver {
    
    static public $msg = array();
    static public $code = null;
    
    static public function create() {
        return new SalmonDriver();
    }
    
    public function processBlubber($event, $activity) {
        $success = OstatusPosting::createFromActivity($activity);
        if ($success) {
            self::$code = "201 Created";
        }
    }
    
    public function processFollowers($event, $activity) {
        $success = OstatusContact::externalFollower($activity);
        if ($success) {
            self::$code = "200 OK";
        }
    }
    
    public function federateComment($event, $blubber) {
        if ($blubber['description'] && ($blubber['root_id'] !== $blubber['topic_id'])) {
            $parent = new BlubberPosting($blubber['root_id']);
            if ($parent['external_contact']) {
                $contact = BlubberExternalContact::find($parent['user_id']);
                if (is_a($contact, "OstatusContact")) {
                    $activity = new StreamActivity();
                    $activity->id = $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/comment/".$blubber->getId();
                    $activity->title = get_fullname($blubber['user_id'])." commented on ".$contact->getName()."'s posting";
                    $activity->links = array(
                        'alternate' => array(
                            'href' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/".$blubber['root_id'],
                            'type' => "text/html"
                        )
                    );
                    $activity->published = $blubber['mkdate'];
                    $activity->updated = $blubber['chdate'];
                    $activity->verb = "http://activitystrea.ms/schema/1.0/post";
                    $activity->author = array(
                        'id' => $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".get_username(),
                        'uri' => "acct:".get_username()."@".$_SERVER['SERVER_NAME'],
                        'objectType' => "http://activitystrea.ms/schema/1.0/person"
                    );
                    $activity->actor = array(
                        'id' => $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".get_username(),
                        'objectType' => "http://activitystrea.ms/schema/1.0/person"
                    );
                    $activity->object = array(
                        'id' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/comment/".$blubber->getId(),
                        'objectType' => "http://activitystrea.ms/schema/1.0/comment",
                        'title' => $blubber['name'],
                        'content' => $blubber['description']
                    );
                    $activity->content = $blubber['description'];
                            
                    $xml = $activity->toXML();
                    $envelope_xml = SalmonDriver::createEnvelope($xml);
                    $thread = new BlubberPosting($blubber['root_id']);

                    //POST-Request
                    $request = curl_init($contact['data']['salmon_url']);
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
                    var_dump($error);
                    var_dump($response);
                    die();

                    //and the other server does the rest.
                    return $error ? $error : true;
                }
            }
        }
    }
    
    /**
     * Creates a magicenvelope for an xml document for a given user_id or current user.
     * A magic envelope is an xml-doc on its own and has signed the xml with the 
     * public key of the user.
     * See: http://salmon-protocol.googlecode.com/svn/trunk/draft-panzer-magicsig-01.html#anchor4
     * @param string $xml
     * @param stringnull : $user_id
     * @return string 
     */
    static public function createEnvelope($xml, $user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        $keys = OstatusUsersKeys::get($user_id);
        $data = MagicSignature::base64_url_encode($xml);
        $sig = MagicSignature::sign($xml, $keys['private_key']);
        
        $template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../views");
        $follow_template = $template_factory->open("salmon/envelope.php");
        $follow_template->set_attribute('base64data', $data);
        $follow_template->set_attribute('sig', $sig);
        return $follow_template->render();
    }
    
}