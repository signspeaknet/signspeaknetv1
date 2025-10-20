<?php
/**
 * User Helper Functions
 * Contains utility functions for user-related operations
 */

/**
 * Get user initials from username/email
 * @param string $username The username or email address
 * @return string The initials (1-2 characters)
 */
function getUserInitials($username) {
    if (empty($username)) {
        return 'U'; // Default initial
    }
    
    // Remove email domain if it's an email
    $name = explode('@', $username)[0];
    
    // Split by common separators (., -, _, space)
    $parts = preg_split('/[.\s\-_]+/', $name);
    
    // Filter out empty parts
    $parts = array_filter($parts, function($part) {
        return !empty(trim($part));
    });
    
    if (empty($parts)) {
        return strtoupper(substr($name, 0, 1));
    }
    
    // Get first character of each part
    $initials = '';
    foreach ($parts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr(trim($part), 0, 1));
        }
    }
    
    // Return maximum 2 characters
    return substr($initials, 0, 2);
}

/**
 * Get user display name from username/email
 * @param string $username The username or email address
 * @return string The display name
 */
function getUserDisplayName($username) {
    if (empty($username)) {
        return 'User';
    }
    
    // If it's an email, extract the name part
    if (strpos($username, '@') !== false) {
        $name = explode('@', $username)[0];
        // Replace dots and underscores with spaces for better display
        $name = str_replace(['.', '_'], ' ', $name);
        return ucwords($name);
    }
    
    return ucwords($username);
}
?>
