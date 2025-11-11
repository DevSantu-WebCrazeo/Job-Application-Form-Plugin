<?php
/**
 * Form template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>


<div class="jaf-form-container">
    <h2 class="jaf-form-title">Apply Now!</h2>
    
    <form id="jaf-application-form" class="jaf-form" enctype="multipart/form-data">
        <?php wp_nonce_field('jaf_nonce', 'jaf_nonce_field'); ?>
        
        <input type="hidden" name="page_title" id="jaf-page-title" value="<?php echo esc_attr(get_the_title()); ?>">
        <input type="hidden" name="page_url" id="jaf-page-url" value="<?php echo esc_url(get_permalink()); ?>">
        
        <div class="jaf-form-group">
            <input 
                type="text" 
                name="name" 
                id="jaf-name" 
                class="jaf-input" 
                placeholder="Enter Your Name" 
                required
            >
        </div>
        
        <div class="jaf-form-row">
            <div class="jaf-form-group jaf-form-group-half">
                <input 
                    type="tel" 
                    name="phone" 
                    id="jaf-phone" 
                    class="jaf-input" 
                    placeholder="Phone Number" 
                    required
                >
            </div>
            
            <div class="jaf-form-group jaf-form-group-half">
                <input 
                    type="email" 
                    name="email" 
                    id="jaf-email" 
                    class="jaf-input" 
                    placeholder="Email Address" 
                    required
                >
            </div>
        </div>
        
        <div class="jaf-form-group">
            <select 
                name="position" 
                id="jaf-position" 
                class="jaf-select" 
                required
            >
                <option value="">Select Your Position</option>
                <option value="Software Developer">Software Developer</option>
                <option value="Web Designer">Web Designer</option>
                <option value="Marketing Manager">Marketing Manager</option>
                <option value="Sales Executive">Sales Executive</option>
                <option value="HR Manager">HR Manager</option>
                <option value="Project Manager">Project Manager</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="jaf-form-group">
            <textarea 
                name="message" 
                id="jaf-message" 
                class="jaf-textarea" 
                placeholder="Message" 
                rows="5"
            ></textarea>
        </div>
        
        <div class="jaf-form-group">
            <div class="jaf-file-upload-wrapper">
                <button type="button" class="jaf-file-button" id="jaf-file-button">Choose File</button>
                <span class="jaf-file-label">Upload Resume</span>
            </div>
            <?php
            // Get allowed file types from settings and format for accept attribute
            $allowed_types_str = get_option('jaf_allowed_file_types', 'pdf,doc,docx,txt,rtf');
            $allowed_types = array_map('trim', explode(',', strtolower($allowed_types_str)));
            $accept_attr = implode(',', array_map(function($ext) { return '.' . $ext; }, $allowed_types));
            ?>
            <input 
                type="file" 
                name="resume" 
                id="jaf-resume" 
                class="jaf-file-input" 
                accept="<?php echo esc_attr($accept_attr); ?>"
            >
            <div id="jaf-file-list" class="jaf-file-list"></div>
        </div>
        
        <div class="jaf-form-group text-center">
            <button type="submit" class="jaf-submit-button">submit</button>
        </div>
        
        <div id="jaf-message-container" class="jaf-message-container"></div>
    </form>
    
</div>

