<?php
namespace Summit\Controllers;

class HomeController {
    public function index() {
        $pageTitle = 'Home - North Central Education Summit';
        $viewPath = __DIR__ . '/../../views/home.php';
        require_once __DIR__ . '/../../views/layouts/main.php';
    }
}
