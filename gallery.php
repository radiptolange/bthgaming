<?php
require_once __DIR__ . '/config/config.php';

// ensure gallery table exists so page doesn't break
$pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT '',
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Gallery',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
// add column if missing
$col = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'category'")->fetch();
if (!$col) {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN category VARCHAR(100) DEFAULT 'Gallery'");
}

$gallery = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC")->fetchAll();

$page_title = "Gallery - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-4 fw-bold">GALLERY</h1>
            <p class="text-secondary lead">Community photos and tournament highlights.</p>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <?php foreach($gallery as $img): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 bg-black border-secondary border-opacity-25 overflow-hidden">
                        <img src="uploads/gallery/<?php echo $img['image']; ?>" class="card-img-top" height="300" style="object-fit:cover;">
                        <div class="card-body p-3 text-center">
                            <h6 class="text-secondary fw-bold text-uppercase mb-0 small"><?php echo htmlspecialchars($img['title']); ?></h6>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($gallery)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-secondary opacity-50">NO GALLERY IMAGES UPLOADED YET.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
