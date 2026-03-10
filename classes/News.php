<?php
class News {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        // ensure news table exists to avoid fatal errors when not initialized
        $this->db->exec("CREATE TABLE IF NOT EXISTS news (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            image VARCHAR(255),
            author VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function all($limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO news (title, content, image, author) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['title'], $data['content'], $data['image'], $data['author']]);
    }
}
?>
