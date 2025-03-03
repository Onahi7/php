<?php
namespace Summit\Core;

class Controller {
    protected $view;
    protected $auth;
    protected $db;

    public function __construct() {
        $this->view = new View();
        $this->auth = Auth::getInstance();
        $this->db = Database::getInstance();
    }

    protected function render($view, $data = []) {
        return $this->view->render($view, $data);
    }

    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header('Location: ' . BASE_PATH . '/' . ltrim($url, '/'));
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    protected function getQuery($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    protected function validateCSRF() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new \Exception('Invalid CSRF token');
        }
    }
}
