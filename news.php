<?php
require_once __DIR__ . '/config/config.php';

$news_obj = new News($pdo);
$news = $news_obj->all(20);

$id = $_GET['id'] ?? null;
$article = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
}

$page_title = ($article ? htmlspecialchars($article['title']) : "Esports News") . " - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <?php if ($article): ?>
            <div class="col-lg-8 mx-auto">
                <img src="uploads/news/<?php echo $article['image'] ?: 'default-news.jpg'; ?>" class="img-fluid rounded border border-secondary mb-4 w-100" style="max-height: 400px; object-fit: cover;">
                <h1 class="neon-text mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="d-flex gap-3 small text-secondary mb-4 border-bottom border-secondary pb-3">
                    <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                    <span>•</span>
                    <span><?php echo date('F d, Y', strtotime($article['created_at'])); ?></span>
                </div>
                <div class="article-content text-light lead">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
                <div class="mt-5">
                    <a href="news.php" class="btn btn-outline-info">← BACK TO ALL NEWS</a>
                </div>
            </div>
        <?php else: ?>
            <div class="col-12 mb-5">
                <h1 class="neon-text display-4 fw-bold text-uppercase">Esports News</h1>
                <p class="text-secondary lead">Stay updated with the latest tournament announcements and community highlights.</p>
            </div>
            <div class="col-12">
                <div class="row g-4">
                    <?php foreach($news as $n): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-secondary">
                            <img src="uploads/news/<?php echo $n['image'] ?: 'default-news.jpg'; ?>" class="card-img-top" height="200" style="object-fit:cover;">
                            <div class="card-body">
                                <small class="text-info d-block mb-2"><?php echo date('M d, Y', strtotime($n['created_at'])); ?></small>
                                <h5 class="fw-bold text-white mb-3"><?php echo htmlspecialchars($n['title']); ?></h5>
                                <p class="small text-secondary mb-4"><?php echo substr(strip_tags($n['content']), 0, 100); ?>...</p>
                                <a href="?id=<?php echo $n['id']; ?>" class="btn btn-sm btn-neon w-100">READ ARTICLE</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
