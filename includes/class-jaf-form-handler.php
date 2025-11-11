<?php
/**
 * Form submission handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class JAF_Form_Handler {
    
    private $file_uploader;
    
    public function __construct() {
        $this->file_uploader = new JAF_File_Uploader();
    }
    
    public function process_submission() {
        // Validate nonce - check both possible field names
        $nonce = '';
        if (isset($_POST['nonce'])) {
            $nonce = $_POST['nonce'];
        } elseif (isset($_POST['jaf_nonce_field'])) {
            $nonce = $_POST['jaf_nonce_field'];
        }
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'jaf_nonce')) {
            return array(
                'success' => false,
                'message' => 'Security check failed. Please refresh the page and try again.'
            );
        }
        
        // Validate required fields
        $validation = $this->validate_fields();
        if (!$validation['valid']) {
            return array(
                'success' => false,
                'message' => $validation['message']
            );
        }
        
        // Generate unique ID
        $unique_id = $this->generate_unique_id();
        
        // Handle file upload
        $resume_path = '';
        if (!empty($_FILES['resume']['name'])) {
            $upload_result = $this->file_uploader->handle_upload($unique_id);
            if (!$upload_result['success']) {
                return array(
                    'success' => false,
                    'message' => $upload_result['message']
                );
            }
            $resume_path = $upload_result['file_path'];
        }
        
        // Get page information
        $page_title = isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        
        // Fallback if not provided (shouldn't happen with JS, but just in case)
        if (empty($page_title)) {
            $page_title = function_exists('get_the_title') ? get_the_title() : 'Unknown Page';
        }
        if (empty($page_url)) {
            $page_url = function_exists('get_permalink') ? get_permalink() : (isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : home_url());
        }
        
        // Prepare data for database
        $data = array(
            'unique_id' => $unique_id,
            'name' => sanitize_text_field($_POST['name']),
            'phone' => sanitize_text_field($_POST['phone']),
            'email' => sanitize_email($_POST['email']),
            'position' => sanitize_text_field($_POST['position']),
            'message' => isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '',
            'resume_path' => $resume_path,
            'page_title' => $page_title,
            'page_url' => $page_url
        );
        
        // Save to database
        $db = JAF_Database::get_instance();
        $insert_id = $db->insert_submission($data);
        
        if (!$insert_id) {
            // Get more detailed error message
            global $wpdb;
            $error_message = 'Failed to save your application. Please try again.';
            
            if (defined('WP_DEBUG') && WP_DEBUG && !empty($wpdb->last_error)) {
                $error_message .= ' Error: ' . $wpdb->last_error;
            }
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        // Send email notification
        $email_sent = $this->send_email_notification($data);
        
        // Get thank you page URL from settings
        $thank_you_page = get_option('jaf_thank_you_page', '');
        
        // If no custom thank you page is set, use default
        if (empty($thank_you_page)) {
            $thank_you_page = home_url('/thank-you/');
        }
        
        // Ensure it's a valid URL
        if (!filter_var($thank_you_page, FILTER_VALIDATE_URL)) {
            $thank_you_page = home_url('/thank-you/');
        }
        
        // Add application ID as query parameter
        $redirect_url = add_query_arg('app_id', $unique_id, $thank_you_page);
        
        return array(
            'success' => true,
            'message' => 'Thank you! Your application has been submitted successfully.',
            'unique_id' => $unique_id,
            'email_sent' => $email_sent,
            'redirect_url' => $redirect_url
        );
    }
    
    private function validate_fields() {
        $required_fields = array('name', 'phone', 'email', 'position');
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return array(
                    'valid' => false,
                    'message' => ucfirst($field) . ' is required.'
                );
            }
        }
        
        // Validate email format
        if (!is_email($_POST['email'])) {
            return array(
                'valid' => false,
                'message' => 'Please enter a valid email address.'
            );
        }
        
        return array('valid' => true);
    }
    
    private function generate_unique_id() {
        // Get settings from admin options
        $prefix = get_option('jaf_app_id_prefix', 'JAF');
        $length = intval(get_option('jaf_app_id_length', 8));
        
        // Sanitize prefix (alphanumeric only, max 10 chars)
        $prefix = preg_replace('/[^A-Za-z0-9]/', '', $prefix);
        $prefix = substr(strtoupper($prefix), 0, 10);
        
        // Ensure length is valid (4-12)
        if ($length < 4) $length = 4;
        if ($length > 12) $length = 12;
        
        // Generate random alphanumeric string
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[mt_rand(0, $characters_length - 1)];
        }
        
        // Format: PREFIX-RANDOM (e.g., JAF-12345678)
        $unique_id = $prefix . '-' . $random_string;
        
        // Ensure uniqueness by checking database
        $db = JAF_Database::get_instance();
        $max_attempts = 10;
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            $existing = $db->get_submission($unique_id);
            
            if (!$existing) {
                // Unique ID found
                break;
            }
            
            // Generate new random string if collision
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[mt_rand(0, $characters_length - 1)];
            }
            $unique_id = $prefix . '-' . $random_string;
            $attempt++;
        }
        
        // Ensure it fits in database field (varchar(20))
        if (strlen($unique_id) > 20) {
            // Adjust prefix if needed
            $max_prefix_length = 20 - $length - 1; // -1 for separator
            if (strlen($prefix) > $max_prefix_length) {
                $prefix = substr($prefix, 0, $max_prefix_length);
            }
            $unique_id = $prefix . '-' . $random_string;
            $unique_id = substr($unique_id, 0, 20);
        }
        
        return $unique_id;
    }
    
    private function send_email_notification($data) {
        // Get notification email from settings or use admin email
        $notification_email = get_option('jaf_notification_email', get_option('admin_email'));
        
        // Send HTML email to admin
        $admin_subject = 'New Job Application: ' . $data['position'];
        $admin_message = $this->get_email_template('admin', $data);
        $admin_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        $admin_email_sent = wp_mail($notification_email, $admin_subject, $admin_message, $admin_headers);
        
        // Send HTML confirmation email to applicant
        $applicant_subject = 'Application Received - ' . get_bloginfo('name');
        $applicant_message = $this->get_email_template('applicant', $data);
        $applicant_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        $applicant_email_sent = wp_mail($data['email'], $applicant_subject, $applicant_message, $applicant_headers);
        
        return $admin_email_sent && $applicant_email_sent;
    }
    
    private function get_email_template($type, $data) {
        $template_file = '';
        
        if ($type === 'admin') {
            $template_file = JAF_PLUGIN_DIR . 'templates/email-admin-notification.php';
        } elseif ($type === 'applicant') {
            $template_file = JAF_PLUGIN_DIR . 'templates/email-applicant-confirmation.php';
        }
        
        if (empty($template_file) || !file_exists($template_file)) {
            return '';
        }
        
        // Make $data available in template scope
        extract(array('data' => $data));
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
}

