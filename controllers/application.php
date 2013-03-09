<?
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
