<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_team'])) {
    $tournament_id = $_POST['tournament_id'];
    $team_id = $_POST['team_id'];
    $captain_name = $_POST['captain_name'];
    $contact_info = $_POST['contact_info'];

    // Check if team is already registered
    $stmt = $pdo->prepare("SELECT id FROM tournament_registrations WHERE tournament_id = ? AND team_id = ?");
    $stmt->execute([$tournament_id, $team_id]);
    if ($stmt->fetch()) {
        $message = "This team is already registered for this tournament.";
    } else {
        // Insert registration
        $stmt = $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, team_id, captain_name, contact_info, status) VALUES (?, ?, ?, ?, 'Pending')");
        if ($stmt->execute([$tournament_id, $team_id, $captain_name, $contact_info])) {
            $message = "Registration submitted successfully! Awaiting approval.";
        } else {
            $message = "Registration failed. Please try again.";
        }
    }

    // Redirect back with message
    header("Location: tournament_details.php?id=$tournament_id&message=" . urlencode($message));
    exit;
}

header("Location: index.php");
exit;
?>