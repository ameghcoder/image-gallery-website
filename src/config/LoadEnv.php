<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Dotenv\Dotenv;

$loadEnv = Dotenv::createImmutable(__DIR__ . '/../../');
$loadEnv->load();
