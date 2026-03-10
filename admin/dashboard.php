<?php
require_once __DIR__ . '/../includes/auth_check.php';

$tournament_obj = new Tournament($pdo);

// Auto-update tournament statuses based on dates
$tournament_obj->updateStatusesBasedOnDates();

$stats = $tournament_obj->getStats();

$page_title = "Admin Dashboard - " . SITE_NAME;
include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row px-lg-4">
        <div class="col-12 mb-4">
            <h2 class="neon-text border-start border-4 border-info ps-3">ADMIN DASHBOARD</h2>
            <p class="text-secondary small">Welcome, <?php echo $_SESSION['admin_username']; ?>!</p>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 p-3 shadow-sm border-info border-opacity-25">
                    <h6 class="text-secondary text-uppercase mb-1 small fw-bold">Total Tournaments</h6>
                    <h2 class="mb-0 neon-text"><?php echo $stats['total']; ?></h2>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 p-3 shadow-sm border-success border-opacity-25">
                    <h6 class="text-secondary text-uppercase mb-1 small fw-bold">Registered Players</h6>
                    <h2 class="mb-0 text-success"><?php echo $stats['registered_players']; ?></h2>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 p-3 shadow-sm border-warning border-opacity-25">
                    <h6 class="text-secondary text-uppercase mb-1 small fw-bold">Upcoming</h6>
                    <h2 class="mb-0 text-warning"><?php echo $stats['upcoming']; ?></h2>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 p-3 shadow-sm border-danger border-opacity-25">
                    <h6 class="text-secondary text-uppercase mb-1 small fw-bold">Ongoing</h6>
                    <h2 class="mb-0 text-danger"><?php echo $stats['by_status']['Ongoing'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Professional Management Links -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm p-4 h-100 border-info border-opacity-50">
                    <h4 class="mb-3 border-bottom border-info border-opacity-50 pb-2 neon-text text-uppercase">Management Portal</h4>
                    <div class="list-group list-group-flush bg-transparent">
                        <a href="tournaments.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> Manage Tournaments
                        </a>
                        <a href="bth_kings.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> BTH King Rankings
                        </a>
                        <a href="champions_manage.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> Hall of Fame
                        </a>
                        <a href="news_manage.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> Post Announcements
                        </a>
                        <a href="gallery_manage.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> Gallery Management
                        </a>
                        <a href="messages_manage.php" class="list-group-item list-group-item-action bg-transparent text-light border-secondary">
                            <span class="me-2 text-info">➤</span> View Messages
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action bg-transparent text-warning border-secondary">
                            <span class="me-2">⚙</span> Security Settings
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-secondary">
                            <span class="me-2">✖</span> Logout Session
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mb-4">
                <div class="card shadow-sm p-4 h-100 border-info border-opacity-50">
                    <h4 class="mb-3 border-bottom border-info border-opacity-50 pb-2 neon-text text-uppercase">Recent Live Activity</h4>
                    <div id="liveActivity" class="small">
                        <p class="text-secondary text-center py-5">Loading live matches...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('../api/matches.php')
        .then(res => res.json())
        .then(data => {
            let html = '<ul class="list-group list-group-flush bg-transparent">';
            if(data.length === 0) html += '<li class="list-group-item bg-transparent text-secondary text-center">No live matches at the moment.</li>';
            data.forEach(m => {
                html += `<li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-info fw-bold">${m.team_a_name}</span> vs <span class="text-info fw-bold">${m.team_b_name}</span>
                                <br><small class="text-secondary">${m.tournament_name}</small>
                            </div>
                            <span class="badge bg-danger pulse small">${m.score_a} - ${m.score_b}</span>
                         </li>`;
            });
            html += '</ul>';
            document.getElementById('liveActivity').innerHTML = html;
        });
});
</script>

<?php include '../includes/footer.php'; ?>
