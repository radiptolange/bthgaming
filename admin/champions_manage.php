<?php
require_once __DIR__ . '/../includes/auth_check.php';

$auth = new Auth($pdo);
$message = '';

// make sure hall_of_fame table has image columns for each winner
$pdo->exec("CREATE TABLE IF NOT EXISTS hall_of_fame (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_name VARCHAR(150) NOT NULL,
    winner_1st VARCHAR(100),
    winner_2nd VARCHAR(100),
    winner_3rd VARCHAR(100),
    top_scorer VARCHAR(100),
    game_title VARCHAR(100) DEFAULT 'eFootball',
    end_date DATE,
    winner_1st_img VARCHAR(255),
    winner_2nd_img VARCHAR(255),
    winner_3rd_img VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
// add columns individually if table existed earlier
foreach (['winner_1st_img','winner_2nd_img','winner_3rd_img'] as $col) {
    $exists = $pdo->query("SHOW COLUMNS FROM hall_of_fame LIKE '$col'")->fetch();
    if (!$exists) {
        $pdo->exec("ALTER TABLE hall_of_fame ADD COLUMN $col VARCHAR(255)");
    }
}

if (isset($_POST['add_entry'])) {
    $tournament_name = $_POST['tournament_name'];
    $game_title = $_POST['game_title'];
    $end_date = $_POST['end_date'];
    $top_scorer = $_POST['top_scorer'];

    // winners may be disabled via checkboxes
    $winner_1st = isset($_POST['has_1st']) ? $_POST['winner_1st'] : '';
    $winner_2nd = isset($_POST['has_2nd']) ? $_POST['winner_2nd'] : '';
    $winner_3rd = isset($_POST['has_3rd']) ? $_POST['winner_3rd'] : '';

    // handle uploaded images
    $winner_1st_img = '';
    $winner_2nd_img = '';
    $winner_3rd_img = '';
    $target_dir = "../uploads/hall_of_fame/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (isset($_POST['has_1st']) && isset($_FILES['winner_1st_img']) && $_FILES['winner_1st_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $fn = $_FILES['winner_1st_img']['name'];
        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $winner_1st_img = time().'_1st_'.basename($fn);
            move_uploaded_file($_FILES['winner_1st_img']['tmp_name'], $target_dir . $winner_1st_img);
        }
    }
    if (isset($_POST['has_2nd']) && isset($_FILES['winner_2nd_img']) && $_FILES['winner_2nd_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $fn = $_FILES['winner_2nd_img']['name'];
        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $winner_2nd_img = time().'_2nd_'.basename($fn);
            move_uploaded_file($_FILES['winner_2nd_img']['tmp_name'], $target_dir . $winner_2nd_img);
        }
    }
    if (isset($_POST['has_3rd']) && isset($_FILES['winner_3rd_img']) && $_FILES['winner_3rd_img']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $fn = $_FILES['winner_3rd_img']['name'];
        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $winner_3rd_img = time().'_3rd_'.basename($fn);
            move_uploaded_file($_FILES['winner_3rd_img']['tmp_name'], $target_dir . $winner_3rd_img);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO hall_of_fame (tournament_name, winner_1st, winner_2nd, winner_3rd, top_scorer, game_title, end_date, winner_1st_img, winner_2nd_img, winner_3rd_img) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$tournament_name, $winner_1st, $winner_2nd, $winner_3rd, $top_scorer, $game_title, $end_date, $winner_1st_img, $winner_2nd_img, $winner_3rd_img])) {
        $message = '<div class="alert alert-success">Hall of Fame entry added successfully!</div>';
    }
}

