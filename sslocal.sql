-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 01:33 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Disable foreign key checks temporarily for easier import
-- This allows tables to be created in any order without constraint violations
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sslocal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `username`, `password`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@signspeak.com', 'super_admin', 'active', '2025-07-14 23:02:49', '2025-07-12 02:09:55', '2025-07-14 23:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quizzes` (REMOVED)
--
-- REMOVED advanced_quizzes table definition

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_attempts` (REMOVED)
--
-- REMOVED advanced_quiz_attempts table definition

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_questions` (REMOVED)
--
-- REMOVED advanced_quiz_questions table definition

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_results` (REMOVED)
--
-- REMOVED advanced_quiz_results table definition

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `auth_provider` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `auth_provider`) VALUES
(1, 'kim@gmail.com', '$2y$10$NNJO3FNYfkbSOQRCmvIHdOX/kRU9MChjno7STwwp4.KrwJvFLn0z2', 'local'),
(2, 'eal@gmail.com', '$2y$10$30/hyYw1g9Ng5fGrL9jG8.d/fwSINaRIzLrOOee3C9Vh7UDs1syCe', 'local');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_scores`
--

CREATE TABLE IF NOT EXISTS `quiz_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `quiz_type` enum('basic_quiz','time_rush','math_quiz') NOT NULL,
  `score` decimal(10,2) NOT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `correct_answers` int(11) DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_quiz` (`user_id`,`quiz_type`),
  KEY `idx_completed` (`completed_at`),
  CONSTRAINT `fk_quiz_scores_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `user_id` int(11) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_recent_activity` (`last_activity` DESC),
  CONSTRAINT `fk_user_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `advanced_quizzes` (REMOVED)
--
-- REMOVED index creation for advanced_quizzes

--
-- Indexes for table `advanced_quiz_attempts` (REMOVED)
--
-- REMOVED indexes for advanced_quiz_attempts

--
-- Indexes for table `advanced_quiz_questions` (REMOVED)
--
-- REMOVED indexes for advanced_quiz_questions

--
-- Indexes for table `advanced_quiz_results` (REMOVED)
--
-- REMOVED indexes for advanced_quiz_results

--
-- Indexes for table `quiz_scores` (defined in table creation)
--

--
-- Indexes for table `users` (defined in table creation)
--


--
-- Indexes for table `user_sessions` (defined in table creation)
--

-- --------------------------------------------------------

--
-- Table structure for table `user_presence_minutely`
-- Stores a single presence record per user per minute for trend charts
--

CREATE TABLE `user_presence_minutely` (
  `bucket_minute` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`bucket_minute`,`user_id`),
  KEY `idx_bucket_minute` (`bucket_minute`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_presence_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Triggers to populate minutely presence from user_sessions updates
DELIMITER //
CREATE TRIGGER `trg_user_sessions_minutely_ins` AFTER INSERT ON `user_sessions`
FOR EACH ROW
BEGIN
  INSERT IGNORE INTO user_presence_minutely (bucket_minute, user_id)
  VALUES (DATE_FORMAT(NEW.last_activity, '%Y-%m-%d %H:%i:00'), NEW.user_id);
END //

CREATE TRIGGER `trg_user_sessions_minutely_upd` AFTER UPDATE ON `user_sessions`
FOR EACH ROW
BEGIN
  INSERT IGNORE INTO user_presence_minutely (bucket_minute, user_id)
  VALUES (DATE_FORMAT(NEW.last_activity, '%Y-%m-%d %H:%i:00'), NEW.user_id);
END //
DELIMITER ;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_scores` (defined in table creation)
--

--
-- AUTO_INCREMENT for table `advanced_quizzes` (REMOVED)
--
-- REMOVED auto_increment for advanced_quizzes

--
-- AUTO_INCREMENT for table `advanced_quiz_attempts` (REMOVED)
--
-- REMOVED auto_increment for advanced_quiz_attempts

--
-- AUTO_INCREMENT for table `advanced_quiz_questions` (REMOVED)
--
-- REMOVED auto_increment for advanced_quiz_questions

--
-- AUTO_INCREMENT for table `advanced_quiz_results` (REMOVED)
--
-- REMOVED auto_increment for advanced_quiz_results

--
-- AUTO_INCREMENT for table `users` (defined in table creation)
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `advanced_quiz_attempts` (REMOVED)
--
-- REMOVED foreign keys for advanced_quiz_attempts

--
-- Constraints for table `advanced_quiz_questions` (REMOVED)
--
-- REMOVED foreign keys for advanced_quiz_questions

--
-- Constraints for table `advanced_quiz_results` (REMOVED)
--
-- REMOVED foreign keys for advanced_quiz_results


--
-- Constraints for table `quiz_scores` (defined in table creation)
--

--
-- Constraints for table `user_sessions` (defined in table creation)
--

--
-- Constraints for table `admin_users` (independent admin table)
--
-- Note: admin_users is independent and not linked to users table

-- --------------------------------------------------------

--
-- Views for user presence tracking
--

-- View for easy active user queries
CREATE OR REPLACE VIEW `active_users_view` AS
SELECT 
    u.user_id,
    u.username,
    u.auth_provider,
    us.last_activity,
    us.session_data,
    us.ip_address,
    CASE 
        WHEN us.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'active'
        WHEN us.last_activity >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'recent'
        ELSE 'inactive'
    END as status
FROM users u
LEFT JOIN user_sessions us ON u.user_id = us.user_id
WHERE us.last_activity IS NOT NULL
ORDER BY us.last_activity DESC;

-- --------------------------------------------------------

--
-- Stored procedures for user presence management
--

DELIMITER //
CREATE PROCEDURE CleanupInactiveSessions()
BEGIN
    -- Remove sessions older than 24 hours
    DELETE FROM user_sessions 
    WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
    
    -- Return count of cleaned sessions
    SELECT ROW_COUNT() as sessions_cleaned;
END //
DELIMITER ;

-- --------------------------------------------------------

--
-- Sample data for testing (optional)
--

-- Insert sample user session data for testing (after users table is created)
-- INSERT INTO `user_sessions` (`user_id`, `last_activity`, `session_data`, `ip_address`, `user_agent`) VALUES
-- (1, NOW(), '{"page": "/", "action": "browsing", "timestamp": "2025-01-20T10:00:00"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
-- (2, NOW(), '{"page": "/tutorial", "action": "learning", "timestamp": "2025-01-20T10:05:00"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

-- Insert sample quiz scores data for testing (optional - remove in production)
-- INSERT INTO `quiz_scores` (`user_id`, `quiz_type`, `score`, `total_questions`, `correct_answers`, `completed_at`) VALUES
-- (1, 'basic_quiz', 85.00, 10, 8, NOW()),
-- (1, 'time_rush', 15.00, NULL, 15, NOW()),
-- (1, 'math_quiz', 90.00, 10, 9, NOW());

-- --------------------------------------------------------
--
-- View: v_user_activity_stats
-- Aggregates user activity data for efficient querying
--

CREATE VIEW v_user_activity_stats AS
SELECT 
    u.user_id,
    u.username,
    COUNT(upm.user_id) as total_minutes,
    MAX(upm.bucket_minute) as last_activity
FROM users u
LEFT JOIN user_presence_minutely upm ON u.user_id = upm.user_id
GROUP BY u.user_id, u.username;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
