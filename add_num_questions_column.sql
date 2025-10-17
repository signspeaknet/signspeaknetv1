-- Add num_questions column to advanced_quizzes table if it doesn't exist
ALTER TABLE `advanced_quizzes` 
ADD COLUMN `num_questions` int(11) DEFAULT 10 AFTER `passing_score`;

-- Update existing quizzes to have a default number of questions
UPDATE `advanced_quizzes` SET `num_questions` = 10 WHERE `num_questions` IS NULL; 