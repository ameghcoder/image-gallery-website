<?php

namespace App\Controllers;

use App\Models\Wallpaper;
use App\Helpers\ErrorMessages;
use App\Helpers\JsonResponse;
use App\Helpers\Validate;
use App\Helpers\Sanitizer;
use App\Services\AuthService;
use App\Services\SessionManagement;
use Respect\Validation\Rules\Json;

class WallpaperController{
    public function __construct($check = true) {
        $check 
        ? SessionManagement::VerifyToken("csrfToken")
        : true;
    }
    
    private function checkUserSession(){
        $AuthService = new AuthService("/");    
        $UserId = $AuthService->verifyJWT("user");
        if($UserId){
            return $UserId;
        } else{
            return false;
        }
    }

    public function getSpecificWallpaper($wallpaperName = null){
        // Manage Session, Verify Request Parameters using POST
        // PASS 

        if($wallpaperName){
            // Validate and Sanitize Data
            // PASS
            
            return Wallpaper::getSingleWallpaper($wallpaperName);
        } else{
            return [];
        }
    }

    public function getRecentWallpaper($from, $limit){
        return Wallpaper::getRangeWallpaper($from, $limit);
    }

    public function getRelatedWallpaper($search, $page, $range = 30){
        // Manage Session, Verify Request Parameters using POST
        // PASS
        $from = (($page * $range) - $range);
        return Wallpaper::getRelatedRangeWallpaper($search, $from , $range);
    }

    public function getRelatedWallpaperCount($search){
        // Manage Session, Verify Request Parameters using POST
        // PASS
        
        return Wallpaper::getRelatedRangeWallpaper($search, 0 , 0, true);
    }

    public function oneRandomWallpaper() {
        if(!isset($_GET['device']) && empty($_GET['device'])){
            print_r(Wallpaper::oneRandomWallpaper());
        } else if(
            $_GET['device'] == 'a' || 
            $_GET['device'] == 'c'
        ){
            print_r(Wallpaper::oneRandomWallpaper($_GET['device']));
        } else{
            JsonResponse::send(
                "Invalid Request",
                "error"
            );
        }
    }

    public function getMobileWallpaper($page, $range){
        $from = (($page * $range) - $range);

        return Wallpaper::getByDeviceWallpapers("a", $from, $range);
    }

    public function getDesktopWallpaper($page, $range){
        $from = (($page * $range) - $range);
        return Wallpaper::getByDeviceWallpapers("c", $from, $range);
    }

    public function uploadWallpaper(){
        // If user want to update their information then first check it is logged in or not then extract the user from the jwt token
        // $getSessionUserId = self::checkUserSession();
        // if(!$getSessionUserId){
        //     JsonResponse::send(
        //         "Unauthoriazed Access, You'll be logged out in 3 seconds",
        //         "error",
        //         [
        //             "redirectionTo" => "/logout"
        //         ]
        //     );
        // }


        if(
            !isset($_POST['title']) &&
            !isset($_POST['description']) &&
            $_FILES['wallpaper']['name'] != null &&
            $_FILES['wallpaper']['name'] != ""
        ){
            JsonResponse::send(
                "Data is empty, pass valid data for upload the wallpaper"
            );
        }

        // Sanitize data and then validate
        $data = [
            "title" => Sanitizer::sanitizeTitle($_POST["title"]),
            "description" => Sanitizer::sanitizeDescription($_POST['description']),
            "wallpaperName" => $_FILES['wallpaper'],
            "id" => 1
        ];

        if(
            Validate::validateTitle($data['title']) &&
            Validate::validateDescription($data['description'])
        ){
            $response = Wallpaper::save($data);
            // print_r($data);

            ErrorMessages::HandleResponse($response);
        } else{
            $message = "";
            switch(null|true){
                case $data['title']: 
                    $message = "Invalid title";
                    break;
                case $data['description']:
                    $message = "Invalid description";
                    break;

                default:
                    $message = "Something went wrong, try again";
                    break;
            }

            JsonResponse::send(
                $message,
                "error"
            );
        }
    }
}