<?php

require_once __DIR__ . '/../config/LoadEnv.php';

if($_ENV["APP_PHASE"] === "development"){
    // Display all errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

use App\config\Database;
use App\Models\Assets;

$icons = Assets::Icons();
$images = Assets::Images();

// Define the path of Twig Template
$templateLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../Views');
$twig = new \Twig\Environment($templateLoader);

$navigationData = [];

// Add some more global variable values like static icons and image
$navigationData["logoWithoutText"] = $icons['logoWithoutText']['src'];
$navigationData["hamburger"] = $icons['hamburger']['src'];
$navigationData["search"] = $icons['search']['src'];
$navigationData["home"] = $icons['home']['src'];
$navigationData["upload"] = $icons['upload']['src'];
$navigationData["email"] = $icons['email']['src'];
$navigationData["download"] = $icons['download']['src'];
$navigationData["share"] = $icons['share']['src'];

// Social Media Icons
$navigationData["instagramSvg"] = $icons['instagram']['src'];
$navigationData["pinterestSvg"] = $icons['pinterest']['src'];
$navigationData["threadsSvg"] = $icons['threads']['src'];
$navigationData["tumblrSvg"] = $icons['tumblr']['src'];
$navigationData["instagramLink"] = "https://instagram.com/gloztik_";
$navigationData["tumblrLink"] = "https://www.tumblr.com/gloztik";
$navigationData["threadsLink"] = "https://www.threads.net/@gloztik_";

// Set Navigation links as a global variable in Twig
// Add each global variable from the array
foreach($navigationData as $key => $value){
    $twig->addGlobal($key, $value);
}

// init db
function getDbConnection(){
    return Database::connect();
}

// Make some variable global
global $twig, $icons, $images;