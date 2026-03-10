<?php
require_once __DIR__ . '/config/config.php';

$champions = $pdo->query("SELECT * FROM hall_of_fame ORDER BY end_date DESC")->fetchAll();

$page_title = "Hall of Fame - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4 text-center">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-4 fw-bold">HALL OF FAME</h1>
            <p class="text-secondary lead">Honoring the champions and top performers of BTH Gaming tournaments.</p>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <?php foreach($champions as $c): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 bg-black border-warning border-opacity-25 py-5 shadow p-3">
                        <div class="mb-4"><span style="font-size: 5rem;">🏆</span></div>
                        <h6 class="text-secondary text-uppercase mb-1 fw-bold"><?php echo htmlspecialchars($c['tournament_name']); ?></h6>
                        <?php if($c['winner_1st_img']): ?>
                            <div class="mb-3"><img src="uploads/hall_of_fame/<?php echo htmlspecialchars($c['winner_1st_img']); ?>" class="img-fluid rounded" style="max-height:120px;"></div>
                        <?php endif; ?>
                        <h1 class="text-warning fw-bold mb-4 text-uppercase"><?php echo htmlspecialchars($c['winner_1st']); ?></h1>
                        <div class="d-flex justify-content-center gap-3 small text-uppercase fw-bold text-info mb-2">
                            <span><?php echo htmlspecialchars($c['game_title']); ?></span>
                            <span class="text-secondary">|</span>
                            <span><?php echo $c['end_date'] ? date('M Y', strtotime($c['end_date'])) : ''; ?></span>
                        </div>
                        <div class="row text-center g-2 mb-4">
                            <div class="col-4">
                                <div class="small text-secondary">2ND</div>
                                <?php if($c['winner_2nd_img']): ?>
                                    <img src="uploads/hall_of_fame/<?php echo htmlspecialchars($c['winner_2nd_img']); ?>" class="d-block mx-auto mb-1" style="width:50px;height:50px;object-fit:cover;" />
                                <?php endif; ?>
                                <div class="fw-bold text-silver"><?php echo htmlspecialchars($c['winner_2nd'] ?: '-'); ?></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-secondary">3RD</div>
                                <?php if($c['winner_3rd_img']): ?>
                                    <img src="uploads/hall_of_fame/<?php echo htmlspecialchars($c['winner_3rd_img']); ?>" class="d-block mx-auto mb-1" style="width:50px;height:50px;object-fit:cover;" />
                                <?php endif; ?>
                                <div class="fw-bold text-bronze"><?php echo htmlspecialchars($c['winner_3rd'] ?: '-'); ?></div>
                            </div>
                            <div class="col-4">
                                <div class="small text-secondary">TOP SCORER</div>
                                <div class="fw-bold text-danger"><?php echo htmlspecialchars($c['top_scorer'] ?: '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($champions)): ?>
                <div class="col-12 py-5">
                    <p class="text-secondary opacity-50">THE HALL OF FAME IS EMPTY. WHO WILL BE FIRST?</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
