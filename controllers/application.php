<?
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class ApplicationController extends Trails_Controller{

    function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $this->dispatcher->current_plugin;
    }

    function before_filter($action, $args) {
        $this->current_action = $action;
        $this->flash = Trails_Flash::instance();
        $this->standard_templates = $GLOBALS['STUDIP_BASE_PATH'] . '/templates/';
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));

        $this->assets_url = $this->plugin->getPluginUrl(). '/assets/';
    }

    function rescue($exception) {
        throw $exception;
    }

    function after_filter($action, $args) {
        page_close();
    }

    function url_for($to = '', $params = array()) {
        if($to === '') {
            $to = substr(strtolower(get_class($this)), 0, -10) . '/' . $this->current_action;
        }
        $url = PluginEngine::getUrl($this->plugin, $params, $to);
        return $url;
    }

    function link_for($to = '', $params = array()) {
        if($to === '') {
            $to = substr(strtolower(get_class($this)), 0, -10) . '/' . $this->current_action;
        }
        return PluginEngine::getLink($this->plugin, $params, $to);
    }

    function flash_set($type, $message, $submessage = array()) {
        $old = (array)$this->flash->get('msg');
        $new = array_merge($old, array(array($type, $message, (array)$submessage)));
        $this->flash->set('msg', $new);
    }

    function flash_now($type, $message, $submessage = array()) {
        $old = (array)$this->flash->get('msg');
        $new = array_merge($old, array(array($type, $message, (array)$submessage)));
        $this->flash->set('msg', $new);
        $this->flash->discard('msg');
    }

    function render_json($data) {
        $this->set_content_type('application/json;charset=utf-8');
        return $this->render_text(json_encode($data));
    }
}
