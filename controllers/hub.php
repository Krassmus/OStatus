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

class HubController extends ApplicationController {
    
    public function register_action() {
        if (Request::isPost()) {
            if (Request::get("hub.callback") && Request::get("hub.topic") && Request::get("hub.mode") && Request::get("hub.verify")) {
                //subscribe/unsubscribe
                $username = substr(Request::get("hub.topic"), strrpos(Request::get("hub.topic"), "/") + 1);
                $user_id = get_userid($username);
                if (!$user_id) {
                    $this->set_status("404 Not Found");
                    $this->render_text("Requested topic-feed is not from a local user.");
                    return;
                }
                if (Request::get("hub.verify") === "async") {
                    $this->set_status("500 internal server error");
                    $this->render_text("Async verify-mode not yet supported.");
                    return;
                }
                if (Request::get("hub.mode") === "subscribe") {
                    $challenge1 = md5(uniqid());
                    $challenge2 = file_get_contents(URLHelper::getURL(
                        Request::get("hub.callback"),
                        array(
                            'hub.mode' => "subscribe",
                            'hub.topic' => Request::get("hub.topic"),
                            'hub.challenge' => $challenge1,
                            'hub.verify_token' => Request::get("hub.verify_token")
                        ),
                        true
                    ));
                    if ($challenge2 === $challenge1) {
                        $subscriber = OstatusHubSubscription::findBySQL("callback = ? AND user_id = ?", array(Request::get("hub.callback"), $user_id));
                        if (!$subscriber) {
                            $subscriber = new OstatusHubSubscription();
                            $subscriber['callback'] = Request::get("hub.callback");
                            $subscriber['user_id'] = $user_id;
                            $subscriber['verify_token'] = Request::get("hub.verify_token");
                            $subscriber['verified'] = 1;
                            $subscriber['end_date'] = Request("hub.lease_seconds") ? time() + Request("hub.lease_seconds") : null;
                            $subscriber->store();
                        }
                        $this->set_status("204 No Content");
                        $this->render_nothing();
                        return;
                    }
                } elseif(Request::get("hub.mode") === "unsubscribe") {
                    $subscriber = OstatusHubSubscription::findBySQL("callback = ? AND user_id = ?", array(Request::get("hub.callback"), $user_id));
                    if ($subscriber && (!$subscriber['secret'] or $subscriber['secret'] === Request::get("hub.secret"))) {
                        $subscriber->delete();
                        $this->set_status("204 No Content");
                        $this->render_nothing();
                        return;
                    }
                }
            }
        }
        $this->set_status("404 Not found");
        $this->render_nothing();
    }
    
    public function verify_challenge_action() {
        $this->render_text(Request::get("hub.challenge"));
    }
    
}