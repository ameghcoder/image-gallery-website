<?php

namespace App\config;

use Dotenv\Dotenv;

try{

    class Database{
       
        public static function connect(){
            $loadEnv = Dotenv::createImmutable(__DIR__ . '/../../');
            $loadEnv->load();
    
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];
            // Create a new PDO instance
            $pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo; // Return the PDO instance
        }
    }

} catch(\PDOException $e){
    // Handle connection error
    die('Connection failed: ' . $e->getMessage());
}