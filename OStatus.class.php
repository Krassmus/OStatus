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


require_once dirname(__file__)."/models/TinyXMLParser.php";
require_once dirname(__file__)."/models/OstatusPosting.class.php";
require_once dirname(__file__)."/models/OstatusContact.class.php";
require_once dirname(__file__)."/models/OstatusUser.class.php";
require_once dirname(__file__)."/models/OstatusUsersKeys.class.php";
require_once dirname(__file__)."/models/MagicSignature.class.php";
require_once dirname(__file__)."/models/StreamActivity.class.php";
require_once dirname(__file__)."/models/SalmonDriver.class.php";
require_once dirname(__file__)."/models/OstatusHubSubscription.class.php";

/*if (!function_exists("l")) {
    function l($text) {
        return gettext(htmlReady($text));
    }
    function ll($text) {
        return gettext($text);
    }
}*/

class OStatus extends StudIPPlugin implements SystemPlugin {
    
    public function __construct() {
        parent::__construct();
        
        $nav = new AutoNavigation(_("Externe Kontakte"), PluginEngine::getURL($this, array(), "contacts/my"));
        if ($GLOBALS['perm']->have_perm("autor")) {
            Navigation::addItem("/community/ostatuscontacts", $nav);
        }
        if (stripos($_SERVER['REQUEST_URI'], "plugins.php/blubber/streams/profile") !== false) {
            if (Request::get('user_id') && Request::get("extern")) {
                $contact = BlubberExternalContact::find(Request::get('user_id'));
                if (is_a($contact, "BlubberContact")) {
                    $contact->restore();
                    $contact->refresh_feed();
                }
            } elseif(Request::get('user_id') && !Request::get("extern")) {
                PageLayout::addHeadElement("link", array('href' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/webfinger/feed/".get_username(Request::get("user_id"))));
            }
        }
        if (stripos($_SERVER['REQUEST_URI'], "dispatch.php/profile") !== false) {
            $username = Request::get("username") ? Request::username("username") : get_username();
            PageLayout::addHeadElement("link", array('href' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/ostatus/webfinger/feed/".$username));
        }
        
        //Salmon
        //  receive
        NotificationCenter::addObserver(SalmonDriver::create(), "processBlubber", "ActivityStreamProcesses");
        NotificationCenter::addObserver(SalmonDriver::create(), "processFollowers", "ActivityStreamProcesses");
        //  send
        NotificationCenter::addObserver(SalmonDriver::create(), "federateComment", "PostingHasSaved");
    }
    
    /**
    * This method dispatches and displays all actions. It uses the template
    * method design pattern, so you may want to implement the methods #route
    * and/or #display to adapt to your needs.
    *
    * @param  string  the part of the dispatch path, that were not consumed yet
    *
    * @return void
    */
    public function perform($unconsumed_path) {
        if(!$unconsumed_path) {
            header("Location: " . PluginEngine::getUrl($this), 302);
            return false;
        }
        $trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, null, 'show');
        $dispatcher->current_plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }
}