<?php
namespace FormBuilder\InputCore;

interface iInput{
    function getName();
    function getLabel();
    function getValidations();
    function getValue();
    function setValue($value);
    function getErrorMessage();
    function setErrorMessage($value);
    function render();
}

abstract class InputCore implements iInput{
    protected
        $name,
        $label,
        $validations = array(),
        $value = "",
        $error_message = "",
        $class;


    abstract protected function validate();
    abstract public function render();

    public function getName(){ return $this->name; }
    public function getLabel(){ return $this->label; }
    public function getValidations(){ return $this->validations; }
    public function getValue(){ return $this->value; }
    public function setValue($value){$this->value = $value;}
    public function getErrorMessage(){ return $this->error_message; }
    public function setErrorMessage($value){ if ( empty($this->error_message) ) $this->error_message = $value; }

}


//class InputType{
//    const color = "color";
//    const date = "date";
//    const datetime = "datetime";
//    const datetimelocal = "datetime-local";
//    const checkbox = "checkbox";
//    const file = "file";
//    const hidden = "hidden";
//    const month = "month";
//    const number = "number";
//    const password = "password";
//    const radio = "radio";
//    const range = "range";
//    const time = "time";
//    const url = "url";
//    const week = "week";
// radio
// checkbox
//}