<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand neon-text" href="<?php echo BASE_URL; ?>index.php">BTH GAMING</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto text-uppercase">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>tournaments.php">Tournaments</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>leaderboard.php">Hall of Fame</a></li>
                <li class="nav-item"><a class="nav-link text-warning fw-bold" href="<?php echo BASE_URL; ?>kings.php">👑 BTH KINGS</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>gallery.php">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>news.php">News</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                <?php if (Auth::check()): ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-info btn-sm" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Panel</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
