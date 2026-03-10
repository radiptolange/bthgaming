<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    if (isset($_POST['add_registration'])) {
        $tid = $_POST['tournament_id'];
        $team_id = $_POST['team_id'];
        $cap = $_POST['captain_name'];
        $contact = $_POST['contact_info'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, team_id, captain_name, contact_info, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$tid, $team_id, $cap, $contact, $status])) {
            $message = '<div class="alert alert-success">Registration added!</div>';
        }
    }

    if (isset($_POST['edit_registration'])) {
        $id = $_POST['reg_id'];
        $status = $_POST['status'];
        $cap = $_POST['captain_name'];
        $contact = $_POST['contact_info'];

        $stmt = $pdo->prepare("UPDATE tournament_registrations SET status = ?, captain_name = ?, contact_info = ? WHERE id = ?");
        if ($stmt->execute([$status, $cap, $contact, $id])) {
            $message = '<div class="alert alert-success">Registration updated!</div>';
        }
    }
}

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM tournament_registrations WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: registrations.php");
    exit;
}

$sql = "SELECT tr.*, t.team_name, trn.name as tournament_name
        FROM tournament_registrations tr
        JOIN teams t ON tr.team_id = t.id
        JOIN tournaments trn ON tr.tournament_id = trn.id
        ORDER BY tr.registered_at DESC";
$registrations = $pdo->query($sql)->fetchAll();

$tournaments = $pdo->query("SELECT id, name FROM tournaments ORDER BY name ASC")->fetchAll();
$teams = $pdo->query("SELECT id, team_name FROM teams ORDER BY team_name ASC")->fetchAll();

$page_title = "Team Registrations - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Team Registrations</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addRegModal">ADD REGISTRATION</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">TEAM</th>
                                <th>TOURNAMENT</th>
                                <th>CAPTAIN</th>
                                <th>STATUS</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($registrations as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($r['team_name']); ?></td>
                                <td><small class="text-info"><?php echo htmlspecialchars($r['tournament_name']); ?></small></td>
                                <td><?php echo htmlspecialchars($r['captain_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($r['status'] == 'Approved' ? 'success' : ($r['status'] == 'Pending' ? 'warning' : 'danger')); ?> rounded-pill small px-3">
                                        <?php echo $r['status']; ?>
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editRegModal<?php echo $r['id']; ?>">Edit</button>
                                        <a href="?del=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove registration?')">Del</a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editRegModal<?php echo $r['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content bg-dark text-light border-warning">
                                        <form action="" method="POST">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title text-warning">EDIT REGISTRATION</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="reg_id" value="<?php echo $r['id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">CAPTAIN NAME</label>
                                                    <input type="text" name="captain_name" class="form-control" value="<?php echo htmlspecialchars($r['captain_name']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">CONTACT INFO</label>
                                                    <input type="text" name="contact_info" class="form-control" value="<?php echo htmlspecialchars($r['contact_info']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">STATUS</label>
                                                    <select name="status" class="form-select">
                                                        <option <?php echo $r['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option <?php echo $r['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                        <option <?php echo $r['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary">
                                                <button type="submit" name="edit_registration" class="btn btn-warning w-100">UPDATE REGISTRATION</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addRegModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">ADD TEAM REGISTRATION</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">TOURNAMENT</label>
                        <select name="tournament_id" class="form-select" required>
                            <?php foreach($tournaments as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">TEAM</label>
                        <select name="team_id" class="form-select" required>
                            <?php foreach($teams as $tm): ?>
                                <option value="<?php echo $tm['id']; ?>"><?php echo htmlspecialchars($tm['team_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CAPTAIN NAME</label>
                        <input type="text" name="captain_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CONTACT INFO</label>
                        <input type="text" name="contact_info" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">STATUS</label>
                        <select name="status" class="form-select">
                            <option>Pending</option>
                            <option selected>Approved</option>
                            <option>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_registration" class="btn btn-neon w-100">ADD REGISTRATION</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
