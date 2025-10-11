<?php
session_start();
error_reporting(0);
include 'includes/config.php';
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
} else {

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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/modern.css">
    
    <style>
        .chat-container {
            height: 70vh;
            display: flex;
            flex-direction: column;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius);
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
        }
        .message.employee {
            justify-content: flex-end;
        }
        .message.admin {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            position: relative;
        }
        .message.employee .message-bubble {
            background: var(--primary-500);
            color: white;
            border-bottom-right-radius: 0.25rem;
        }
        .message.admin .message-bubble {
            background: white;
            border: 1px solid var(--gray-200);
            border-bottom-left-radius: 0.25rem;
        }
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }
        .chat-input-container {
            border-top: 1px solid var(--gray-200);
            padding: 1rem;
            background: white;
        }
        .online-indicator {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Employee Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-alt me-2"></i>
                ELMS - Employee Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Chat Header -->
                <div class="enhanced-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-headset text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1">Admin Support</h4>
                                <p class="text-muted mb-0">
                                    <span class="online-indicator"></span>
                                    Get help with leave applications, account issues, and general inquiries
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge-enhanced bg-success">
                                    <i class="fas fa-circle"></i> Online
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Container -->
                <div class="enhanced-card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-comments"></i> Support Chat</h5>
                        <small class="text-muted">Typical response time: 1-2 business hours</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="chat-container">
                            <!-- Chat Messages -->
                            <div class="chat-messages" id="chatMessages">
                                <?php if($query->rowCount() > 0) {
                                    foreach(array_reverse($messages) as $message) {
                                        if($message->empid == $empid) {
                                            // Employee message
                                ?>
                                <div class="message employee">
                                    <div class="message-bubble">
                                        <div class="message-text"><?php echo htmlentities($message->message); ?></div>
                                        <div class="message-time text-end">
                                            <?php echo date('M j, g:i A', strtotime($message->timestamp)); ?>
                                            <i class="fas fa-check-double text-success ms-1"></i>
                                        </div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <!-- Admin message -->
                                <div class="message admin">
                                    <div class="message-bubble">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 24px; height: 24px;">
                                                <i class="fas fa-user-shield text-white" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <small class="fw-semibold">Admin Support</small>
                                        </div>
                                        <div class="message-text"><?php echo htmlentities($message->message); ?></div>
                                        <div class="message-time">
                                            <?php echo date('M j, g:i A', strtotime($message->timestamp)); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php }
                                    }
                                } else { ?>
                                <!-- Welcome Message -->
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Start a conversation</h5>
                                    <p class="text-muted">Send a message to get help from our admin team.</p>
                                </div>
                                <?php } ?>
                            </div>

                            <!-- Chat Input -->
                            <div class="chat-input-container">
                                <form method="post" id="chatForm">
                                    <div class="input-group">
                                        <textarea class="form-control" name="message" rows="2" 
                                                  placeholder="Type your message here..." 
                                                  required></textarea>
                                        <button type="submit" name="send" class="btn-enhanced btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send
                                        </button>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        For urgent matters, please call the admin office directly.
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Help Topics -->
                <div class="enhanced-card mt-4">
                    <div class="card-header">
                        <h6><i class="fas fa-question-circle"></i> Quick Help Topics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fas fa-calendar-plus text-primary mt-1 me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Leave Applications</h6>
                                        <p class="text-muted mb-0 small">Questions about applying for leave, status updates, or approval process</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fas fa-user-cog text-success mt-1 me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Account Issues</h6>
                                        <p class="text-muted mb-0 small">Password reset, profile updates, or login problems</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fas fa-file-alt text-warning mt-1 me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Documentation</h6>
                                        <p class="text-muted mb-0 small">Help with required documents or supporting materials</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-clock text-info mt-1 me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Leave Balance</h6>
                                        <p class="text-muted mb-0 small">Questions about available leave days or balance calculations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modern.js"></script>
    
    <script>
        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Scroll to bottom on page load
        $(document).ready(function() {
            scrollToBottom();
        });
        
        // Form submission handling
        $('#chatForm').on('submit', function(e) {
            const message = $('textarea[name="message"]').val().trim();
            if(message === '') {
                e.preventDefault();
                showToast('Please enter a message before sending.', 'warning');
                return false;
            }
            
            // Add loading state
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<div class="spinner"></div> Sending...');
        });
        
        // Auto-refresh chat every 30 seconds
        setInterval(function() {
            $.ajax({
                url: 'get-messages.php', // You'll need to create this endpoint
                type: 'GET',
                success: function(response) {
                    // Update chat messages
                    $('#chatMessages').html(response);
                    scrollToBottom();
                }
            });
        }, 30000);
    </script>
</body>
</html>
<?php } ?>