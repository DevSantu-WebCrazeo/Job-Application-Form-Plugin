<?php
/**
 * Secure file download handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class JAF_File_Downloader {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'handle_download_request'));
    }
    
    public function handle_download_request() {
        if (!isset($_GET['jaf_download']) || empty($_GET['jaf_download'])) {
            return;
        }
        
        $file_id = sanitize_text_field($_GET['jaf_download']);
        $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';
        
        // Verify nonce
        if (!wp_verify_nonce($nonce, 'jaf_download_' . $file_id)) {
            wp_die('Invalid download link.', 'Error', array('response' => 403));
        }
        
        // Get submission by unique_id or filename
        $db = JAF_Database::get_instance();
        $submission = $db->get_submission_by_filename($file_id);
        
        if (!$submission) {
            // Try by unique_id
            $submission = $db->get_submission($file_id);
        }
        
        if (!$submission || empty($submission->resume_path)) {
            wp_die('File not found.', 'Error', array('response' => 404));
        }
        
        // Check if user has permission (admin or the applicant)
        $can_download = false;
        
        // Admin can always download - no restrictions
        if (current_user_can('manage_options')) {
            $can_download = true;
        } else {
            // Check if it's the applicant (by email)
            if (isset($_GET['email']) && is_email($_GET['email'])) {
                $email = sanitize_email($_GET['email']);
                if ($email === $submission->email) {
                    $can_download = true;
                }
            }
        }
        
        if (!$can_download) {
            wp_die('You do not have permission to download this file.', 'Error', array('response' => 403));
        }
        
        // Get file path
        $upload_dir = wp_upload_dir();
        $file_path = '';
        
        // Check if resume_path is a URL or relative path
        if (strpos($submission->resume_path, 'http') === 0) {
            // It's a URL, convert to path
            $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $submission->resume_path);
        } else {
            // It's already a path or filename
            if (strpos($submission->resume_path, '/') === 0 || strpos($submission->resume_path, $upload_dir['basedir']) === 0) {
                // Absolute path
                $file_path = $submission->resume_path;
            } else {
                // Relative path or filename
                $file_path = $upload_dir['basedir'] . '/job-applications/' . basename($submission->resume_path);
            }
        }
        
        // Check if file exists
        if (!file_exists($file_path)) {
            wp_die('File not found on server.', 'Error', array('response' => 404));
        }
        
        // Get file info
        $filename = basename($file_path);
        $file_size = filesize($file_path);
        $mime_type = $this->get_mime_type($file_path);
        
        // Output file
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Read and output file
        readfile($file_path);
        exit;
    }
    
    private function get_mime_type($file_path) {
        $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        $mime_types = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        );
        
        return isset($mime_types[$file_ext]) ? $mime_types[$file_ext] : 'application/octet-stream';
    }
    
    public static function get_download_url($submission_id, $unique_id, $email = '') {
        $nonce = wp_create_nonce('jaf_download_' . $unique_id);
        $url = add_query_arg(array(
            'jaf_download' => $unique_id,
            'nonce' => $nonce
        ), home_url('/'));
        
        if (!empty($email)) {
            $url = add_query_arg('email', urlencode($email), $url);
        }
        
        return $url;
    }
}

