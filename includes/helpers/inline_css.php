<?php
function get_critical_css() {
    $critical_css_path = __DIR__ . '/../../assets/css/critical.css';
    if (file_exists($critical_css_path)) {
        return file_get_contents($critical_css_path);
    }
    return '';
}

function minify_css($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    // Remove space after colons
    $css = str_replace(': ', ':', $css);
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
    return $css;
}

