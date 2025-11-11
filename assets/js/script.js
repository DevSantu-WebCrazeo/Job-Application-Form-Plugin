jQuery(document).ready(function($) {
    'use strict';
    
    const form = $('#jaf-application-form');
    const fileInput = $('#jaf-resume');
    const fileButton = $('#jaf-file-button');
    const fileList = $('#jaf-file-list');
    const messageContainer = $('#jaf-message-container');
    
    // File upload button click
    fileButton.on('click', function() {
        fileInput.click();
    });
    
    // File input change
    fileInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            displayFile(file);
        }
    });
    
    // Display selected file
    function displayFile(file) {
        const fileItem = $('<div class="jaf-file-item"></div>');
        const fileName = $('<span></span>').text(file.name);
        const removeBtn = $('<button type="button" class="jaf-file-remove">×</button>');
        
        removeBtn.on('click', function() {
            fileItem.remove();
            fileInput.val('');
        });
        
        fileItem.append(fileName).append(removeBtn);
        fileList.empty().append(fileItem);
    }
    
    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();
        
        // Get page title and URL
        const pageTitle = document.title || $('#jaf-page-title').val();
        const pageUrl = window.location.href || $('#jaf-page-url').val();
        
        $('#jaf-page-title').val(pageTitle);
        $('#jaf-page-url').val(pageUrl);
        
        // Disable submit button
        const submitButton = form.find('.jaf-submit-button');
        submitButton.prop('disabled', true).text('Submitting...');
        
        // Hide previous messages
        messageContainer.removeClass('show success error');
        
        // Prepare form data
        const formData = new FormData(this);
        formData.append('action', 'jaf_submit_form');
        
        // Get nonce from form field or AJAX object
        const nonceField = form.find('input[name="jaf_nonce_field"]');
        const nonce = nonceField.length ? nonceField.val() : (typeof jafAjax !== 'undefined' ? jafAjax.nonce : '');
        formData.append('nonce', nonce);
        
        // Submit via AJAX
        $.ajax({
            url: jafAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
            
                    // Redirect to Thank You page
                    if (response.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.redirect_url;
                        }, 1500);
                    }
            
                    form[0].reset();
                    fileList.empty();
            
                } else {
                    showMessage(response.message || 'An error occurred. Please try again.', 'error');
                }
            },
            
            error: function() {
                showMessage('Network error. Please check your connection and try again.', 'error');
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Submit');
            }
        });
    });
    
    // Show message
    function showMessage(message, type) {
        messageContainer
            .removeClass('success error')
            .addClass('show ' + type)
            .text(message)
            .fadeIn();
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                messageContainer.fadeOut();
            }, 5000);
        }
    }

    
});

