<?php
session_start();
include('includes/config.php');

// FIXED: Check for the correct session variables
if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Admin | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Header should be fixed at top */
        .header-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 70px;
        }
        
        /* Main layout container */
        .main-container {
            display: flex;
            min-height: 100vh;
            padding-top: 70px; /* Account for fixed header */
        }
        
        /* Sidebar styling */
        .sidebar-container {
            width: 280px;
            position: fixed;
            left: 0;
            top: 70px;
            bottom: 0;
            z-index: 1020;
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        /* Main content area */
        .content-container {
            flex: 1;
            margin-left: 280px;
            min-height: calc(100vh - 70px);
            transition: margin-left 0.3s ease;
        }
        
        .content-area {
            padding: 30px;
            min-height: 100%;
        }
        
        @media (max-width: 991.98px) {
            .sidebar-container {
                transform: translateX(-100%);
            }
            
            .sidebar-container.show {
                transform: translateX(0);
            }
            
            .content-container {
                margin-left: 0;
            }
        }
        
        .page-title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }
        
        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: #4f46e5;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: var(--secondary-gradient);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .chat-container {
            height: 400px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            overflow: hidden;
        }
        
        .chat-messages {
            height: 320px;
            overflow-y: auto;
            padding: 1rem;
            background: #f8fafc;
        }
        
        .message {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            max-width: 70%;
        }
        
        .message.user {
            background: var(--primary-gradient);
            color: white;
            margin-left: auto;
        }
        
        .message.admin {
            background: white;
            border: 1px solid #e2e8f0;
            margin-right: auto;
        }
        
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }
        
        .chat-input {
            border-top: 1px solid #e2e8f0;
            padding: 1rem;
            background: white;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .admin-info {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Mobile sidebar backdrop */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1019;
            display: none;
        }
        
        .sidebar-backdrop.show {
            display: block;
        }
    </style>
</head>

<body>
    <!-- Header Container (Fixed at top) -->
    <div class="header-container">
        <?php include 'includes/header.php'; ?>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar Container (Fixed beside content) -->
        <div class="sidebar-container" id="sidebarContainer">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Content Container (Beside sidebar) -->
        <div class="content-container">
            <div class="content-area">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="page-title">
                                <i class="fas fa-comments me-3"></i>Chat with Admin
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="employee/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-primary">Chat with Admin</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Admin Information -->
                    <div class="admin-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="text-primary mb-2">
                                    <i class="fas fa-user-shield me-2"></i>Administrator Support
                                </h5>
                                <p class="text-muted mb-0">
                                    Get help with leave applications, policy questions, or any other concerns. 
                                    Our admin team is here to assist you.
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="status-indicator">
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle me-1"></i>Online
                                    </span>
                                </div>
                                <small class="text-muted">Response time: 1-2 hours</small>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Interface -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comment-dots me-2"></i>Live Chat
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="chat-container">
                                <div class="chat-messages" id="chatMessages">
                                    <!-- Sample messages -->
                                    <div class="message user">
                                        <div class="message-text">Hello, I have a question about my leave balance.</div>
                                        <div class="message-time">10:30 AM</div>
                                    </div>
                                    <div class="message admin">
                                        <div class="message-text">Hello! I'd be happy to help. What would you like to know about your leave balance?</div>
                                        <div class="message-time">10:32 AM</div>
                                    </div>
                                    <div class="message user">
                                        <div class="message-text">How many sick leaves do I have remaining this year?</div>
                                        <div class="message-time">10:33 AM</div>
                                    </div>
                                    <div class="message admin">
                                        <div class="message-text">Let me check that for you. According to our records, you have 8 sick leaves remaining for this year.</div>
                                        <div class="message-time">10:35 AM</div>
                                    </div>
                                </div>
                                <div class="chat-input">
                                    <form id="chatForm">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Type your message..." id="messageInput">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Information -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>Support Hours
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><strong>Weekdays:</strong> 9:00 AM - 6:00 PM</li>
                                        <li class="mb-2"><strong>Weekends:</strong> 10:00 AM - 4:00 PM</li>
                                        <li class="mb-0"><strong>Emergency:</strong> 24/7 via email</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-phone me-2"></i>Contact Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> admin@company.com</li>
                                        <li class="mb-2"><i class="fas fa-phone me-2 text-success"></i> +1 (555) 123-4567</li>
                                        <li class="mb-0"><i class="fas fa-building me-2 text-secondary"></i> Office: Room 205, Main Building</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Auto-scroll to bottom of chat
            function scrollToBottom() {
                const chatMessages = document.getElementById('chatMessages');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Initial scroll to bottom
            scrollToBottom();

            // Chat form submission
            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                const messageInput = $('#messageInput');
                const message = messageInput.val().trim();
                
                if (message) {
                    // Add user message to chat
                    const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const userMessage = `
                        <div class="message user">
                            <div class="message-text">${message}</div>
                            <div class="message-time">${timestamp}</div>
                        </div>
                    `;
                    $('#chatMessages').append(userMessage);
                    
                    // Clear input
                    messageInput.val('');
                    
                    // Scroll to bottom
                    scrollToBottom();
                    
                    // Simulate admin response after 2 seconds
                    setTimeout(() => {
                        const responses = [
                            "Thank you for your message. I'll look into this for you.",
                            "I understand your concern. Let me check the details.",
                            "That's a good question. Here's what I can tell you...",
                            "I appreciate you bringing this to my attention.",
                            "Let me get that information for you right away."
                        ];
                        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                        const adminTimestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        const adminMessage = `
                            <div class="message admin">
                                <div class="message-text">${randomResponse}</div>
                                <div class="message-time">${adminTimestamp}</div>
                            </div>
                        `;
                        $('#chatMessages').append(adminMessage);
                        scrollToBottom();
                    }, 2000);
                }
            });

            // Mobile sidebar functionality
            $('.mobile-menu-btn').on('click', function() {
                $('#sidebarContainer').toggleClass('show');
                $('.sidebar-backdrop').toggleClass('show');
            });
            
            // Close sidebar when clicking on backdrop
            $('.sidebar-backdrop').on('click', function() {
                $('#sidebarContainer').removeClass('show');
                $(this).removeClass('show');
            });
            
            // Auto-close sidebar on mobile when clicking a link
            $('.sidebar-container .nav-link').on('click', function() {
                if ($(window).width() < 992) {
                    $('#sidebarContainer').removeClass('show');
                    $('.sidebar-backdrop').removeClass('show');
                }
            });
        });
    </script>
</body>
</html>
<?php } ?>