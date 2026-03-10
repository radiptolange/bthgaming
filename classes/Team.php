<?php
class Team {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function all() {
        return $this->db->query("SELECT t.*, p.username as captain_name FROM teams t LEFT JOIN players p ON t.captain_id = p.id ORDER BY t.id DESC")->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT t.*, p.username as captain_name FROM teams t LEFT JOIN players p ON t.captain_id = p.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO teams (team_name, logo, captain_id) VALUES (?, ?, ?)");
        return $stmt->execute([$data['team_name'], $data['logo'], $data['captain_id']]);
    }

    public function getPlayers($team_id) {
        $stmt = $this->db->prepare("SELECT p.* FROM players p JOIN team_players tp ON p.id = tp.player_id WHERE tp.team_id = ?");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    public function addPlayer($team_id, $player_id) {
        $stmt = $this->db->prepare("INSERT INTO team_players (team_id, player_id) VALUES (?, ?)");
        return $stmt->execute([$team_id, $player_id]);
    }
}
?>
