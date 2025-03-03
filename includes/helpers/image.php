<?php
function get_image_tag($src, $alt, $class = '', $lazy = true) {
    $img = '<img';
    if ($lazy) {
        $img .= ' loading="lazy"';
        $img .= ' data-src="' . htmlspecialchars($src) . '"';
        $img .= ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';
    } else {
        $img .= ' src="' . htmlspecialchars($src) . '"';
    }
    $img .= ' alt="' . htmlspecialchars($alt) . '"';
    if ($class) {
        $img .= ' class="' . htmlspecialchars($class) . '"';
    }
    $img .= '>';
    return $img;
}

