<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? SITE_NAME; ?></title>
    <meta name="description" content="BTH Gaming Esports - The ultimate platform for community tournaments and professional gaming events.">
    <meta property="og:title" content="BTH Gaming Esports Platform">
    <meta property="og:description" content="Elite tournament management for eSports communities.">
    <meta property="og:type" content="website">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        :root {
            --neon-blue: #00f3ff;
            --neon-pink: #ff00ff;
            --dark-bg: #0b0e14;
            --card-bg: #161b22;
        }
        body {
            background-color: var(--dark-bg);
            color: #e6edf3;
            font-family: 'Roboto', sans-serif;
        }
        .navbar-brand, h1, h2, h3, .btn-neon {
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
        }
        .navbar {
            background-color: rgba(11, 14, 20, 0.95);
            border-bottom: 2px solid var(--neon-blue);
        }
        .neon-text {
            color: var(--neon-blue);
            text-shadow: 0 0 10px var(--neon-blue);
        }
        .btn-neon {
            background: transparent;
            border: 2px solid var(--neon-blue);
            color: var(--neon-blue);
            transition: 0.3s;
            box-shadow: 0 0 10px var(--neon-blue);
        }
        .btn-neon:hover {
            background: var(--neon-blue);
            color: #000;
        }
        .card {
            background-color: var(--card-bg);
            border: 1px solid #30363d;
            transition: 0.3s;
        }
        .card:hover {
            border-color: var(--neon-blue);
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
