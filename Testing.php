<?php


namespace FormBuilder\Testing;

use FormBuilder\FormBuilder;

class FormBuilderTests{ //change to class
    private $TEST_badToken = null;
    private $formBuilder;
    private $inputs;

    function __construct(FormBuilder &$formBuilder){
        $this->formBuilder = $formBuilder;
        $this->inputs = $formBuilder->getInputs();
    }

    public function TEST_removeNativeValidation(){
//        foreach($this->inputs as $name => $input){
//            $key = array_search(ValidationType::Required, $input->validations);
//            if ($key !== false ){ unset($input->validations[$key]); }
//
//            if ($input->type === InputType::email){ //TODO: switch for more
//                $input->type = InputType::text;
//            }
//        }

        return $this;
    }

    public function TEST_values($values){
        foreach($values as $name => $value){
            $this->inputs[$name]->setValue($value);
        }
        return $this;
    }

    public function TEST_badCsrfToken($badToken){
//        $this->formBuilder->TEST_badToken = $badToken;
        return $this;
    }

    public function renderTestingScript(){
        return "";
    }

}

class Testing {

} 