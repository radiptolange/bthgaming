<?php
require_once __DIR__ . '/config/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT p.*, t.team_name, t.id as team_id FROM players p LEFT JOIN team_players tp ON p.id = tp.player_id LEFT JOIN teams t ON tp.team_id = t.id WHERE p.id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) { header("Location: 404.php"); exit; }

// Stats calculation
$stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE (team_a_id = ? OR team_b_id = ?) AND status = 'Completed'");
$stmt->execute([$player['team_id'], $player['team_id']]);
$played = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE winner_id = ? AND status = 'Completed'");
$stmt->execute([$player['team_id']]);
$wins = $stmt->fetchColumn();

$win_rate = $played > 0 ? round(($wins / $played) * 100) : 0;

$page_title = htmlspecialchars($player['username']) . " Profile - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-lg-4 mb-4">
            <div class="card text-center p-4 border-info border-opacity-25 shadow">
                <img src="uploads/players/<?php echo $player['profile_image'] ?: 'default-player.jpg'; ?>" class="rounded-circle mx-auto mb-4 border border-info border-3 p-1" width="150" height="150" style="object-fit: cover;">
                <h2 class="neon-text mb-1"><?php echo htmlspecialchars($player['username']); ?></h2>
                <p class="text-secondary fw-bold text-uppercase small mb-4"><?php echo $player['role']; ?></p>

                <div class="d-grid gap-2">
                    <?php if ($player['team_id']): ?>
                        <a href="team.php?id=<?php echo $player['team_id']; ?>" class="btn btn-outline-info btn-sm">TEAM: <?php echo htmlspecialchars($player['team_name']); ?></a>
                    <?php else: ?>
                        <span class="badge bg-dark border border-secondary py-2">FREE AGENT</span>
                    <?php endif; ?>
                </div>

                <hr class="border-secondary opacity-25 my-4">

                <div class="text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">FULL NAME:</span>
                        <span class="text-white fw-bold"><?php echo htmlspecialchars($player['full_name']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">COUNTRY:</span>
                        <span class="text-white fw-bold"><?php echo htmlspecialchars($player['country']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">GAME ID:</span>
                        <span class="text-info fw-bold"><?php echo htmlspecialchars($player['game_id']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h2 class="neon-text mb-4 border-start border-4 border-info ps-3 text-uppercase">Career Statistics</h2>
            <div class="row g-4 mb-5 text-center">
                <div class="col-md-4">
                    <div class="card p-4 bg-black border-secondary border-opacity-25">
                        <h1 class="display-4 fw-bold text-white"><?php echo $played; ?></h1>
                        <small class="text-secondary fw-bold text-uppercase">Matches</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 bg-black border-secondary border-opacity-25">
                        <h1 class="display-4 fw-bold text-success"><?php echo $wins; ?></h1>
                        <small class="text-secondary fw-bold text-uppercase">Wins</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-4 bg-black border-secondary border-opacity-25">
                        <h1 class="display-4 fw-bold text-info"><?php echo $win_rate; ?>%</h1>
                        <small class="text-secondary fw-bold text-uppercase">Win Rate</small>
                    </div>
                </div>
            </div>

            <h2 class="neon-text mb-4 border-start border-4 border-info ps-3 text-uppercase">Recent Performance</h2>
            <div class="card border-secondary border-opacity-25 shadow p-0">
                <div class="table-responsive">
                    <table class="table table-dark mb-0">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold">
                                <th class="ps-4">RESULT</th>
                                <th>MATCHUP</th>
                                <th>SCORE</th>
                                <th class="pe-4">DATE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT m.*, t1.team_name as team_a, t2.team_name as team_b FROM matches m LEFT JOIN teams t1 ON m.team_a_id = t1.id LEFT JOIN teams t2 ON m.team_b_id = t2.id WHERE (m.team_a_id = ? OR m.team_b_id = ?) AND m.status = 'Completed' ORDER BY m.match_time DESC LIMIT 5");
                            $stmt->execute([$player['team_id'], $player['team_id']]);
                            while($m = $stmt->fetch()):
                                $is_win = $m['winner_id'] == $player['team_id'];
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-<?php echo $is_win ? 'success' : 'danger'; ?> rounded-pill small px-3"><?php echo $is_win ? 'WIN' : 'LOSS'; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($m['team_a']); ?> <small class="text-info mx-1">vs</small> <?php echo htmlspecialchars($m['team_b']); ?></td>
                                <td class="fw-bold"><?php echo $m['score_a']; ?> - <?php echo $m['score_b']; ?></td>
                                <td class="pe-4 small text-secondary"><?php echo date('M d, Y', strtotime($m['match_time'] ?: $m['created_at'] ?? 'now')); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if ($played == 0): ?>
                                <tr><td colspan="4" class="text-center py-5 text-secondary">No match data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