if (isset($_POST['edit_entry'])) {
    $id = $_POST['id'];
    $tournament_name = $_POST['tournament_name'];
    $game_title = $_POST['game_title'];
    $end_date = $_POST['end_date'];
    $top_scorer = $_POST['top_scorer'];

    // winners flags
    $winner_1st = isset($_POST['has_1st']) ? $_POST['winner_1st'] : '';
    $winner_2nd = isset($_POST['has_2nd']) ? $_POST['winner_2nd'] : '';
    $winner_3rd = isset($_POST['has_3rd']) ? $_POST['winner_3rd'] : '';

    // current images
    $stmt = $pdo->prepare("SELECT winner_1st_img, winner_2nd_img, winner_3rd_img FROM hall_of_fame WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $winner_1st_img = $row['winner_1st_img'];
    $winner_2nd_img = $row['winner_2nd_img'];
    $winner_3rd_img = $row['winner_3rd_img'];

    $target_dir = "../uploads/hall_of_fame/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (isset($_POST['has_1st'])) {
        if (isset($_FILES['winner_1st_img']) && $_FILES['winner_1st_img']['error'] == 0) {
            if ($winner_1st_img && file_exists($target_dir.$winner_1st_img)) unlink($target_dir.$winner_1st_img);
            $fn = $_FILES['winner_1st_img']['name'];
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed)) {
                $winner_1st_img = time().'_1st_'.basename($fn);
                move_uploaded_file($_FILES['winner_1st_img']['tmp_name'], $target_dir.$winner_1st_img);
            }
        }
    } else {
        // remove name and image if checkbox unchecked
        $winner_1st_img && file_exists($target_dir.$winner_1st_img) && unlink($target_dir.$winner_1st_img);
        $winner_1st_img = '';
    }
    if (isset($_POST['has_2nd'])) {
        if (isset($_FILES['winner_2nd_img']) && $_FILES['winner_2nd_img']['error'] == 0) {
            if ($winner_2nd_img && file_exists($target_dir.$winner_2nd_img)) unlink($target_dir.$winner_2nd_img);
            $fn = $_FILES['winner_2nd_img']['name'];
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed)) {
                $winner_2nd_img = time().'_2nd_'.basename($fn);
                move_uploaded_file($_FILES['winner_2nd_img']['tmp_name'], $target_dir.$winner_2nd_img);
            }
        }
    } else {
        $winner_2nd_img && file_exists($target_dir.$winner_2nd_img) && unlink($target_dir.$winner_2nd_img);
        $winner_2nd_img = '';
    }
    if (isset($_POST['has_3rd'])) {
        if (isset($_FILES['winner_3rd_img']) && $_FILES['winner_3rd_img']['error'] == 0) {
            if ($winner_3rd_img && file_exists($target_dir.$winner_3rd_img)) unlink($target_dir.$winner_3rd_img);
            $fn = $_FILES['winner_3rd_img']['name'];
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed)) {
                $winner_3rd_img = time().'_3rd_'.basename($fn);
                move_uploaded_file($_FILES['winner_3rd_img']['tmp_name'], $target_dir.$winner_3rd_img);
            }
        }
    } else {
        $winner_3rd_img && file_exists($target_dir.$winner_3rd_img) && unlink($target_dir.$winner_3rd_img);
        $winner_3rd_img = '';
    }

    $stmt = $pdo->prepare("UPDATE hall_of_fame SET tournament_name = ?, winner_1st = ?, winner_2nd = ?, winner_3rd = ?, top_scorer = ?, game_title = ?, end_date = ?, winner_1st_img = ?, winner_2nd_img = ?, winner_3rd_img = ? WHERE id = ?");
    if ($stmt->execute([$tournament_name, $winner_1st, $winner_2nd, $winner_3rd, $top_scorer, $game_title, $end_date, $winner_1st_img, $winner_2nd_img, $winner_3rd_img, $id])) {
        $message = '<div class="alert alert-success">Hall of Fame entry updated successfully!</div>';
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // remove any associated images
    $stmt = $pdo->prepare("SELECT winner_1st_img, winner_2nd_img, winner_3rd_img FROM hall_of_fame WHERE id = ?");
    $stmt->execute([$id]);
    $imgs = $stmt->fetch();
    if ($imgs) {
        foreach (['winner_1st_img','winner_2nd_img','winner_3rd_img'] as $col) {
            if (!empty($imgs[$col])) {
                $path = __DIR__ . '/../uploads/hall_of_fame/' . $imgs[$col];
                if (file_exists($path)) unlink($path);
            }
        }
    }
    $stmt = $pdo->prepare("DELETE FROM hall_of_fame WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="alert alert-success">Hall of Fame entry deleted successfully!</div>';
    }
}

