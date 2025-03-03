<?php
namespace Summit\Controllers;

class AdminController {
    public function dashboard() {
        $pageTitle = 'Admin Dashboard - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/admin/dashboard.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function registrations() {
        $pageTitle = 'Registrations - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/admin/registrations.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function verify_registrations() {
        $pageTitle = 'Verify Registrations - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/admin/verify-registrations.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }

    public function verify_registration($id) {
        // Handle registration verification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Add your verification logic here
            header('Location: ' . BASE_PATH . '/admin/verify-registrations');
            exit;
        }
    }

    public function registration_settings() {
        $pageTitle = 'Registration Settings - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/admin/registration-settings.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }
}
