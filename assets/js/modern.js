// Enhanced JavaScript for ELMS
$(document).ready(function() {
    initializeEnhancedFeatures();
    setupFormEnhancements();
    setupTableEnhancements();
});

function initializeEnhancedFeatures() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Setup loading states
    setupLoadingStates();
    
    // Setup form validation
    setupFormValidation();
}

function setupLoadingStates() {
    $(document).on('click', '.btn-enhanced', function() {
        const btn = $(this);
        if (btn.prop('disabled')) return;
        
        btn.prop('disabled', true);
        const originalText = btn.html();
        btn.html('<div class="spinner"></div> Processing...');
        
        // Auto reset after 5 seconds
        setTimeout(() => {
            if (btn.prop('disabled')) {
                btn.prop('disabled', false).html(originalText);
            }
        }, 5000);
    });
}

function setupFormValidation() {
    $('.form-modern').on('submit', function(e) {
        const form = $(this);
        let isValid = true;
        
        form.find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields correctly.', 'warning');
        }
    });
    
    // Real-time validation
    $('.form-modern [required]').on('input', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
}

function setupTableEnhancements() {
    // Add hover effects to table rows
    $('.table-modern tbody tr').hover(
        function() { $(this).addClass('table-active'); },
        function() { $(this).removeClass('table-active'); }
    );
}

// Toast notification system
function showToast(message, type = 'info', duration = 5000) {
    const toastId = 'toast-' + Date.now();
    const icon = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    }[type] || 'info-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="alert-modern alert-${type} d-flex align-items-center" role="alert">
            <i class="fas fa-${icon} me-2"></i>
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" onclick="$(this).closest('.alert-modern').remove()"></button>
        </div>
    `;
    
    // Create toast container if it doesn't exist
    if (!$('.toast-container').length) {
        $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050"></div>');
    }
    
    $('.toast-container').append(toastHtml);
    
    // Auto remove after duration
    setTimeout(() => {
        $('#' + toastId).remove();
    }, duration);
}

// Utility functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function calculateBusinessDays(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    let count = 0;
    
    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
        if (d.getDay() !== 0 && d.getDay() !== 6) {
            count++;
        }
    }
    
    return count;
}

// Make functions available globally
window.showToast = showToast;
window.formatDate = formatDate;
window.calculateBusinessDays = calculateBusinessDays;