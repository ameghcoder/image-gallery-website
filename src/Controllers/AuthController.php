<?php

require __DIR__ . '/../services/init.php';

class AuthController {
    public function login($email, $password) {
        $pdo = getDbConnection(); // Get the database connection

        // Query the database for the user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verify password
        if ($user && password_verify($password, $user['password'])) {

            // Start session and set user data
            session_start();
            $_SESSION['user_id'] = $user['id'];

            // Redirect to homepage or dashboard
            header('Location: /');
            exit;
        } else {
            // Handle login failure
            return "Invalid credentials.";
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login'); // Redirect to login page
        exit;
    }
}
