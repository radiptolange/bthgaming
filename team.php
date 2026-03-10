<?php
require_once __DIR__ . '/config/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: teams.php"); exit; }

$stmt = $pdo->prepare("SELECT t.*, p.username as captain_name FROM teams t LEFT JOIN players p ON t.captain_id = p.id WHERE t.id = ?");
$stmt->execute([$id]);
$team = $stmt->fetch();

if (!$team) { header("Location: 404.php"); exit; }

$stmt = $pdo->prepare("SELECT p.* FROM players p JOIN team_players tp ON p.id = tp.player_id WHERE tp.team_id = ?");
$stmt->execute([$id]);
$roster = $stmt->fetchAll();

$page_title = htmlspecialchars($team['team_name']) . " - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-lg-4 mb-4">
            <div class="card p-4 border-info border-opacity-25 shadow">
                <img src="uploads/teams/<?php echo $team['logo'] ?: 'default-team.jpg'; ?>" class="rounded mb-4 border border-info border-3 p-1 w-100" style="max-height: 250px; object-fit: cover;">
                <h2 class="neon-text mb-2 text-uppercase"><?php echo htmlspecialchars($team['team_name']); ?></h2>
                <div class="small text-secondary mb-4 fw-bold">CAPTAIN: <span class="text-white"><?php echo htmlspecialchars($team['captain_name'] ?: 'Not Assigned'); ?></span></div>

                <div class="d-flex flex-column gap-2 small">
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">MEMBERS:</span>
                        <span class="text-white fw-bold"><?php echo count($roster); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">ESTABLISHED:</span>
                        <span class="text-white fw-bold"><?php echo date('M Y', strtotime($team['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h2 class="neon-text mb-4 border-start border-4 border-info ps-3 text-uppercase">Active Roster</h2>
            <div class="row g-3 mb-5">
                <?php foreach($roster as $p): ?>
                <div class="col-md-6">
                    <a href="player.php?id=<?php echo $p['id']; ?>" class="text-decoration-none">
                        <div class="card p-3 border-secondary border-opacity-25 bg-black h-100">
                            <div class="d-flex align-items-center gap-3">
                                <img src="uploads/players/<?php echo $p['profile_image'] ?: 'default-player.jpg'; ?>" class="rounded-circle border border-info" width="50" height="50">
                                <div>
                                    <h6 class="mb-0 text-white fw-bold"><?php echo htmlspecialchars($p['username']); ?></h6>
                                    <small class="text-secondary text-uppercase"><?php echo $p['role']; ?></small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <h2 class="neon-text mb-4 border-start border-4 border-info ps-3 text-uppercase">Tournament History</h2>
            <div class="card border-secondary border-opacity-25 shadow p-0">
                <div class="table-responsive">
                    <table class="table table-dark mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold">
                                <th class="ps-4">TOURNAMENT</th>
                                <th>RESULT</th>
                                <th class="pe-4">DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT trn.*, tr.status as reg_status FROM tournament_registrations tr JOIN tournaments trn ON tr.tournament_id = trn.id WHERE tr.team_id = ? ORDER BY trn.start_date DESC");
                            $stmt->execute([$id]);
                            while($trn = $stmt->fetch()):
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <strong><?php echo htmlspecialchars($trn['name']); ?></strong><br>
                                    <small class="text-info"><?php echo $trn['game_title']; ?></small>
                                </td>
                                <td>
                                    <?php if($trn['champion_team_id'] == $id): ?>
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-bold">🏆 CHAMPION</span>
                                    <?php else: ?>
                                        <span class="text-secondary small">Participant</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 small text-secondary"><?php echo date('M d, Y', strtotime($trn['start_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
