<?php

namespace App\Helpers;

use App\Helpers\JsonResponse;

// All message function based on their Controller or Model files for other error here is a universal function
class ErrorMessages{
    public static function Users(){
        return [
            "success" => [
                "001" => "Registration successful! OTP sent to your email",
                "002" => "User logged in successfully, You'll be redirected",
                "003" => "Email verified successfully, You'll be redirected to login page",
                "004" => "Address added successfully",
                "005" => "User is verified",
                "006" => "Verification Code Sent Successfully, You'll be redirected to verify page",
                "007" => "Registration Successfully, You'll be redirected to verify page"
            ],
            "error" => [
                "001" => "Failed to register",
                "002" => "Failed to send OTP, Try again",
                "003" => "This Email is already exists, Try another on or login",
                "004" => "Wrong Password",
                "005" => "Email not exists, Create account first",
                "006" => "Wrong OTP",
                "007" => "User is not verified",
                "008" => "OTP is expiry, click to Resend",
                "009" => "Failed to verify, Try again",
                "010" => "Failed to login, Try again"
            ]
        ];
    }

    public static function Universal(){
        return [
            "success" => [
                "001" => "Wallpaper Uploaded Successfully"
            ],
            "error" => [
                "001" => "Something goes wrong, Try again",
                "002_csrf" => "Invalid requested token",
                "003" => "Invalid Authorization",
                "004" => "Unauthorized Access"
            ]
        ];
    }

    public static function HandleResponse($response){
        if(
            method_exists(
                self::class, 
                $response["func"]
            )
        ){
            $errorMethod = new \ReflectionMethod(self::class, $response["func"]);
            $responseCodes = $errorMethod->invoke(null);

            JsonResponse::send(
                $responseCodes[$response["type"]][$response["code"]],
                $response["type"],
                key_exists("data", $response) ? $response["data"] : []
            );
        } else{
            JsonResponse::send(
                "Message class function not found", 
                "error",
                [],
                500
            );
        }
    }
}