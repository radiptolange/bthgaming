<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';
$tournament_obj = new Tournament($pdo);

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: tournaments.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tournament'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $name = $_POST['name'] ?? '';
    $game_title = $_POST['game_title'] ?? 'eFootball';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $max_teams = $_POST['max_teams'] ?? 8;
    $prize_info = $_POST['prize_info'] ?? '';
    $status = $_POST['status'] ?? 'Upcoming';
    $format = $_POST['format'] ?? 'Knockout';

    $banner = '';
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['banner']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $banner = time() . '_' . $filename;
            $target_dir = "../uploads/tournaments/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . $banner);
        }
    }

    $data = [
        'name' => $name,
        'game_title' => $game_title,
        'description' => $description,
        'banner' => $banner,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'max_teams' => $max_teams,
        'prize_info' => $prize_info,
        'status' => $status,
        'format' => $format
    ];

    if ($tournament_obj->create($data)) {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">Tournament created successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Error creating tournament.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

$tournaments = $tournament_obj->all();

$page_title = "Manage Tournaments - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-3">
            <nav class="breadcrumb bg-transparent">
                <a class="breadcrumb-item text-secondary" href="../index.php">Public Site</a>
                <a class="breadcrumb-item text-secondary" href="../tournaments.php">View Tournaments</a>
                <span class="breadcrumb-item active text-info">Admin Management</span>
            </nav>
        </div>
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3">TOURNAMENT MANAGEMENT</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addTournamentModal">ADD NEW</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="row g-4">
                <?php foreach($tournaments as $t): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card h-100 border-info border-opacity-25 shadow-lg position-relative overflow-hidden">
                        <!-- Banner Image -->
                        <div class="position-relative" style="height: 180px; overflow: hidden;">
                            <img src="../uploads/tournaments/<?php echo $t['banner'] ?: 'default-pro.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge rounded-pill bg-<?php echo ($t['status'] == 'Ongoing' ? 'success' : ($t['status'] == 'Completed' ? 'info' : ($t['status'] == 'Cancelled' ? 'danger' : 'warning'))); ?> small px-3">
                                    <?php 
                                    $emoji = ($t['status'] == 'Ongoing' ? '🔴 ' : ($t['status'] == 'Completed' ? '✅ ' : ($t['status'] == 'Cancelled' ? '❌ ' : '📅 ')));
                                    echo $emoji . strtoupper($t['status']); 
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Tournament Name -->
                            <h5 class="card-title fw-bold text-info mb-1">🏆 <?php echo htmlspecialchars($t['name']); ?></h5>

                            <!-- Game Title -->
                            <p class="small text-secondary mb-3">
                                🎮 <span class="text-info fw-bold"><?php echo $t['game_title']; ?></span>
                            </p>

                            <!-- Description -->
                            <p class="small text-secondary mb-3">
                                <?php echo substr(htmlspecialchars($t['description']), 0, 80); ?>...
                            </p>

                            <!-- Tournament Details Grid -->
                            <div class="tournament-info-mini mb-3">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">📅 Start</div>
                                            <div class="text-info fw-bold"><?php echo date('M d, Y', strtotime($t['start_date'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">📅 End</div>
                                            <div class="text-info fw-bold"><?php echo date('M d, Y', strtotime($t['end_date'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">👥 Teams</div>
                                            <div class="text-warning fw-bold">
                                                <?php
                                                    $count = $pdo->prepare("SELECT COUNT(*) FROM tournament_players WHERE tournament_id = ? AND status = 'Approved'");
                                                    $count->execute([$t['id']]);
                                                    echo $count->fetchColumn() . " / " . $t['max_teams'];
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">⚙️ Format</div>
                                            <div class="text-info fw-bold"><?php echo htmlspecialchars($t['format'] ?? 'Knockout'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">🏅 Prize</div>
                                            <div class="text-success fw-bold"><?php echo htmlspecialchars($t['prize_info'] ?: 'Trophies'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <a href="edit_tournament.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-warning btn-sm">✏️ Edit Details</a>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <a href="../tournament_details.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-info" title="View public page" target="_blank">🔍 View</a>
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?php echo $t['id']; ?>, '<?php echo htmlspecialchars($t['name']); ?>')" title="Delete">🗑️ Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addTournamentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">CREATE TOURNAMENT</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">TOURNAMENT NAME</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">GAME</label>
                            <select name="game_title" class="form-select">
                                <option>eFootball</option>
                                <option>PUBG Mobile</option>
                                <option>Free Fire</option>
                                <option>COD Mobile</option>
                                <option>FIFA</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">DESCRIPTION</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">START DATE</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">END DATE</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">MAX TEAMS</label>
                            <input type="number" name="max_teams" class="form-control" value="8" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">PRIZE INFO</label>
                            <input type="text" name="prize_info" class="form-control" placeholder="e.g. $1000 + Trophy">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">INITIAL STATUS</label>
                            <select name="status" class="form-select">
                                <option>Upcoming</option>
                                <option>Registration Open</option>
                                <option>Ongoing</option>
                                <option>Completed</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">TOURNAMENT FORMAT</label>
                            <select name="format" class="form-select">
                                <option value="Knockout">🥊 Knockout</option>
                                <option value="Group Stage">👥 Group Stage</option>
                                <option value="League">🏆 League</option>
                                <option value="Multiple Stage">🔄 Multiple Stage</option>
                                <option value="Custom">🎯 Custom</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">BANNER IMAGE</label>
                            <input type="file" name="banner" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_tournament" class="btn btn-neon px-5">CREATE TOURNAMENT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content bg-dark text-light border-danger">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-danger">⚠️ CONFIRM DELETE</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2">Are you sure you want to delete:</p>
                <h6 class="text-info" id="deleteTournamentName"></h6>
                <p class="text-secondary small mt-3">This action cannot be undone!</p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">🗑️ Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteTournamentName').textContent = name;
    document.getElementById('confirmDeleteBtn').href = '?del=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('fade')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
