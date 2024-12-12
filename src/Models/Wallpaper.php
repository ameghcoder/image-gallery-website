<?php 

namespace App\Models;


use PDO;
use App\config\Database;
use App\Helpers\ErrorMessages;
use App\Helpers\ImageUploader;
use App\Helpers\JsonResponse;
use App\Services\AuthService;

class Wallpaper{

    private static function dbConnection(){
        return Database::connect();
    }

    private static function createSlug($title, $id) {
        // Remove special characters and convert to lowercase
        $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($title));
        // Limit the length to 60 characters
        $slug = substr($slug, 0, 60);
        // Append the unique ID
        return $slug . '-' . $id;
    }

    private static function createDynamicDescription($tag, $resolution){
        $templates = [
            "Download this {adjective} {tags} wallpaper in {resolution}. This ultra-HD image is perfect for {audience} and will bring your screen to life.",
            "Experience this {adjective} with the beauty of {tags} with this ultra-HD wallpaper in {resolution}. Perfect for {audience}.",
            "Get this high-quality {tags} wallpaper in {resolution}. This {adjective} image will bring energy and life to your screen."
        ];

        $audienceOptions = ['car lovers', 'anime lovers', 'racing fans', 'gamers', 'car enthusiasts'];
        $adjectives = ['stunning', 'breathtaking', 'eye-catching', 'mind-blowing', 'captivating'];

        $audience = $audienceOptions[array_rand($audienceOptions)];
        $adjective = $adjectives[array_rand($adjectives)];

        $descriptionTemplate = $templates[array_rand($templates)];
        $descriptionTemplate = str_replace(
            [
                '{adjective}',
                '{tags}',
                '{resolution}',
                '{audience}'
            ],
            [
                $adjective,
                $tag,
                $resolution,
                $audience
            ],
            $descriptionTemplate
        );
        return $descriptionTemplate;
    }

    public static function save($data){
        $conn = self::dbConnection();     
        try{
            // Wallpaper Data
            $tempName = $data['wallpaperName']['tmp_name'];
            $type = $data['wallpaperName']['type'];
            $id = $data['id'];

            list($width, $height) = getimagesize($tempName);

            // Additional Information to save in database
            $resolution = "$width,$height";
            $device = $width >= $height ? "c" : "a";

            $extension = explode('/', $type);
            $extension = $extension == 'jpg' || $extension == 'jfif' ? 'jpeg' : $extension;

            if($extension === "png"){
                JsonResponse::send(
                    "We support only JPEG image at this time, this image is PNG",
                    "error"
                );
            }

            $title = $data['title'];
            $description = self::createDynamicDescription($data['description'], $resolution);

            $views = $downloads = $shares = 0;

            $query = "INSERT INTO 
                        wallpapers(title, description, filename, views, downloads, shares, device, resolution, userId) 
                        VALUES (:title, :description, '', :views, :downloads, :shares, :device, :resolution, :userId)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":views", $views);
            $stmt->bindParam(":downloads", $downloads);
            $stmt->bindParam(":shares", $shares);
            $stmt->bindParam(":device", $device);
            $stmt->bindParam(":resolution", $resolution);
            $stmt->bindParam(":userId", $id);
            $lastId = "";
            if($stmt->execute()){
                $lastId = $conn->lastInsertId();

                $slug = self::createSlug($title, $lastId);

                $imageUploader = new ImageUploader($slug, $tempName, $extension, $width, $height);
                if($imageUploader->uploadWebp() && $imageUploader->uploadJpeg()){
                    $size = filesize(__DIR__ . "/../../public/image/jpeg/" . $slug . ".jpeg");

                    $query3 = "UPDATE wallpapers SET filename = :filename, size = :size WHERE id = :id";
                    $stmt3 = $conn->prepare($query3);

                    $stmt3->bindParam(":filename", $slug);
                    $stmt3->bindParam(":size", $size);
                    $stmt3->bindParam(":id", $lastId);

                    if($stmt3->execute()){
                        return [
                            "code" => "001",
                            "func" => "Universal",
                            "type" => "success",
                            "data" => [
                                "url" => "https://gloztik.com/wallpaper/$slug"
                            ]
                        ];
                    } else{
                        $query2 = "DELETE FROM wallpapers WHERE id=:id";
                        $stmt2 = $conn->prepare($query2);
                        $stmt2->bindParam(":id", $lastId);
                        $stmt2->execute();

                        JsonResponse::send(
                            "Something goes wrong when create the image, Changes rollbacked",
                            "error"
                        );
                    }

                } else{
                    // rollback data
                    $query2 = "DELETE FROM wallpapers WHERE id=:id";
                    $stmt2 = $conn->prepare($query2);
                    $stmt2->bindParam(":id", $lastId);
                    $stmt2->execute();

                    JsonResponse::send(
                        "Something goes wrong when create the image, Changes rollbacked",
                        "error"
                    );
                }

            } else{
                return [
                    "code" => "001",
                    "func" => "Universal",
                    "type" => "error"
                ];
            }

            return true;
        } catch(\Exception $e){
            // rollback data
            $query2 = "DELETE FROM wallpapers WHERE id=:id";
            $stmt2 = $conn->prepare($query2);
            $stmt2->bindParam(":id", $lastId);
            $stmt2->execute();

            JsonResponse::send(
                "Something goes wrong when create the image, Changes rollbacked",
                "error"
            );
        }
        
    }

    public static function update(){

    }

    public static function delete(){

    }

    public static function getSingleWallpaper($filename){
        $conn = self::dbConnection();

        $query = "SELECT * FROM wallpapers WHERE filename=:filename";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":filename", $filename, PDO::PARAM_STR);

        return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
    }

    public static function getRangeWallpaper($from, $range){
        $conn = self::dbConnection();

        $query = "SELECT * FROM wallpapers ORDER BY 1 DESC LIMIT :from, :range";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":from", $from, PDO::PARAM_INT);
        $stmt->bindParam(":range", $range, PDO::PARAM_INT);

        return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private static function getSingleCategoryWallpaper($category, $from = 0, $range = 30){
        $conn = self::dbConnection();

        $query = "SELECT * FROM wallpapers WHERE ORDER BY 1 DESC LIMIT :from, :range";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":from", $from, PDO::PARAM_INT);
        $stmt->bindParam(":range", $range, PDO::PARAM_INT);

        return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
    }

    public static function getRelatedRangeWallpaper($search, $from, $range, $count = false, $currentId = null){
        $search = strtolower($search);

        // filter the search query
        $_removable_words = ['and', 'super', 'wallpaper', 'image', 'pic', 'picture', 'best', 'full'];
        for($i = 0; $i < count($_removable_words); $i++){
            $search = str_replace($_removable_words[$i],'', $search);
        }

        // make query singular
        $search = str_replace(
            [
                'animals',
                'cars',
                'flowers',
                'sports'
            ], 
            [
                'animal',
                'car',
                'flower',
                'sport'
            ],  
            $search
        );

        $conn = self::dbConnection();

        if(!$count){
            // $query = "SELECT * FROM wallpapers WHERE MATCH(description, title) AGAINST(:search) ORDER BY 1 DESC LIMIT :range OFFSET :from";
            $query = "SELECT * FROM wallpapers WHERE MATCH(title, description) AGAINST (+:search IN BOOLEAN MODE) ORDER BY 1 DESC LIMIT :range OFFSET :from";
    
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":from", $from, PDO::PARAM_INT);
            $stmt->bindParam(":range", $range, PDO::PARAM_INT);
            $stmt->bindParam(":search", $search, PDO::PARAM_STR);
    
            return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } else{
            $query = "SELECT COUNT(*) AS total_rows FROM wallpapers WHERE MATCH(title, description) AGAINST (+:search IN BOOLEAN MODE)";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(":search", $search, PDO::PARAM_STR);

            return $stmt->execute() ? ($stmt->fetch(PDO::FETCH_ASSOC))['total_rows'] : 0;
        }
    }

    public static function oneRandomWallpaper($device = "c"){
        $conn = self::dbConnection();

        // query
        
        $query = "SELECT filename FROM wallpapers WHERE device = :device ORDER BY RAND() LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":device", $device);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($data)){
            return json_encode([
                "url" => "https://gloztik.com/public/image/jpeg/" . $data['filename'] . ".jpeg"
            ]);
        } else{
            return json_encode([
                "url" => "none"
            ]);
        }
    }

    /**
     * This function give you the wallpaper for specific device like desktop, android and iphone
     * @param mixed $for use a, if you want wallpapers for android and iphone, use c if you want wallpapers for desktop, computers
     * @param mixed $from from where you want the wallpapers like from 30, 60 etc.
     * @param mixed $range limit of one time wallpaper load
     * @return array on success or data exists return the array that contains the wallpapers data or empty array otherwise
     */
    public static function getByDeviceWallpapers($for, $from, $range){
        $conn = self::dbConnection();
        
        // $query = "SELECT * FROM wallpapers WHERE MATCH(description, title) AGAINST(:search) ORDER BY 1 DESC LIMIT :range OFFSET :from";
        $query = "SELECT * FROM wallpapers WHERE device = :device ORDER BY 1 DESC LIMIT :range OFFSET :from";
    
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":from", $from, PDO::PARAM_INT);
        $stmt->bindParam(":range", $range, PDO::PARAM_INT);
        $stmt->bindParam(":device", $for, PDO::PARAM_STR);
    
        return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}