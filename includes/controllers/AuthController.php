<?php
namespace Summit\Controllers;

class AuthController {
    public function login() {
        $pageTitle = 'Login - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/auth/login.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function authenticate() {
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            // Add your authentication logic here
            // For now, just redirect back to login
            header('Location: ' . BASE_PATH . '/login');
            exit;
        }
    }

    public function register() {
        $pageTitle = 'Register - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/auth/register.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function store() {
        // Handle registration form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            // Add your registration logic here
            // For now, just redirect back to register
            header('Location: ' . BASE_PATH . '/register');
            exit;
        }
    }

    public function forgot_password() {
        $pageTitle = 'Forgot Password - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/auth/forgot-password.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function reset_password() {
        $pageTitle = 'Reset Password - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/auth/reset-password.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }
}
