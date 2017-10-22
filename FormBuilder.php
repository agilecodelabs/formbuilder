<?php

namespace FormBuilder;

use FormBuilder\InputCore\iInput;

class FormType{
    const Normal = 0;
    const Multipage = 1;
    const Ajax = 3;
}

class FormBuilder {

    protected $inputs = array();

    private
        $version,
        $formName,
        $saveData,
        $formPosted = false, //True if form has been posted and stored in session. NOTE: Does not mean the request type is post, due to redirection.
        $formSent = null, //applies only if a send function is specified
        $passedValidation = null, //applies only if a form is posted
        $formChanged = false,
        $csrfSetup = false,
        $csrfTokenValid= false,
        $csrfTokenRecent= false,
        $userCustomValidationFunction = null
    ;

    function __construct($formName, $saveData = false, $version = null){ //TODO: AJAX, multipage, confirmation
        if ( session_status() !== 2 ) session_start();  //Start session if not started already

        $this->formName = $formName;
        $this->version = $version;

        if ( isset( $_SESSION[$this->formName]) ){

            if ( !isset($version) || $_SESSION[$this->formName]->version === $this->version){
                $this->formPosted = true;
                $this->getDataFromSession();
            } else {
                $this->formChanged = true;
            }

            if (!$saveData) unset( $_SESSION[ $this->formName ] );

        }

        return $this;
    }

    public static function  createInstance($formName, $saveData, $version){
        $formBuilder = new FormBuilder($formName, $saveData, $version);
        return $formBuilder;
    }

    public function getInputs(){ return $this->inputs; }

    private function isRequestTypePost(){ return $_SERVER["REQUEST_METHOD"] == "POST"; }

    public function isFormSendFunctionSuccessful(){
        if ($this->formSent === null)
            throw new\BadFunctionCallException(
                "'isFormSent' cannot be used as no send function was set as a parameter during the function 'processForm()'");
        return $this->formSent;
    }

    /**
     * Checks if a form was posted
     * @return bool Return True if a from was posted. False otherwise.
     */
    public function isFormPosted(){ return $this->formPosted; }

    /**
     * Adds an input field to the form builder
     * @param iInput $input Input Field to add
     * @return $this
     */
    public function addInputField(iInput $input){
        if ($this->formPosted === false)
            $this->inputs[ $input->getName() ] = $input;
        return $this;
    }

    private function checkIfInputExist($name){
        if (!array_key_exists($name, $this->inputs)){
            throw new \OutOfBoundsException("Input '{$name}' was not created before rendering!");
        }
        return true;
    }

    public function renderInput($name){
        $this->checkIfFormHasBeenProcessed("renderInput");
        $this->checkIfInputExist($name);
        return $this->inputs[$name]->render();
    }

    //TODO: render InputField

    public function renderErrorMessage($name){
        $this->checkIfFormHasBeenProcessed("renderErrorMessage");
        $this->checkIfInputExist($name);
        return $this->inputs[$name]->getErrorMessage();
    }

    public function addCustomValidation($function){
        $this->userCustomValidationFunction = $function;
        return $this;
    }

    public function getFormStatusMessage(
        $validationMsg = "Please correct the errors shown below before attempting to resend",
        $formChangedMsg = "The form has been updated before you were able to send the form! Please attempt to send the form again.",
        $timeOutMsg = "Session timeout! Please resubmit form.",
        $invalidToken = "Invalid Session Token! Please resubmit form using the submit button.",
        $sendFunctionFail = "Your message failed to send due to a technical error! Please resubmit the form. If this problem persists, please contact us directly by phone or by email as shown below.",
        $sendFunctionSuccess = "Your message has been sent successfully. Check your Inbox for confirmation (including Junk folder)"
    ){
        if ( $this->formPosted){
            if ( $this->formChanged ) return $formChangedMsg;
            if ( $this->csrfSetup = true ){
                if ( !$this->isCsrfTokenIsRecent() ) return $timeOutMsg;
                elseif ( !$this->isCsrfTokenValid() ) return $invalidToken;
            }
            if ( !$this->passedValidation) return $validationMsg;
            if ( $this->formSent === false ) return $sendFunctionFail;
            return $sendFunctionSuccess;
        }
        return null; //If no message was posted
    }

    public function processForm( $sendFormFunction = null ){
        if ( $sendFormFunction !== null && $this->formSent == null ) $this->formSent = false;
        if ( !$this->formPosted ) {
            $this->validateInputs(); //Set error messages & flags

            if ( $this->isRequestTypePost() ) {

                //Call the send function, if it is set and validation has passed
                if ( $this->hasPassedValidation() && $sendFormFunction !== null) {
                    if (call_user_func($sendFormFunction, $this)) {
                        $this->formSent = true;
                        $this->resetData();
                    } else {
                        $this->formSent = false;
                    }
                }

                $this->storeDataInSession();
                header('Location: ' . $_SERVER['REQUEST_URI']); //Redirect to same page
                die();
            }
        }
        return $this;
    }

