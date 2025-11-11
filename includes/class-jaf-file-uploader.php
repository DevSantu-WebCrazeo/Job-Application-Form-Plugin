<?php
/**
 * File upload handler with virus checking
 */

if (!defined('ABSPATH')) {
    exit;
}

class JAF_File_Uploader {
    
    private $allowed_types;
    private $max_file_size;
    
    public function __construct() {
        // Get settings from options
        $allowed_types_str = get_option('jaf_allowed_file_types', 'pdf,doc,docx,txt,rtf');
        $this->allowed_types = array_map('trim', explode(',', strtolower($allowed_types_str)));
        $this->max_file_size = intval(get_option('jaf_max_file_size', 5)) * 1048576; // Convert MB to bytes
    }
    
    /**
     * Get MIME types mapping for file extensions
     */
    private function get_mime_type_map() {
        return array(
            'pdf' => array('application/pdf'),
            'doc' => array('application/msword'),
            'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'txt' => array('text/plain'),
            'rtf' => array('application/rtf', 'text/rtf'),
            'png' => array('image/png'),
            'jpg' => array('image/jpeg', 'image/jpg'),
            'jpeg' => array('image/jpeg', 'image/jpg'),
            'gif' => array('image/gif'),
            'webp' => array('image/webp'),
        );
    }
    
    /**
     * Get allowed MIME types based on allowed file extensions
     */
    private function get_allowed_mime_types() {
        $mime_map = $this->get_mime_type_map();
        $allowed_mimes = array();
        
        foreach ($this->allowed_types as $ext) {
            if (isset($mime_map[$ext])) {
                $allowed_mimes = array_merge($allowed_mimes, $mime_map[$ext]);
            }
        }
        
        // Remove duplicates and return
        return array_unique($allowed_mimes);
    }
    
    /**
     * Ensure upload directory and .htaccess file exist with proper permissions
     * Called on plugin activation and when needed
     */
    public static function ensure_upload_directory() {
        $upload_dir = wp_upload_dir();
        $jaf_dir = $upload_dir['basedir'] . '/job-applications';
        
        // Create directory if it doesn't exist
        if (!file_exists($jaf_dir)) {
            wp_mkdir_p($jaf_dir);
            @chmod($jaf_dir, 0755);
        }
        
        // Ensure .htaccess exists and is up-to-date
        self::create_htaccess_file($jaf_dir);
    }
    
    /**
     * Create or update .htaccess file for security
     */
    private static function create_htaccess_file($directory) {
        $htaccess_file = $directory . '/.htaccess';
        
        // Enhanced .htaccess for security (Apache 2.2 and 2.4 compatible)
        $htaccess_content = "# Job Application Form - Secure File Protection\n";
        $htaccess_content .= "# Deny all direct access (Apache 2.2)\n";
        $htaccess_content .= "<IfModule mod_authz_host.c>\n";
        $htaccess_content .= "    Order deny,allow\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</IfModule>\n\n";
        $htaccess_content .= "# Deny all direct access (Apache 2.4+)\n";
        $htaccess_content .= "<IfModule mod_authz_core.c>\n";
        $htaccess_content .= "    Require all denied\n";
        $htaccess_content .= "</IfModule>\n\n";
        $htaccess_content .= "# Prevent directory listing\n";
        $htaccess_content .= "Options -Indexes\n\n";
        $htaccess_content .= "# Prevent PHP execution (if PHP files somehow get uploaded)\n";
        $htaccess_content .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
        $htaccess_content .= "    <IfModule mod_authz_host.c>\n";
        $htaccess_content .= "        Order allow,deny\n";
        $htaccess_content .= "        Deny from all\n";
        $htaccess_content .= "    </IfModule>\n";
        $htaccess_content .= "    <IfModule mod_authz_core.c>\n";
        $htaccess_content .= "        Require all denied\n";
        $htaccess_content .= "    </IfModule>\n";
        $htaccess_content .= "</FilesMatch>\n";
        
        // Only update if file doesn't exist or content is different (to preserve manual edits if needed)
        $needs_update = true;
        if (file_exists($htaccess_file)) {
            $current_content = @file_get_contents($htaccess_file);
            // Check if it's our security file (contains our marker)
            if ($current_content && strpos($current_content, 'Job Application Form - Secure File Protection') !== false) {
                // File exists and is our security file - update it to ensure latest security rules
                $needs_update = true;
            } else {
                // File exists but might be custom - don't overwrite
                $needs_update = false;
            }
        }
        
        if ($needs_update) {
            @file_put_contents($htaccess_file, $htaccess_content);
            @chmod($htaccess_file, 0644);
        }
    }
    
    public function handle_upload($unique_id) {
        if (!isset($_FILES['resume'])) {
            return array(
                'success' => false,
                'message' => 'No file was uploaded.'
            );
        }
        
        if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $error_messages = array(
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
            );
            
            $error_message = isset($error_messages[$_FILES['resume']['error']]) 
                ? $error_messages[$_FILES['resume']['error']] 
                : 'File upload error. Please try again.';
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        $file = $_FILES['resume'];
        
        // Check file size
        $max_size_mb = $this->max_file_size / 1048576;
        if ($file['size'] > $this->max_file_size) {
            return array(
                'success' => false,
                'message' => sprintf('File size exceeds the maximum limit of %dMB.', $max_size_mb)
            );
        }
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($file_ext, $this->allowed_types)) {
            $allowed_types_display = strtoupper(implode(', ', $this->allowed_types));
            return array(
                'success' => false,
                'message' => sprintf('Invalid file type. Allowed types: %s.', $allowed_types_display)
            );
        }
        
