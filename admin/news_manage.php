<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCSRFToken($token)) { die("CSRF Token validation failed."); }

    if (isset($_POST['add_news'])) {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $author = $_SESSION['admin_username'];

        $img = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            if (in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $allowed)) {
                $img = time() . '_' . $filename;
                $target_dir = "../uploads/news/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $img);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO news (title, content, image, author) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $img, $author])) {
            $message = '<div class="alert alert-success">News posted!</div>';
        }
    }

    if (isset($_POST['edit_news'])) {
        $id = $_POST['news_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];

        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $id])) {
            $message = '<div class="alert alert-success">News updated!</div>';
        }
    }
}

if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../uploads/news/" . $img)) unlink("../uploads/news/" . $img);

    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: news_manage.php");
    exit;
}

$news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();

$page_title = "Manage News - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h2 class="neon-text border-start border-4 border-info ps-3 text-uppercase">Manage News</h2>
            <button type="button" class="btn btn-neon btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addNewsModal">POST NEWS</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="row g-4">
                <?php foreach($news as $n): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-secondary">
                        <img src="../uploads/news/<?php echo $n['image'] ?: 'default-news.jpg'; ?>" class="card-img-top" height="150" style="object-fit:cover;">
                        <div class="card-body">
                            <h6 class="fw-bold text-white"><?php echo htmlspecialchars($n['title']); ?></h6>
                            <p class="small text-secondary"><?php echo substr(strip_tags($n['content']), 0, 80); ?>...</p>
                            <div class="btn-group w-100 mt-2">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editNewsModal<?php echo $n['id']; ?>">Edit</button>
                                <a href="?del=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete news?')">Del</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editNewsModal<?php echo $n['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark text-light border-warning">
                            <form action="" method="POST">
                                <div class="modal-header border-secondary">
                                    <h5 class="modal-title text-warning">EDIT NEWS</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="news_id" value="<?php echo $n['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-info">TITLE</label>
                                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($n['title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-info">CONTENT</label>
                                        <textarea name="content" class="form-control" rows="6" required><?php echo htmlspecialchars($n['content']); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-secondary">
                                    <button type="submit" name="edit_news" class="btn btn-warning w-100">SAVE CHANGES</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light border-info">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title neon-text">POST NEWS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-info">TITLE</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-info">CONTENT</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-info">IMAGE</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" name="add_news" class="btn btn-neon w-100">POST NEWS</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
