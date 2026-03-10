<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_player'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $username = $_POST['username'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $game_id = $_POST['game_id'] ?? '';
    $country = $_POST['country'] ?? '';
    $role = $_POST['role'] ?? 'Player';

    $img = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['profile_image']['name'];
        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed)) {
            $img = time() . '_' . $filename;
            $target_dir = "../uploads/players/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $img);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO players (username, full_name, game_id, country, role, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $full_name, $game_id, $country, $role, $img])) {
        $message = '<div class="alert alert-success">Player added!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error adding player.</div>';
    }
}

$players = $pdo->query("SELECT * FROM players ORDER BY username ASC")->fetchAll();

$page_title = "Player Database - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Player Database</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addPlayerModal">ADD PLAYER</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">PLAYER</th>
                                <th>GAME ID</th>
                                <th>COUNTRY</th>
                                <th>ROLE</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($players as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <div class="d-flex align-items-center">
                                        <img src="../uploads/players/<?php echo $p['profile_image'] ?: 'default-player.jpg'; ?>" width="35" height="35" class="rounded-circle me-3 border border-secondary">
                                        <div>
                                            <?php echo htmlspecialchars($p['username']); ?>
                                            <small class="d-block text-secondary"><?php echo htmlspecialchars($p['full_name']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><small class="text-info"><?php echo htmlspecialchars($p['game_id']); ?></small></td>
                                <td><?php echo htmlspecialchars($p['country']); ?></td>
                                <td><span class="badge bg-dark border border-secondary small"><?php echo $p['role']; ?></span></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-info" onclick="alert('Profile view coming soon')">Profile</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">REGISTER PLAYER</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">USERNAME</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">FULL NAME</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">GAME ID</label>
                        <input type="text" name="game_id" class="form-control" placeholder="e.g. 123-456-789">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">COUNTRY</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">ROLE</label>
                            <select name="role" class="form-select">
                                <option>Player</option>
                                <option>Captain</option>
                                <option>Substitute</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">PROFILE IMAGE</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_player" class="btn btn-neon w-100">REGISTER PLAYER</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
