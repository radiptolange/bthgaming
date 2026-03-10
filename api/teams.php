<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM teams ORDER BY team_name ASC LIMIT 20");
echo json_encode($stmt->fetchAll());
?>
