<?php
namespace FormBuilder\Validation;

use FormBuilder\InputCore\iInput;

class ValidationType{
    //Constants referring to validation methods to speed up input by taking advantage of IDE code hinting features
    const Required = "isRequired";
    const LettersAndSpaces = "isLettersAndSpaces";
    const Email = "isEmail";
    const Pattern = "testPattern";
}

class Validation {
    public static function testInput(iInput &$input){
        $input->setValue( trim($input->getValue()) );
//        $input = stripslashes($input);
    }

    public static function isRequired(iInput $input){
        $value = $input->getValue();
        if ( !isset( $value ) || $value == ""){
            $input->setErrorMessage( "*" . $input->getLabel() ." is required" );
            return false;
        }
        return true;
    }

    public static function isLettersAndSpaces(iInput $input){
        if (!preg_match("/^[a-zA-Z ]*$/", $input->getValue() )){
            $input->setErrorMessage( "*Only letters and white space are allowed for " . $input->getLabel() );
            return false;
        }
        return true;
    }

    public static function isEmail(iInput $input){
        if (!filter_var($input->getValue(), FILTER_VALIDATE_EMAIL)){
            $input->setErrorMessage( "*Invalid Email format" );
            return false;
        }
        return true;
    }
}