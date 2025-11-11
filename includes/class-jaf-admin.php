<?php
/**
 * Admin interface for Job Application Form
 */

if (!defined('ABSPATH')) {
    exit;
}

class JAF_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_jaf_delete_submission', array($this, 'delete_submission'));
        add_action('wp_ajax_jaf_get_submission_details', array($this, 'get_submission_details'));
        add_action('admin_post_jaf_export_submissions', array($this, 'export_submissions'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Job Applications',
            'Job Applications',
            'manage_options',
            'job-applications',
            array($this, 'render_admin_page'),
            'dashicons-clipboard',
            30
        );
        
        add_submenu_page(
            'job-applications',
            'All Applications',
            'All Applications',
            'manage_options',
            'job-applications',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'job-applications',
            'Settings',
            'Settings',
            'manage_options',
            'job-applications-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'job-applications') === false) {
            return;
        }
        
        wp_enqueue_style('jaf-admin-style', JAF_PLUGIN_URL . 'assets/css/admin.css', array(), JAF_VERSION);
        wp_enqueue_script('jaf-admin-script', JAF_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), JAF_VERSION, true);
        
        wp_localize_script('jaf-admin-script', 'jafAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jaf_admin_nonce')
        ));
    }
    
    public function render_admin_page() {
        $db = JAF_Database::get_instance();
        
        // Handle pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Handle search
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // Handle filter by position
        $position_filter = isset($_GET['position']) ? sanitize_text_field($_GET['position']) : '';
        
        // Get submissions
        $submissions = $db->get_submissions($per_page, $offset, $search, $position_filter);
        $total = $db->get_submissions_count($search, $position_filter);
        $total_pages = ceil($total / $per_page);
        
        // Get unique positions for filter
        $positions = $db->get_unique_positions();
        
        include JAF_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    public function render_settings_page() {
        if (isset($_POST['jaf_settings_submit'])) {
            check_admin_referer('jaf_settings_nonce');
            
            update_option('jaf_notification_email', sanitize_email($_POST['notification_email']));
            update_option('jaf_allowed_file_types', sanitize_text_field($_POST['allowed_file_types']));
            update_option('jaf_max_file_size', intval($_POST['max_file_size']));
            update_option('jaf_app_id_prefix', sanitize_text_field($_POST['app_id_prefix']));
            update_option('jaf_app_id_length', intval($_POST['app_id_length']));
            
            // Thank you page URL
            $thank_you_page = isset($_POST['thank_you_page']) ? esc_url_raw($_POST['thank_you_page']) : '';
            update_option('jaf_thank_you_page', $thank_you_page);
            
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        }
        
        $notification_email = get_option('jaf_notification_email', get_option('admin_email'));
        $allowed_file_types = get_option('jaf_allowed_file_types', 'pdf,doc,docx,txt,rtf');
        $max_file_size = get_option('jaf_max_file_size', 5);
        $app_id_prefix = get_option('jaf_app_id_prefix', 'JAF');
        $app_id_length = get_option('jaf_app_id_length', 8);
        $thank_you_page = get_option('jaf_thank_you_page', '');
        
        include JAF_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    public function delete_submission() {
        check_ajax_referer('jaf_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error(array('message' => 'Invalid ID'));
        }
        
        $db = JAF_Database::get_instance();
        $result = $db->delete_submission($id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Submission deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete submission'));
        }
    }
    
    public function get_submission_details() {
        check_ajax_referer('jaf_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($id <= 0) {
            wp_send_json_error(array('message' => 'Invalid ID'));
        }
        
        $db = JAF_Database::get_instance();
        $submission = $db->get_submission_by_id($id);
        
        if (!$submission) {
            wp_send_json_error(array('message' => 'Submission not found'));
        }
        
        ob_start();
        include JAF_PLUGIN_DIR . 'templates/submission-details.php';
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function export_submissions() {
        // check_admin_referer defaults to checking '_wpnonce' parameter (which wp_nonce_url creates)
        check_admin_referer('jaf_admin_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $db = JAF_Database::get_instance();
        $submissions = $db->get_all_submissions();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="job-applications-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, array(
            'ID',
            'Unique ID',
            'Name',
            'Phone',
            'Email',
            'Position',
            'Message',
            'Resume',
            'Page Title',
            'Page URL',
            'Submitted At'
        ));
        
        // Data rows
        foreach ($submissions as $submission) {
            fputcsv($output, array(
                $submission->id,
                $submission->unique_id,
                $submission->name,
                $submission->phone,
                $submission->email,
                $submission->position,
                $submission->message,
                $submission->resume_path,
                $submission->page_title,
                $submission->page_url,
                $submission->submitted_at
            ));
        }
        
        fclose($output);
        exit;
    }
}

