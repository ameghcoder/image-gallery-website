<?php

namespace App\Helpers;

use App\Helpers\JsonResponse;
class ImageUploader{
    private $THUMB, $SMALL, $MEDIUM, $LARGE, $JPEG, $WEBP, $SLUG, $EXTENSION, $ORIGINALWidth, $ORIGINALHeight, $TEMPNAME;

    public function __construct($slug, $tempName, $imageExtension, $originalWidth, $originalHeight) {
        $this->ORIGINALWidth = $originalWidth;
        $this->ORIGINALHeight = $originalHeight;
        $this->SLUG = $slug;
        $this->TEMPNAME = $tempName;
        $this->EXTENSION = $imageExtension;
        $this->THUMB = "thumb/";
        $this->SMALL = "200/";
        $this->MEDIUM = "500/";
        $this->LARGE = "1000/";
        $this->WEBP = "/public/image/webp/";
        $this->JPEG = "/public/image/jpeg/";
    }

    public function sourceImage($originalName, $extension){
        if($extension == "jpg" || $extension == "jpeg"){
            return @imagecreatefromjpeg($originalName);
        } else if($extension == "png"){
            return @imagecreatefrompng($originalName);
        } else{
            return @imagecreatefromjpeg($originalName);
        }
    }

    public function uploadWebp(){
        $width_200 = $height_200 = $width_500 = $height_500 = $width_1000 = $height_1000 = $path_200 = $path_500 = $path_1000 = "";

        if($this->ORIGINALWidth < 200){
            $width_200 = $this->ORIGINALWidth;
            $height_200 = $this->ORIGINALHeight;
        } else{
            $width_200 = 200;
            $height_200 = round(($this->ORIGINALHeight * $width_200) / $this->ORIGINALWidth);
        }

        if($this->ORIGINALWidth < 500){
            $width_500 = $this->ORIGINALWidth;
            $height_500 = $this->ORIGINALHeight;
        } else{
            $width_500 = 500;
            $height_500 = round(($this->ORIGINALHeight * $width_500) / $this->ORIGINALWidth);
        }

        if($this->ORIGINALWidth < 1000){
            $width_1000 = $this->ORIGINALWidth;
            $height_1000 = $this->ORIGINALHeight;
        } else{
            $width_1000 = 1000;
            $height_1000 = round(($this->ORIGINALHeight * $width_1000) / $this->ORIGINALWidth);
        }

        $path_200 = __DIR__ . "/../../" . $this->WEBP . $this->SMALL . $this->SLUG . ".webp";
        $path_500 = __DIR__ . "/../../" . $this->WEBP . $this->MEDIUM . $this->SLUG . ".webp";
        $path_1000 = __DIR__ . "/../../" . $this->WEBP . $this->LARGE . $this->SLUG . ".webp";

        return  self::uploadWebpImage($width_200, $height_200, $path_200) && 
                self::uploadWebpImage($width_500, $height_500, $path_500) && 
                self::uploadWebpImage($width_1000, $height_1000, $path_1000);
    }   

    public function uploadWebpImage($width, $height, $path){
        $sourceImage = self::sourceImage($this->TEMPNAME, $this->EXTENSION);
        if(!$sourceImage){
            JsonResponse::send(
                "Source Image for WEBBP is not created",
                "error"
            );
        } else{
            $blankImage = imagecreatetruecolor($width, $height);

            if($blankImage){
                if(imagecopyresized($blankImage, $sourceImage, 0, 0, 0, 0, $width, $height, $this->ORIGINALWidth, $this->ORIGINALHeight)){
                    imagedestroy($sourceImage);
                    if(imagewebp($blankImage, $path)){
                        imagedestroy($blankImage);
                        return true;
                    } else{
                        JsonResponse::send(
                            "Webp Image is not created",
                            "error"
                        );
                    }
                } else{
                    JsonResponse::send(
                        "Image copy resized is not created",
                        "error"
                    );
                }
            } else{
                JsonResponse::send(
                    "Image not created",
                    "error"
                );
            }
        }
    }

    public function uploadJpeg(){   
        $PATH = __DIR__ . "/../../" . $this->JPEG . $this->SLUG . ".jpeg";

        $sourceImage = self::sourceImage($this->TEMPNAME, $this->EXTENSION);
        if(!$sourceImage){
            JsonResponse::send(
                "Source Image for JPEG is not created",
                "error"
            );
        } else{
            $blankImage = imagecreatetruecolor($this->ORIGINALWidth, $this->ORIGINALHeight);
            if($blankImage){
                if(
                    imagecopyresized($blankImage, $sourceImage, 0, 0, 0, 0, $this->ORIGINALWidth, $this->ORIGINALHeight, $this->ORIGINALWidth, $this->ORIGINALHeight)
                ){
                    imagedestroy($sourceImage);
                    if(imagejpeg($blankImage, $PATH)){
                        imagedestroy($blankImage);
                        return true;
                    } else{
                        JsonResponse::send(
                             "JPEG Image is not created",
                            "error"
                        );
                    }
                } else{
                    JsonResponse::send(
                        "Image copy resized is not work for JPEG",
                        "error"
                    );
                }
            } else{
                JsonResponse::send(
                    "Blank Image for JPEG not created",
                    "error"
                );
            }
        }
    }
}