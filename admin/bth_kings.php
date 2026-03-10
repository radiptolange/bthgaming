<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

// make sure bth_kings table (and column) exists to avoid schema errors
$pdo->exec("CREATE TABLE IF NOT EXISTS bth_kings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generation INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slayed_player VARCHAR(100),
    reign_days INT DEFAULT 0,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// if table existed previously without profile_image column, add it
$col = $pdo->query("SHOW COLUMNS FROM bth_kings LIKE 'profile_image'")->fetch();
if (!$col) {
    $pdo->exec("ALTER TABLE bth_kings ADD COLUMN profile_image VARCHAR(255) DEFAULT ''");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    if (isset($_POST['add_king'])) {
        $gen = $_POST['generation'];
        $name = $_POST['name'];
        $slayed = $_POST['slayed_player'];
        $reign = $_POST['reign_days'];
        
        $image = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $image = time() . '_' . $filename;
                $target_dir = "../uploads/kings/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $image);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO bth_kings (generation, name, slayed_player, reign_days, profile_image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$gen, $name, $slayed, $reign, $image])) {
            $message = '<div class="alert alert-success">BTH King added!</div>';
        }
    }

    if (isset($_POST['edit_king'])) {
        $id = $_POST['king_id'];
        $gen = $_POST['generation'];
        $name = $_POST['name'];
        $slayed = $_POST['slayed_player'];
        $reign = $_POST['reign_days'];
        
        // Get current image
        $stmt = $pdo->prepare("SELECT profile_image FROM bth_kings WHERE id = ?");
        $stmt->execute([$id]);
        $current_image = $stmt->fetchColumn();
        $image = $current_image;
        
        // Handle new image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                // Delete old image if exists
                if ($current_image && file_exists("../uploads/kings/" . $current_image)) {
                    unlink("../uploads/kings/" . $current_image);
                }
                $image = time() . '_' . $filename;
                $target_dir = "../uploads/kings/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $image);
            }
        }

        $stmt = $pdo->prepare("UPDATE bth_kings SET generation = ?, name = ?, slayed_player = ?, reign_days = ?, profile_image = ? WHERE id = ?");
        if ($stmt->execute([$gen, $name, $slayed, $reign, $image, $id])) {
            $message = '<div class="alert alert-success">BTH King updated!</div>';
        }
    }
}

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM bth_kings WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: bth_kings.php");
    exit;
}

$kings = $pdo->query("SELECT * FROM bth_kings ORDER BY generation DESC")->fetchAll();

$page_title = "Manage BTH Kings - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">BTH King Rankings</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addKingModal">ADD GENERATION</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">GEN</th>
                                <th>NAME</th>
                                <th>SLAYED PLAYER</th>
                                <th>REIGN DAYS</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kings as $k): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-info"><?php echo $k['generation']; ?></td>
                                <td class="text-white fw-bold"><?php echo htmlspecialchars($k['name']); ?></td>
                                <td><?php echo htmlspecialchars($k['slayed_player']); ?></td>
                                <td><?php echo $k['reign_days']; ?> Days</td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editKingModal<?php echo $k['id']; ?>">Edit</button>
                                        <a href="?del=<?php echo $k['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this king?')">Del</a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editKingModal<?php echo $k['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content bg-dark text-light border-warning">
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title text-warning">EDIT KING: GEN <?php echo $k['generation']; ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="king_id" value="<?php echo $k['id']; ?>">
                                                <?php if ($k['profile_image']): ?>
                                                <div class="mb-3 text-center">
                                                    <img src="../uploads/kings/<?php echo $k['profile_image']; ?>" class="rounded border border-warning" width="100" height="100" style="object-fit: cover;">
                                                </div>
                                                <?php endif; ?>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">PROFILE IMAGE</label>
                                                    <input type="file" name="profile_image" class="form-control" accept="image/*">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">GENERATION NUMBER</label>
                                                    <input type="number" name="generation" class="form-control" value="<?php echo $k['generation']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">KING NAME</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($k['name']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">SLAYED PLAYER</label>
                                                    <input type="text" name="slayed_player" class="form-control" value="<?php echo htmlspecialchars($k['slayed_player']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">REIGN DAYS</label>
                                                    <input type="number" name="reign_days" class="form-control" value="<?php echo $k['reign_days']; ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary">
                                                <button type="submit" name="edit_king" class="btn btn-warning w-100">SAVE CHANGES</button>
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
<div class="modal fade" id="addKingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">ADD NEW BTH KING</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">PROFILE IMAGE</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">GENERATION NUMBER</label>
                        <input type="number" name="generation" class="form-control" required placeholder="e.g. 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">KING NAME</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SLAYED PLAYER</label>
                        <input type="text" name="slayed_player" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">REIGN DAYS</label>
                        <input type="number" name="reign_days" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_king" class="btn btn-neon w-100">ADD KING</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
