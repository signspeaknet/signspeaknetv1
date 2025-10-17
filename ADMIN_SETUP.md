# Admin System Setup Guide

This guide explains how to set up the admin system for the SignSpeak platform with the new database structure.

## Prerequisites

1. XAMPP or similar local server environment
2. MySQL database running
3. PHP 7.4 or higher

## Database Setup

1. **Import the database schema:**
   ```bash
   # Import the SQL file into your MySQL database
   mysql -u root -p sslocal < sslocal.sql
   ```

2. **Verify the tables were created:**
   - `admin_users` - Stores admin account information
   - `advanced_quizzes` - Stores quiz metadata
   - `advanced_quiz_questions` - Links quizzes to questions from the question bank

## Admin System Setup

1. **Create the default admin user:**
   - Navigate to `http://localhost/signspeak2.6/admin_setup.php`
   - This will create a default admin user with:
     - Username: `admin`
     - Password: `admin123`
     - Role: `super_admin`

2. **Access the admin panel:**
   - Go to `http://localhost/signspeak2.6/admin_login.php`
   - Login with the default credentials

3. **Change the default password:**
   - After first login, go to Profile page
   - Change the default password for security

## Admin Features

### Dashboard
- Overview of system statistics
- Recent quiz activity
- Quick access to main functions

### User Management
- View all registered users
- Manage user status
- View user activity statistics

### Advanced Quiz Management
- Create custom quizzes
- Select questions from the question bank
- Set difficulty levels and question counts
- Manage quiz questions and settings

### Basic Quiz Analytics
- View statistics for existing EPT quizzes
- Generate reports
- Monitor user performance

### Profile Management
- Change admin password
- View account information
- Security tips

## Security Considerations

1. **Change default credentials immediately** after setup
2. **Use strong passwords** (minimum 6 characters, recommended 8+)
3. **Limit admin access** to trusted personnel only
4. **Regular password updates** recommended
5. **Secure server environment** with proper firewall settings

## Database Structure

### admin_users Table
- `admin_id` (Primary Key)
- `username` (Unique)
- `password` (Hashed)
- `email`
- `role` (super_admin, admin, moderator)
- `status` (active, inactive)
- `created_at`
- `last_login`

### advanced_quizzes Table
- `id` (Primary Key)
- `title`
- `description`
- `difficulty_level` (beginner, intermediate, advanced)
- `num_questions`
- `created_at`
- `updated_at`

### advanced_quiz_questions Table
- `id` (Primary Key)
- `quiz_id` (Foreign Key to advanced_quizzes)
- `question_bank_id` (References question in JSON bank)
- `question_order`

## Troubleshooting

### Common Issues

1. **"admin_users table does not exist"**
   - Ensure you've imported the `sslocal.sql` file
   - Check database connection in `config.php`

2. **"Invalid username or password"**
   - Verify the admin user was created via `admin_setup.php`
   - Check if the password was properly hashed

3. **Database connection errors**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running

4. **Permission errors**
   - Check file permissions for PHP files
   - Ensure web server has read access to the directory

### Support

For additional support or questions about the admin system, please refer to the main project documentation or contact the development team.

## File Structure

```
signspeak2.6/
├── admin_login.php          # Admin login page
├── admin_logout.php         # Admin logout handler
├── admin_dashboard.php      # Main admin dashboard
├── admin_users.php          # User management
├── admin_quizzes.php        # Basic quiz analytics
├── admin_advanced_quizzes.php # Advanced quiz management
├── admin_manage_quiz_questions.php # Quiz question management
├── admin_profile.php        # Admin profile and password change
├── admin_setup.php          # Initial admin user creation
├── admin_nav_helper.php     # Navigation helper
├── config.php              # Database configuration
└── sslocal.sql             # Database schema
``` 