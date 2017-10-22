<?php
namespace FormBuilder\InputCore;

use FormBuilder\Validation\ValidationType;
use FormBuilder\Validation\Validation;

class InputCommon extends InputCore{
    protected $type;

    function __construct($label, $name, $validations = [], $placeholder = null, $defaultValue = null){
        $this->name = $name;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->value = $defaultValue;
        $this->setType();
        $this->getValidation( $validations );
    }

    protected function getValidation( $validations ){
        if ( !empty($validations) ) {
            if (is_array($validations)) {
                $this->validations = $validations;
            } else {
                $this->validations = array($validations);
            }
        }
    }

    protected function setType(){
        $this->type = "text";
    }

    public function validate(){
        Validation::testInput( $this );
        foreach ($this->validations as $validation){
            if (is_array($validation)) { //i.e. the function has additional parameters
                $functionName = $validation[0];
                $parameters = $validation;
                array_shift($parameters); //remove the function name from parameters
                array_unshift($validation, $this); //append the input type at the start of the parameters
            } else {
                $functionName = $validation;
                $parameters = array($this);
            }
            if (call_user_func_array('\FormBuilder\Validation\Validation::'.$functionName, $parameters) === false) return false;
        }
        return true;
    }

    public function render(){
        $html = "";

        $nativeValidationHtml = $this->getNativeValidation();
        $placeholderHtml = "";
        if ( !empty($this->placeholder) ) $placeholderHtml = " placeholder='". htmlspecialchars($this->placeholder) . "' ";

        //TODO option to turn this feature off or customise
        if ( in_array( ValidationType::Required, $this->validations ) ){
            $label = $this->label . "*";
        } else {
            $label = $this->label . " (optional)";
        }

        //Display Input Field
        $html.=
            "<label for='input_{$this->name}'>{$label}:" .
            $this->getInputHtml($nativeValidationHtml, $placeholderHtml);
            

        if ($this->error_message !== "") {
            $html .= $this->getErrorHtml();
        }

        $html .= "</label>";

        return $html;
    }


    protected function getInputHtml($nativeValidationHtml, $placeholderHtml){
        $nameHtml = htmlspecialchars( $this->name );
        $valueHtml = htmlspecialchars( $this->value );
        return
            "<input type='{$this->type}' name='$nameHtml' id='input_{$this->name}'" .
            "{$placeholderHtml}{$nativeValidationHtml} value='$valueHtml' />";
    }

    protected function getErrorHtml(){
        // old foundation
        // return "<small class='error'>$this->error_message</small>";
        return "<span class='form-error'>{$this->error_message}</span>";
    }

    protected function getNativeValidation(){
        $nativeValidation = "";

        foreach ($this->validations as $validation) {
            if ( $validation === ValidationType::Required)
                $nativeValidation .= "Required ";
            elseif ( is_array($validation) ){
                if ( $validation[0] === ValidationType::Pattern ){//TODO
                    $nativeValidation .= "pattern=\"{$validation[1]}\" ";
                }
            }
        }
        if (!empty($nativeValidation)){ $nativeValidation = " " . $nativeValidation; };
        return $nativeValidation;
    }
}