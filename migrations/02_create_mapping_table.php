<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CreateMappingTable extends DBMigration
{
    function up() {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `ostatus_mapping` (
                `item_id` varchar(32) NOT NULL,
                `foreign_id` varchar(256) NOT NULL,
                `type` varchar(256) NOT NULL,
                UNIQUE KEY `uniq` (`item_id`,`foreign_id`,`type`),
                KEY `item_id` (`item_id`,`foreign_id`)
            ) ENGINE=MyISAM;
        ");
    }
    
    function down() {
        DBManager::get()->exec("DROP TABLE IF EXISTS `ostatus_mapping` ");
    }
    
    
}