<?php

/*
 * Copyright (C) 2011 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__file__)."/../models/StreamActivity.class.php";

class StreamActivityTestCase extends UnitTestCase {

    function setUp() {
        
    }

    function tearDown() {
    }

    function test_from_xml() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <entry xmlns="http://www.w3.org/2005/Atom" xmlns:activity="http://activitystrea.ms/spec/1.0/">
                <id>my_id</id>
                <title>my_title</title>
                <author>
                    <uri>acct:krassmus@blubber.it</uri>
                    <id>http://test_server/RasmusFuhse</id>
                    <name>Rasmus Fuhse</name>
                    <link href="http://www.blubber.it"/>
                </author>
                <activity:actor>
                    <activity:object-type>http://activitystrea.ms/schema/1.0/person</activity:object-type>
                    <id>dispatch.php/profile?username=Krassmus</id>
                    <title>Rasmus Fuhse</title>
                    <link rel="alternate" type="text/html" href="http://www.blubber.it/dispatch.php/profile?username=Krassmus"/>
                </activity:actor>
                <activity:verb>http://activitystrea.ms/schema/1.0/follow</activity:verb>
                <activity:object>
                    <activity:object-type>http://activitystrea.ms/schema/1.0/person</activity:object-type>
                    <title>André Noack</title>
                    <id>http://develop.studip.de/studip/dispatch.php/profile?username=anoack</id>
                    <link rel="alternate" type="text/html" href="http://develop.studip.de/studip/dispatch.php/profile?username=anoack"/>
                </activity:object>
            </entry>
        ';
        $activity = StreamActivity::fromXML(studip_utf8encode($xml));
        $this->assertEqual($activity->verb, "http://activitystrea.ms/schema/1.0/follow");
        $this->assertEqual($activity->object['title'], "André Noack");
    }

}


