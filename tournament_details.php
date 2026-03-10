<?php
require_once __DIR__ . '/config/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$tournament_obj = new Tournament($pdo);
$t = $tournament_obj->find($id);
if (!$t) { header("Location: index.php"); exit; }

$matches_obj = new MatchManager($pdo);
$matches = $matches_obj->getByTournament($id);

// registrations not needed here; counts are queried inline if required
// $registrations = $tournament_obj->getRegistrations($id);

$message = $_GET['message'] ?? '';

$page_title = htmlspecialchars($t['name']) . " - " . SITE_NAME;
include 'includes/header.php';
?>

<!-- Tournament Header -->
<section class="py-5 bg-black border-bottom border-info border-opacity-10">
    <div class="container py-lg-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Main Tournament Card -->
                <div class="card border-info border-opacity-25 shadow-lg overflow-hidden">
                    <!-- Banner Image -->
                    <div class="position-relative" style="height: 300px; overflow: hidden;">
                        <img src="uploads/tournaments/<?php echo $t['banner'] ?: 'default-pro.jpg'; ?>" class="w-100 h-100" style="object-fit: cover;">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge rounded-pill bg-<?php echo ($t['status'] == 'Ongoing' ? 'success' : ($t['status'] == 'Completed' ? 'info' : 'warning')); ?> px-4 py-2" style="font-size: 14px;">
                                <?php 
                                $emoji = ($t['status'] == 'Ongoing' ? '🔴 ' : ($t['status'] == 'Completed' ? '✅ ' : '📅 '));
                                echo $emoji . strtoupper($t['status']); 
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Tournament Name -->
                        <h1 class="display-5 fw-bold text-info mb-2">🏆 <?php echo htmlspecialchars($t['name']); ?></h1>

                        <!-- Game Title -->
                        <p class="lead mb-4">
                            🎮 <span class="text-warning fw-bold text-uppercase"><?php echo $t['game_title']; ?></span>
                        </p>

                        <!-- Description -->
                        <div class="mb-4">
                            <h5 class="text-secondary mb-2">📝 Description</h5>
                            <p class="text-secondary lead"><?php echo nl2br(htmlspecialchars($t['description'])); ?></p>
                        </div>

                        <!-- Tournament Details Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                                    <div class="text-secondary small fw-bold mb-1">📅 START DATE</div>
                                    <div class="text-info fw-bold h5 mb-0"><?php echo date('M d, Y', strtotime($t['start_date'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                                    <div class="text-secondary small fw-bold mb-1">📅 END DATE</div>
                                    <div class="text-info fw-bold h5 mb-0"><?php echo date('M d, Y', strtotime($t['end_date'])); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                                    <div class="text-secondary small fw-bold mb-1">👥 TOTAL TEAMS</div>
                                    <div class="text-warning fw-bold h5 mb-0">
                                        <?php
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tournament_players WHERE tournament_id = ? AND status = 'Approved'");
                                            $stmt->execute([$t['id']]);
                                            $player_count = $stmt->fetchColumn();
                                            echo $player_count;
                                        ?>
                                    </div>
                                </div>
                            </div>
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25">
                                    <div class="text-secondary small fw-bold mb-1">🏅 PRIZE POOL</div>
                                    <div class="text-success fw-bold h5 mb-0"><?php echo htmlspecialchars($t['prize_info'] ?: 'Elite Trophies'); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info Card -->
                        <div class="bg-info bg-opacity-10 border border-info border-opacity-25 rounded p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-4 text-center">
                                    <div class="h6 text-secondary mb-1">⚙️ FORMAT</div>
                                    <div class="text-info fw-bold"><?php echo htmlspecialchars($t['format'] ?? 'Knockout'); ?></div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="h6 text-secondary mb-1">📊 STATUS</div>
                                    <div class="text-warning fw-bold"><?php echo strtoupper($t['status']); ?></div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="h6 text-secondary mb-1">✍️ REGISTRATIONS</div>
                                    <div class="text-success fw-bold"><?php echo $player_count; ?> Entries</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="d-grid gap-2">
                            <a href="tournaments.php" class="btn btn-neon btn-lg fw-bold">← Back to Tournaments</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container py-5">

<?php include 'includes/footer.php'; ?>
