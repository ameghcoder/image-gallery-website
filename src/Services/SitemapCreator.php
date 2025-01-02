<?php

namespace App\Services;

use App\config\Database;
use App\Helpers\JsonResponse;
use Exception;
use PDO;

class SitemapCreator{
    private $pageSitemapCode, $sitemapBaseCode, $homeAndCategoryCode, $baseCode;

    public function __construct() {
        $this->pageSitemapCode = __DIR__ . "/../Views/sitemap/WallpaperPageSitemapCode.html";
        $this->sitemapBaseCode = __DIR__ . "/../Views/sitemap/BaseTemplate.html";
        $this->homeAndCategoryCode = __DIR__ . "/../Views/sitemap/HomeAndCategorLinks.html";

        if(
            !file_exists($this->pageSitemapCode) ||
            !file_exists($this->sitemapBaseCode) ||
            !file_exists($this->homeAndCategoryCode)
        ){
            throw new Exception("Some sitemap code files not found");
        }

        $this->baseCode = file_get_contents($this->sitemapBaseCode);
        $this->homeAndCategoryCode = file_get_contents($this->homeAndCategoryCode);
        $this->pageSitemapCode = file_get_contents($this->pageSitemapCode);

    }

    public function generateSitemap(){
        $data = self::generateSitemapCodeForWallpaperPages();

        if(!$data){
            throw new Exception("No Wallpaper Found");
        }

        // now set data in the page sitemap code
        $pageFinalSitemapCode = "";

        foreach ($data as $wallData) {
            $fileName = $wallData['filename'];
            $date = explode(" ", $wallData['date'])[0];

            // wallpaper url
            $imgUrl = "https://gloztik.com/public/image/jpeg/" . $fileName . ".jpeg";
            $pageUrl = "https://gloztik.com/wallpaper/" . $fileName;

            $tempData = "";
            $tempData = str_replace(
                [
                    "{URL}",
                    "{DATE}",
                    "{IMGURL}"
                ],
                [
                    $pageUrl,
                    $date,
                    $imgUrl
                ],
                $this->pageSitemapCode
            );

            $pageFinalSitemapCode .= $tempData;
        }

        $finalSitemapData = $this->homeAndCategoryCode . $pageFinalSitemapCode;

        $finalSitemapData = str_replace(
            "{CONTENT}",
            $finalSitemapData,
            $this->baseCode
        );

        // Save to sitemap.xml file
        $filePath = __DIR__ . "/../../sitemap.xml"; // Save sitemap.xml in the root directory
        if(file_put_contents($filePath, $finalSitemapData)){
            return "Sitemap has been generated and saved as sitemap.xml";
        } else{
            return false;   
        }
    }    

    private function generateSitemapCodeForWallpaperPages(){
        $conn = Database::connect();

        $query = "SELECT filename, updated_at AS date FROM wallpapers";
        $stmt = $conn->prepare($query);

        if($stmt->execute()){
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $data;
        } else{ 
            return false;
        }
    }

}