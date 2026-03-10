<?php
class MatchManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByTournament($tournament_id) {
        $stmt = $this->db->prepare("SELECT m.*, p1.player_name as team_a_name, p1.profile_image as team_a_logo, p2.player_name as team_b_name, p2.profile_image as team_b_logo, wp.player_name as winner_name
                                    FROM matches m
                                    LEFT JOIN tournament_players p1 ON m.team_a_id = p1.id
                                    LEFT JOIN tournament_players p2 ON m.team_b_id = p2.id
                                    LEFT JOIN tournament_players wp ON m.winner_id = wp.id
                                    WHERE m.tournament_id = ? ORDER BY m.match_number ASC");
        $stmt->execute([$tournament_id]);
        return $stmt->fetchAll();
    }

    public function updateScore($match_id, $score_a, $score_b, $status = 'Completed') {
        $stmt = $this->db->prepare("SELECT team_a_id, team_b_id FROM matches WHERE id = ?");
        $stmt->execute([$match_id]);
        $match = $stmt->fetch();

        $winner_id = null;
        if ($score_a > $score_b) $winner_id = $match['team_a_id'];
        elseif ($score_b > $score_a) $winner_id = $match['team_b_id'];

        $stmt = $this->db->prepare("UPDATE matches SET score_a = ?, score_b = ?, winner_id = ?, status = ? WHERE id = ?");
        return $stmt->execute([$score_a, $score_b, $winner_id, $status, $match_id]);
    }
}
?>
