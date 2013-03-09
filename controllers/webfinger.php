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
require_once dirname(__file__)."/application.php";

class WebfingerController extends ApplicationController {
    
    public function profile_action() {
        //LRDD
        $resource = Request::get("resource");
        $username = substr($resource, 0, stripos($resource, "@"));
        
        $this->set_layout(null);
        $this->user = new BlubberUser(get_userid($username));
        if (!$this->user->isNew()) {
            $this->keys = OstatusUsersKeys::get($this->user->getId());
        }
        $this->set_content_type("text/xml");
    }
    
    public function feed_action($username) {
        //atom-feed
        $this->user = new OstatusUser(get_userid($username));
        $this->blubber = BlubberPosting::findBySQL("user_id = ? AND external_contact = '0' ORDER BY mkdate DESC", array($this->user->getId()));
        $this->set_content_type("text/xml");
    }
    
}