    private function validateInputs(){
        if ( $this->isRequestTypePost() ) {
            /*
             * 1) Check if all data is set. If data is not set, assume value is empty, likely due to the field being
             * disabled by client side coding.
             */
            foreach ($this->inputs as $name => $input) {
                if (isset($_POST[$name])) {
                    $input->setValue( $_POST[$name] );
                } else {
                    $input->setValue("");
                }
            }

            //(2) Check if all data passes validation and set appropriate error messages
            foreach ($this->inputs as $name => $input) {
                if ( !$input->validate() ) { $this->passedValidation = false; };
            }
            if ($this->passedValidation === false) { return false; }

            //(3) If data passes initial validation, check if data passes User's Validations if set (optional).
            if ($this->userCustomValidationFunction !== null){
                if (call_user_func($this->userCustomValidationFunction, $this->inputs) === false){
                    $this->passedValidation = false;
                    return false;
                };
            }

            //(4) Perform CSRF check (cross site request forgery), if it has been set up (optional)
            if ( $this->csrfSetup === true && (!$this->isCsrfTokenValid() || !$this->isCsrfTokenIsRecent()) ){
                $this->passedValidation = false;
                return false;
            }

            $this->passedValidation = true;
            return true;
        } else {
            $this->passedValidation = false;
            return false;
        }
    }

    private function storeDataInSession(){
        $this->userCustomValidationFunction = null; //Cannot serialise functions
        $_SESSION[$this->formName] = $this;
//        session_write_close();
    }

    private function getDataFromSession(){
        $newFormBuilder = $_SESSION[$this->formName];
//        if ($newFormBuilder instanceof FormBuilder){};

//        $formName
//        $saveData,
//        $this->formPosted = $newFormBuilder->formPosted;
        $this->formSent = $newFormBuilder->formSent;
        $this->passedValidation = $newFormBuilder->passedValidation;
        $this->formChanged = $newFormBuilder->formChanged;
        $this->csrfSetup = $newFormBuilder->csrfSetup;
        $this->csrfTokenValid= $newFormBuilder->csrfTokenValid;
        $this->csrfTokenRecent= $newFormBuilder->csrfTokenRecent;
        $this->inputs = $newFormBuilder->inputs;
//        $this->userCustomValidationFunction = null

    }

    public function resetData(){
        if ( isset( $_SESSION[$this->formName] ) ) unset ($_SESSION[$this->formName]);
        foreach ($this->inputs as $input){ $input->setValue(""); }
    }

    private function checkIfFormHasBeenProcessed($methodName){
        if ( !is_bool( $this->passedValidation ) )
            throw new \BadMethodCallException("'processForm' function must be run before using the function '$methodName'!");
    }

    public function hasPassedValidation(){
        $this->checkIfFormHasBeenProcessed('hasPassedValidation');
        return $this->passedValidation;
    }

    public function getInputErrorMsg($name){
        $this->checkIfInputExist($name);
        return $this->inputs[$name]->error_message;
    }

    public function getInputValue($name){
        $this->checkIfInputExist($name);
        return $this->inputs[$name]->getValue();
    }

    //CSRF Functions
    public function csrfSetup($max_elapsed = 86400){
        $this->csrfSetup = true;

        //Check if token variables are set
        if ( !isset($_SESSION['csrf_token_time']) || !isset($_SESSION['csrf_token']) || !$this->isRequestTypePost() )
        {
            $this->destroyCsrfToken();
            return $this;
        }

        //Set variables
        $stored_time = $_SESSION['csrf_token_time'];
        $stored_token =  $_SESSION['csrf_token'];
        $user_token = $_POST['csrf_token'];

        //Check if token is recent
        $this->csrfTokenRecent = ($stored_time + $max_elapsed) >= time();

        //Check if token is valid (i.e matches the token stored as a session)
        $this->csrfTokenValid = ($user_token === $stored_token);

        return $this;
    }

    public function checkIfCsrfSetupRun($method){
        if ($this->csrfSetup === false)
            throw new \BadMethodCallException("'csrfSetup' function must be run before using function '$method''");
    }

    public function isCsrfTokenValid(){
        $this->checkIfCsrfSetupRun('isCsrfTokenValid');
        return $this->csrfTokenValid;
    }

    public  function isCsrfTokenIsRecent(){
        $this->checkIfCsrfSetupRun('isCsrfTokenIsRecent');
        return $this->csrfTokenRecent;
    }

    private function destroyCsrfToken() {
        unset( $_SESSION['csrf_token'] );
        unset( $_SESSION['csrf_token_time'] );
    }

    public function renderHiddenCsrfCheckField(){
        $this->checkIfCsrfSetupRun('renderHiddenCsrfCheckField');
        $token = md5(uniqid(rand(), true));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        if ( isset($this->TEST_badToken) ){
            $token = $this->TEST_badToken;
        }
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
    }

}



