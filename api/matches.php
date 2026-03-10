<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$tournament_id = $_GET['tournament_id'] ?? null;

if ($tournament_id) {
    $stmt = $pdo->prepare("SELECT m.*, t1.team_name as team_a_name, t2.team_name as team_b_name
                           FROM matches m
                           LEFT JOIN teams t1 ON m.team_a_id = t1.id
                           LEFT JOIN teams t2 ON m.team_b_id = t2.id
                           WHERE m.tournament_id = ? ORDER BY m.match_time DESC");
    $stmt->execute([$tournament_id]);
    echo json_encode($stmt->fetchAll());
} else {
    // Recent Live Matches
    $stmt = $pdo->query("SELECT m.*, t1.team_name as team_a_name, t2.team_name as team_b_name, tr.name as tournament_name
                         FROM matches m
                         JOIN tournaments tr ON m.tournament_id = tr.id
                         LEFT JOIN teams t1 ON m.team_a_id = t1.id
                         LEFT JOIN teams t2 ON m.team_b_id = t2.id
                         WHERE m.status = 'Live' LIMIT 5");
    echo json_encode($stmt->fetchAll());
}
?>
