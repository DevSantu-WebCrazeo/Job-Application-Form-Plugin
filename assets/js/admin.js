jQuery(document).ready(function($) {
    'use strict';
    
    // View submission details
    $('.jaf-view-btn').on('click', function() {
        const id = $(this).data('id');
        loadSubmissionDetails(id);
    });
    
    // Delete submission
    $('.jaf-delete-btn').on('click', function() {
        if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            return;
        }
        
        const id = $(this).data('id');
        const row = $(this).closest('tr');
        
        $.ajax({
            url: jafAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'jaf_delete_submission',
                id: id,
                nonce: jafAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                        // Check if table is empty
                        if ($('.jaf-submissions-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data.message || 'Failed to delete submission.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Close modal
    $('.jaf-modal-close, .jaf-modal').on('click', function(e) {
        if (e.target === this) {
            $('#jaf-view-modal').fadeOut();
        }
    });
    
    // Load submission details
    function loadSubmissionDetails(id) {
        const modal = $('#jaf-view-modal');
        const modalBody = $('#jaf-modal-body');
        
        modalBody.html('<p>Loading...</p>');
        modal.fadeIn();
        
        $.ajax({
            url: jafAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'jaf_get_submission_details',
                id: id,
                nonce: jafAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    modalBody.html(response.data.html);
                } else {
                    modalBody.html('<p>Error loading submission details.</p>');
                }
            },
            error: function() {
                modalBody.html('<p>An error occurred. Please try again.</p>');
            }
        });
    }
    
    // Export CSV
    $('#jaf-export-btn').on('click', function(e) {
        // Let the link work normally for CSV export
        // No need to prevent default
    });
});

