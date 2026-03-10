<?php
define('BASE_URL', 'http://localhost/bth-gaming/');
define('SITE_NAME', 'BTH Gaming');
define('ADMIN_EMAIL', 'admin@bthgaming.com');

// Load Classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once 'database.php';
Auth::startSession();

// Advanced Esports Schema Synchronization
// We use the new schema: tournaments, matches, teams, players, gallery, news
?>
