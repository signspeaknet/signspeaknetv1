<?php
session_start();

// Check if the user confirmed logout
if (isset($_GET['confirmed']) && $_GET['confirmed'] === 'true') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: login.php");
    exit();
} else {
    // Show styled confirmation modal
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
                padding: 20px;
            }
            .modal-content {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                text-align: center;
                width: 100%;
                max-width: 340px;
            }
            .modal-title {
                color: #06BBCC;
                margin-bottom: 20px;
                font-size: 1.8em;
                font-weight: 600;
            }
            .modal-content p {
                font-size: 1.1em;
                color: #333;
                margin-bottom: 25px;
                line-height: 1.4;
            }
            .modal-buttons {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 20px;
            }
            .btn {
                padding: 14px 20px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 1.1em;
                font-weight: 500;
                transition: all 0.3s ease;
                width: 100%;
            }
            .btn-confirm {
                background-color: #06BBCC;
                color: white;
            }
            .btn-confirm:hover {
                background-color: #05a3b1;
            }
            .btn-cancel {
                background-color: #f1f3f4;
                color: #3c4043;
            }
            .btn-cancel:hover {
                background-color: #e8eaed;
            }
            @media (max-width: 375px) {
                .modal-content {
                    padding: 20px;
                }
                .modal-title {
                    font-size: 1.6em;
                }
                .modal-content p {
                    font-size: 1em;
                }
                .btn {
                    padding: 12px 20px;
                    font-size: 1em;
                }
            }
        </style>
    </head>
    <body>
        <div class="modal-overlay">
            <div class="modal-content">
                <div class="modal-title">Confirm Logout</div>
                <p>Are you sure you want to logout?</p>
                <div class="modal-buttons">
                    <button class="btn btn-confirm" onclick="window.location.href=\'logout.php?confirmed=true\'">Logout</button>
                    <button class="btn btn-cancel" onclick="window.history.back()">Cancel</button>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
?> 