<?php
// Set the current working directory to the project root
chdir(dirname(__DIR__));

// Route requests to the requested php file in the project root
$uri = $_SERVER['REQUEST_URI'];

// Remove query string
$uri = explode('?', $uri)[0];

// Handle clean URLs if necessary
$file = ltrim($uri, '/');

if ($file === '' || $file === '/') {
    $file = 'index.php';
}

// If the file doesn't have a .php extension but exists as a .php file, handle it (cleanUrls)
if (!preg_match('/\.php$/', $file)) {
    if (file_exists($file . '.php')) {
        $file .= '.php';
    }
}

// Make sure the file exists, is a PHP file, and is not inside the api folder itself (to prevent recursion)
if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php' && strpos($file, 'api/') !== 0) {
    // Modify SCRIPT_NAME, SCRIPT_FILENAME, and PHP_SELF to mimic direct execution
    $_SERVER['SCRIPT_NAME'] = '/' . $file;
    $_SERVER['SCRIPT_FILENAME'] = realpath($file);
    $_SERVER['PHP_SELF'] = '/' . $file;
    
    require $file;
} else {
    http_response_code(404);
    echo "404 Not Found";
}
