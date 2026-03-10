<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

// ensure gallery table exists with category column
$pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) DEFAULT '',
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Gallery',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
// add category column if missing (older installations)
$col = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'category'")->fetch();
if (!$col) {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN category VARCHAR(100) DEFAULT 'Gallery'");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_gallery'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $title = $_POST['title'] ?? 'Gallery Image';
    $category = $_POST['category'] ?? 'Gallery';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed)) {
            $img = time() . '_' . $filename;
            $target_dir = "../uploads/gallery/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $img);

            $stmt = $pdo->prepare("INSERT INTO gallery (image, title, category) VALUES (?, ?, ?)");
            $stmt->execute([$img, $title, $category]);
            $message = '<div class="alert alert-success">Image uploaded!</div>';
        }
    }
}

if (isset($_POST['edit_gallery'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    $id = $_POST['gallery_id'];
    $title = $_POST['title'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("UPDATE gallery SET title = ?, category = ? WHERE id = ?");
    if ($stmt->execute([$title, $category, $id])) {
        $message = '<div class="alert alert-success">Gallery item updated!</div>';
    }
}

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../uploads/gallery/" . $img)) unlink("../uploads/gallery/" . $img);

    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: gallery_manage.php");
    exit;
}

$gallery = $pdo->query("SELECT * FROM gallery ORDER BY uploaded_at DESC")->fetchAll();

$page_title = "Manage Gallery - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Manage Gallery</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#uploadGalleryModal">UPLOAD PHOTO</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="row g-3">
                <?php foreach($gallery as $g): ?>
                <div class="col-md-3">
                    <div class="card h-100 border-secondary overflow-hidden">
                        <img src="../uploads/gallery/<?php echo $g['image']; ?>" class="card-img-top" height="150" style="object-fit:cover;">
                        <div class="card-body p-2 text-center">
                            <h6 class="small fw-bold text-white text-truncate"><?php echo htmlspecialchars($g['title']); ?></h6>
                            <div class="btn-group w-100 mt-2">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editGalleryModal<?php echo $g['id']; ?>">EDIT</button>
                                <a href="?del=<?php echo $g['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete photo?')">DEL</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editGalleryModal<?php echo $g['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark text-light border-warning">
                            <form action="" method="POST">
                                <div class="modal-header border-secondary">
                                    <h5 class="modal-title text-warning">EDIT GALLERY ITEM</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="gallery_id" value="<?php echo $g['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">PHOTO TITLE</label>
                                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($g['title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">CATEGORY</label>
                                        <select name="category" class="form-select">
                                            <option <?php echo $g['category'] == 'Tournament' ? 'selected' : ''; ?>>Tournament</option>
                                            <option <?php echo $g['category'] == 'Community' ? 'selected' : ''; ?>>Community</option>
                                            <option <?php echo $g['category'] == 'Match Highlight' ? 'selected' : ''; ?>>Match Highlight</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer border-secondary">
                                    <button type="submit" name="edit_gallery" class="btn btn-warning w-100">SAVE CHANGES</button>
                                </div>
                            </form>
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
<div class="modal fade" id="uploadGalleryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">UPLOAD TO GALLERY</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">PHOTO TITLE</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">CATEGORY</label>
                        <select name="category" class="form-select">
                            <option>Tournament</option>
                            <option>Community</option>
                            <option>Match Highlight</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SELECT IMAGE</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="upload_gallery" class="btn btn-neon w-100">UPLOAD PHOTO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
