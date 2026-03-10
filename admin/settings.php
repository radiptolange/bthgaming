<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($new_pass === $confirm_pass && strlen($new_pass) >= 6) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        if ($stmt->execute([$hash, $_SESSION['admin_id']])) {
            $message = '<div class="alert alert-success">Password updated successfully!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Passwords do not match or are too short (min 6 chars).</div>';
    }
}

$page_title = "Admin Settings - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-info">
                <div class="card-header border-secondary">
                    <h4 class="neon-text mb-0 pt-2 text-uppercase">Security Settings</h4>
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NEW PASSWORD</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">CONFIRM PASSWORD</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-neon w-100 py-3 fw-bold">UPDATE PASSWORD</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
