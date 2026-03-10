<?php
class Fixture {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function generate($tournament_id, $bracket_size = 0) {
        // Fetch approved players
        $stmt = $this->db->prepare("SELECT id, player_name FROM tournament_players WHERE tournament_id = ? AND status = 'Approved' ORDER BY registered_at ASC");
        $stmt->execute([$tournament_id]);
        $players = $stmt->fetchAll();

        $num_players = count($players);
        if ($num_players < 2) return false;

        // Clear existing
        $stmt = $this->db->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);

        // Determine rounds using optional bracket_size override
        $rounds = [];
        $count = $bracket_size > 0 ? min($bracket_size, $num_players) : $num_players;
        if ($count >= 16) {
            $rounds = ['Round of 16', 'Quarterfinal', 'Semifinal', 'Final'];
        } elseif ($count >= 8) {
            $rounds = ['Quarterfinal', 'Semifinal', 'Final'];
        } elseif ($count >= 4) {
            $rounds = ['Semifinal', 'Final'];
        } else {
            $rounds = ['Final'];
        }

        $match_number = 1;
        $current_players = $players;

        foreach ($rounds as $round) {
            if (count($current_players) < 2) break;
            $next_round_players = [];
            for ($i = 0; $i < count($current_players); $i += 2) {
                $player_a = $current_players[$i] ?? null;
                $player_b = $current_players[$i + 1] ?? null;
                $stmt = $this->db->prepare("INSERT INTO matches (tournament_id, round, match_number, team_a_id, team_b_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                $stmt->execute([$tournament_id, $round, $match_number, $player_a['id'], $player_b ? $player_b['id'] : null]);
                if ($player_a) $next_round_players[] = $player_a;
                if ($player_b) $next_round_players[] = $player_b;
                $match_number++;
            }
            $current_players = $next_round_players;
        }

        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Ongoing' WHERE id = ?");
        $stmt->execute([$tournament_id]);
        return true;
    }

    // Round Robin (League Format)
    public function generateRoundRobin($tournament_id) {
        $stmt = $this->db->prepare("SELECT id, player_name FROM tournament_players WHERE tournament_id = ? AND status = 'Approved' ORDER BY registered_at ASC");
        $stmt->execute([$tournament_id]);
        $players = $stmt->fetchAll();

        $num_players = count($players);
        if ($num_players < 2) return false;

        $stmt = $this->db->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);

        $teams = $players;
        $is_odd = $num_players % 2 != 0;
        if ($is_odd) {
            $teams[] = ['id' => null, 'player_name' => 'BYE'];
        }

        $total_teams = count($teams);
        $rounds = $total_teams - 1;
        $matches_per_round = $total_teams / 2;

        $match_number = 1;
        for ($round = 1; $round <= $rounds; $round++) {
            for ($i = 0; $i < $matches_per_round; $i++) {
                $team_a = $teams[$i];
                $team_b = $teams[$total_teams - 1 - $i];

                if ($team_a['id'] !== null && $team_b['id'] !== null) {
                    $stmt = $this->db->prepare("INSERT INTO matches (tournament_id, round, match_number, team_a_id, team_b_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $stmt->execute([$tournament_id, "Round $round", $match_number, $team_a['id'], $team_b['id']]);
                    $match_number++;
                }
            }

            // Rotate teams (keep first fixed, rotate others clockwise)
            $first = array_shift($teams);
            $last = array_pop($teams);
            array_unshift($teams, $first);
            $teams[] = $last;
        }

        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Ongoing' WHERE id = ?");
        $stmt->execute([$tournament_id]);
        return true;
    }

    // Knockout (Single Elimination)
    public function generateKnockout($tournament_id) {
        $stmt = $this->db->prepare("SELECT id, player_name FROM tournament_players WHERE tournament_id = ? AND status = 'Approved' ORDER BY registered_at ASC");
        $stmt->execute([$tournament_id]);
        $players = $stmt->fetchAll();

        $num_players = count($players);
        if ($num_players < 2) return false;

        $stmt = $this->db->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);

        // Shuffle teams
        shuffle($players);

        // Find nearest power of 2
        $target_size = pow(2, ceil(log($num_players, 2)));
        $byes_needed = $target_size - $num_players;

        // Add BYE slots
        for ($i = 0; $i < $byes_needed; $i++) {
            $players[] = ['id' => null, 'player_name' => 'BYE'];
        }

