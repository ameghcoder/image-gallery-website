<?php

namespace App\Helpers;

class Sanitizer
{
    /**
     * Sanitize a full name by trimming extra spaces and removing any HTML tags.
     */
    public static function sanitizeFullName(string $name): string
    {
        $name = trim(strip_tags($name));
        return preg_replace('/\s+/', ' ', $name);
    }

    public static function sanitizeNumber(string $number){
        return preg_replace('/[^0-9.\-]/', '', $number);
    }

    /**
     * Sanitize an address by trimming and removing any HTML tags.
     */
    public static function sanitizeAddress(string $address): string
    {
        $address = trim(strip_tags($address));
        return preg_replace('/\s+/', ' ', $address);
    }

    /**
     * Sanitize a general string by removing HTML tags and trimming whitespace.
     */
    public static function sanitizeString(string $string): string
    {   
        $string = str_replace('&', 'and', $string);
        $string = preg_replace('/[^A-Za-z0-9 ]/', '', $string);
        return trim(strip_tags($string));
    }

    /**
     * Sanitize a password by trimming spaces.
     */
    public static function sanitizePassword(string $password): string
    {
        return trim($password);
    }

    /**
     * Sanitize an email address by removing unwanted characters.
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize a mobile number by removing non-numeric characters.
     */
    public static function sanitizeMobile(string $mobile): string
    {
        return preg_replace('/\D/', '', $mobile);
    }

    /**
     * Sanitize a otp validation code by removing non-numeric characters.
     */
    public static function sanitizeOTP(string $otp){
        return preg_replace("/[^0-9]/", "", $otp);
    }

    public static function sanitizeTitle($title) {
        // Remove HTML tags to avoid XSS attacks
        $title = strip_tags($title);
    
        // Convert special characters to HTML entities (e.g., < > & becomes &lt; &gt; &amp;)
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
        // Remove any unwanted special characters or spaces
        // This will keep letters, numbers, spaces, hyphens, and underscores only
        $title = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $title);
    
        // Trim excess spaces
        $title = trim($title);
    
        // Optionally, ensure the title length is between 5 and 60 characters
        if (strlen($title) < 5 || strlen($title) > 60) {
            return false;  // Invalid length
        }
    
        return $title; // Return sanitized title
    }

    public static function sanitizeDescription($description) {
        // Remove HTML tags to avoid XSS attacks
        $description = strip_tags($description);
    
        // Convert special characters to HTML entities (e.g., < > & becomes &lt; &gt; &amp;)
        $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    
        // Remove any unwanted special characters
        // Allow letters, numbers, spaces, commas, periods, and hyphens only
        $description = preg_replace('/[^a-zA-Z0-9\s,\.\-]/', '', $description);
    
        // Trim excess spaces
        $description = trim($description);
    
        // Optionally, ensure the description length is between 20 and 160 characters
        if (strlen($description) < 20 || strlen($description) > 160) {
            return false;  // Invalid length
        }
    
        return $description; // Return sanitized description
    }
}
