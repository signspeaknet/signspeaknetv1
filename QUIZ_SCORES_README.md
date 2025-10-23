# Quiz Score Tracking System

## Overview
This system records all quiz attempts for each user across three quiz types: Basic Quiz, Time Rush, and Math Quiz. Each quiz has its own scoring system, and users can view their best scores, last played dates, and achievement badges on the progress page.

## Database Schema

### Table: `quiz_scores`
```sql
CREATE TABLE `quiz_scores` (
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
);
```

## Scoring Systems

### 1. Basic Quiz (Percentage-based)
- **Score**: `(correct_answers / total_questions) * 100`
- **Range**: 0-100%
- **Stored**: score, total_questions, correct_answers

### 2. Time Rush (Points-based)
- **Score**: Raw points (number of correct answers)
- **Range**: 0-∞ points
- **Stored**: score (as points), correct_answers

### 3. Math Quiz (Percentage-based)
- **Score**: `(correct_answers / total_questions) * 100`
- **Range**: 0-100%
- **Stored**: score, total_questions, correct_answers

## Achievement Badges

### For Percentage-based Quizzes (Basic & Math)
- 🥇 **Gold**: 85-100%
- 🥈 **Silver**: 70-84%
- 🥉 **Bronze**: 0-69%

### For Time Rush (Points-based)
- 🥇 **Gold**: 20+ points
- 🥈 **Silver**: 10-19 points
- 🥉 **Bronze**: 0-9 points

## Installation

### 1. Run Database Migration
Execute the migration file to add the new table:
```bash
mysql -u your_username -p sslocal < migration_add_quiz_scores.sql
```

Or manually run the SQL in phpMyAdmin or your database tool.

### 2. Verify Table Creation
Check that the `quiz_scores` table exists:
```sql
SHOW TABLES LIKE 'quiz_scores';
DESC quiz_scores;
```

## API Endpoint

### `save_quiz_score.php`
Saves quiz scores to the database.

#### Request
```javascript
POST /save_quiz_score.php
Content-Type: application/json

{
  "quiz_type": "basic_quiz",  // or "time_rush", "math_quiz"
  "score": 85.00,
  "total_questions": 10,      // optional
  "correct_answers": 8,       // optional
  "time_taken": 120           // optional (in seconds)
}
```

#### Response (Success)
```json
{
  "success": true,
  "message": "Score saved successfully",
  "id": 123,
  "best_score": 90.00,
  "is_new_best": false
}
```

#### Response (Error)
```json
{
  "success": false,
  "error": "Error message"
}
```

## Frontend Implementation

### Quiz Files Modified
1. **basic_quiz.php**: Saves percentage score on completion
2. **time_rush_quiz.php**: Saves points score on game end
3. **math_quiz.php**: Saves percentage score on completion

### Progress Page (`progress.php`)
- Displays three quiz performance cards
- Shows best score for each quiz
- Displays achievement badges based on performance
- Shows last played date and total attempts
- Provides "Play Now" buttons for easy access

## Features

### 1. Score Recording
- All quiz attempts are recorded (full history)
- Automatic tracking on quiz completion
- No manual intervention required

### 2. Best Score Tracking
- Automatically calculates best score for each quiz type
- Displays "New Best Score!" message when achieved
- Real-time updates on progress page

### 3. Achievement System
- Color-coded badges (Gold, Silver, Bronze)
- Different criteria for different quiz types
- Visual feedback with animations

### 4. Statistics
- Last played date for each quiz
- Total attempts counter
- Historical tracking for future analytics

## Testing

### Test Checklist
- [ ] Database table created successfully
- [ ] Complete Basic Quiz → verify score saved
- [ ] Complete Time Rush → verify points saved
- [ ] Complete Math Quiz → verify score saved
- [ ] Check progress page displays all scores
- [ ] Verify badges show correctly
- [ ] Test "New Best Score!" message
- [ ] Verify last played date updates
- [ ] Check total attempts counter

### Manual Testing
1. Complete each quiz type at least twice
2. Verify scores are saved in database:
```sql
SELECT * FROM quiz_scores WHERE user_id = YOUR_USER_ID ORDER BY completed_at DESC;
```
3. Check progress page for correct display
4. Verify badge colors match score ranges

## Troubleshooting

### Issue: Scores not saving
- Check browser console for JavaScript errors
- Verify `save_quiz_score.php` is accessible
- Check database connection in `config.php`
- Ensure user is logged in (session active)

### Issue: Badges not displaying
- Check browser console for JavaScript errors
- Verify `quizScores` data is passed to JavaScript
- Check badge criteria logic in JavaScript

### Issue: Database errors
- Verify table exists: `SHOW TABLES LIKE 'quiz_scores';`
- Check foreign key constraint on user_id
- Ensure proper permissions for database user

## Future Enhancements

Potential improvements for this system:
- Leaderboards (top scores across all users)
- Score history graphs/charts
- Weekly/monthly challenges
- Social sharing of achievements
- Export score history to CSV
- Detailed analytics dashboard

## Files Modified

### Created
- `save_quiz_score.php` - Score saving API endpoint
- `migration_add_quiz_scores.sql` - Database migration
- `QUIZ_SCORES_README.md` - This documentation

### Modified
- `sslocal.sql` - Added quiz_scores table definition
- `basic_quiz.php` - Added score saving on completion
- `time_rush_quiz.php` - Added score saving on game end
- `math_quiz.php` - Added score saving on completion
- `progress.php` - Added quiz performance cards and badges

## Support

For issues or questions, refer to the main project README or contact the development team.

