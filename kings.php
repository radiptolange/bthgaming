<?php
require_once __DIR__ . '/config/config.php';

$kings = $pdo->query("SELECT * FROM bth_kings ORDER BY generation DESC")->fetchAll();

$page_title = "BTH Kings - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row px-lg-4">
        <div class="col-12 mb-5">
            <h1 class="neon-text display-4 fw-bold">👑 BTH KINGS 👑</h1>
            <p class="text-secondary lead">Legendary champions of the Boys And The Hood gaming dynasty.</p>
        </div>

        <div class="col-12">
            <div class="row g-4">
                <?php foreach($kings as $k): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 bg-black border-warning border-opacity-25 p-4 shadow position-relative">
                        <!-- Crown Badge -->
                        <div class="position-absolute top-0 start-50 translate-middle-x" style="margin-top: -15px;">
                            <span class="badge bg-warning text-dark fw-bold py-2 px-3 rounded-pill">GEN <?php echo $k['generation']; ?></span>
                        </div>
                        
                        <div class="text-center mb-4 mt-3">
                            <?php if ($k['profile_image']): ?>
                                <img src="uploads/kings/<?php echo $k['profile_image']; ?>" class="rounded-circle border border-warning" width="120" height="120" style="object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 4rem;">👑</div>
                            <?php endif; ?>
                        </div>

                        <h2 class="text-warning fw-bold mb-4 text-uppercase text-center"><?php echo htmlspecialchars($k['name']); ?></h2>
                        
                        <div class="d-flex flex-column gap-3 small text-uppercase fw-bold tracking-widest">
                            <div class="border-top border-secondary pt-3">
                                <div class="text-secondary mb-1">CONQUERED</div>
                                <div class="text-danger"><?php echo htmlspecialchars($k['slayed_player'] ?: 'NONE'); ?></div>
                            </div>
                            <div class="border-top border-secondary pt-3">
                                <div class="text-secondary mb-1">REIGN DURATION</div>
                                <div class="text-info"><?php echo $k['reign_days']; ?> DAYS</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary">
                            <small class="text-secondary">CROWNED: <?php echo date('M d, Y', strtotime($k['created_at'])); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($kings)): ?>
                <div class="col-12 py-5 text-center">
                    <p class="text-secondary opacity-50">NO KINGS HAVE BEEN CROWNED YET. WHO WILL BE FIRST?</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
