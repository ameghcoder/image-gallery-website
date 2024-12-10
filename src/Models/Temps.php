<?php 


namespace App\Models;

use PDO;
use App\config\Database;

class Temps{
    public static function add(){
        // $SD = new PDO("mysql:host=localhost;dbname=temporary;charset=utf8", 'root', 'Akki@6377');
        // $SD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // $stmt2 = $SD->prepare("SELECT * FROM wallpaper_gallery");

        // $stmt2->execute();

        // $data2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // $conn = Database::connect();
        // for ($i=0; $i < count($data2); $i++) { 
        //     $newId = $i + 1;
        //     $id = $data2[$i]["wg_id"];
        //     $title = $data2[$i]["wg_title"];
        //     $description = $data2[$i]["wg_description"];
        //     $views = $data2[$i]["wg_views"];
        //     $downloads = $data2[$i]["wg_downloads"];
        //     $size = $data2[$i]["wg_size"];
        //     $device = $data2[$i]["wg_for"];
        //     $resolution = $data2[$i]["wg_px"];
        //     $filename = $data2[$i]["wg_raw_url"];
        //     $userId = 1;
        //     $date = $data2[$i]["wg_upload_at"];
        //     $share = $data2[$i]["wg_share"];

        //     $stmt = $conn->prepare(
        //         "INSERT INTO wallpapers 
        //         (id, title, description, filename, views, downloads, shares, size, device, resolution, userId, created_at, updated_at) VALUES 
        //         (:id, :title, :description, :filename, :views, :downloads, :share, :size, :device, :resolution, :userId, :date1, :date2)"
        //     );

        //     $stmt->bindParam(':id', $newId);
        //     $stmt->bindParam(':title', $title);
        //     $stmt->bindParam(':description', $description);
        //     $stmt->bindParam(':filename', $filename);
        //     $stmt->bindParam(':views', $views);
        //     $stmt->bindParam(':downloads', $downloads);
        //     $stmt->bindParam(':share', $share);
        //     $stmt->bindParam(':size', $size);
        //     $stmt->bindParam(':device', $device);
        //     $stmt->bindParam(':resolution', $resolution);
        //     $stmt->bindParam(':userId', $userId);
        //     $stmt->bindParam(':date1', $date);
        //     $stmt->bindParam(':date2', $date);
            
        //     $response = $stmt->execute();
        //     echo $response 
        //             ? "Row id: " . $id . " saved." 
        //             : "Row id: " . $id . " failed to save.";
        // }
        


        // return true;
    }
}