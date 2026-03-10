<?php
class Player {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function all() {
        return $this->db->query("SELECT p.*, e.title as event_title FROM players p LEFT JOIN tournaments e ON p.event_id = e.id ORDER BY p.id DESC")->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEvent($event_id) {
        $stmt = $this->db->prepare("SELECT * FROM players WHERE event_id = ?");
        $stmt->execute([$event_id]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $cols = "player_name, event_id";
        $placeholders = "?, ?";
        $values = [$data['player_name'], $data['event_id']];

        if (defined('HAS_PLAYER_TEAM_NAME') && HAS_PLAYER_TEAM_NAME) { $cols .= ", team_name"; $placeholders .= ", ?"; $values[] = $data['team_name']; }
        if (defined('HAS_PLAYER_USERNAME') && HAS_PLAYER_USERNAME) { $cols .= ", username"; $placeholders .= ", ?"; $values[] = $data['username']; }
        if (defined('HAS_PLAYER_COUNTRY') && HAS_PLAYER_COUNTRY) { $cols .= ", country"; $placeholders .= ", ?"; $values[] = $data['country']; }
        if (defined('HAS_PLAYER_PROFILE_IMAGE') && HAS_PLAYER_PROFILE_IMAGE) { $cols .= ", profile_image"; $placeholders .= ", ?"; $values[] = $data['profile_image']; }

        $sql = "INSERT INTO players ($cols) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute($values)) {
            error_log("Player creation failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
        return true;
    }

    public function update($id, $data) {
        $sql = "UPDATE players SET player_name = ?, event_id = ?";
        $values = [$data['player_name'], $data['event_id']];

        if (defined('HAS_PLAYER_TEAM_NAME') && HAS_PLAYER_TEAM_NAME) { $sql .= ", team_name = ?"; $values[] = $data['team_name']; }
        if (defined('HAS_PLAYER_USERNAME') && HAS_PLAYER_USERNAME) { $sql .= ", username = ?"; $values[] = $data['username']; }
        if (defined('HAS_PLAYER_COUNTRY') && HAS_PLAYER_COUNTRY) { $sql .= ", country = ?"; $values[] = $data['country']; }
        if (defined('HAS_PLAYER_PROFILE_IMAGE') && HAS_PLAYER_PROFILE_IMAGE) { $sql .= ", profile_image = ?"; $values[] = $data['profile_image']; }

        $sql .= " WHERE id = ?";
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM players WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
