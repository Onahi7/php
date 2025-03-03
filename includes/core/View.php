<?php
namespace Summit\Core;

class View {
    private $layout = 'default';
    private $sections = [];
    private $currentSection = null;

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function render($view, $data = []) {
        // Extract data to make it available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        require $viewPath;
        
        // Get the view content
        $content = ob_get_clean();
        
        // If we're not in a section, render the layout
        if ($this->currentSection === null) {
            // Start output buffering again for the layout
            ob_start();
            
            // Include the layout file
            $layoutPath = __DIR__ . '/../../views/layouts/' . $this->layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout file not found: {$layoutPath}");
            }
            require $layoutPath;
            
            // Return the complete page
            return ob_get_clean();
        }
        
        return $content;
    }

    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection() {
        if ($this->currentSection === null) {
            throw new \Exception("No section started");
        }
        
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function getSection($name) {
        return $this->sections[$name] ?? '';
    }

    public function include($view, $data = []) {
        echo $this->render($view, $data);
    }

    public function csrf() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    public function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function asset($path) {
        return BASE_PATH . '/assets/' . ltrim($path, '/');
    }

    public function url($path) {
        return BASE_PATH . '/' . ltrim($path, '/');
    }

    public function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }

    public function error($key) {
        return $_SESSION['errors'][$key] ?? '';
    }

    public function hasError($key) {
        return isset($_SESSION['errors'][$key]);
    }

    public function flash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return '';
    }
}
