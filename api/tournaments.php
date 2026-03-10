<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

if ($action == 'list') {
    $stmt = $pdo->query("SELECT * FROM tournaments WHERE status != 'Completed' ORDER BY start_date ASC");
    echo json_encode($stmt->fetchAll());
} elseif ($action == 'details' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch());
}
?>
