<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CreateHostMeta extends DBMigration
{
    function up() {
        $well_known_path = dirname(__file__)."/../../../../.well-known";
        $template = $this->getTemplate("host-meta.php");
        if (!file_exists($well_known_path) && !mkdir($well_known_path)) {
            echo "Not able to install public/.well-known/host-meta . Please do that manually.";
            @file_put_contents(dirname(__file__)."/../host-meta", $template->render());
        } else {
            file_put_contents($well_known_path."/host-meta", $template->render());
        }
    }
    
    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../well-known");
        $template = $this->template_factory->open($template_file_name);
        return $template;
    }
}