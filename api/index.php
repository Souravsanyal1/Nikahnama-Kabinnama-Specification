<?php
// Set the current working directory to the project root
chdir(dirname(__DIR__));

// Route requests to the requested php file in the project root
$uri = $_SERVER['REQUEST_URI'];

// Remove query string
$uri = explode('?', $uri)[0];

// Handle clean URLs if necessary
$file = ltrim($uri, '/');

// Diagnostic endpoint
if ($file === 'debug') {
    header('Content-Type: text/plain');
    echo "Current working directory: " . getcwd() . "\n";
    echo "Directory of this script: " . __DIR__ . "\n";
    echo "Parent directory: " . dirname(__DIR__) . "\n";
    echo "Parent directory files:\n";
    print_r(scandir(dirname(__DIR__)));
    echo "App directory files:\n";
    if (file_exists(dirname(__DIR__) . '/app')) {
        print_r(scandir(dirname(__DIR__) . '/app'));
    } else {
        echo "App directory does not exist!\n";
    }
    exit;
}

// Static file fallback server
if (file_exists($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'json' => 'application/json',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf'
    ];
    
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Cache-Control: public, max-age=3600');
        readfile($file);
        exit;
    }
}

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
    echo "404 Not Found (Request file: " . htmlspecialchars($file) . ")";
}
