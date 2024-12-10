<?php

// src/middleware/authMiddleware.php
function authMiddleware() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
}
