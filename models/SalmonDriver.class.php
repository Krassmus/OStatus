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
    
    public function deliverComment($event, $blubber) {
        if (($blubber['root_id'] !== $blubber['topic_id'])) {
            $parent = new BlubberPosting($blubber['root_id']);
            if ($parent['external_contact']) {
                $contact = BlubberExternalContact::find($parent['user_id']);
                if (is_a($contact, "OstatusContact")) {
                    
                }
            }
        }
    }
    
}