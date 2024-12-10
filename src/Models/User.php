<?php

namespace App\Models;

use PDO;
use App\config\Database;
use App\Helpers\ErrorMessages;
use App\Services\AuthService;
use App\Services\MailService;


class User
{
    /**
     * Connect to the data and return the PDO object
     * @return PDO return the PDO connection object
     */
    private static function getConnection() {
        return Database::connect(); // Using Vulcans for DB config
    }

    /**
     * This function checks the existing user is verified or not by email
     * @param mixed $email Pass E-mail that you want to check
     * @return bool return true on verified, false otherwise
     */
    private static function isVerified($email){
        $conn = self::getConnection();
        $query = "SELECT is_verified FROM users WHERE email = :email";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        
        if($stmt->execute()){
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if( 
                !empty($data) &&
                key_exists("is_verified", $data) && 
                $data["is_verified"] > 0
            ){
                return true;
            } else if(
                !empty($data) &&
                key_exists("is_verified", $data) && 
                $data["is_verified"] == 0
            ){
                return false;
            } else{
                return false;                
            }
        } else{
            return false;
        }
    }

    /**
     * OTP Generate that generate the otp code and expiry time of that otp
     * @return int[] return an associative array that contains the otp code and expiry time in seconds
     */
    private static function otpCodeGenerator(){
        $otpCode = rand(100000, 999999);
        $currentTime = time();

        // After 15 minute expiry | want to change expiry time change 15 to what to want
        $expiryTimeInSecond = 15 * 60;

        return [
            "code" => $otpCode,
            "expiry" => $currentTime + $expiryTimeInSecond
        ];
    }
    
    /**
     * This function is used for sending the Verification Code on Users email id
     * @param mixed $email Pass email of the user
     * @param mixed $otp Pass OTP array object that returned by the otpCodeGenerator function
     * @param mixed $saveOtp Pass true/false, true if you want to save these details in database or false otherwise
     * @return bool return true on successfully sent E-mail, false otherwise
     */
    private static function sendVerificationCode($email, $otp, $saveOtp = false){
        if($saveOtp){
            $conn = self::getConnection();
            $query = "UPDATE users SET verify_code = :otp, verify_code_expiry = :expiry WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":otp", $otp["code"], PDO::PARAM_INT);
            $stmt->bindParam(":expiry", $otp["expiry"], PDO::PARAM_INT);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
        }

        $EmailService = new MailService();
        session_write_close();
        $EmailResponse = $EmailService->sendEmail($_ENV['MAIL_VERIFICATION'], $email, "Account Verification Email", "Your OTP Code is: " . $otp["code"] . ", It automatically expires in 15 minutes");
        return $EmailResponse;
    }

    /**
     * Check user existence by E-mail Id
     * @param mixed $email User E-mail
     * @return bool|int if user exist it return the number of column that e-mail id appears means 1 or false otherwise
     */
    private static function isExists($email) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private static function redirectToVerifyEmailPage($email){
        try{
            // check session and start it if not started already
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["can_verify"] = true;
            $_SESSION["can_verify_timestamp"] = time() + 20 * 60;
            $_SESSION["can_verify_email"] = $email;
    
            // return true, so other method can process and send response
            return true;
        } catch(\Exception $e){
            return false;
        }
    }

    /**
     * This function create a user if it not exists in the database and also send a verification Email to the user. It returns some code depends on what repsonse show to the use.
     * <br>001 - Registration successful! OTP sent to your email
     * <br>002 - Failed to register
     * <br>003 - Failed to send OTP, Try again
     * <br>004 - This Email is already exists, Try another one or Login
     * <br>005 - Something went wrong, Try again
     * <br>
     * <b>Note:</b>
     * <p>
     *    Want to learn more about Error codes goto the src/Helpers/ErrorMessages.
     *    This file include all the error codes you can include this class into your Controller, Models or anywhere you want to show code instead the length Error Message.
     *    You can also create your own Message.
     * </p>
     * 
     * @param mixed $data This data must contain Email, Password and Full Name of the user
     * @return array|void
     */
    public static function create($data) {
        $conn = self::getConnection();
        $checkUserExistence = self::isExists($data['email']);
        $otpCode = self::otpCodeGenerator();

        if(!$checkUserExistence){
            $hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, email, password, verify_code, verify_code_expiry) VALUES (:name, :email, :password, :otp, :expiry)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':otp', $otpCode['code']);
            $stmt->bindParam(':expiry', $otpCode['expiry']);
            $response = $stmt->execute();

            // Sent Verification Email
            $EmailResponse = self::sendVerificationCode($data["email"], $otpCode);

