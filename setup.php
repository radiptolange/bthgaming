<?php
/**
 * BTH Gaming Professional Setup Script
 * Use this to initialize the database on localhost.
 */

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // Connect to MySQL without database selection
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute database.sql
    $sql = file_get_contents('database.sql');
    $conn->exec($sql);

    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #00f3ff;'>BTH GAMING SETUP</h1>";
    echo "<p style='color: #27ae60; font-weight: bold;'>Database and tables initialized successfully!</p>";
    echo "<div style='background: #f4f4f4; padding: 20px; display: inline-block; border-radius: 10px; margin: 20px;'>";
    echo "<p><strong>Default Admin Username:</strong> admin</p>";
    echo "<p><strong>Default Admin Password:</strong> admin123</p>";
    echo "</div><br>";
    echo "<a href='index.php' style='text-decoration: none; color: white; background: #00f3ff; padding: 10px 20px; border-radius: 5px;'>Go to Website</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #e94560;'>SETUP FAILED</h1>";
    echo "<p style='color: #e94560;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Ensure your MySQL server is running and the credentials in setup.php are correct.</p>";
    echo "</div>";
}
?>
