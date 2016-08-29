<?php namespace Joyce\DomTemplate;

use DOMNodeList;

class BladeDOMTemplate extends DOMTemplate
{

    public static function create($path,$template_id = null) {
        return new self($path,$template_id);
    }

    public function __construct($path,$template_id = null) {
        $html = file_get_contents($path);

        if($template_id) {
            $res =  new DOMTemplate($html);
            $node = $res->query($template_id)->item(0);
            $html = $node->ownerDocument->saveHTML($node);
        }

        parent::__construct($html);
        return $this;
    }

    public function setValues($data) {
        foreach($data as $key => $item) {
            $this->setValue($key,$item);
        }
        return $this;
    }

    public function setClasses($data) {
        foreach($data as $key => $item) {
            $this->addClass($key,$item);
        }
        return $this;
    }


    public function prependValue ($query, $value, $asHTML=false) {
        foreach ($this->query ($query) as $node) {
            $node->nodeValue = $value . htmlspecialchars ($node->nodeValue, ENT_NOQUOTES);
        }
        return $this;
    }

    public function removeAllButOne($query) {
        $all = $this->query($query);

        $i = 0;
        foreach($all as $node) {
            if($i != 0) {
                $node->parentNode->removeChild($node);
            }
            $i++;
        }

    }

    public function cycle($query,$data,$function) {
        $this->removeAllButOne($query);
        $item = $this->repeat($query);

        foreach ($data as $d) {
            $function($item,$d);
        }

    }

    public function setToAbsolutePath() {
        $this->prependValue('//link[@href != "" and not(starts-with(@href,"http"))]/@href','/')
            ->prependValue('//script[@src != "" and not(starts-with(@src,"http"))]/@src','/');

    }

}