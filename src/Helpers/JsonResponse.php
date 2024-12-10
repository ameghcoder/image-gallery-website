<?php

namespace App\Helpers;

class JsonResponse {
    public static function send($message, $type = 'success', $data = [], $statusCode = 200) {
        http_response_code($statusCode);
        $response = [
            'message' => $message,
            'type' => $type,
            'data' => $data
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
