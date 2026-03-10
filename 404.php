<?php
require_once __DIR__ . '/config/config.php';
$page_title = "Page Not Found - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-bold neon-text">404</h1>
        <h2 class="mb-4">OPPS! PAGE NOT FOUND</h2>
        <p class="text-secondary lead mb-5">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="index.php" class="btn btn-neon px-5 py-3 fw-bold">RETURN TO BATTLEFIELD</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
