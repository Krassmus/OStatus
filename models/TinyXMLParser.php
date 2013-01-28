<?php

class TinyXMLParser {
   
    protected $arrOutput = array();
    protected $resParser;
    protected $strXmlData;
    
    static public function getArray($string) {
        return studip_utf8decode(self::get()->parse($string));
    }
    
    static public function getXML($arr) {
        return self::get()->arr2xml(studip_utf8encode($arr));
    }
    
    static public function get() {
        return new TinyXMLParser();
    }
    
    public function arr2xml($arr) {
        $output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $output .= $this->getXmlNode($arr);
        return $output;
    }
    
    protected function getXmlNode($arr) {
        $output = '<'.htmlReady($arr['name']);
        foreach ((array) $arr['attrs'] as $attribute_name => $attribute) {
            $output .= ' '.htmlReady($attribute_name).'="'.htmlReady($attribute).'"';
        }
        $output .= '>';
        if (count((array) $arr['children'])) {
            $output .= "\n";
            foreach ($arr['children'] as $childnode) {
                $output .= $this->getXmlNode($childnode);
            }
            $output .= "\n";
        } else {
            $output .= htmlReady($arr['tagData']);
        }
        $output .= '</'.htmlReady($arr['name']).'>';
        return $output;
    }
   
    public function parse($strInputXML) {
   
        $this->resParser = xml_parser_create ();
        xml_set_object($this->resParser,$this);
        xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

        xml_set_character_data_handler($this->resParser, "tagData");

        $this->strXmlData = xml_parse($this->resParser,$strInputXML );
        if(!$this->strXmlData) {
            die(sprintf("XML error: %s at line %d",
        xml_error_string(xml_get_error_code($this->resParser)),
        xml_get_current_line_number($this->resParser)));
        }

        xml_parser_free($this->resParser);

        return $this->arrOutput;
    }
    protected function tagOpen($parser, $name, $attrs) {
       $tag=array("name"=>$name,"attrs"=>$attrs);
       array_push($this->arrOutput,$tag);
    }
   
    protected function tagData($parser, $tagData) {
       if(trim($tagData)) {
            if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
            }
            else {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
            }
       }
    }
   
    protected function tagClosed($parser, $name) {
       $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
       array_pop($this->arrOutput);
    }
}