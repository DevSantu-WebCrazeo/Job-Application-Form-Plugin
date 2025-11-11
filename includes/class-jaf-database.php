<?php
/**
 * Database handler for Job Application Form
 */

if (!defined('ABSPATH')) {
    exit;
}

class JAF_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
            // Ensure table exists on first load
            self::$instance->ensure_table_exists();
        }
        return self::$instance;
    }
    
    private function ensure_table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_applications';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            self::create_table();
        }
    }
    
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            unique_id varchar(20) NOT NULL,
            name varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            email varchar(255) NOT NULL,
            position varchar(255) NOT NULL,
            message text,
            resume_path varchar(500),
            page_title varchar(500),
            page_url varchar(500),
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_id (unique_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function insert_submission($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            self::create_table();
        }
        
        // Check for duplicate unique_id
        $unique_id = $data['unique_id'];
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE unique_id = %s",
            $unique_id
        ));
        
        if ($existing) {
            // Generate new unique ID if duplicate found (fallback)
            $prefix = get_option('jaf_app_id_prefix', 'JAF');
            $length = intval(get_option('jaf_app_id_length', 8));
            $prefix = preg_replace('/[^A-Za-z0-9]/', '', $prefix);
            $prefix = substr(strtoupper($prefix), 0, 10);
            if ($length < 4) $length = 4;
            if ($length > 12) $length = 12;
            
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            $unique_id = $prefix . '-' . $random_string;
            $unique_id = substr($unique_id, 0, 20);
            $data['unique_id'] = $unique_id; // Update in original data array too
        }
        
        // Ensure all required fields have values
        $insert_data = array(
            'unique_id' => substr($unique_id, 0, 20), // Ensure it fits in varchar(20) - format: PREFIX-RANDOM
            'name' => substr(sanitize_text_field($data['name']), 0, 255),
            'phone' => substr(sanitize_text_field($data['phone']), 0, 50),
            'email' => substr(sanitize_email($data['email']), 0, 255),
            'position' => substr(sanitize_text_field($data['position']), 0, 255),
            'message' => sanitize_textarea_field($data['message']),
            'resume_path' => !empty($data['resume_path']) ? substr($data['resume_path'], 0, 500) : '',
            'page_title' => !empty($data['page_title']) ? substr(sanitize_text_field($data['page_title']), 0, 500) : '',
            'page_url' => !empty($data['page_url']) ? substr(esc_url_raw($data['page_url']), 0, 500) : ''
        );
        
        $result = $wpdb->insert(
            $table_name,
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Log the error for debugging (only in debug mode)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JAF Database Insert Error: ' . $wpdb->last_error);
                error_log('JAF Insert Data: ' . print_r($insert_data, true));
            }
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    public function get_submission($unique_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE unique_id = %s",
                $unique_id
            )
        );
    }
    
    public function get_submission_by_id($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $id
            )
        );
    }
    
    public function get_submission_by_filename($filename) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE resume_path LIKE %s",
                '%' . $wpdb->esc_like($filename) . '%'
            )
        );
    }
    
    public function get_submissions($per_page = 20, $offset = 0, $search = '', $position_filter = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        $where = array();
        $where_values = array();
        
        if (!empty($search)) {
            $where[] = "(name LIKE %s OR email LIKE %s OR phone LIKE %s OR unique_id LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($position_filter)) {
            $where[] = "position = %s";
            $where_values[] = $position_filter;
        }
        
        // Build WHERE clause
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where_clause = '';
        }
        
        // Build query with proper placeholder handling
        $query = "SELECT * FROM $table_name $where_clause ORDER BY submitted_at DESC LIMIT %d OFFSET %d";
        
        // Combine where values with limit/offset values
        $all_values = array_merge($where_values, array($per_page, $offset));
        
        // Prepare and execute query
        return $wpdb->get_results($wpdb->prepare($query, $all_values));
    }
    
    public function get_submissions_count($search = '', $position_filter = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        $where = array();
        $where_values = array();
        
        if (!empty($search)) {
            $where[] = "(name LIKE %s OR email LIKE %s OR phone LIKE %s OR unique_id LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($position_filter)) {
            $where[] = "position = %s";
            $where_values[] = $position_filter;
        }
        
        // Build WHERE clause
        if (!empty($where)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where);
            $query = "SELECT COUNT(*) FROM $table_name $where_clause";
            return (int) $wpdb->get_var($wpdb->prepare($query, $where_values));
        } else {
            // No conditions, just get count directly
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }
    }
    
    public function get_unique_positions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        return $wpdb->get_col("SELECT DISTINCT position FROM $table_name WHERE position != '' ORDER BY position ASC");
    }
    
    public function delete_submission($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        // Get submission to delete associated file
        $submission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        
        if ($submission && !empty($submission->resume_path)) {
            // Delete the file
            $upload_dir = wp_upload_dir();
            $file_path = '';
            
            // Handle different path formats
            if (strpos($submission->resume_path, 'http') === 0) {
                // It's a URL, convert to path
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $submission->resume_path);
            } elseif (strpos($submission->resume_path, '/') === 0 || strpos($submission->resume_path, $upload_dir['basedir']) === 0) {
                // Absolute path
                $file_path = $submission->resume_path;
            } else {
                // Filename only or relative path
                $file_path = $upload_dir['basedir'] . '/job-applications/' . basename($submission->resume_path);
            }
            
            if (file_exists($file_path) && is_file($file_path)) {
                // Attempt to delete the file
                if (!@unlink($file_path)) {
                    // Log error if deletion fails (only in debug mode)
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('JAF: Failed to delete file: ' . $file_path);
                    }
                }
            }
        }
        
        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }
    
    public function get_all_submissions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_applications';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");
    }
}