$entries = $pdo->query("SELECT * FROM hall_of_fame ORDER BY end_date DESC")->fetchAll();

$page_title = "Manage Hall of Fame - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-4 border-bottom border-secondary pb-3 text-center">
            <h2 class="neon-text text-uppercase">Hall of Fame Management</h2>
            <p class="text-secondary small">Add, edit, and manage Hall of Fame entries.</p>
        </div>

        <div class="col-12 mb-4">
            <button class="btn btn-neon" data-bs-toggle="modal" data-bs-target="#addModal">Add New Entry</button>
        </div>

        <div class="col-12">
            <?php echo $message; ?>
            <div class="card shadow p-0 border-secondary">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead class="bg-black">
                            <tr class="small text-secondary fw-bold border-bottom border-secondary">
                                <th class="ps-4">TOURNAMENT</th>
                                <th>1ST PLACE</th>
                                <th>2ND PLACE</th>
                                <th>3RD PLACE</th>
                                <th>TOP SCORER</th>
                                <th>GAME</th>
                                <th>DATE</th>
                                <th class="text-center pe-4">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($entries as $entry): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($entry['tournament_name']); ?></td>
                                <td>
                                    <?php if ($entry['winner_1st']) {
                                        echo htmlspecialchars($entry['winner_1st']);
                                        if ($entry['winner_1st_img']) echo '<br><img src="../uploads/hall_of_fame/'.htmlspecialchars($entry['winner_1st_img']).'" width="40" class="mt-1">';
                                    } else echo '-';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($entry['winner_2nd']) {
                                        echo htmlspecialchars($entry['winner_2nd']);
                                        if ($entry['winner_2nd_img']) echo '<br><img src="../uploads/hall_of_fame/'.htmlspecialchars($entry['winner_2nd_img']).'" width="40" class="mt-1">';
                                    } else echo '-';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($entry['winner_3rd']) {
                                        echo htmlspecialchars($entry['winner_3rd']);
                                        if ($entry['winner_3rd_img']) echo '<br><img src="../uploads/hall_of_fame/'.htmlspecialchars($entry['winner_3rd_img']).'" width="40" class="mt-1">';
                                    } else echo '-';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($entry['top_scorer'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($entry['game_title']); ?></td>
                                <td><?php echo $entry['end_date'] ? date('M Y', strtotime($entry['end_date'])) : '-'; ?></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-warning me-2" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $entry['id']; ?>">Edit</button>
                                    <a href="?delete=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $entry['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content bg-dark text-white">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title">Edit Hall of Fame Entry</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="" method="POST" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tournament Name</label>
                                                        <input type="text" name="tournament_name" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($entry['tournament_name']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Game Title</label>
                                                        <input type="text" name="game_title" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($entry['game_title']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch mb-1">
                                                            <input class="form-check-input" type="checkbox" id="has1st<?php echo $entry['id']; ?>" name="has_1st" <?php echo $entry['winner_1st'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="has1st<?php echo $entry['id']; ?>">Include 1st Place</label>
                                                        </div>
                                                        <label class="form-label">1st Place Winner</label>
                                                        <input type="text" name="winner_1st" class="form-control bg-dark text-white border-secondary winner-field" value="<?php echo htmlspecialchars($entry['winner_1st']); ?>" <?php echo $entry['winner_1st'] ? '' : 'disabled'; ?> >
                                                        <label class="form-label mt-2">1st Place Image</label>
                                                        <input type="file" name="winner_1st_img" class="form-control bg-dark text-white border-secondary" accept="image/*" <?php echo $entry['winner_1st'] ? '' : 'disabled'; ?> >
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch mb-1">
                                                            <input class="form-check-input" type="checkbox" id="has2nd<?php echo $entry['id']; ?>" name="has_2nd" <?php echo $entry['winner_2nd'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="has2nd<?php echo $entry['id']; ?>">Include 2nd Place</label>
                                                        </div>
                                                        <label class="form-label">2nd Place Winner</label>
                                                        <input type="text" name="winner_2nd" class="form-control bg-dark text-white border-secondary winner-field" value="<?php echo htmlspecialchars($entry['winner_2nd']); ?>" <?php echo $entry['winner_2nd'] ? '' : 'disabled'; ?> >
                                                        <label class="form-label mt-2">2nd Place Image</label>
                                                        <input type="file" name="winner_2nd_img" class="form-control bg-dark text-white border-secondary" accept="image/*" <?php echo $entry['winner_2nd'] ? '' : 'disabled'; ?> >
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch mb-1">
                                                            <input class="form-check-input" type="checkbox" id="has3rd<?php echo $entry['id']; ?>" name="has_3rd" <?php echo $entry['winner_3rd'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="has3rd<?php echo $entry['id']; ?>">Include 3rd Place</label>
                                                        </div>
                                                        <label class="form-label">3rd Place Winner</label>
                                                        <input type="text" name="winner_3rd" class="form-control bg-dark text-white border-secondary winner-field" value="<?php echo htmlspecialchars($entry['winner_3rd']); ?>" <?php echo $entry['winner_3rd'] ? '' : 'disabled'; ?> >
                                                        <label class="form-label mt-2">3rd Place Image</label>
                                                        <input type="file" name="winner_3rd_img" class="form-control bg-dark text-white border-secondary" accept="image/*" <?php echo $entry['winner_3rd'] ? '' : 'disabled'; ?> >
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Top Scorer</label>
                                                        <input type="text" name="top_scorer" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($entry['top_scorer']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" name="end_date" class="form-control bg-dark text-white border-secondary" value="<?php echo $entry['end_date']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="edit_entry" class="btn btn-neon">Update Entry</button>
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
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Add Hall of Fame Entry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tournament Name</label>
                            <input type="text" name="tournament_name" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Game Title</label>
                            <input type="text" name="game_title" class="form-control bg-dark text-white border-secondary" value="eFootball">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="has1st_new" name="has_1st" checked>
                                <label class="form-check-label" for="has1st_new">Include 1st Place</label>
                            </div>
                            <label class="form-label">1st Place Winner</label>
                            <input type="text" name="winner_1st" class="form-control bg-dark text-white border-secondary winner-field">
                            <label class="form-label mt-2">1st Place Image</label>
                            <input type="file" name="winner_1st_img" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="has2nd_new" name="has_2nd" checked>
                                <label class="form-check-label" for="has2nd_new">Include 2nd Place</label>
                            </div>
                            <label class="form-label">2nd Place Winner</label>
                            <input type="text" name="winner_2nd" class="form-control bg-dark text-white border-secondary winner-field">
                            <label class="form-label mt-2">2nd Place Image</label>
                            <input type="file" name="winner_2nd_img" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="has3rd_new" name="has_3rd" checked>
                                <label class="form-check-label" for="has3rd_new">Include 3rd Place</label>
                            </div>
                            <label class="form-label">3rd Place Winner</label>
                            <input type="text" name="winner_3rd" class="form-control bg-dark text-white border-secondary winner-field">
                            <label class="form-label mt-2">3rd Place Image</label>
                            <input type="file" name="winner_3rd_img" class="form-control bg-dark text-white border-secondary" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Top Scorer</label>
                            <input type="text" name="top_scorer" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_entry" class="btn btn-neon">Add Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// enable/disable winner fields based on checkbox
function toggleWinner(checkbox, prefix) {
    var checked = checkbox.checked;
    var container = checkbox.closest('.col-md-6');
    var inputs = container.querySelectorAll('.winner-field, input[type=file]');
    inputs.forEach(function(inp){ inp.disabled = !checked; });
}
document.querySelectorAll('input[name^="has_"]').forEach(function(cb){
    cb.addEventListener('change', function(){ toggleWinner(cb); });
    // initialize state
    toggleWinner(cb);
});
</script>

<?php include '../includes/footer.php'; ?>
