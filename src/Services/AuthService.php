<?php

namespace App\Services;

// check session and start it if not started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService{
    private $secretKey, $pathToVerify, $cookieSecurity;

    /**
     * This class is used to generate a Json Web Token for verify request after Authentication, this can be use for verify request for a specific routes.
     * @param string Path to verify is basically the path for that you want to verify the specific token, for example if generate token verify the users you can give the "/" for accessing this token across the whole domain
     */
    public function __construct($pathToVerify) {
        $this->secretKey = $_ENV["SECRET_KEY"];
        $this->pathToVerify = $pathToVerify;
        $this->cookieSecurity = $_ENV["APP_PHASE"] === "development" ? false : true;
    }

    private function saveCookieWithJWT ($jwt, $cookie_prepend_name, $expiryTime){
        // After generate the token set the JWT in an HTTP-only cookies
        $cookie_name = $cookie_prepend_name . "_auth_token"; // cookie name
        $cookie_value = $jwt; // Jwt token
        $cookie_lifetime = time() + ($expiryTime * 3600); // Expire in 1 day by default or as defined by user
        $cookie_path = $this->pathToVerify; // Cookie available only the routes that starts with /users/
        $cookie_domain = $_ENV["DOMAIN_NAME"]; // Domain name
        $secure = $this->cookieSecurity; // Set to true for using HTTPS
        $http_only = true; // Prevent JavaScript access

        // Set the cookie
        setcookie($cookie_name, $cookie_value, $cookie_lifetime, $cookie_path, $cookie_domain, $secure, $http_only);

        return true;
    }
    private function verifyCookie($jwt): string|bool
    {
        try{
            //Decode and verify the token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));

            //Access user data from the token
            $userName = $decoded->username;

            return $userName;
        } catch(\Exception $e){
            // Token is invalid
            return false;
        }
    }

    /**
     * This method generate a JWT (Json Web Token) for 
     * authentication save this to the client side 
     * local storage and get everytime you need to 
     * verify for specific routes.
     * 
     * @param string $userId Unique Id of the user
     * @param string $userName Unique username of the user, username is not exists you can also send the user Email
     * @param string $audience Represents who the token is intended for, This is usually the expected recipient of the token, For example if this is for the users, simply return the "user", if this is for the adminpanel users simply return the "rootUser"
     * @param int $expiryTime (Optional) Expiry time of token in hours, default is 24
     * @param string $issuer (Optional) Represents who issued the token, maybe domain or name of the system that generated the token, 
     * @return string return The valid JWT token return this to the client side and save on local storage
     */
    public function generateJWT($userId, $userName, $audience, $expiryTime = 24, $issuer = null){
        $issuer = $issuer == null ? $_ENV["APP_NAME"] : $issuer;
        try{
            // Payload for the token
            $payload = [
                'iss' => $issuer,
                'aud' => $audience,
                'iat' => time(),
                'exp' => time() + ($expiryTime * 3600),
                'userId' => $userId,
                'username' => $userName
            ];
    
            // Generate the token
            $jwt = JWT::encode($payload, $this->secretKey, 'HS256');
    
            // For any reason if you don't want to use the cookies for request verification, you can add your own method
            return self::saveCookieWithJWT($jwt, $audience, $expiryTime);
        } catch(\Exception $e){
            // Token generation is failed for any reason
            return false;
        }
    }   

    public function verifyJWT($audience): array|string{
        $cookie_name = $audience . "_auth_token";

        if(isset($_COOKIE[$cookie_name])){
            $jwt = $_COOKIE[$cookie_name];

            // Now, verify the JWT
            $decoded = self::verifyCookie($jwt);
            if($decoded){
                // Token is valid and return the username (means email id)
                return $decoded;
            } else{
                return false;
            }
        } else{
            return false;
        }
    }

    public function destroyJWT($audience){

        // After generate the token set the JWT in an HTTP-only cookies
        $cookie_name = $audience . "_auth_token"; // cookie name
        $cookie_value = ''; // Jwt token
        $cookie_lifetime = time() - 3600; // Expire in 1 day by default or as defined by user
        $cookie_path = $this->pathToVerify; // Cookie available only the routes that starts with /users/
        $cookie_domain = $_ENV["DOMAIN_NAME"]; // Domain name
        $secure = $this->cookieSecurity; // Set to true for using HTTPS
        $http_only = true; // Prevent JavaScript access

        // Set the cookie
        setcookie($cookie_name, $cookie_value, $cookie_lifetime, $cookie_path, $cookie_domain, $secure, $http_only);

        return true;
    }
}