<?php
// Autoloader PSR-4 simplifié
spl_autoload_register(function ($class) {
    $prefixes = [
        'Config\\' => __DIR__ . '/../config/',
        'App\\' => __DIR__ . '/../src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Démarrer la session
\App\Core\Session::start();