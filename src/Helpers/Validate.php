<?php

namespace App\Helpers;

use Respect\Validation\Validator as v;

class Validate {

    // Validate email address
    public static function validateEmail($email) {
        return v::email()->validate($email);
    }

    // Validate full name (Only allows alphabets and spaces)
    public static function validateFullName($name) {
        return v::alpha()->noWhitespace()->length(3, 100)->validate($name);
    }

    // Validate password (At least 8 characters, at least 1 letter, and at least 1 number)
    public static function validatePassword($password) {
        return v::stringType()->length(8, null)->regex('/[a-zA-Z]/')->regex('/\d/')->validate($password);
    }

    // Validate address (non-empty, length 10-255 characters)
    public static function validateAddress($address) {
        return v::stringType()->length(10, 255)->validate($address);
    }

    // Validate OTP (Numeric, 6 digits)
    public static function validateOTP($otp) {
        return v::numericVal()->length(6, 6)->validate($otp);
    }

    // Validate mobile number (10 digits, starts with 7, 8, or 9)
    public static function validateMobile($mobile) {
        return v::numericVal()->length(10, 10)->regex('/^[789]\d{9}$/')->validate($mobile);
    }

    // Validate search string (alphanumeric and spaces only, length between 3 and 100)
    public static function validateSearchString($search) {
        return v::alnum(' "')->length(1, 100)->validate($search);
    }

    // Validate a number
    public static function validateNumber($number){
        return is_numeric($number);
    }

    // Validate string (only alphabet)
    public static function validateString($str){
        return v::stringVal()->length(3, 100)->validate($str);
    }

    // Validate ID (positive integer)
    public static function validateId($id) {
        return v::intVal()->positive()->validate($id);
    }

    // Validate URL (valid URL format)
    public static function validateURL($url) {
        return v::url()->validate($url);
    }

    public static function validateTitle($title){
          // 1. Remove excess spaces
        $title = trim($title);
        
        // 2. Check length (between 5 and 60 characters)
        if (strlen($title) < 5 || strlen($title) > 60) {
            return "Title must be between 5 and 60 characters.";
        }

        // 3. Check if it contains only allowed characters (letters, numbers, spaces, hyphens, underscores)
        if (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $title)) {
            return "Title can only contain letters, numbers, spaces, hyphens (-), and underscores (_).";
        }

        // If all checks pass, return true (valid)
        return true;
    }

    public static function validateDescription($description){
        // 1. Remove excess spaces
        $description = trim($description);

        // 2. Check length (between 20 and 160 characters)
        if (strlen($description) < 20 || strlen($description) > 160) {
            return "Description must be between 20 and 160 characters.";
        }

        // 3. Check if it contains only allowed characters
        if (!preg_match('/^[a-zA-Z0-9\s,\.\-]+$/', $description)) {
            return "Description can only contain letters, numbers, spaces, commas (,), periods (.), and hyphens (-).";
        }

        // If all checks pass, return true (valid)
        return true;
    }
}

