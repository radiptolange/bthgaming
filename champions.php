<?php
require_once __DIR__ . '/config/config.php';

$champions = $pdo->query("SELECT t.*, tm.team_name as champ_name
                          FROM tournaments t
                          JOIN teams tm ON t.champion_team_id = tm.id
                          WHERE t.status = 'Completed'
                          ORDER BY t.id DESC")->fetchAll();

$page_title = "Tournament Champions - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4 text-center">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-1 fw-bold">HALL OF FAME</h1>
            <p class="text-secondary lead">Honoring the teams who conquered BTH Gaming tournaments.</p>
        </div>

        <div class="col-12">
            <div class="row g-5">
                <?php foreach($champions as $t): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 bg-black border-warning border-opacity-25 py-5 shadow p-3">
                        <div class="mb-4"><span style="font-size: 5rem;">🏆</span></div>
                        <h6 class="text-secondary text-uppercase mb-1 fw-bold"><?php echo htmlspecialchars($t['name']); ?></h6>
                        <h1 class="text-warning fw-bold mb-4 text-uppercase"><?php echo htmlspecialchars($t['champ_name']); ?></h1>
                        <div class="d-flex justify-content-center gap-3 small text-uppercase fw-bold text-info mb-4">
                            <span><?php echo $t['game_title']; ?></span>
                            <span class="text-secondary">|</span>
                            <span><?php echo date('M Y', strtotime($t['end_date'])); ?></span>
                        </div>
                        <a href="../tournament_details.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-warning rounded-pill px-5 py-2 fw-bold small">VIEW FULL BRACKETS</a>
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
