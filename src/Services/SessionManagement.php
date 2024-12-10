<?php

namespace App\Services;

// check session and start it if not started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


use App\Helpers\ErrorMessages;
use Exception;

class SessionManagement{
    private $tokenName, $tokenValue, $number;
    public function __construct($tokenName = "csrfToken") {
        $this->tokenName = $tokenName;
        $this->number = 0;
        $this->tokenValue = $this->GenerateCSRFToken();
    }
    // Get the CSRF token value
    public function GetTokenValue() {
        return $this->tokenValue;
    }
    // Generate token
    public function GenerateCSRFToken(){
        $randomString = bin2hex(random_bytes(10));
        return hash('sha256', $randomString, false);
    }
    // Add CSRF token in session variable
    public function AddCSRF(){
        try{
            // Store the CSRF token in the session
            $_SESSION[$this->tokenName] = $this->GetTokenValue();
        } catch(Exception $e){
            echo $e;
        }
    }
    /**
     * This function verify your CSRF token
     * @param mixed $tokenName Enter the token name that use to save the CSRF token in session
     * @param mixed $tokenValue Enter the token value to verify
     * @return bool|void true on verify, false otherwise
     */
    public static function VerifyToken($tokenName){
        // get the csrf token from header
        $headers = getallheaders();
        $csrfToken = null;

        if(array_key_exists('X-Csrf-Token', $headers)){
            $csrfToken = $headers['X-Csrf-Token'];
        } else if(array_key_exists('x-csrf-token', $headers)){
            $csrfToken = $headers['x-csrf-token'];
        } else if(array_key_exists('HTTP_X_CSRF_TOKEN', $_SERVER)){
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
        } 
        
        if(!empty($_SESSION[$tokenName]) && ($_SESSION[$tokenName] === $csrfToken)){
            return true;
        } else{
            ErrorMessages::HandleResponse([
                "type" => "error",
                "code" => "002_csrf",
                "func" => "Universal",
                "data" => [
                    'session' => $_SESSION[$tokenName],
                    'request' => $csrfToken
                ]
            ]);
        }
    }    
}
