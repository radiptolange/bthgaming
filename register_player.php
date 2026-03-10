<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_player'])) {
    $tournament_id = $_POST['tournament_id'];
    $player_name = $_POST['player_name'];
    $contact_info = $_POST['contact_info'];
    
    // Check if player is already registered
    $stmt = $pdo->prepare("SELECT id FROM tournament_players WHERE tournament_id = ? AND player_name = ?");
    $stmt->execute([$tournament_id, $player_name]);
    if ($stmt->fetch()) {
        $message = "This player name is already registered for this tournament.";
    } else {
        $profile_image = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $profile_image = time() . '_' . $filename;
                $target_dir = "uploads/tournament_players/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $profile_image);
            }
        }
        
        // Insert player registration
        $stmt = $pdo->prepare("INSERT INTO tournament_players (tournament_id, player_name, contact_info, profile_image, status) VALUES (?, ?, ?, ?, 'Pending')");
        if ($stmt->execute([$tournament_id, $player_name, $contact_info, $profile_image])) {
            $message = "Registration successful! Your application is pending approval.";
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
