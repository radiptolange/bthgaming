<?php
require_once __DIR__ . '/config/config.php';

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$game = $_GET['game'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9; // 9 tournaments per page (3x3 grid)

$sql = "SELECT * FROM tournaments WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}
if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}
if ($game) {
    $sql .= " AND game_title = ?";
    $params[] = $game;
}

// Get total count for pagination
$count_sql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_tournaments = $count_stmt->fetchColumn();
$total_pages = ceil($total_tournaments / $per_page);

// Add pagination to main query
$sql .= " ORDER BY id DESC LIMIT " . (($page - 1) * $per_page) . ", $per_page";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tournaments = $stmt->fetchAll();

$page_title = "Tournaments - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-4 fw-bold">ELITE TOURNAMENTS</h1>
            <p class="text-secondary lead">Discover and participate in professional eSports competitions.</p>
        </div>

        <!-- Filters -->
        <div class="col-12 mb-5">
            <div class="card bg-dark border-secondary p-4 shadow">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">SEARCH TITLE</label>
                        <input type="text" name="search" class="form-control bg-black text-light border-secondary" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">STATUS</label>
                        <select name="status" class="form-select bg-black text-light border-secondary">
                            <option value="">All Status</option>
                            <option value="Upcoming" <?php echo $status == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="Registration Open" <?php echo $status == 'Registration Open' ? 'selected' : ''; ?>>Registration Open</option>
                            <option value="Ongoing" <?php echo $status == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">GAME</label>
                        <input type="text" name="game" class="form-control bg-black text-light border-secondary" value="<?php echo htmlspecialchars($game); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-neon w-100 py-2">FILTER</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tournament List -->
        <div class="col-12">
            <div class="row g-4">
                <?php foreach($tournaments as $t): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-info border-opacity-25 shadow-lg position-relative overflow-hidden">
                        <!-- Banner Image -->
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <img src="uploads/tournaments/<?php echo $t['banner'] ?: 'default-pro.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;">
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
                            <h5 class="card-title fw-bold text-info mb-2">🏆 <?php echo htmlspecialchars($t['name']); ?></h5>

                            <!-- Game Title -->
                            <p class="small text-secondary mb-3">
                                🎮 <span class="text-warning fw-bold"><?php echo $t['game_title']; ?></span>
                            </p>

                            <!-- Description -->
                            <p class="small text-secondary mb-3">
                                <?php echo substr(htmlspecialchars($t['description']), 0, 100); ?>...
                            </p>

                            <!-- Tournament Details Grid -->
                            <div class="tournament-info-mini mb-3">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">📅 Start</div>
                                            <div class="text-info fw-bold"><?php echo date('M d', strtotime($t['start_date'])); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-dark bg-opacity-50 p-2 rounded border border-secondary border-opacity-25">
                                            <div class="text-secondary">📅 End</div>
                                            <div class="text-info fw-bold"><?php echo date('M d', strtotime($t['end_date'])); ?></div>
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

                            <!-- Action Button -->
                            <a href="tournament_details.php?id=<?php echo $t['id']; ?>" class="btn btn-neon btn-sm w-100">🔍 View Full Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link bg-dark border-secondary text-light" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link bg-dark border-secondary text-light" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link bg-dark border-secondary text-light" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