            if($response && is_bool($EmailResponse) && $EmailResponse){
                if(self::redirectToVerifyEmailPage($data['email'])){
                    return [
                        "code" => "007", 
                        "type" => "success",
                        "func" => "Users",
                        "data" => [
                            "redirectionTo" => "/verify-email"
                        ]
                    ];
                } else{
                    return [
                        "code" => "010",
                        "type" => "error",
                        "func" => "Users"
                    ];
                }
            } else{
                if(!$response){
                    return [
                        "code" => "001",
                        "type" => "error",
                        "func" => "Users"
                    ];
                } else if(!$EmailResponse){
                    self::delete($data['email']);
                    return [
                        "code" => "002",
                        "type" => "error",
                        "func" => "Users"
                    ];
                } else{
                    return [
                        "code" => "001",
                        "type" => "error",
                        "func" => "Universal"
                    ];
                }
            }
        } else{
            return [
                "code" => "003",
                "type" => "error",
                "func" => "Users"
            ];
        }
    }

    /**
     * This function check that Email or Password are exists in or not if exists then check Email is verified or not, if not then it sends and verification Email to the user to Verify their Email by OTP
     * @param mixed $data Pass Email and Password in associative array format
     * @return array|void return an object that contanis the response code, type, callback function, additional data(optional)
     */
    public static function read($data){
        $conn = self::getConnection();
        $getEmail = $data["email"];
        $getPassword = $data["password"];
        
        $query = "SELECT password, id FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $getEmail);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data){
            $checkEmailIsVerified = self::isVerified($getEmail);

            $passwordHash = $data['password'];
            $userId = $data['id'];
            if(password_verify($getPassword, $passwordHash)){

                if(!$checkEmailIsVerified){
                    $otpCode = self::otpCodeGenerator();
                    $EmailResponse = self::sendVerificationCode($getEmail, $otpCode, true);

                    if($EmailResponse){
                        if(self::redirectToVerifyEmailPage($getEmail)){
                            return [
                                "code" => "006", 
                                "type" => "success",
                                "func" => "Users",
                                "data" => [
                                    "redirectionTo" => "/dashboard/upload"
                                ]
                            ];
                        } else{
                            return [
                                "code" => "010",
                                "type" => "error",
                                "func" => "Users",
                                "data" => [
                                    "redirectionTo" => "/dashboard/upload"
                                ]
                            ];
                        }
                    } else{
                        return [
                            "code" => "002",
                            "type" => "error",
                            "func" => "Users"
                        ];
                    }
                } else{
                    $AuthService = new AuthService("/");

                    $jwt = $AuthService->generateJWT(
                        $userId, 
                        $getEmail,
                        "user"
                    );

                    if($jwt){                        
                        return [
                            "code" => "002",
                            "type" => "success",
                            "func" => "Users",
                            "data" => [
                                "redirectionTo" => "/dashboard/upload"
                            ]
                        ];
                    } else{
                        return [
                            "code" => "010",
                            "type" => "error",
                            "func" => "Users"
                        ];
                    }
                }

            } else{
                return [
                    "code" => "004",
                    "type" => "error",
                    "func" => "Users"
                ];
            }   
        } else{
            return [
                "code" => "005",
                "type" => "error",
                "func" => "Users"
            ];
        }
    }

    private static function updateVerified($email){
        $conn = self::getConnection();
        
        $query = "UPDATE users SET is_verified=true WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    public static function checkOTP($data){
        $conn = self::getConnection();
        $getEmail = $data["email"];
        $getOTP = $data["otp"];

        $query = "SELECT verify_code, verify_code_expiry FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":email", $getEmail);
        $stmt->execute();

        $fetchData = $stmt->fetch(PDO::FETCH_ASSOC);
        if($fetchData){
            $savedCode = $fetchData["verify_code"];
            $codeExpiry = $fetchData["verify_code_expiry"];
            $currentTime = time();

            if($codeExpiry > $currentTime){
                if($savedCode === $getOTP){
                    if(self::updateVerified($getEmail)){
                        return [
                            "code" => "003",
                            "type" => "success",
                            "func" => "Users",
                            "data" => [
                                "redirectionTo" => "/login"
                            ]
                        ];
                    } else{
                        return [
                            "code" => "009",
                            "type" => "error",
                            "func" => "Users"
                        ];
                    }
                } else{
                    return [
                        "code" => "006",
                        "type" => "error",
                        "func" => "Users"
                    ];
                }
            } else{
                return [
                    "code" => "008",
                    "type" => "error",
                    "func" => "Users"
                ];
            }
        } else{
            return [
                "code" => "001",
                "type" => "error",
                "func" => "Universal"
            ];
        }
    }

    public static function resendEmail($email){
        $conn = self::getConnection();
        $otpCode = self::otpCodeGenerator();
        $query = "UPDATE users SET verify_code=:code, verify_code_expiry=:expiry WHERE email=:email";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":code", $otpCode['code']);
        $stmt->bindParam(":expiry", $otpCode['expiry']);
        $response = $stmt->execute();

        $EmailResponse = self::sendVerificationCode($email, $otpCode);

        if($response && is_bool($EmailResponse) && $EmailResponse){
            return [
                "code" => "006", 
                "type" => "success",
                "func" => "Users"
            ];
        } else{
            return [
                "code" => "002",
                "type" => "error",
                "func" => "Users"
            ];
        }
    }

    /**
     * Delete user from database using E-mail id
     * @param mixed $email User E-mail id
     * @return array return the default associative response array
     */
    public static function delete($email) {
        // $conn = self::getConnection();
        // $stmt = $conn->prepare("DELETE FROM users WHERE email = :email");
        // $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        // return $stmt->execute();
        return [
            "code" => "001",
            "type" => "erro",
            "func" => "Universal"
        ];
    }
}
