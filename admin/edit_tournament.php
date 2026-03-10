<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';
$tournament_obj = new Tournament($pdo);
$id = $_GET['id'] ?? null;
if (!$id) { header("Location: tournaments.php"); exit; }

$tournament = $tournament_obj->find($id);
if (!$tournament) { header("Location: tournaments.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tournament'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF validation failed."); }

    $banner = $tournament['banner'];
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['banner']['name'];
        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed)) {
            $banner = time() . '_' . $filename;
            $target_dir = "../uploads/tournaments/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . $banner);
        }
    }

    $data = [
        'name' => $_POST['name'],
        'game_title' => $_POST['game_title'],
        'description' => $_POST['description'],
        'banner' => $banner,
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'max_teams' => $_POST['max_teams'],
        'prize_info' => $_POST['prize_info'],
        'status' => $_POST['status'],
        'format' => $_POST['format']
    ];

    if ($tournament_obj->update($id, $data)) {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">Tournament updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        $tournament = $tournament_obj->find($id);
    }
}

$page_title = "Edit Tournament - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="neon-text mb-0">EDIT TOURNAMENT</h3>
                    <a href="tournaments.php" class="btn btn-sm btn-outline-secondary">BACK</a>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <?php echo $message; ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="row mb-4">
                            <div class="col-md-8 mb-3">
                                <label class="form-label small fw-bold">TOURNAMENT NAME</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($tournament['name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">GAME</label>
                                <input type="text" name="game_title" class="form-control" value="<?php echo htmlspecialchars($tournament['game_title']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">DESCRIPTION</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($tournament['description']); ?></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">START DATE</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $tournament['start_date']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">END DATE</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $tournament['end_date']; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">MAX TEAMS</label>
                                <input type="number" name="max_teams" class="form-control" value="<?php echo $tournament['max_teams']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">PRIZE POOL</label>
                                <input type="text" name="prize_info" class="form-control" value="<?php echo htmlspecialchars($tournament['prize_info']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">STATUS</label>
                                <select name="status" class="form-select">
                                    <option <?php echo $tournament['status'] == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option <?php echo $tournament['status'] == 'Registration Open' ? 'selected' : ''; ?>>Registration Open</option>
                                    <option <?php echo $tournament['status'] == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option <?php echo $tournament['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option <?php echo $tournament['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">TOURNAMENT FORMAT</label>
                                <select name="format" class="form-select">
                                    <option value="Knockout" <?php echo ($tournament['format'] ?? 'Knockout') == 'Knockout' ? 'selected' : ''; ?>>🥊 Knockout</option>
                                    <option value="Group Stage" <?php echo ($tournament['format'] ?? 'Knockout') == 'Group Stage' ? 'selected' : ''; ?>>👥 Group Stage</option>
                                    <option value="League" <?php echo ($tournament['format'] ?? 'Knockout') == 'League' ? 'selected' : ''; ?>>🏆 League</option>
                                    <option value="Multiple Stage" <?php echo ($tournament['format'] ?? 'Knockout') == 'Multiple Stage' ? 'selected' : ''; ?>>🔄 Multiple Stage</option>
                                    <option value="Custom" <?php echo ($tournament['format'] ?? 'Knockout') == 'Custom' ? 'selected' : ''; ?>>🎯 Custom</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">BANNER IMAGE</label>
                            <input type="file" name="banner" class="form-control" accept="image/*">
                            <?php if ($tournament['banner']): ?>
                                <small class="text-secondary">Current: <?php echo $tournament['banner']; ?></small>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="update_tournament" class="btn btn-neon w-100 py-3">UPDATE TOURNAMENT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
