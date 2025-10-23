-- Migration: Add quiz_scores table
-- Date: 2025
-- Description: Add table to track all quiz attempts with different scoring systems

-- Create quiz_scores table
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

-- Add some sample data for testing (optional - remove in production)
-- INSERT INTO `quiz_scores` (`user_id`, `quiz_type`, `score`, `total_questions`, `correct_answers`, `completed_at`) VALUES
-- (1, 'basic_quiz', 85.00, 10, 8, NOW()),
-- (1, 'time_rush', 15.00, NULL, 15, NOW()),
-- (1, 'math_quiz', 90.00, 10, 9, NOW());

