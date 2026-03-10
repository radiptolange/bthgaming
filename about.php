<?php
require_once __DIR__ . '/config/config.php';

$page_title = "About Us - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center text-center">
        <div class="col-lg-8 py-lg-5">
            <h1 class="display-3 fw-bold neon-text mb-4">BTH GAMING</h1>
            <p class="lead mb-5 text-secondary">The ultimate destination for professional eFootball tournament management. We provide a robust platform for gamers, event organizers, and community managers to host, track, and showcase elite competitions.</p>

            <div class="row g-4 text-start">
                <div class="col-md-6 mb-4">
                    <div class="card p-4 h-100 bg-black border-info border-opacity-25 shadow-sm">
                        <h4 class="neon-text mb-3">OUR MISSION</h4>
                        <p class="small text-secondary mb-0">To empower the global eFootball community by providing a professional-grade tournament management platform that ensures fair play, transparent tracking, and community engagement.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card p-4 h-100 bg-black border-info border-opacity-25 shadow-sm">
                        <h4 class="neon-text mb-3">ELITE MANAGEMENT</h4>
                        <p class="small text-secondary mb-0">Our platform simplifies complex tournament operations, from participant registration to automated bracket generation and global leaderboard management.</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top border-secondary pt-5">
                <p class="text-secondary small mb-4 fw-bold text-uppercase ls-wide">Trusted by competitive gamers worldwide.</p>
                <div class="d-flex justify-content-center gap-4 opacity-50">
                    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/9/9b/PES_Logo_2021.png/250px-PES_Logo_2021.png" height="40">
                    <img src="https://logos-world.net/wp-content/uploads/2021/08/UEFA-Champions-League-Logo.png" height="40">
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
