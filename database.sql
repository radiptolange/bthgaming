CREATE DATABASE IF NOT EXISTS bth_gaming_esports;
USE bth_gaming_esports;

DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS gallery;
DROP TABLE IF EXISTS leaderboard;
DROP TABLE IF EXISTS brackets;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS tournament_registrations;
DROP TABLE IF EXISTS team_players;
DROP TABLE IF EXISTS tournaments;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100),
    game_id VARCHAR(100),
    country VARCHAR(100),
    role VARCHAR(50),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    logo VARCHAR(255),
    captain_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (captain_id) REFERENCES players(id) ON DELETE SET NULL
);

CREATE TABLE team_players (
    team_id INT,
    player_id INT,
    PRIMARY KEY (team_id, player_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

CREATE TABLE tournaments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    game_title VARCHAR(100) DEFAULT 'eFootball',
    description TEXT,
    banner VARCHAR(255),
    start_date DATE,
    end_date DATE,
    max_teams INT DEFAULT 8,
    prize_info TEXT,
    format VARCHAR(50) DEFAULT 'Knockout',
    status ENUM('Upcoming', 'Registration Open', 'Ongoing', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
    champion_team_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (champion_team_id) REFERENCES teams(id) ON DELETE SET NULL
);

CREATE TABLE tournament_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    team_id INT,
    captain_name VARCHAR(100),
    contact_info VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

CREATE TABLE tournament_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    player_name VARCHAR(100) NOT NULL,
    contact_info VARCHAR(255),
    profile_image VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    round VARCHAR(50),
    match_number INT,
    team_a_id INT,
    team_b_id INT,
    score_a INT DEFAULT 0,
    score_b INT DEFAULT 0,
    winner_id INT,
    match_time DATETIME,
    status ENUM('Pending', 'Live', 'Completed') DEFAULT 'Pending',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (team_a_id) REFERENCES tournament_players(id) ON DELETE SET NULL,
    FOREIGN KEY (team_b_id) REFERENCES tournament_players(id) ON DELETE SET NULL,
    FOREIGN KEY (winner_id) REFERENCES tournament_players(id) ON DELETE SET NULL
);

CREATE TABLE brackets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT,
    data LONGTEXT, -- JSON representation for complex bracket layouts
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

CREATE TABLE leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    played INT DEFAULT 0,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    points INT DEFAULT 0,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255),
    title VARCHAR(100),
    category VARCHAR(50) DEFAULT 'Gallery',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    image VARCHAR(255),
    author VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    subject VARCHAR(200),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bth_kings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generation INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slayed_player VARCHAR(100),
    reign_days INT DEFAULT 0,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meta_key VARCHAR(100) UNIQUE,
    meta_value TEXT
);

CREATE TABLE hall_of_fame (
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
);

-- Default data
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$1413FR4u2KkxVQASntK1aeE4BCTNjHU3378hdPD.5QU1IM0S5m7Ge');

INSERT INTO settings (meta_key, meta_value) VALUES
('site_name', 'BTH Gaming Esports'),
('meta_description', 'Professional Esports Tournament Management Platform for the BTH community.'),
('contact_email', 'contact@bthgaming.com');
