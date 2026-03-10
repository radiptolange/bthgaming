<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    if (isset($_POST['update_status'])) {
        $id = $_POST['registration_id'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE tournament_players SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = '<div class="alert alert-success">Player registration status updated!</div>';
        }
    }
}

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM tournament_players WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: player_registrations.php");
    exit;
}

$sql = "SELECT tp.*, tr.name as tournament_name
        FROM tournament_players tp
        JOIN tournaments tr ON tp.tournament_id = tr.id
        ORDER BY tp.registered_at DESC";
$registrations = $pdo->query($sql)->fetchAll();

$page_title = "Manage Player Registrations - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Player Registrations</h2>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">IMAGE</th>
                                <th>PLAYER NAME</th>
                                <th>TOURNAMENT</th>
                                <th>CONTACT</th>
                                <th>STATUS</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($registrations as $reg): ?>
                            <tr>
                                <td class="ps-4">
                                    <?php if ($reg['profile_image']): ?>
                                        <img src="../uploads/tournament_players/<?php echo $reg['profile_image']; ?>" width="40" height="40" class="rounded border border-secondary" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            <small class="text-white">No IMG</small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($reg['player_name']); ?></td>
                                <td><?php echo htmlspecialchars($reg['tournament_name']); ?></td>
                                <td class="small text-secondary"><?php echo htmlspecialchars($reg['contact_info']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($reg['status'] == 'Approved') ? 'success' : (($reg['status'] == 'Rejected') ? 'danger' : 'warning'); ?>"><?php echo $reg['status']; ?></span>
                                </td>
                                <td class="text-center pe-4">
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                        <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary d-inline-block" style="width: auto;">
                                            <option value="Pending" <?php echo ($reg['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo ($reg['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Rejected" <?php echo ($reg['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-outline-info ms-1">Update</button>
                                    </form>
                                    <a href="?del=<?php echo $reg['id']; ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Remove this registration?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (empty($registrations)): ?>
            <div class="text-center py-5">
                <p class="text-secondary opacity-50">NO PLAYER REGISTRATIONS YET.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
