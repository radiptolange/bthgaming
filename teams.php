<?php
require_once __DIR__ . '/config/config.php';

$stmt = $pdo->query("SELECT t.*, COUNT(tp.player_id) as member_count
                     FROM teams t
                     LEFT JOIN team_players tp ON t.id = tp.team_id
                     GROUP BY t.id
                     ORDER BY t.team_name ASC");
$teams = $stmt->fetchAll();

$page_title = "Elite Teams - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-4 fw-bold">ELITE TEAMS</h1>
            <p class="text-secondary lead">Meet the professional organizations competing in BTH Gaming tournaments.</p>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <?php foreach($teams as $t): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 text-center p-4 border-secondary border-opacity-25">
                        <div class="mb-3">
                            <img src="uploads/teams/<?php echo $t['logo'] ?: 'default-team.jpg'; ?>" class="rounded border border-info border-2" width="100" height="100" style="object-fit: cover;">
                        </div>
                        <h5 class="fw-bold text-white mb-2 text-uppercase"><?php echo htmlspecialchars($t['team_name']); ?></h5>
                        <p class="small text-secondary mb-4"><?php echo $t['member_count']; ?> ROSTER MEMBERS</p>
                        <a href="team.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-neon w-100">VIEW PROFILE</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($teams)): ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-secondary opacity-50">NO TEAMS REGISTERED YET.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
