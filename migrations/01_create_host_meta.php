<?php
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