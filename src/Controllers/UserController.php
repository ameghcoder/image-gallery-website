<?php


namespace App\Controllers;

use App\Models\User;
use App\Helpers\ErrorMessages;
use App\Helpers\JsonResponse;
use App\Helpers\Validate;
use App\Helpers\Sanitizer;
use App\Services\SessionManagement;
use Respect\Validation\Exceptions\EmailException;

class UserController
{
    public function __construct() {
        // First verify request
        SessionManagement::VerifyToken("csrfToken");
    }

    public function store() {
        $userEmail = Validate::validateEmail($_POST["email"]) ? $_POST["email"] : null; 
        $userPassword = Validate::validatePassword($_POST["password"]) ? $_POST["password"] : null; 
        $userName = Validate::validateFullName($_POST["name"]) ? $_POST["name"] : null; 

        if(
            $userEmail != null && 
            $userPassword != null && 
            $userName != null
        ) {
            // Register User
            $response = User::create([
                "name" => $userName,
                "email" => $userEmail,
                "password" => $userPassword
            ]);

            // Handle Response | Pass response to following function
            ErrorMessages::HandleResponse($response);        
        } else{
            $message = "";
            switch (null) {
                case $userEmail:
                    $message = "Invalid Email";
                    break;
                
                case $userPassword:
                    $message = "Invalid Password";
                    break;
                
                case $userName:
                    $message = "Invalid Name";
                    break;
                
                default:
                    $message = "Email or Password or Name is invalide, check or Try again";
                    break;
            }

            JsonResponse::send(
                $message,
                "error"
            );
        }
    }
    
    public function enter(){
        // First check that data is exists or not
        if(
            !$_POST["email"] && !$_POST["password"]
        ){
            JsonResponse::send(
                "Data is empty, pass valid data for login",
                "error"
            );
        }

        // Sanitize Data and then validate
        $data = [
            "email" => Sanitizer::sanitizeEmail($_POST["email"]),
            "password" => Sanitizer::sanitizePassword($_POST["password"])
        ];

        // Now Validate data
        if(

            Validate::validateEmail($data['email']) &&
            Validate::validatePassword($data['password'])
        ){
            // Verify User
            $response = User::read( [
                "email" => $data['email'],
                "password" => $data['password']
            ]);

            // Handle Response | Pass response to following function
            ErrorMessages::HandleResponse($response);
        } else{
            $message = "";
            switch (null) {
                case $data['email']:
                    $message = "Invalid Email";
                    break;
                
                case $data['password']:
                    $message = "Invalid Password";
                    break;
                
                default:
                    $message = "Email or Password is invalid, check or Try again";
                    break;
            }

            JsonResponse::send(
                $message,
                "error"
            );
        }
    }

    public function verifyEmail(){
        // First check that data is exists or not
        if(!$_POST["email"] && !$_POST["otp"]){
            JsonResponse::send(
                "OTP or Email is empty, pass valid data for Verify Email",
                "error"
            );
        }

        // Sanitize Data and then validate
        $data = [
            "email" => Sanitizer::sanitizeEmail($_POST["email"]),
            "otp" => Sanitizer::sanitizeOTP($_POST["otp"])
        ];

        // Now validate data
        if(
            Validate::validateEmail($data['email']) &&
            Validate::validateOTP($data['otp'])
        ){
            $response = User::checkOTP($data);

             // Handle Response | Pass response to following function
             ErrorMessages::HandleResponse($response);
        }else{
            $message = "";
            switch (null) {
                case $data['email']:
                    $message = "Invalid Email";
                    break;
                
                case $data['otp']:
                    $message = "Invalid OTP";
                    break;
                
                default:
                    $message = "Email or OTP is invalid, check or Try again";
                    break;
            }

            JsonResponse::send(
                $message,
                "error"
            );
        }
    }

    public function resendEmail(){
        // First check that data is exists or not
        if(!$_POST["email"] && !$_POST["type"]){
            JsonResponse::send(
                "Email or Request type is empty, pass valid data for Resend Email",
                "error"
            );
        }

        // Sanitize data and then validate
        $data = [
            "email" => Sanitizer::sanitizeEmail($_POST["email"]),
            "type" => Sanitizer::sanitizeString($_POST["type"])
        ];

        // Now validate data
        if(
            Validate::validateEmail($data["email"]) &&
            Validate::validateString($data["type"])
        ){
            if($data["type"] == "resend"){
                $response = User::resendEmail($data["email"]);
                ErrorMessages::HandleResponse($response);
            } else{
                JsonResponse::send(
                    "Request type is invalid",
                    "error",
                    [],
                    403
                );
            }
        }else{
            JsonResponse::send(
                "Email or Request type is invalid, pass valid data for Resend Email",
                "error"
            );
        }
    }
}
