# Advanced Quiz System - SignSpeak

## Overview

The Advanced Quiz System is a comprehensive feature that allows users to take challenging sign language quizzes after completing the basic exercises. It includes admin management capabilities for creating, editing, and managing advanced quizzes.

## Features

### For Users
- **Advanced Quiz Center**: Browse and select from various advanced quizzes
- **Dynamic Difficulty**: Quizzes categorized by difficulty level (Beginner, Intermediate, Advanced)
- **Timer Support**: Optional time limits for quizzes
- **Detailed Results**: Comprehensive feedback with correct/incorrect answers
- **Progress Tracking**: View quiz history and performance statistics
- **Auto-Unlock**: Advanced quizzes unlock after achieving 70%+ on basic exercises

### For Admins
- **Quiz Management**: Create, edit, and delete advanced quizzes
- **Question Management**: Add multiple-choice questions with text, image, or video support
- **Answer Management**: Set correct answers and manage answer options
- **Statistics**: View quiz performance and user engagement metrics
- **Quiz Activation**: Enable/disable quizzes as needed

## Database Structure

### Tables Created
1. **advanced_quizzes**: Main quiz information
2. **advanced_quiz_questions**: Individual questions for each quiz
3. **advanced_quiz_answers**: Answer options for each question
4. **advanced_quiz_attempts**: User quiz attempts and scores
5. **advanced_quiz_user_answers**: Detailed user responses

## Files Created/Modified

### New Files
- `advanced_quiz.php` - Main advanced quiz listing page
- `take_advanced_quiz.php` - Quiz taking interface
- `advanced_quiz_results.php` - Results and feedback page
- `admin_advanced_quizzes.php` - Admin quiz management
- `admin_manage_quiz_questions.php` - Question management interface
- `advanced_quiz_setup.sql` - Database setup script

### Modified Files
- `js/exercise.js` - Added advanced quiz unlock logic
- `ept1alphabet.php` - Added advanced quiz section (example)
- `index.php` - Added navigation link

## Installation

1. **Database Setup**:
   ```bash
   mysql -u root sslocal -e "source advanced_quiz_setup.sql"
   ```

2. **File Permissions**: Ensure web server has read/write access to the project directory

3. **Configuration**: Verify database connection in `config.php`

## Usage

### For Users

1. **Complete Basic Exercises**: Achieve 70% or higher on any basic exercise
2. **Access Advanced Quizzes**: Click "Advanced Quiz" in navigation or unlock after exercise completion
3. **Select Quiz**: Choose from available quizzes based on difficulty
4. **Take Quiz**: Answer questions within time limit (if applicable)
5. **Review Results**: Get detailed feedback and performance statistics

### For Admins

1. **Access Admin Panel**: Login to admin dashboard
2. **Navigate to Advanced Quizzes**: Click "Advanced Quizzes" in sidebar
3. **Create Quiz**: Click "Add Advanced Quiz" button
4. **Configure Quiz**: Set title, description, difficulty, time limit, and passing score
5. **Add Questions**: Use "Manage Questions" to add multiple-choice questions
6. **Set Answers**: Mark correct answers and add distractors
7. **Activate Quiz**: Enable quiz for user access

## Quiz Configuration Options

### Quiz Settings
- **Title**: Quiz name displayed to users
- **Description**: Detailed explanation of quiz content
- **Difficulty Level**: Beginner, Intermediate, or Advanced
- **Time Limit**: Optional time restriction (0 = no limit)
- **Passing Score**: Minimum percentage to pass (default: 70%)

### Question Types
- **Text Questions**: Standard text-based questions
- **Image Questions**: Questions with image media
- **Video Questions**: Questions with video media (future enhancement)

### Answer Configuration
- **Multiple Choice**: 4 answer options per question
- **Single Correct Answer**: Only one answer marked as correct
- **Points System**: Configurable points per question

## Security Features

- **Session Management**: User authentication required
- **Admin Authentication**: Secure admin access control
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: HTML escaping for user inputs
- **CSRF Protection**: Form validation and session checks

## Performance Considerations

- **Database Indexing**: Proper indexes on foreign keys
- **Query Optimization**: Efficient queries with JOINs
- **Caching**: Session-based caching for quiz data
- **Media Optimization**: Responsive images and videos

## Customization

### Styling
- Bootstrap-based responsive design
- Custom CSS classes for quiz-specific styling
- Theme-consistent color scheme

### Functionality
- Modular JavaScript for easy extension
- Configurable scoring algorithms
- Extensible question types

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running

2. **Quiz Not Appearing**:
   - Check if quiz is marked as active
   - Verify user has completed prerequisite exercises

3. **Questions Not Loading**:
   - Check database for question data
   - Verify quiz-question relationships

4. **Results Not Saving**:
   - Check database permissions
   - Verify form submission process

### Debug Mode
Enable error reporting in PHP for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Future Enhancements

1. **Question Types**: Add support for drag-and-drop, matching, and fill-in-the-blank
2. **Analytics**: Advanced reporting and analytics dashboard
3. **Social Features**: Leaderboards and user comparisons
4. **Mobile App**: Native mobile application
5. **AI Integration**: Intelligent question generation and adaptive difficulty

## Support

For technical support or feature requests, contact the development team or refer to the main project documentation.

## License

This advanced quiz system is part of the SignSpeak project and follows the same licensing terms. 