        $rounds = ['Quarterfinal', 'Semifinal', 'Final'];
        if ($target_size >= 16) array_unshift($rounds, 'Round of 16');
        if ($target_size >= 32) array_unshift($rounds, 'Round of 32');

        $current_round_players = $players;
        $match_number = 1;

        foreach ($rounds as $round) {
            $next_round = [];
            for ($i = 0; $i < count($current_round_players); $i += 2) {
                $team_a = $current_round_players[$i];
                $team_b = $current_round_players[$i + 1];

                if ($team_a['id'] !== null || $team_b['id'] !== null) {
                    $stmt = $this->db->prepare("INSERT INTO matches (tournament_id, round, match_number, team_a_id, team_b_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $stmt->execute([$tournament_id, $round, $match_number, $team_a['id'], $team_b['id']]);
                    $match_number++;
                }

                // Both advance if not BYE
                if ($team_a['id'] !== null) $next_round[] = $team_a;
                if ($team_b['id'] !== null) $next_round[] = $team_b;
            }
            $current_round_players = $next_round;
            if (count($current_round_players) <= 1) break;
        }

        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Ongoing' WHERE id = ?");
        $stmt->execute([$tournament_id]);
        return true;
    }

    // Group Stage + Knockout (World Cup Style)
    public function generateGroupStage($tournament_id, $group_size = 4) {
        $stmt = $this->db->prepare("SELECT id, player_name FROM tournament_players WHERE tournament_id = ? AND status = 'Approved' ORDER BY registered_at ASC");
        $stmt->execute([$tournament_id]);
        $players = $stmt->fetchAll();

        $num_players = count($players);
        if ($num_players < 4) return false;

        $stmt = $this->db->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);

        // Divide into groups
        $groups = array_chunk($players, $group_size);
        $group_names = ['A', 'B', 'C', 'D', 'E', 'F'];

        $match_number = 1;

        foreach ($groups as $group_index => $group) {
            $group_name = $group_names[$group_index] ?? chr(65 + $group_index);
            $group_teams = $group;

            // Add BYE if odd
            $is_odd = count($group_teams) % 2 != 0;
            if ($is_odd) {
                $group_teams[] = ['id' => null, 'player_name' => 'BYE'];
            }

            $total_teams = count($group_teams);
            $rounds = $total_teams - 1;

            for ($round = 1; $round <= $rounds; $round++) {
                for ($i = 0; $i < $total_teams / 2; $i++) {
                    $team_a = $group_teams[$i];
                    $team_b = $group_teams[$total_teams - 1 - $i];

                    if ($team_a['id'] !== null && $team_b['id'] !== null) {
                        $stmt = $this->db->prepare("INSERT INTO matches (tournament_id, round, match_number, team_a_id, team_b_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                        $stmt->execute([$tournament_id, "Group $group_name - Round $round", $match_number, $team_a['id'], $team_b['id']]);
                        $match_number++;
                    }
                }

                // Rotate for next round
                $first = array_shift($group_teams);
                $last = array_pop($group_teams);
                array_unshift($group_teams, $first);
                $group_teams[] = $last;
            }
        }

        // For simplicity, assume top 2 from each group advance to knockout
        // In a real implementation, you'd calculate standings based on match results
        $advancing_teams = [];
        foreach ($groups as $group) {
            // Take first 2 (in order)
            $advancing_teams = array_merge($advancing_teams, array_slice($group, 0, 2));
        }

        // Generate knockout from advancing teams
        shuffle($advancing_teams);
        $knockout_rounds = ['Quarterfinal', 'Semifinal', 'Final'];

        $current_round_players = $advancing_teams;

        foreach ($knockout_rounds as $round) {
            $next_round = [];
            for ($i = 0; $i < count($current_round_players); $i += 2) {
                $team_a = $current_round_players[$i];
                $team_b = $current_round_players[$i + 1] ?? null;

                $stmt = $this->db->prepare("INSERT INTO matches (tournament_id, round, match_number, team_a_id, team_b_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                $stmt->execute([$tournament_id, $round, $match_number, $team_a['id'], $team_b ? $team_b['id'] : null]);
                $match_number++;

                if ($team_a) $next_round[] = $team_a;
                if ($team_b) $next_round[] = $team_b;
            }
            $current_round_players = $next_round;
            if (count($current_round_players) <= 1) break;
        }

        $stmt = $this->db->prepare("UPDATE tournaments SET status = 'Ongoing' WHERE id = ?");
        $stmt->execute([$tournament_id]);
        return true;
    }
}
?>