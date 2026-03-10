<?php
class Tournament {
    private $db;

    public function __construct($db) {
        $this->db = $db;
        // ensure tournament_players exists so registration features don't break
        $this->db->exec("CREATE TABLE IF NOT EXISTS tournament_players (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tournament_id INT,
            player_name VARCHAR(100) NOT NULL,
            contact_info VARCHAR(255),
            profile_image VARCHAR(255),
            status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
        )");
    }

    public function all() {
        return $this->db->query("SELECT * FROM tournaments ORDER BY id DESC")->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO tournaments (name, game_title, description, banner, start_date, end_date, max_teams, prize_info, status, format)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['game_title'], $data['description'], $data['banner'],
            $data['start_date'], $data['end_date'], $data['max_teams'], $data['prize_info'], $data['status'], $data['format']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE tournaments SET name = ?, game_title = ?, description = ?, banner = ?, start_date = ?, end_date = ?, max_teams = ?, prize_info = ?, status = ?, format = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['game_title'], $data['description'], $data['banner'],
            $data['start_date'], $data['end_date'], $data['max_teams'], $data['prize_info'], $data['status'], $data['format'], $id
        ]);
    }

    public function getByCategory($status) {
        $stmt = $this->db->prepare("SELECT * FROM tournaments WHERE status = ? ORDER BY id DESC");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    // return all players registered for a tournament (pending/approved/rejected)
    public function getRegistrations($tournament_id) {
        $stmt = $this->db->prepare("SELECT * FROM tournament_players WHERE tournament_id = ? ORDER BY registered_at ASC");
        $stmt->execute([$tournament_id]);
        return $stmt->fetchAll();
    }

    // convenience method for approving a player registration
    public function approvePlayer($player_id) {
        $stmt = $this->db->prepare("UPDATE tournament_players SET status = 'Approved' WHERE id = ?");
        return $stmt->execute([$player_id]);
    }

    // maintain compatibility if older code still calls approveRegistration
    public function approveRegistration($reg_id) {
        return $this->approvePlayer($reg_id);
    }

    // Auto-update tournament statuses based on dates
    public function updateStatusesBasedOnDates() {
        $today = date('Y-m-d');

        // Update to Ongoing if start_date has passed and status is Upcoming/Registration Open
        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Ongoing' WHERE start_date <= ? AND status IN ('Upcoming', 'Registration Open')");
        $stmt->execute([$today]);

        // Update to Completed if end_date has passed and status is Ongoing
        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Completed' WHERE end_date < ? AND status = 'Ongoing'");
        $stmt->execute([$today]);
    }

    // Get dashboard statistics
    public function getStats() {
        $stats = [];

        // Total tournaments
        $stmt = $this->db->query("SELECT COUNT(*) FROM tournaments");
        $stats['total'] = $stmt->fetchColumn();

        // By status
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM tournaments GROUP BY status");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Total registered players
        $stmt = $this->db->query("SELECT COUNT(*) FROM tournament_players WHERE status = 'Approved'");
        $stats['registered_players'] = $stmt->fetchColumn();

        // Upcoming tournaments
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tournaments WHERE status IN ('Upcoming', 'Registration Open') AND start_date > ?");
        $stmt->execute([date('Y-m-d')]);
        $stats['upcoming'] = $stmt->fetchColumn();

        return $stats;
    }
}
?>
