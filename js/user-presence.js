// User Presence Manager for SignSpeak
// Real-time user presence tracking with Python server

class UserPresenceManager {
    constructor() {
        this.socket = null;
        this.isConnected = false;
        this.activeUsers = [];
        // Replace with your actual Render URL after deployment
        this.pythonServerUrl = 'https://active-user-server.onrender.com';
        this.init();
    }
    
    init() {
        this.connectToServer();
        this.trackActivity();
        setInterval(() => this.updatePresence(), 30000); // Update every 30 seconds
    }
    
    connectToServer() {
        // Connect to Python server
        this.socket = io(this.pythonServerUrl);
        
        this.socket.on('connect', () => {
            console.log('Connected to SignSpeak presence server');
            this.isConnected = true;
            this.updatePresence();
        });
        
        this.socket.on('active_users_update', (data) => {
            this.activeUsers = data.users;
            this.updateUI(data.count);
        });
        
        this.socket.on('disconnect', () => {
            console.log('Disconnected from presence server');
            this.isConnected = false;
        });
        
        this.socket.on('connect_error', (error) => {
            console.error('Connection error:', error);
        });
    }
    
    async updatePresence() {
        if (!this.isConnected) return;
        
        try {
            const response = await fetch('api_user_presence.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'update_presence',
                    page: window.location.pathname,
                    user_action: this.getCurrentAction()
                })
            });
            
            const data = await response.json();
            if (data.success && this.socket) {
                this.socket.emit('user_login', {
                    userId: data.user_id,
                    userInfo: data.user_info
                });
            }
        } catch (error) {
            console.error('Error updating presence:', error);
        }
    }
    
    trackActivity() {
        // Track various user activities to keep them "active"
        ['click', 'scroll', 'keypress', 'mousemove'].forEach(event => {
            document.addEventListener(event, () => {
                if (this.socket && this.isConnected) {
                    this.socket.emit('user_activity', {
                        userId: this.getCurrentUserId()
                    });
                }
            }, { passive: true });
        });
    }
    
    updateUI(userCount) {
        // Update active users count in UI
        const activeUsersElement = document.getElementById('active-users-count');
        if (activeUsersElement) {
            activeUsersElement.textContent = userCount;
        }
        
        // Update admin dashboard if present
        if (window.updateAdminDashboard) {
            window.updateAdminDashboard(userCount, this.activeUsers);
        }
        
        // Update any other UI elements
        const activeUsersDisplay = document.getElementById('active-users-display');
        if (activeUsersDisplay) {
            activeUsersDisplay.textContent = `${userCount} users online`;
        }
        
        console.log(`Active users: ${userCount}`);
    }
    
    getCurrentAction() {
        // Determine current user action based on page
        const path = window.location.pathname;
        if (path.includes('tutorial')) return 'learning';
        if (path.includes('exercise')) return 'practicing';
        if (path.includes('quiz')) return 'testing';
        if (path.includes('progress')) return 'reviewing';
        if (path.includes('translate')) return 'translating';
        if (path.includes('admin')) return 'admin';
        return 'browsing';
    }
    
    getCurrentUserId() {
        // Get user ID from your session or global variable
        return window.currentUserId || null;
    }
    
    // Public method to get current active users
    getActiveUsers() {
        return this.activeUsers;
    }
    
    // Public method to get active user count
    getActiveUserCount() {
        return this.activeUsers.length;
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if user is logged in
    if (window.currentUserId) {
        window.userPresenceManager = new UserPresenceManager();
    }
});

// Export for use in other scripts
window.UserPresenceManager = UserPresenceManager;
