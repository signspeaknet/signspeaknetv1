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
-- Table structure for table `advanced_quizzes`
--

CREATE TABLE `advanced_quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `time_limit` int(11) DEFAULT 0,
  `passing_score` int(11) DEFAULT 70,
  `num_questions` int(11) DEFAULT 10,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_attempts`
--

CREATE TABLE `advanced_quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) DEFAULT 0,
  `total_questions` int(11) DEFAULT 0,
  `correct_answers` int(11) DEFAULT 0,
  `time_taken` int(11) DEFAULT 0,
  `passed` tinyint(1) DEFAULT 0,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `answers_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_questions`
--

CREATE TABLE `advanced_quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_bank_id` varchar(50) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('text','image','video') DEFAULT 'text',
  `media_url` varchar(500) DEFAULT NULL,
  `points` int(11) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advanced_quiz_results`
--

CREATE TABLE `advanced_quiz_results` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(255) NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `time_spent` int(11) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `auth_provider` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `auth_provider`) VALUES
(1, 'kim@gmail.com', '$2y$10$NNJO3FNYfkbSOQRCmvIHdOX/kRU9MChjno7STwwp4.KrwJvFLn0z2', 'local'),
(2, 'eal@gmail.com', '$2y$10$30/hyYw1g9Ng5fGrL9jG8.d/fwSINaRIzLrOOee3C9Vh7UDs1syCe', 'local');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exercise_number` int(11) DEFAULT NULL,
  `quiz_number` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`progress_id`, `user_id`, `exercise_number`, `quiz_number`, `completed_at`, `score`) VALUES
(1, 2, 1, 1, '2025-07-16 20:32:58', 9);

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
-- Indexes for table `advanced_quizzes`
--
ALTER TABLE `advanced_quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `advanced_quiz_attempts`
--
ALTER TABLE `advanced_quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `completed_at` (`completed_at`),
  ADD KEY `idx_quiz_attempts_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `idx_quiz_attempts_completed` (`completed_at`);

--
-- Indexes for table `advanced_quiz_questions`
--
ALTER TABLE `advanced_quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `question_bank_id` (`question_bank_id`);

--
-- Indexes for table `advanced_quiz_results`
--
ALTER TABLE `advanced_quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `idx_quiz_results_attempt` (`attempt_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `advanced_quizzes`
--
ALTER TABLE `advanced_quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `advanced_quiz_attempts`
--
ALTER TABLE `advanced_quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `advanced_quiz_questions`
--
ALTER TABLE `advanced_quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `advanced_quiz_results`
--
ALTER TABLE `advanced_quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `advanced_quiz_attempts`
--
ALTER TABLE `advanced_quiz_attempts`
  ADD CONSTRAINT `advanced_quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advanced_quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `advanced_quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advanced_quiz_questions`
--
ALTER TABLE `advanced_quiz_questions`
  ADD CONSTRAINT `advanced_quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `advanced_quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `advanced_quiz_results`
--
ALTER TABLE `advanced_quiz_results`
  ADD CONSTRAINT `advanced_quiz_results_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `advanced_quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advanced_quiz_results_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `advanced_quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
