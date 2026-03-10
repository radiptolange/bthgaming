<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';
$team_obj = new Team($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_team'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $name = $_POST['team_name'] ?? '';
    $captain_id = $_POST['captain_id'] ?: null;

    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $logo = time() . '_' . $filename;
            $target_dir = "../uploads/teams/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo);
        }
    }

    if ($team_obj->create(['team_name' => $name, 'logo' => $logo, 'captain_id' => $captain_id])) {
        $message = '<div class="alert alert-success">Team created!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error creating team.</div>';
    }
}

$teams = $team_obj->all();
$players = $pdo->query("SELECT id, username FROM players ORDER BY username ASC")->fetchAll();

$page_title = "Manage Teams - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Team Management</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addTeamModal">ADD TEAM</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">LOGO</th>
                                <th>TEAM NAME</th>
                                <th>CAPTAIN</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($teams as $t): ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="../uploads/teams/<?php echo $t['logo'] ?: 'default-team.jpg'; ?>" width="40" height="40" class="rounded border border-secondary">
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($t['team_name']); ?></td>
                                <td><?php echo htmlspecialchars($t['captain_name'] ?: 'None'); ?></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-info" onclick="alert('Feature coming soon')">View</button>
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
<div class="modal fade" id="addTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">CREATE TEAM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">TEAM NAME</label>
                        <input type="text" name="team_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CAPTAIN (Optional)</label>
                        <select name="captain_id" class="form-select">
                            <option value="">Select Player</option>
                            <?php foreach($players as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">TEAM LOGO</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_team" class="btn btn-neon w-100">CREATE TEAM</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
