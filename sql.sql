-- guess_game_db.sql
CREATE DATABASE IF NOT EXISTS guess_game_db;
USE guess_game_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255) DEFAULT 'uploads/avatar_default.png',
    country VARCHAR(50),
    date_of_birth DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE bank_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bank_name VARCHAR(100),
    account_holder_name VARCHAR(100),
    account_number VARCHAR(50),
    branch VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE game_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_games INT DEFAULT 0,
    easy_wins INT DEFAULT 0,
    normal_wins INT DEFAULT 0,
    advance_wins INT DEFAULT 0,
    total_earnings DECIMAL(10, 3) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_mode ENUM('easy', 'normal', 'advance'),
    amount_won DECIMAL(10, 3),
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Example countries
INSERT INTO countries (name) VALUES
("Sri Lanka"),
('United States'),
('Canada'),
('United Kingdom'),
('Australia'),
('India'),
('Germany'),
('France'),
('Japan'),
('China'),
('Brazil');


