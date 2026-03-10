<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';
$tournament_id = $_GET['tournament_id'] ?? null;

// ensure required tables exist (safe to run repeatedly)
$pdo->exec("CREATE TABLE IF NOT EXISTS tournament_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    player_name VARCHAR(100) NOT NULL,
    contact_info VARCHAR(255),
    profile_image VARCHAR(255),
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    round VARCHAR(50),
    match_number INT,
    team_a_id INT,
    team_b_id INT,
    score_a INT DEFAULT 0,
    score_b INT DEFAULT 0,
    winner_id INT,
    match_time DATETIME,
    status ENUM('Pending','Live','Completed') DEFAULT 'Pending',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (team_a_id) REFERENCES tournament_players(id) ON DELETE SET NULL,
    FOREIGN KEY (team_b_id) REFERENCES tournament_players(id) ON DELETE SET NULL,
    FOREIGN KEY (winner_id) REFERENCES tournament_players(id) ON DELETE SET NULL
)");

if (isset($_POST['generate_brackets'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF validation failed."); }

    $format = $_POST['tournament_format'] ?? 'knockout';
    $size = intval($_POST['bracket_size'] ?? 0);
    $fixture_obj = new Fixture($pdo);

    $success = false;
    switch ($format) {
        case 'round_robin':
            $success = $fixture_obj->generateRoundRobin($tournament_id);
            break;
        case 'knockout':
            $success = $fixture_obj->generate($tournament_id, $size); // existing method
            break;
        case 'group_stage':
            $success = $fixture_obj->generateGroupStage($tournament_id);
            break;
        default:
            $success = $fixture_obj->generate($tournament_id, $size);
    }

    if ($success) {
        $message = '<div class="alert alert-success">Brackets generated successfully using ' . ucfirst(str_replace('_', ' ', $format)) . ' format!</div>';
    } else {
        $message = '<div class="alert alert-danger">Need at least 2 approved players to generate brackets.</div>';
    }
}

if (isset($_POST['update_match'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF validation failed."); }

    $match_id = $_POST['match_id'];
    $score_a = $_POST['score_a'];
    $score_b = $_POST['score_b'];
    $status = $_POST['match_status'];
    $team_a_id = !empty($_POST['team_a_id']) ? $_POST['team_a_id'] : null;
    $team_b_id = !empty($_POST['team_b_id']) ? $_POST['team_b_id'] : null;

    $winner_id = null;
    if ($score_a > $score_b) $winner_id = $team_a_id;
    elseif ($score_b > $score_a) $winner_id = $team_b_id;

    $stmt = $pdo->prepare("UPDATE matches SET team_a_id = ?, team_b_id = ?, score_a = ?, score_b = ?, winner_id = ?, status = ? WHERE id = ?");
    $stmt->execute([$team_a_id, $team_b_id, $score_a, $score_b, $winner_id, $status, $match_id]);

    // Auto-update tournament status when final match completes
    $stmt = $pdo->prepare("SELECT round FROM matches WHERE id = ?");
    $stmt->execute([$match_id]);
    if ($stmt->fetchColumn() == 'Final' && $status == 'Completed') {
        $stmt = $pdo->prepare("UPDATE tournaments SET status = 'Completed' WHERE id = ?");
        $stmt->execute([$tournament_id]);
        // Champion is now tracked via match results, not tournament.champion_team_id
    }

    $message = '<div class="alert alert-success">Match data saved.</div>';
}

$stmt = $pdo->prepare("SELECT m.*, p1.player_name as team_a, p2.player_name as team_b FROM matches m LEFT JOIN tournament_players p1 ON m.team_a_id = p1.id LEFT JOIN tournament_players p2 ON m.team_b_id = p2.id WHERE m.tournament_id = ? ORDER BY m.round DESC, match_number ASC");
$stmt->execute([$tournament_id]);
$matches = $stmt->fetchAll();

$players = $pdo->query("SELECT id, player_name FROM tournament_players WHERE status = 'Approved' ORDER BY player_name ASC")->fetchAll();

$page_title = "Fixtures & Scoring - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <div>
                <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Fixtures & Scoring</h2>
                <a href="tournaments.php" class="small text-secondary text-decoration-none">← BACK TO TOURNAMENTS</a>
            </div>
            <?php if ($tournament_id): ?>
            <form action="" method="POST" class="d-flex align-items-center gap-2">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label class="small text-secondary mb-0">Format:</label>
                <select name="tournament_format" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: auto;">
                    <option value="knockout">Knockout</option>
                    <option value="round_robin">Round Robin</option>
                    <option value="group_stage">Group + Knockout</option>
                </select>
                <label class="small text-secondary mb-0">Bracket size:</label>
                <select name="bracket_size" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: auto;">
                    <option value="0">Auto</option>
                    <option value="2">Final only</option>
                    <option value="4">Semifinal</option>
                    <option value="8">Quarterfinal</option>
                    <option value="16">Round of 16</option>
                </select>
                <button type="submit" name="generate_brackets" class="btn btn-neon btn-sm px-4">GENERATE BRACKETS</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="row g-4">
                <?php foreach($matches as $m): ?>
                <div class="col-xl-3 col-md-6">
                    <div class="card h-100 p-3 border-info border-opacity-25 shadow">
                        <form action="" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-secondary fw-bold">ROUND: <?php echo strtoupper($m['round']); ?></span>
                                <span class="badge <?php echo $m['status'] == 'Live' ? 'bg-danger pulse' : ($m['status'] == 'Completed' ? 'bg-info' : 'bg-dark'); ?>"><?php echo strtoupper($m['status']); ?></span>
                            </div>

                            <div class="mb-2">
                                <select name="team_a_id" class="form-select form-select-sm bg-dark text-white border-secondary mb-1">
                                    <option value="">Select Player A</option>
                                    <?php foreach($players as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo $m['team_a_id'] == $p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['player_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="score_a" class="form-control form-control-sm bg-black text-white border-secondary text-center" value="<?php echo $m['score_a']; ?>">
                            </div>

                            <div class="text-center my-1 small text-secondary">VS</div>

                            <div class="mb-3">
                                <select name="team_b_id" class="form-select form-select-sm bg-dark text-white border-secondary mb-1">
                                    <option value="">Select Player B</option>
                                    <?php foreach($players as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo $m['team_b_id'] == $p['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['player_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="score_b" class="form-control form-control-sm bg-black text-white border-secondary text-center" value="<?php echo $m['score_b']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-secondary fw-bold">MATCH STATUS</label>
                                <select name="match_status" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <option <?php echo $m['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option <?php echo $m['status'] == 'Live' ? 'selected' : ''; ?>>Live</option>
                                    <option <?php echo $m['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <button type="submit" name="update_match" class="btn btn-sm btn-neon w-100">SAVE MATCH DATA</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($matches)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-secondary opacity-50">NO FIXTURES GENERATED. CLICK 'GENERATE BRACKETS' TO START.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
