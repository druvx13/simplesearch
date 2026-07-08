/**
 * SimpleSearch - Admin Panel Interactions
 */

// Confirm before destructive actions (extra safety for mobile where onclick may behave differently)
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 8 seconds
    var alerts = document.querySelectorAll('.alert-success, .alert-error');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 8000);
    });
});
