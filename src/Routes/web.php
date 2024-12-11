<?php

use App\Controllers\WallpaperController;
use App\Helpers\Sanitizer;
use App\Helpers\Validate;
use App\Services\AuthService;
use App\Services\SessionManagement;

// Use of Global Variables from Init.php
global $twig, $icons, $images;


function addCSRFToken($twig): void{
    // CSRF Token
    $CSRF = new SessionManagement();
    // Generate and add token to session
    $CSRF->AddCSRF();  // Store the token in the session
    
    // Get the token value from the session to use in the meta tag
    $tokenValue = $CSRF->GetTokenValue();
    $twig->addGlobal("csrfToken", $tokenValue);  // Set csrfToken in header
}

// Functions for 404 Page 
// Why Function? For more customization and usability
function notFoundPage($twig){
    header("HTTP/1.0 404 Not Found");
    echo $twig->render("404.twig");
    exit();
}

// Function for redirection
// Why this Function? 
// First check if this user already looged in or not, For redirect user to the homepage if user already logged in
function checkUserSession($ifNotThenPath = null, $ifYesThenPath = null, $redirection = true){
    $AuthService = new AuthService("/");
    $SessionId = $AuthService->verifyJWT("user");
    if($SessionId){
        if($redirection){
            if($ifYesThenPath !== null){
                header("Location: $ifYesThenPath");
            } else{
                header("Location: /");
            }
            exit();
        } else{
            return $SessionId;
        }
    } else if($ifNotThenPath !== null){
        header("Location: $ifNotThenPath");
        exit();
    }
}


// Define Routes
// Home
$router->map('GET', '/', function() use ($twig, $images) {
    addCSRFToken($twig);

    $wallpaperController = new WallpaperController(false);
    $recentWallpaper = $wallpaperController->getRecentWallpaper(0, 20);

    // Get Category Content
    $category = [];
    $categoryPath = __DIR__ . '/../../public/assets/json/categories.json';
    if(file_exists($categoryPath)){ 
        $getContent = file_get_contents($categoryPath);
        $category = json_decode($getContent, true);
    }

    $templateData = [
        "tags" => [
            [
                "url"=> "/search?q=girl",
                "text" => "girl"
            ],
            [
                "url"=> "/search?q=flower",
                "text" => "flower"
            ],
            [
                "url"=> "/search?q=bird",
                "text" => "bird"
            ],
            [
                "url"=> "/search?q=danger",
                "text" => "danger"
            ],
            [
                "url"=> "/search?q=car",
                "text" => "car"
            ],
            [
                "url"=> "/search?q=anime",
                "text" => "anime"
            ],
            [
                "url" => "/search?q=boy",
                "text" => "boy"
            ]
        ],
        "categories" => $category,
        "wallpapers" => $recentWallpaper,
        "wallpaperCardPixel" => "500",
        "homeHeader" => "true",
        "wallpaper" => [
            "title" => "Android, iPhone and 4k Desktop Wallpapers",
            "description" => "Explore a stunning collection of high-quality wallpapers for PC, desktop, Android, and iPhone. Download and share your favorite wallpapers with ease!",
            "imgUrl" => "https://gloztik.com/public/assets/img/images/desktop-wallpapers.jpg",
            "url" => "https://gloztik.com"
        ]
    ];

    echo $twig->render('home.twig', $templateData);
});

// Category Routes
$router->map('GET', '/category/[**:category]?', function ($category = null) use ($twig){
    addCSRFToken($twig);
    if(!empty($category) || $category == null){
        $categoryData = [];
        $categoryPath = __DIR__ . '/../../public/assets/json/categories.json';
        if(file_exists($categoryPath)){ 
            $getContent = file_get_contents($categoryPath);
            $categoryData = json_decode($getContent, true);
        } else{
            notFoundPage($twig);
        }

        if(key_exists($category, $categoryData)){
            $query = $categoryData[$category]['query'];
            $wallpaperController = new WallpaperController(false);
            $searchedWallpapers = $wallpaperController->getRelatedWallpaper($query, 1, 25);
            
            $templateData = [
                "wallpapers" => "",
                "query" => $query,
                "wallpaperCardPixel" => "500",
                "endpoint" => "search",
                "wallpaper" => [
                    "title" => "$category | Search Wallpapers",
                    "description" => "Explore a stunning collection of $category high-quality wallpapers for PC, desktop, Android, and iPhone.",
                    "imgUrl" => "https://gloztik.com/public/assets/img/images/desktop-wallpapers.jpg",
                    "url" => "https://gloztik.com/category/$category"
                ]
            ];

            if(empty($searchedWallpapers)){
                $templateData["wallpapers"] = [];
            } else{
                $templateData["wallpapers"] = $searchedWallpapers;
            }

            echo $twig->render("search.twig", $templateData);
        } else{
            notFoundPage($twig);
        }
    } else{
        header("Location: /");
        exit();
    }
});

