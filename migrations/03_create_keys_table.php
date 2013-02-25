<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CreateKeysTable extends DBMigration
{
    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `ostatus_users_keys` (
                `user_id` varchar(32) NOT NULL,
                `private_key` text NOT NULL,
                `public_key` text NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`user_id`)
            ) ENGINE=MyISAM
        ");
    }
    
    function down() {
        DBManager::get()->exec("DROP TABLE IF EXISTS `ostatus_users_keys` ");
    }
    
    
}