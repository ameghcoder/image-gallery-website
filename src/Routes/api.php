<?php


// Authentication APIs

use App\Controllers\WallpaperController;
use App\Helpers\JsonResponse;
use App\Helpers\Sanitizer;
use App\Helpers\Validate;
use App\Services\AuthService;
use App\Services\SessionManagement;
use App\Services\SitemapCreator;

global $twig, $icons, $images;

$router->map('POST', '/api/signin', 'UserController@enter');        // List all users
$router->map('POST', '/api/contact', 'ContactController@save');

// Suffle wallpaper
$router->map('GET', '/api/bgsuffle', 'WallpaperController@oneRandomWallpaper' );

// Search Wallpaper By Page
$router->map('POST', '/api/search', function() use ($twig) {
    if(
        isset($_POST['query']) && 
        isset($_POST['page']) && 
        isset($_POST['range']) &&
        !empty($_POST['query']) &&
        !empty($_POST['page']) &&
        !empty($_POST['range'])
    ){
        $data = [
            "query" => Sanitizer::sanitizeString($_POST['query']),
            "page" => Sanitizer::sanitizeNumber($_POST['page']),
            "range" => Sanitizer::sanitizeNumber($_POST['range'])
        ];

        if(
            Validate::validateSearchString($data['query']) && 
            Validate::validateNumber($data['page']) &&
            Validate::validateNumber($data['range'])
        ){
            $wallpaperController = new WallpaperController();
            $searchedWallpapers = $wallpaperController->getRelatedWallpaper($data['query'], $data['page'], $data['range']);

            $templateData = [
                "wallpapers" => "",
                "wallpaperCardPixel" => "500",
                "lazyload" => true
            ];

            if(empty($searchedWallpapers)){
                echo "end";
                exit();
            } else{
                $templateData["wallpapers"] = $searchedWallpapers;
            }

            $html = $twig->render("./ui/WallpaperCard.twig", $templateData);

            echo $html;
        } else{
            JsonResponse::send(
                "Invalid Values",
                "error"
            );
        }

    } else{
        JsonResponse::send(
            "Invalid or Empty values",
            "error"
        );
    }
}); 

// Api for mobile wallpapers
$router->map("POST", '/api/mobWallpaper', function() use ($twig){
    if(
        isset($_POST['page']) && 
        isset($_POST['range']) &&
        !empty($_POST['page']) &&
        !empty($_POST['range'])
    ){
        $data = [
            "page" => Sanitizer::sanitizeNumber($_POST['page']),
            "range" => Sanitizer::sanitizeNumber($_POST['range'])
        ];

        if(
            Validate::validateNumber($data['page']) &&
            Validate::validateNumber($data['range'])
        ){
            $wallpaperController = new WallpaperController();
            $searchedWallpapers = $wallpaperController->getMobileWallpaper($data['page'], $data['range']);

            $templateData = [
                "wallpapers" => "",
                "wallpaperCardPixel" => "500",
                "lazyload" => true
            ];

            if(empty($searchedWallpapers)){
                echo "end";
                exit();
            } else{
                $templateData["wallpapers"] = $searchedWallpapers;
            }

            $html = $twig->render("./ui/WallpaperCard.twig", $templateData);

            echo $html;
        } else{
            JsonResponse::send(
                "Invalid Values",
                "error"
            );
        }

    } else{
        JsonResponse::send(
            "Invalid or Empty values",
            "error"
        );
    }
});

// Api for desktop wallpapers
$router->map("POST", '/api/desktopWallpaper', function() use ($twig){
    if(
        isset($_POST['page']) && 
        isset($_POST['range']) &&
        !empty($_POST['page']) &&
        !empty($_POST['range'])
    ){
        $data = [
            "page" => Sanitizer::sanitizeNumber($_POST['page']),
            "range" => Sanitizer::sanitizeNumber($_POST['range'])
        ];

        if(
            Validate::validateNumber($data['page']) &&
            Validate::validateNumber($data['range'])
        ){
            $wallpaperController = new WallpaperController();
            $searchedWallpapers = $wallpaperController->getDesktopWallpaper($data['page'], $data['range']);

            $templateData = [
                "wallpapers" => "",
                "wallpaperCardPixel" => "500",
                "lazyload" => true
            ];

            if(empty($searchedWallpapers)){
                echo "end";
                exit();
            } else{
                $templateData["wallpapers"] = $searchedWallpapers;
            }

            $html = $twig->render("./ui/WallpaperCard.twig", $templateData);

            echo $html;
        } else{
            JsonResponse::send(
                "Invalid Values",
                "error"
            );
        }

    } else{
        JsonResponse::send(
            "Invalid or Empty values",
            "error"
        );
    }
});

// Api for uploading wallpapers
$router->map('POST', '/api/upload', "WallpaperController@uploadWallpaper");

// Api for update statis
$router->map('POST', '/api/updateStatis', function() {
    if(
        isset($_POST['type']) &&
        isset($_POST['id']) &&
        !empty($_POST['type']) &&
        !empty($_POST['id'])
    ){
        $data = [
            "type" => Sanitizer::sanitizeString($_POST['type']),
            "id" => Sanitizer::sanitizeNumber($_POST['id'])
        ];

        if(
            Validate::validateNumber($data['id']) &&
            Validate::validateString($data['type'])
        ){
            $wallpaperController = new WallpaperController();
            $wallpaperController->updateStatis($data['type'], $data['id']);
        } else{
            echo "Invalid";
        }
        
    } else{
        echo "Error";
    }
});

// Api for generate sitemap
$router->map('POST', '/api/sitemap', function() {

    try{
        $AuthService = new AuthService("/");    
        $UserId = $AuthService->verifyJWT("user");
        if($UserId){
            $sitemapCreator = new SitemapCreator();
            $resp = $sitemapCreator->generateSitemap();

            if($resp){
                JsonResponse::send(
                    $resp,
                    "success"  
                );
            } else{
                throw new Exception("Failed to save the sitemap file");
            }

        } else{
            JsonResponse::send(
                "Unauthoriazed Access, You'll be logged out in 3 seconds",
                "error",
                [
                    "redirectionTo" => "/logout"
                ]
            );
        }
    } catch(Exception $e){
        JsonResponse::send(
            "An error occurred",
            "error",
            [
                "message" => $e->getMessage()
            ]
        );
    }

});