<?php
namespace FormBuilder\Input;

use FormBuilder\InputCore\InputCore;
use FormBuilder\Validation\ValidationType;
//use FormBuilder\Validation\Validation;

class Select extends InputCore{
    protected
        $selectOptionText,
        $options,
        $selected;

    function __construct($label, $name, array $options, $validations, $selectOptionText = '', $selected = null){
        $this->label = $label;
        $this->name = $name;

        if ($selectOptionText === '')
            $this->selectOptionText = "<Select $label>";
        else
            $this->selectOptionText = $selectOptionText;

        $this->options = $options;
        $this->selected = empty($selected) ? "" : $selected;

        //TODO: check if accepts any other validation except required
        if ( !empty($validations) ) {
            if (is_array($validations)) $this->validations = $validations;
            else $this->validations = array($validations);
        }

        return $this;
    }

    public function validate(){
        //If a valid value is not chosen, then the value is set to empty
//        $this->value = html_entity_decode($this->value); //TODO: checkbox, radio... CHECK!
        if ( !empty( $this->options ) ){
            if ( empty($this->value) && !in_array(ValidationType::Required, $this->validations) ){ //TODO: Test;
                return true;
            }

            $validOption = false;
            if ( !$this->isAssoc($this->options) ){
                $validOption = in_array( $this->value, $this->options );
            } else {
                foreach ($this->options as $group => $optionsArray){
                    if (in_array($this->value, $optionsArray)){ return true; }
                }
            }

            if ( !$validOption ){
                $this->error_message = "*Invalid option chosen";
                $this->value = "";
                return false;
            }

            return true;
        }
        return true;
    }

    protected function isAssoc($arr){ return array_keys($arr) !== range(0, count($arr) - 1); }

    public function render(){
        $html = "";

        $valueHtml = htmlspecialchars($this->value);

        $selected = !empty($valueHtml) ?  $valueHtml : $this->selected;

        if ( in_array(ValidationType::Required , $this->validations) ){
            $nativeValidationHtml = " Required ";
            $labelHtml = $this->label . "*"; //TODO option to turn this feature off or customise
        } else {
            $nativeValidationHtml = "";
            $labelHtml = $this->label . " (optional)";
        }

        //Display Input Field
        $html.= "<label for='input_{$this->name}'>{$labelHtml}:" .
                "<select name='$this->name' id='input_{$this->name}'{$nativeValidationHtml}>";


        if ( !empty($this->selectOptionText) ) {
            $html .= "<option value=''>". htmlspecialchars($this->selectOptionText) ."</option>";
        }

        if ( !empty($this->options) ){
            if ( !$this->isAssoc( $this->options ) ){
                $this->outputComboboxOptions($html, $this->options, $selected);
            } else {
                foreach ( $this->options as $groupName => $optionsArray){
                    $groupNameLabel = htmlspecialchars($groupName);
                    $html .= "<optgroup label='$groupNameLabel'>";
                    $this->outputComboboxOptions($html, $optionsArray, $selected);
                    $html .= "</optgroup>";
                }
            }
        }
        $html .= "</select>" .
                 "</label>";

        if ( !empty($this->error_message) ) $html .= "<small class='error'>$this->error_message</small>";

        return $html;
    }

    private function outputComboboxOptions(&$html, $options, $selected){
        foreach ($options as $option) {
            $optionValue = htmlspecialchars($option);
            //TODO: Cross browser testing to see safe characters on value-> spaces dots trims
//            $optionValue = str_replace(" ", "&nbsp;", $optionValue);
            if ($option === $selected){
                $html .= "<option value='$optionValue' selected='selected'>$option</option>";
            } else {
                $html .= "<option value='$optionValue'>$option</option>";
            }
        }
    }

    public function getOptions(){
        return $this->options;
    }

}
