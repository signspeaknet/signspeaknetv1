<?php
/**
 * Admin Navigation Helper
 * Automatically detects the current page and sets the correct active state
 */

function getCurrentPage() {
    $current_file = basename($_SERVER['PHP_SELF']);
    return $current_file;
}

function isActivePage($page_name) {
    $current_page = getCurrentPage();
    return ($current_page === $page_name) ? 'active' : '';
}

function renderAdminNav() {
    $current_page = getCurrentPage();
    
    // Define navigation items
    $nav_items = [
        'admin_dashboard.php' => [
            'icon' => 'fas fa-tachometer-alt',
            'text' => 'Dashboard'
        ],
        'admin_users.php' => [
            'icon' => 'fas fa-users',
            'text' => 'User Management'
        ],
        'admin_profile.php' => [
            'icon' => 'fas fa-user-cog',
            'text' => 'Profile'
        ]
    ];
    
    // Special handling for sub-pages
    $parent_pages = [];
    
    // Check if current page is a sub-page
    if (isset($parent_pages[$current_page])) {
        $current_page = $parent_pages[$current_page];
    }
    
    $nav_html = '<nav class="nav flex-column px-3">';
    
    foreach ($nav_items as $page => $item) {
        $active_class = ($current_page === $page) ? 'active' : '';
        $nav_html .= sprintf(
            '<a class="nav-link %s" href="%s">
                <i class="%s me-2"></i>%s
            </a>',
            $active_class,
            $page,
            $item['icon'],
            $item['text']
        );
    }
    
    $nav_html .= '<hr class="text-muted">
                <a class="nav-link" href="admin_logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </nav>';
    
    return $nav_html;
}
?> 