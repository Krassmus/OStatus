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
        if (!file_exists($well_known_path) && !mkdir($well_known_path)) {
            echo "Not able to install public/.well-known/host-meta . Please do that manually.";
        } else {
            $template = $this->getTemplate("host-meta.php", null);
            file_put_contents($well_known_path."/host-meta", $template->render());
        }
    }
    
    protected function getTemplate($template_file_name, $layout = "without_infobox") {
        if (!$this->template_factory) {
            $this->template_factory = new Flexi_TemplateFactory(dirname(__file__)."/../well-known");
        }
        $template = $this->template_factory->open($template_file_name);
        if ($layout) {
            if (method_exists($this, "getDisplayName")) {
                PageLayout::setTitle($this->getDisplayName());
            } else {
                PageLayout::setTitle(get_class($this));
            }
            $template->set_layout($GLOBALS['template_factory']->open($layout === "without_infobox" ? 'layouts/base_without_infobox' : 'layouts/base'));
        }
        return $template;
    }
}