<?php
namespace Summit\Controllers;

class UserController {
    public function dashboard() {
        $pageTitle = 'Dashboard - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/user/dashboard.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function profile() {
        $pageTitle = 'Profile - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/user/profile.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function update_profile() {
        // Handle profile update form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Add your profile update logic here
            header('Location: ' . BASE_PATH . '/profile');
            exit;
        }
    }
}
