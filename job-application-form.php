<?php
/*
Plugin Name: Job Application Form
Plugin URI: https://devsantu.in
Description: A professional WordPress plugin for managing job applications with file upload, email notifications, virus checking, and admin management.
Version: 2.0.0
Author: DevSantu
Author URI: https://devsantu.in
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: job-application-form
*/


// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JAF_VERSION', '1.0.0');
define('JAF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JAF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once JAF_PLUGIN_DIR . 'includes/class-jaf-database.php';
require_once JAF_PLUGIN_DIR . 'includes/class-jaf-form-handler.php';
require_once JAF_PLUGIN_DIR . 'includes/class-jaf-file-uploader.php';
require_once JAF_PLUGIN_DIR . 'includes/class-jaf-file-downloader.php';
require_once JAF_PLUGIN_DIR . 'includes/class-jaf-admin.php';

// Main plugin class
class Job_Application_Form {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcode
        add_shortcode('job_application_form', array($this, 'render_form'));
        
        // AJAX handlers
        add_action('wp_ajax_jaf_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_jaf_submit_form', array($this, 'handle_form_submission'));
        
        // Initialize database
        JAF_Database::get_instance();
        
        // Initialize file downloader
        JAF_File_Downloader::get_instance();
        
        // Initialize admin
        if (is_admin()) {
            JAF_Admin::get_instance();
        }
    }
    
    public function activate() {
        // Create database table
        JAF_Database::create_table();
        
        // Ensure upload directory and .htaccess file exist
        JAF_File_Uploader::ensure_upload_directory();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('jaf-style', JAF_PLUGIN_URL . 'assets/css/style.css', array(), JAF_VERSION);
        wp_enqueue_script('jaf-script', JAF_PLUGIN_URL . 'assets/js/script.js', array('jquery'), JAF_VERSION, true);
        
        wp_localize_script('jaf-script', 'jafAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jaf_nonce')
        ));
    }
    
    public function render_form($atts) {
        ob_start();
        include JAF_PLUGIN_DIR . 'templates/form-template.php';
        return ob_get_clean();
    }
    
    public function handle_form_submission() {
        // Nonce validation is handled in JAF_Form_Handler::process_submission()
        // to avoid duplicate validation and allow flexibility in nonce field names
        $handler = new JAF_Form_Handler();
        $result = $handler->process_submission();
        
        wp_send_json($result);
    }
}

// Show Application ID on thank-you page
function jaf_show_application_id_shortcode() {
    if (isset($_GET['app_id']) && !empty($_GET['app_id'])) {
        return '<div class="jaf-app-id-box">
            <strong>Your Application ID:</strong> ' . esc_html($_GET['app_id']) . '
        </div>';
    }
    return '';
}
add_shortcode('application_id', 'jaf_show_application_id_shortcode');


// Initialize the plugin
Job_Application_Form::get_instance();

