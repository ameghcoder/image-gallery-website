<?php

namespace App\Models;


use PDO;
use App\config\Database;
use App\Helpers\ErrorMessages;
use App\Helpers\JsonResponse;
class Contact{
    public static function save($data)  {
        $conn = Database::connect();

        $query = "INSERT INTO contactus(name, email, country, subject, message) VALUES (:name, :email, :country, :subject, :message)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":subject", $data['subject']);
        $stmt->bindParam(":country", $data['country']);
        $stmt->bindParam(":message", $data['msg']);

        return $stmt->execute() ? true : false;
    }
}