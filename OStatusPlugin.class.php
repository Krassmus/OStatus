<?php

class OStatusPlugin extends StudIPPlugin implements SystemPlugin {
    
    public function __construct() {
        parent::__construct();
        
        $nav = new AutoNavigation(_("Externe Kontakte"), PluginEngine::getURL($this, array(), "contacts/my"));
        Navigation::addItem("/community/ostatuscontacts", $nav);
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