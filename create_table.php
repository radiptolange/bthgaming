<?php
require_once __DIR__ . '/config/config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hall_of_fame (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tournament_name VARCHAR(150) NOT NULL,
        winner_1st VARCHAR(100),
        winner_2nd VARCHAR(100),
        winner_3rd VARCHAR(100),
        top_scorer VARCHAR(100),
        game_title VARCHAR(100) DEFAULT 'eFootball',
        end_date DATE,
        winner_1st_img VARCHAR(255),
        winner_2nd_img VARCHAR(255),
        winner_3rd_img VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'hall_of_fame' created successfully!";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>