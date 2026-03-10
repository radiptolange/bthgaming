<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$tournament_id = $_GET['tournament_id'] ?? null;

if (!$tournament_id) {
    header("Location: tournaments.php");
    exit;
}

// Get tournament details
$stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch();

if (!$tournament) {
    header("Location: tournaments.php");
    exit;
}

// Get approved players for this tournament
$stmt = $pdo->prepare("SELECT * FROM tournament_players WHERE tournament_id = ? AND status = 'Approved' ORDER BY registered_at ASC");
$stmt->execute([$tournament_id]);
$players = $stmt->fetchAll();

$page_title = "Tournament Players - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-4 border-bottom border-secondary pb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase"><?php echo htmlspecialchars($tournament['name']); ?> - Players</h2>
                    <small class="text-secondary"><a href="tournaments.php" class="text-decoration-none">← Back to Tournaments</a></small>
                </div>
                <div>
                    <span class="badge bg-info text-dark fw-bold px-3 py-2"><?php echo count($players); ?> Approved Players</span>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">RANK</th>
                                <th>IMAGE</th>
                                <th>PLAYER NAME</th>
                                <th>CONTACT</th>
                                <th>REGISTERED</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($players as $index => $player): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-warning">#<?php echo ($index + 1); ?></td>
                                <td>
                                    <?php if ($player['profile_image']): ?>
                                        <img src="../uploads/tournament_players/<?php echo $player['profile_image']; ?>" width="40" height="40" class="rounded border border-secondary" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <small class="text-white">No IMG</small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($player['player_name']); ?></td>
                                <td class="small text-secondary"><?php echo htmlspecialchars($player['contact_info']); ?></td>
                                <td class="small text-secondary"><?php echo date('M d, Y', strtotime($player['registered_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (empty($players)): ?>
            <div class="text-center py-5">
                <p class="text-secondary opacity-50">NO APPROVED PLAYERS YET.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
