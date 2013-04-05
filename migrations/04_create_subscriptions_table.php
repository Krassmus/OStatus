<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CreateSubscriptionsTable extends DBMigration
{
    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `ostatus_hub_subscriptions` (
            `subscriber_id` varchar(32) NOT NULL,
            `user_id` varchar(32) NOT NULL,
            `url` varchar(265) NOT NULL,
            `verified` int(11) NOT NULL,
            `end_date` int(11) NOT NULL,
            `chdate` bigint(20) NOT NULL,
            `mkdate` bigint(20) NOT NULL,
            PRIMARY KEY (`subscriber_id`),
            UNIQUE KEY `user_id` (`user_id`),
            KEY `url` (`url`)
            ) ENGINE=MyISAM
        ");
    }
    
    function down() {
        DBManager::get()->exec("DROP TABLE IF EXISTS `ostatus_hub_subscriptions` ");
    }
    
    
}