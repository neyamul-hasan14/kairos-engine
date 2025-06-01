-- Kairos Database Schema

CREATE DATABASE IF NOT EXISTS kairos;
USE kairos;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    origin_year INT NOT NULL,
    timeline_affiliation VARCHAR(50),
    species VARCHAR(50),
    backstory TEXT,
    profile_image VARCHAR(255),
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    content TEXT NOT NULL,
    timeline_tag VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chat rooms table
CREATE TABLE chat_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    theme VARCHAR(50),
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_id INT,
    user_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Missions table
CREATE TABLE missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    points_reward INT DEFAULT 0,
    difficulty_level ENUM('easy', 'medium', 'hard'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User missions (tracking completed missions)
CREATE TABLE user_missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    mission_id INT,
    status ENUM('in_progress', 'completed') DEFAULT 'in_progress',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
);

-- Badges table
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User badges
CREATE TABLE user_badges (
    user_id INT,
    badge_id INT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Insert some sample missions
INSERT INTO missions (title, description, difficulty_level, points_reward) VALUES
('Time Travel Basics', 'Learn the fundamental principles of safe time travel and temporal navigation.', 'easy', 100),
('Temporal Paradox Prevention', 'Master the techniques of avoiding and resolving temporal paradoxes.', 'medium', 250),
('Historical Accuracy', 'Ensure your presence in the past doesn\'t alter the timeline.', 'medium', 300),
('Advanced Time Manipulation', 'Learn to manipulate time streams and create temporal branches.', 'hard', 500),
('Temporal Diplomacy', 'Navigate complex interactions with historical figures.', 'hard', 400),
('Time Stream Navigation', 'Master the art of navigating through complex time streams.', 'medium', 350),
('Temporal Ethics', 'Understand and apply the ethical principles of time travel.', 'easy', 150),
('Time Machine Maintenance', 'Learn to maintain and repair your time travel equipment.', 'medium', 200),
('Historical Research', 'Develop skills in historical research and verification.', 'easy', 100),
('Temporal Combat', 'Learn defensive techniques for temporal conflicts.', 'hard', 450); 

-- Insert badge data
INSERT INTO badges (name, description) VALUES
('Mission Master', 'Complete 10 missions of any difficulty'),
('Hardcore Hero', 'Complete 3 hard difficulty missions'),
('Point Collector', 'Earn 1000 points from missions');