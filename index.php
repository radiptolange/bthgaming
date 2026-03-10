<?php
require_once __DIR__ . '/config/config.php';

$news_obj = new News($pdo);
$latest_news = $news_obj->all(3);

$active_tournaments = (new Tournament($pdo))->getByCategory('Ongoing');
$upcoming_tournaments = (new Tournament($pdo))->getByCategory('Registration Open');

$page_title = SITE_NAME . " - Professional Esports Platform";
include 'includes/header.php';
?>

<!-- Hero Section with Parallax Stars -->
<section class="hero-section position-relative overflow-hidden py-5 d-flex align-items-center">
    <div id="stars"></div>
    <div id="stars2"></div>
    <div id="stars3"></div>
    <div class="container py-lg-5 text-center position-relative" style="z-index: 2;">
        <h1 class="display-2 fw-bold neon-text mb-3 tracking-tighter">BTH GAMING ESPORTS</h1>
        <p class="lead mb-5 text-secondary text-uppercase tracking-widest fw-bold">Dominating the digital pitch. Empowering the community.</p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="tournaments.php" class="btn btn-neon btn-lg px-5 py-3 fw-bold">JOIN TOURNAMENT</a>
            <a href="kings.php" class="btn btn-warning btn-lg px-5 py-3 fw-bold shadow-sm text-dark">👑 BTH KINGS</a>
            <a href="leaderboard.php" class="btn btn-outline-info btn-lg px-5 py-3 fw-bold shadow-sm">HALL OF FAME</a>
        </div>
    </div>
</section>

<!-- Live Matches AJAX section -->
<section class="py-5 bg-black border-bottom border-info border-opacity-10">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="neon-text mb-0 small tracking-widest fw-bold">LIVE MATCHES</h4>
            <span class="badge bg-danger pulse px-3 py-1">LIVE NOW</span>
        </div>
        <div id="liveMatchesGrid" class="row g-4">
            <!-- Populated via AJAX -->
        </div>
    </div>
</section>

<main class="container py-5">
    <!-- Latest News -->
    <section class="mb-5">
        <h2 class="neon-text border-start border-4 border-info ps-3 mb-4 text-uppercase">Latest Updates</h2>
        <div class="row g-4">
            <?php foreach($latest_news as $n): ?>
            <div class="col-md-4">
                <div class="card h-100 border-info border-opacity-10">
                    <div class="card-body">
                        <small class="text-info d-block mb-2"><?php echo date('M d, Y', strtotime($n['created_at'])); ?></small>
                        <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($n['title']); ?></h5>
                        <p class="small text-secondary mb-4"><?php echo substr(strip_tags($n['content']), 0, 120); ?>...</p>
                        <a href="news.php?id=<?php echo $n['id']; ?>" class="small text-info text-decoration-none fw-bold">READ FULL STORY →</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script>
function fetchLiveMatches() {
    fetch('api/matches.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if(data.length === 0) html = '<div class="col-12 text-center py-4 text-secondary opacity-50 small uppercase">No live matches at the moment</div>';
            data.forEach(m => {
                html += `<div class="col-lg-4 col-md-6">
                    <div class="card bg-black border-danger border-opacity-25 shadow-sm">
                        <div class="card-body py-3 d-flex justify-content-between align-items-center">
                            <div class="text-center" style="flex:1">
                                <small class="text-secondary d-block mb-1">TEAM A</small>
                                <div class="fw-bold small text-truncate">${m.team_a_name}</div>
                            </div>
                            <div class="px-3 text-center">
                                <h4 class="mb-0 text-danger fw-bold">${m.score_a} - ${m.score_b}</h4>
                                <small class="text-secondary fw-bold" style="font-size:10px">LIVE</small>
                            </div>
                            <div class="text-center" style="flex:1">
                                <small class="text-secondary d-block mb-1">TEAM B</small>
                                <div class="fw-bold small text-truncate">${m.team_b_name}</div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            document.getElementById('liveMatchesGrid').innerHTML = html;
        });
}
fetchLiveMatches();
setInterval(fetchLiveMatches, 30000); // Polling every 30s
</script>

<?php include 'includes/footer.php'; ?>
