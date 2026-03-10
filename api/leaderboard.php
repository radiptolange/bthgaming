<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$sql = "SELECT t.team_name, t.logo, l.*
        FROM leaderboard l
        JOIN teams t ON l.team_id = t.id
        ORDER BY l.points DESC, l.wins DESC LIMIT 10";
echo json_encode($pdo->query($sql)->fetchAll());
?>
