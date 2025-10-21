-- Remove Exercise and Quiz Related Tables
-- This script removes all exercise and quiz related tables from the database

-- Drop foreign key constraints first
ALTER TABLE `advanced_quiz_attempts` DROP FOREIGN KEY `advanced_quiz_attempts_ibfk_1`;
ALTER TABLE `advanced_quiz_attempts` DROP FOREIGN KEY `advanced_quiz_attempts_ibfk_2`;
ALTER TABLE `advanced_quiz_questions` DROP FOREIGN KEY `advanced_quiz_questions_ibfk_1`;
ALTER TABLE `advanced_quiz_results` DROP FOREIGN KEY `advanced_quiz_results_ibfk_1`;
ALTER TABLE `advanced_quiz_results` DROP FOREIGN KEY `advanced_quiz_results_ibfk_2`;
ALTER TABLE `user_progress` DROP FOREIGN KEY `user_progress_ibfk_1`;

-- Drop tables
DROP TABLE IF EXISTS `advanced_quiz_results`;
DROP TABLE IF EXISTS `advanced_quiz_questions`;
DROP TABLE IF EXISTS `advanced_quiz_attempts`;
DROP TABLE IF EXISTS `advanced_quizzes`;
DROP TABLE IF EXISTS `user_progress`;

-- Remove any remaining exercise/quiz related tables if they exist
DROP TABLE IF EXISTS `exercise_progress`;
DROP TABLE IF EXISTS `quiz_attempts`;
DROP TABLE IF EXISTS `quiz_questions`;
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `exercise_attempts`;
DROP TABLE IF EXISTS `exercise_questions`;
DROP TABLE IF EXISTS `exercises`;

-- Clean up any remaining references
-- Note: This script assumes you want to keep the main users table and admin tables
-- If you need to remove more tables, add them to the DROP statements above
