<?php

namespace App\Models;

class Assets{
    public static function Icons(){
        // remember only absolute path works
        $baseUrl = "/public/assets/img/icons";
        return [
            "404" => [
                "src" => "$baseUrl/404-illustration.svg",
                "alt" => "404 illustration"
            ],
            "logoWithoutText" => [
                "src" => "$baseUrl/logo.svg",
                "alt" => "Website official logo without text"
            ],
            "logoWithText" => [
                "notAvailable"
            ],
            "add" => [
                "src" => "$baseUrl/add.svg",
                "alt" => "add icon"
            ],
            "close" => [
                "src" => "$baseUrl/close.svg",
                "alt" => "close icon"
            ],
            "delete" => [
                "src" => "$baseUrl/delete.svg",
                "alt" => "delete icon"
            ],
            "download" => [
                "src" => "$baseUrl/download.svg",
                "alt" => "download icon"
            ],
            "fullscreen" => [
                "src" => "$baseUrl/fullscreen.svg",
                "alt" => "fullscreen icon"
            ],
            "chevron" => [
                "src" => "$baseUrl/chevron-right.svg",
                "alt" => "chevron icon"
            ],
            "hamburger" => [
                "src" => "$baseUrl/hamburger.svg",
                "alt" => "hamburger icon"
            ],
            "home" => [
                "src" => "$baseUrl/home.svg",
                "alt" => "home icon"
            ],
            "login" => [
                "src" => "$baseUrl/login.svg",
                "alt" => "login icon"
            ],
            "logout" => [
                "src" => "$baseUrl/logout.svg",
                "alt" => "logout icon"
            ],
            "search" => [
                "src" => "$baseUrl/search.svg",
                "alt" => "search icon"
            ],
            "shareReview" => [
                "src" => "$baseUrl/share-review.svg",
                "alt" => "share review icon"
            ],
            "share" => [
                "src" => "$baseUrl/share.svg",
                "alt" => "share icon"
            ],
            "upload" => [
                "src" => "$baseUrl/upload.svg",
                "alt" => "upload icon"
            ],
            "view" => [
                "src" => "$baseUrl/view.svg",
                "alt" => "view icon"
            ],
            "instagram" => [
                "src" => "$baseUrl/Instagram.svg",
                "alt" => "instagram icon"
            ],
            "pinterest" => [
                "src" => "$baseUrl/Pinterest.svg",
                "alt" => "pinterest icon"
            ],
            "tumblr" => [
                "src" => "$baseUrl/Tumblr.svg",
                "alt" => "tumblr icon"
            ],
            "threads" => [
                "src" => "$baseUrl/Threads.svg",
                "alt" => "threads icon"
            ],
            "email" => [
                "src" => "$baseUrl/email.svg",
                "alt" => "email icon"
            ]
           
        ];
    }   
    public static function Images(){
        // remember only absolute path works
        $baseUrl = "/public/assets/img/images";
        return [
           "hero-section-bg" => [
                "src" => "$baseUrl/hero-section-bg.webp",
                "alt" => "hero section background"
           ],
           "category-skeleton" => [
                "src" => "$baseUrl/category-skeleton.webp",
                "alt" => "category skeleton "
           ],
           "newsletter-background" => [
                "src" => "$baseUrl/newletter-background.jpg",
                "alt" => "news letter background"
           ],
           "transparent-avatar-girl" => [
                "src" => "$baseUrl/bg-transparent-avatar.webp",
                "alt" => "Transparent Girl Avatar"
           ]
        ];
        
    }
}