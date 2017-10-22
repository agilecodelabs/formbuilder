<?php

namespace FormBuilder\Input;

use FormBuilder\InputCore\iInput;
use FormBuilder\InputCore\InputCore;
use FormBuilder\InputCore\InputCommon;

class Text extends InputCommon{
    //No additional methods
}

class TextArea extends InputCommon{
    function getInputHtml($nativeValidation, $placeholderHtml){
        $nameHtml = htmlspecialchars( $this->name );
        $valueHtml = htmlspecialchars( $this->value );
        return
            "<textarea name='$nameHtml' id='input_{$this->name}'{$placeholderHtml}{$nativeValidation}>" .
            "$valueHtml" .
            "</textarea>";
    }
}

class Email extends InputCommon {
    protected function setType(){ $this->type = 'email'; }
}

class Telephone extends InputCommon{
    protected function setType(){ $this->type = 'tel'; }
}

class HiddenField extends InputCore{

    function __construct($name, $label="", $value){
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->validations = array();
    }

    public function validate(){ return true; }

    public function render(){
        $nameHtml = htmlspecialchars( $this->name );
        $valueHtml = htmlspecialchars( $this->value );
        return "<input name='$nameHtml' type='hidden' value='$valueHtml'/>";
    }
}