// Load PWA Assets for PWA support and favicon also
$router->map('GET', '/pwa/[**:pwa]?', function($pwa = null) use ($twig) {
    if(!empty($pwa)){
        $imageDirectory = __DIR__ . '/../../pwa/';

        $imagePath = $imageDirectory . $pwa;

        if($pwa && file_exists($imagePath)){
            $MimeType = mime_content_type($imagePath);

            header('Content-Type: ' . $MimeType);
            header('Content-Length: ' . filesize($imagePath));

            // output image content
            readfile($imagePath);
        } else{
            notFoundPage($twig);
        }
    } else{
        notFoundPage($twig);
    }
});

// Wallpapers Route
$router->map('GET', '/wallpaper/[**:pageName]?', function($pageName = null) use ($twig, $images) {
    addCSRFToken($twig);

    if($pageName != null){
        $pageNumber = isset($_GET["page"]) ? $_GET["page"] : false;
        $pageNumber = is_numeric($pageNumber) ? $pageNumber : 1;

        $wallpaperController = new WallpaperController(false);
        $wallpaperData = $wallpaperController->getSpecificWallpaper($pageName);
        $query = $wallpaperData['title'];
        $range = 30;

        $relatedWallpapers = $wallpaperController->getRelatedWallpaper($query, $pageNumber, $range);
        $totalWallpapers = $wallpaperController->getRelatedWallpaperCount($query);

        if(empty($wallpaperData)){
            notFoundPage($twig);
        }

        // Calculate Pagination
        $currentPage = $nextPage = $previousPage = 0;
        $totalPages = floor($totalWallpapers/$range);
        $extraPage = $totalWallpapers - ($totalPages * $range);

        $lastPage = $extraPage == 0 ? $totalPages : $totalPages + 1;

        // Next Page Number
        if($pageNumber >= $lastPage){
            $nextPage = -1;
        } else{
            $nextPage = $pageNumber + 1;
        }

        // Previous Page Number
        if($pageNumber <= 1){
            $previousPage = -1;
        } else{
            $previousPage = $pageNumber - 1;
        }

        $templateData = [
            "wallpapers" => $relatedWallpapers,
            "wallpaper" => $wallpaperData,
            "wallpaperCardPixel" => "500",
            "currentPage" => $pageNumber,
            "nextPage" => $nextPage,
            "previousPage" => $previousPage,
            "baseUrl" => "/wallpaper/$pageName"
        ];


        echo $twig->render('wallpaperPreview.twig', $templateData);
    } else{
        notFoundPage($twig);
    }
});

// Search Page
$router->map('GET', '/search', function() use ($twig, $images){
    addCSRFToken($twig);
    
    if(isset($_GET['q']) && !empty($_GET['q'])){
        $query = $_GET['q'];

        $query = Sanitizer::sanitizeString($query);
        if(Validate::validateSearchString($query)){
            $wallpaperController = new WallpaperController(false);
            $searchedWallpapers = $wallpaperController->getRelatedWallpaper($query, 1, 25);
            
            $templateData = [
                "wallpapers" => "",
                "query" => $query,
                "wallpaperCardPixel" => "500",
                "endpoint" => "search",
                "wallpaper" => [
                    "title" => "$query | Search Wallpapers",
                    "description" => "Explore a stunning collection of $query high-quality wallpapers for PC, desktop, Android, and iPhone.",
                    "imgUrl" => "https://gloztik.com/public/assets/img/images/desktop-wallpapers.jpg",
                    "url" => "https://gloztik.com/search?q=$query"
                ]
            ];

            if(empty($searchedWallpapers)){
                $templateData["wallpapers"] = [];
            } else{
                $templateData["wallpapers"] = $searchedWallpapers;
            }

            echo $twig->render("search.twig", $templateData);
        } else{
            header("Location: /");
        }
    } else{
        header("Location: /");
    }
});

// Android, iPhone and Desktop Wallpapers Route
$router->map('GET', '/android-wallpapers', function () use ($twig) {
    addCSRFToken($twig);
    
    $wallpaperController = new WallpaperController(false);
    $searchedWallpapers = $wallpaperController->getMobileWallpaper(1, 25);
    
    $templateData = [
        "wallpapers" => "",
        "query" => "Android Wallpapers",
        "wallpaperCardPixel" => "500",
        "endpoint" => "mobWallpaper",
        "wallpaper" => [
            "title" => "Android Wallpapers | Search Wallpapers",
            "description" => "Explore a stunning collection of android high-quality wallpapers for PC, desktop, Android, and iPhone.",
            "imgUrl" => "https://gloztik.com/public/assets/img/images/mobile-wallpapers.jpg",
            "url" => "https://gloztik.com/android-wallpapers"
        ]
    ];

    if(empty($searchedWallpapers)){
        $templateData["wallpapers"] = [];
    } else{
        $templateData["wallpapers"] = $searchedWallpapers;
    }

    echo $twig->render("search.twig", $templateData);
});