        // Check for malicious content
        $virus_check = $this->check_file_security($file);
        if (!$virus_check['safe']) {
            return array(
                'success' => false,
                'message' => $virus_check['message']
            );
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $jaf_dir = $upload_dir['basedir'] . '/job-applications';
        
        if (!file_exists($jaf_dir)) {
            // Create directory with proper permissions
            wp_mkdir_p($jaf_dir);
            
            // Set directory permissions (755 = owner read/write/execute, group/others read/execute)
            @chmod($jaf_dir, 0755);
        }
        
        // Ensure .htaccess exists for protection
        self::create_htaccess_file($jaf_dir);
        
        // Generate secure filename
        $filename = sanitize_file_name($unique_id . '_' . time() . '.' . $file_ext);
        $file_path = $jaf_dir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            return array(
                'success' => false,
                'message' => 'Failed to save the file. Please try again.'
            );
        }
        
        // Set proper file permissions (0640 = owner read/write, group read, others no access)
        // More secure than 0644, but still allows web server to read if needed
        @chmod($file_path, 0640);
        
        // Store filename for database (we'll use secure download handler for access)
        // Store both filename and full path for backward compatibility
        return array(
            'success' => true,
            'file_path' => $filename, // Store filename for database
            'file_name' => $filename,
            'full_path' => $file_path, // Full server path
            'relative_path' => 'job-applications/' . $filename // Relative path from uploads dir
        );
    }
    
    private function check_file_security($file) {
        // Check 1: File extension validation (already done, but double-check)
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerous_extensions = array('exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'php3', 'php4', 'php5', 'phtml');
        
        if (in_array($file_ext, $dangerous_extensions)) {
            return array(
                'safe' => false,
                'message' => 'File type is not allowed for security reasons.'
            );
        }
        
        // Check 2: MIME type validation
        // Get allowed MIME types based on allowed file extensions from settings
        $allowed_mimes = $this->get_allowed_mime_types();
        
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_mimes)) {
                return array(
                    'safe' => false,
                    'message' => 'File MIME type validation failed. The file may be corrupted or malicious. Detected MIME type: ' . $mime_type
                );
            }
        } elseif (function_exists('mime_content_type')) {
            // Fallback to mime_content_type if finfo is not available
            $mime_type = mime_content_type($file['tmp_name']);
            
            if (!in_array($mime_type, $allowed_mimes)) {
                return array(
                    'safe' => false,
                    'message' => 'File MIME type validation failed. The file may be corrupted or malicious. Detected MIME type: ' . $mime_type
                );
            }
        }
        
        // Check 3: File content scanning for suspicious patterns
        // Only scan text-based files, skip binary files like images and PDFs
        $text_based_extensions = array('txt', 'rtf', 'doc', 'docx');
        if (in_array($file_ext, $text_based_extensions)) {
            $content = @file_get_contents($file['tmp_name'], false, null, 0, 1024); // Read first 1KB
            
            if ($content !== false) {
                // Check for PHP tags
                if (preg_match('/<\?php|<\?=/i', $content)) {
                    return array(
                        'safe' => false,
                        'message' => 'File contains potentially malicious code.'
                    );
                }
                
                // Check for script tags
                if (preg_match('/<script|javascript:|onerror=|onload=/i', $content)) {
                    return array(
                        'safe' => false,
                        'message' => 'File contains potentially malicious scripts.'
                    );
                }
            }
        }
        
        // For image files, verify they are valid image files
        $image_extensions = array('png', 'jpg', 'jpeg', 'gif', 'webp');
        if (in_array($file_ext, $image_extensions)) {
            // Verify it's a valid image using getimagesize
            $image_info = @getimagesize($file['tmp_name']);
            if ($image_info === false) {
                return array(
                    'safe' => false,
                    'message' => 'File is not a valid image file.'
                );
            }
        }
        
        // Check 4: File size consistency
        if (filesize($file['tmp_name']) !== $file['size']) {
            return array(
                'safe' => false,
                'message' => 'File size mismatch detected.'
            );
        }
        
        // Check 5: If ClamAV is available, use it for virus scanning
        if (function_exists('shell_exec') && $this->is_clamav_available()) {
            $clamav_result = $this->scan_with_clamav($file['tmp_name']);
            if (!$clamav_result['safe']) {
                return $clamav_result;
            }
        }
        
        return array('safe' => true);
    }
    
    private function is_clamav_available() {
        $output = @shell_exec('which clamscan 2>&1');
        return !empty($output);
    }
    
    private function scan_with_clamav($file_path) {
        $command = 'clamscan --no-summary --infected ' . escapeshellarg($file_path) . ' 2>&1';
        $output = shell_exec($command);
        
        if (strpos($output, 'FOUND') !== false || strpos($output, 'Infected') !== false) {
            return array(
                'safe' => false,
                'message' => 'Virus detected in the uploaded file. Please upload a clean file.'
            );
        }
        
        return array('safe' => true);
    }
}

