<?php

namespace App\Controllers;

use App\Models\Contact;
use App\Helpers\ErrorMessages;
use App\Helpers\JsonResponse;
use App\Helpers\Validate;
use App\Helpers\Sanitizer;
use App\Services\SessionManagement;
use Respect\Validation\Rules\Json;

class ContactController{
    public function save(){
        if(
            !isset($_POST['name']) ||
            !isset($_POST['email']) ||
            !isset($_POST['country']) ||
            !isset($_POST['subject']) ||
            !isset($_POST['message']) ||
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['country']) ||
            empty($_POST['subject']) ||
            empty($_POST['message'])
        ){
            JsonResponse::send(
                "Some values are not exists or invalid",
                "error"
            );
        }

        $data = [
            "name" => Sanitizer::sanitizeFullName($_POST['name']),
            "email" => Sanitizer::sanitizeEmail($_POST["email"]),
            "country" => Sanitizer::sanitizeString($_POST["country"]),
            "subject" => Sanitizer::sanitizeString($_POST["subject"]),
            "msg" => Sanitizer::sanitizeDescription($_POST['message'])
        ];

        if(
            Validate::validateFullName($data['name']) &&
            Validate::validateEmail($data['email']) &&
            Validate::validateString($data['country']) &&
            Validate::validateString($data['subject']) &&
            Validate::validateDescription($data['msg'])
        ){
            $resp = Contact::save($data);
            
            if($resp){
                JsonResponse::send(
                    "We received your message, We'll contact you soon",
                    "success"
                );
            } else{
                JsonResponse::send(
                    "Something went wrong, Try again later",
                    "error"
                );
            }
        } else{
            $msg = "";

            switch (false) {
                case Validate::validateFullName($data['name']):
                    $msg = "Name value is invalid";
                    break;
                
                case Validate::validateEmail($data['email']):
                    $msg = "Email value is invalid";
                    break;

                case Validate::validateString($data['country']):
                    $msg = "Country value is invalid";
                    break;

                case Validate::validateString($data['subject']):
                    $msg = "Subject value is invalid";
                    break;

                case Validate::validateDescription($data['msg']):
                    $msg = "Message value is invalid";
                    break;

                default:
                    $msg = "Something goes wrong, Reload the page and try again";
                    break;
            }

            JsonResponse::send(
                $msg, 
                "error"
            );
        }
    }
}