$router->map('GET', '/iphone-wallpapers', function () use ($twig) {
    addCSRFToken($twig);
    
    $wallpaperController = new WallpaperController(false);
    $searchedWallpapers = $wallpaperController->getMobileWallpaper(1, 25);
    
    $templateData = [
        "wallpapers" => "",
        "query" => "iPhone Wallpapers",
        "wallpaperCardPixel" => "500",
        "endpoint" => "mobWallpaper",
        "wallpaper" => [
            "title" => "iPhone Wallpapers | Search Wallpapers",
            "description" => "Explore a stunning collection of iphone high-quality wallpapers for PC, desktop, Android, and iPhone.",
            "imgUrl" => "https://gloztik.com/public/assets/img/images/mobile-wallpapers.jpg",
            "url" => "https://gloztik.com/iphone-wallpapers"
        ]
    ];

    if(empty($searchedWallpapers)){
        $templateData["wallpapers"] = [];
    } else{
        $templateData["wallpapers"] = $searchedWallpapers;
    }

    echo $twig->render("search.twig", $templateData);
});

$router->map('GET', '/desktop-wallpapers', function() use ($twig){
    addCSRFToken($twig);
    
    $wallpaperController = new WallpaperController(false);
    $searchedWallpapers = $wallpaperController->getDesktopWallpaper(1, range: 50);
    
    $templateData = [
        "wallpapers" => "",
        "query" => "Desktop Wallpapers",
        "wallpaperCardPixel" => "500",
        "endpoint" => "desktopWallpaper",
        "nextPage" => 3,
        "maxWidth" => 500,
        "wallpaper" => [
            "title" => "4k Desktop Wallpapers | Search Wallpapers",
            "description" => "Explore a stunning collection of iphone high-quality wallpapers for PC, desktop, Android, and iPhone.",
            "imgUrl" => "https://gloztik.com/public/assets/img/images/desktop-wallpapers.jpg",
            "url" => "https://gloztik.com/desktop-wallpapers"
        ]
    ];

    if(empty($searchedWallpapers)){
        $templateData["wallpapers"] = [];
    } else{
        $templateData["wallpapers"] = $searchedWallpapers;
    }

    echo $twig->render("search.twig", $templateData);
});

// Dashboard Routes
$router->map('GET', '/login', function() use ($twig, $images) {
    checkUserSession();
    addCSRFToken($twig);
    echo $twig->render("./dashboard/login.twig");
});

$router->map("GET", "/logout", function() {
    $AuthService = new AuthService("/");
    if($AuthService->destroyJWT("user")){
        header("Location: /");
        exit();
    }
});

$router->map('GET', '/dashboard/[**:pageName]?', function($pageName = null) use ($twig, $images) {
    // Check user session is still active
    checkUserSession("/login", null, false);
    
    addCSRFToken($twig);

    if($pageName == null){
        notFoundPage($twig);
    } else{
        $templateData = [];

        if($pageName == "upload"){
            $templateData["dashboardTitle"] = "Upload Wallpapers";
            echo $twig->render("./dashboard/upload.twig", $templateData);
        } else if($pageName == "overview"){
            $templateData["dashboardTitle"] = "Overview";
            echo $twig->render("./dashboard/overview.twig", $templateData);
        }
    }
});

// Move Old URL to New URL Permanently
$router->map('GET', '/page/mobile-wallpaper', function(){
    header("Location: /android-wallpapers", true, 301);
    exit();
});
$router->map('GET', '/page/desktop-wallpaper', function(){
    header("Location: /desktop", true, 301);
    exit();
});


// Additional Pages
$router->map('GET', '/page/[**:pageName]?', function($pageName = null) use ($twig){
    addCSRFToken($twig);

    if($pageName == null){
        notFoundPage($twig);
    } else{
        $templateData = [
            "wallpaper" => [
                "title" => $pageName,
                "description" => "Discover more about $pageName — your go-to destination for high-quality, personal-use wallpapers for PC, desktop, Android, and iPhone. Learn about our mission, values, and the inspiration behind our collection.",
                "imgUrl" => "https://gloztik.com/public/assets/img/images/mobile-wallpapers.jpg",
                "url" => "https://gloztik.com/page/$pageName"
            ]
        ];

        if($pageName == "about"){
            echo $twig->render("./info/about.twig");
        } else if($pageName == "contact"){
            echo $twig->render("./info/contact.twig");
        } else if($pageName == "terms-of-use"){
            echo $twig->render("./info/terms-of-use.twig");
        } else if($pageName == "privacy-policy"){
            echo $twig->render("./info/privacy-policy.twig");
        } else {
            notFoundPage($twig);
        }
    }
});

// other important routes
$router->map('GET', '/sitemap.xml', function(){
    // output file
    readfile(__DIR__ . '/../../sitemap.xml');
});
$router->map('GET', '/ads.txt', function(){
    // output file
    readfile(__DIR__ . '/../../ads.txt');
});
$router->map('GET', '/bc648d883815416b882d0bb5a9cecf76.txt', function(){
    // output file
    readfile(__DIR__ . '/../../bc648d883815416b882d0bb5a9cecf76.txt');
});
$router->map('GET', '/yandex_e72c28597d24ab3c.html', function(){
    // output file
    readfile(__DIR__ . '/../../yandex_e72c28597d24ab3c.html');
});
$router->map('GET', '/robots.txt', function(){
    // output file
    readfile(__DIR__ . '/../../robots.txt');
});