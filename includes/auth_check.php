<?php
require_once __DIR__ . '/../config/config.php';
if (!Auth::check()) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}
?>
